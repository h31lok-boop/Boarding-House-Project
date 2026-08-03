<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class PaymentReceiptVerificationController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->isSuperAdmin() || $request->user()?->isStrictOwner(), 403);

        $receipts = PaymentReceipt::with(['user', 'payment.boardingHouse', 'booking.boardingHouse', 'booking.room.boardingHouse'])
            ->when($request->user()?->isStrictOwner(), function ($query) use ($request) {
                $query->where(function ($ownerQuery) use ($request) {
                    $ownerQuery
                        ->whereHas('booking.room.boardingHouse', fn ($house) => $house->where('owner_id', $request->user()->id))
                        ->orWhereHas('booking.boardingHouse', fn ($house) => $house->where('owner_id', $request->user()->id))
                        ->orWhereHas('payment.boardingHouse', fn ($house) => $house->where('owner_id', $request->user()->id));
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.payment-verification', compact('receipts'));
    }

    public function approve(Request $request, PaymentReceipt $receipt)
    {
        $this->authorizeReview($request, $receipt);

        $receipt->forceFill([
            'status' => PaymentReceipt::STATUS_APPROVED,
            'rejection_reason' => null,
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
        ])->save();

        $this->settleRelatedPayment($receipt, $request->user()->id);

        $this->notifyStudent($receipt, 'Your payment has been approved.', 'Your payment proof has been approved.');

        return back()->with('success', 'Payment receipt approved.');
    }

    public function reject(Request $request, PaymentReceipt $receipt)
    {
        $this->authorizeReview($request, $receipt);

        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $receipt->forceFill([
            'status' => PaymentReceipt::STATUS_REJECTED,
            'rejection_reason' => $data['rejection_reason'],
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
        ])->save();

        $this->notifyStudent($receipt, 'Your payment has been rejected.', $data['rejection_reason']);

        return back()->with('success', 'Payment receipt rejected.');
    }

    private function authorizeReview(Request $request, PaymentReceipt $receipt): void
    {
        if ($request->user()?->isSuperAdmin()) {
            return;
        }

        $receipt->loadMissing('payment.boardingHouse', 'booking.boardingHouse', 'booking.room.boardingHouse');
        abort_unless(
            $request->user()?->isStrictOwner()
            && (int) ($receipt->booking?->room?->boardingHouse?->owner_id
                ?? $receipt->booking?->boardingHouse?->owner_id
                ?? $receipt->payment?->boardingHouse?->owner_id) === (int) $request->user()->id,
            403
        );
    }

    private function settleRelatedPayment(PaymentReceipt $receipt, int $reviewedBy): void
    {
        if (! Schema::hasTable('bookings')) {
            return;
        }

        $receipt->loadMissing('booking.reservation', 'booking.boardingHouse', 'payment');
        $reservation = $receipt->booking?->reservation;

        if ($reservation) {
            $reservation->forceFill([
                'payment_status' => 'paid',
                'payment_method' => 'gcash',
                'payment_reference' => $receipt->reference_number,
                'approved_at' => $reservation->approved_at ?: now(),
                'notes' => trim(($reservation->notes ? $reservation->notes."\n" : '').'GCash payment verified on '.now()->format('M d, Y h:i A').'.'),
            ])->save();
        }

        if ($receipt->booking) {
            $receipt->booking->forceFill([
                'payment_status' => 'paid',
                'status' => 'Confirmed',
            ])->save();
        }

        if (! Schema::hasTable('payments') || $receipt->payment_id) {
            return;
        }

        $houseId = $reservation?->boarding_house_id ?? $receipt->booking?->boarding_house_id;
        if (! $houseId) {
            return;
        }

        $tenant = $houseId
            ? Tenant::query()->where('user_id', $receipt->user_id)->where('boarding_house_id', $houseId)->first()
            : Tenant::query()->where('user_id', $receipt->user_id)->latest('id')->first();

        if (! $tenant) {
            return;
        }

        $payment = Payment::create([
            'tenant_id' => $tenant->id,
            'boarding_house_id' => $houseId,
            'amount' => $receipt->amount,
            'due_date' => $reservation?->check_in_date ?: today(),
            'paid_at' => now(),
            'status' => 'paid',
            'payment_method' => 'gcash',
            'payment_type' => 'reservation',
            'reference_no' => $receipt->reference_number,
            'reference_number' => $receipt->reference_number,
            'notes' => 'GCash receipt verified by owner/admin.',
        ]);

        $receiptNumber = $receipt->receipt_number ?: 'RCT-RCP-'.now()->format('Y').'-'.str_pad((string) $receipt->id, 6, '0', STR_PAD_LEFT);
        $receipt->forceFill([
            'payment_id' => $payment->id,
            'receipt_number' => $receiptNumber,
        ])->save();
        $payment->forceFill(['receipt_number' => $receiptNumber])->save();
    }

    private function notifyStudent(PaymentReceipt $receipt, string $title, string $message): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        DB::table('notifications')->updateOrInsert(
            [
                'user_id' => $receipt->user_id,
                'type' => 'payments',
                'reference_id' => 'payment-receipt-review-'.$receipt->id.'-'.$receipt->status,
            ],
            [
                'title' => $title,
                'message' => $message,
                'data' => json_encode(['payment_receipt_id' => $receipt->id]),
                'is_read' => false,
                'read_at' => null,
                'updated_at' => now(),
            ],
        );
    }
}
