<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Models\PaymongoCheckout;
use App\Models\UserNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class PaymongoPaymentService
{
    public function settle(PaymongoCheckout $checkout, array $session, PaymongoService $paymongo): PaymongoCheckout
    {
        $paidCentavos = $paymongo->paidAmountInCentavos($session);
        $expectedCentavos = (int) round((float) $checkout->amount * 100);
        if ($paidCentavos !== null && $paidCentavos !== $expectedCentavos) {
            throw new RuntimeException('The PayMongo payment amount does not match the billing record.');
        }

        return DB::transaction(function () use ($checkout, $session, $paymongo) {
            $locked = PaymongoCheckout::query()->lockForUpdate()->findOrFail($checkout->id);
            if ($locked->status === 'paid') {
                return $locked;
            }

            $payment = $locked->payment()->lockForUpdate()->firstOrFail();
            if (! in_array(strtolower((string) $payment->status), ['pending', 'unpaid', 'overdue'], true)) {
                throw new RuntimeException('This billing record is no longer payable.');
            }

            $paidAt = now();
            $reference = $locked->reference_number;
            $payment->forceFill([
                'status' => 'paid',
                'paid_at' => $paidAt,
                'payment_method' => 'paymongo',
                'reference_no' => $reference,
                'reference_number' => $reference,
            ])->save();

            if ($locked->reservation && Schema::hasColumn('reservations', 'payment_status')) {
                $reservation = $locked->reservation;
                $reservation->forceFill([
                    'payment_status' => 'paid',
                    'payment_method' => 'paymongo',
                    'payment_reference' => $reference,
                    'notes' => trim(($reservation->notes ? $reservation->notes."\n" : '').'PayMongo payment verified on '.$paidAt->format('M d, Y h:i A').'.'),
                ])->save();
            }

            $this->issueReceipt($locked, $payment, $paymongo->paymentId($session), $paidAt);
            $this->createNextBillingItem($payment);
            $this->notifyPaymentVerified($locked, $payment);

            $locked->forceFill([
                'status' => 'paid',
                'paymongo_payment_id' => $paymongo->paymentId($session),
                'paid_at' => $paidAt,
                'raw_payload' => $session,
            ])->save();

            return $locked->refresh();
        });
    }

    private function issueReceipt(PaymongoCheckout $checkout, Payment $payment, ?string $transactionId, $paidAt): void
    {
        if (! Schema::hasTable('payment_receipts') || PaymentReceipt::where('payment_id', $payment->id)->exists()) {
            return;
        }

        $bookingId = null;
        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'user_id')) {
            $bookingId = $checkout->reservation_id && Schema::hasColumn('bookings', 'reservation_id')
                ? Booking::where('user_id', $checkout->user_id)->where('reservation_id', $checkout->reservation_id)->value('id')
                : Booking::where('user_id', $checkout->user_id)->latest('id')->value('id');
        }

        $receiptNumber = 'RCT-PAYMONGO-'.$paidAt->format('Y').'-'.str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT);
        PaymentReceipt::create([
            'user_id' => $checkout->user_id,
            'booking_id' => $bookingId,
            'payment_id' => $payment->id,
            'payment_method' => 'PayMongo',
            'amount' => $payment->amount,
            'reference_number' => $checkout->reference_number,
            'transaction_id' => $transactionId,
            'receipt_number' => $receiptNumber,
            'payment_date' => $paidAt->toDateString(),
            'status' => PaymentReceipt::STATUS_APPROVED,
            'reviewed_at' => $paidAt,
            'notes' => 'Payment verified automatically through PayMongo.',
        ]);

        $payment->forceFill(['receipt_number' => $receiptNumber])->save();
    }

    private function createNextBillingItem(Payment $payment): void
    {
        if (! $payment->tenant_id || ! $payment->boarding_house_id || ! $payment->due_date) {
            return;
        }

        $nextDue = $payment->due_date->copy()->addMonth()->toDateString();
        if (Payment::where('tenant_id', $payment->tenant_id)->whereDate('due_date', $nextDue)->exists()) {
            return;
        }

        $nextPayment = new Payment;
        $nextPayment->forceFill([
            'tenant_id' => $payment->tenant_id,
            'boarding_house_id' => $payment->boarding_house_id,
            'amount' => $payment->amount,
            'due_date' => $nextDue,
            'status' => 'pending',
            'payment_type' => $payment->payment_type ?: 'rent',
            'reference_no' => 'BILL-'.strtoupper(str()->random(12)),
        ])->save();
    }

    private function notifyPaymentVerified(PaymongoCheckout $checkout, Payment $payment): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        UserNotification::updateOrCreate(
            [
                'user_id' => $checkout->user_id,
                'type' => 'payment',
                'reference_id' => 'paymongo:'.$checkout->id.':tenant',
            ],
            [
                'title' => 'PayMongo payment confirmed',
                'message' => 'Your payment of PHP '.number_format((float) $payment->amount, 2).' was verified. Reference: '.$checkout->reference_number.'.',
                'data' => ['payment_id' => $payment->id, 'checkout_id' => $checkout->id],
                'is_read' => false,
                'read_at' => null,
            ],
        );

        if ($checkout->owner_id) {
            UserNotification::updateOrCreate(
                [
                    'user_id' => $checkout->owner_id,
                    'type' => 'payment',
                    'reference_id' => 'paymongo:'.$checkout->id.':owner',
                ],
                [
                    'title' => 'Tenant payment received',
                    'message' => 'PayMongo verified PHP '.number_format((float) $payment->amount, 2).' for '.$checkout->boardingHouse?->name.'.',
                    'data' => ['payment_id' => $payment->id, 'checkout_id' => $checkout->id],
                    'is_read' => false,
                    'read_at' => null,
                ],
            );
        }
    }
}
