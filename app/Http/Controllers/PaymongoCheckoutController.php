<?php

namespace App\Http\Controllers;

use App\Models\OwnerProfile;
use App\Models\Payment;
use App\Models\PaymongoCheckout;
use App\Models\Reservation;
use App\Models\Tenant;
use App\Services\PaymongoPaymentService;
use App\Services\PaymongoService;
use App\Services\ReservationLifecycleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Throwable;

class PaymongoCheckoutController extends Controller
{
    public function __construct(
        private readonly PaymongoService $paymongo,
        private readonly PaymongoPaymentService $payments,
        private readonly ReservationLifecycleService $reservations,
    ) {}

    public function store(Request $request): RedirectResponse
    {
        $this->reservations->expireStaleReservations();
        $user = $request->user();
        abort_unless($user?->isUser(), 403);

        $validated = $request->validate([
            'payment_id' => ['required', 'integer', 'exists:payments,id'],
        ]);

        $tenant = Tenant::where('user_id', $user->id)->latest('id')->first();
        $paymentsHaveUserId = \Illuminate\Support\Facades\Schema::hasColumn('payments', 'user_id');
        abort_if(! $tenant && ! $paymentsHaveUserId, 404);
        $payment = Payment::query()
            ->with(['boardingHouse.owner.ownerProfile', 'boardingHouse.ownerProfile'])
            ->whereKey($validated['payment_id'])
            ->whereIn('status', ['pending', 'unpaid', 'overdue'])
            ->where(function ($query) use ($tenant, $user, $paymentsHaveUserId) {
                if ($tenant) {
                    $query->where('tenant_id', $tenant->id);
                }

                if ($paymentsHaveUserId) {
                    $method = $tenant ? 'orWhere' : 'where';
                    $query->{$method}('user_id', $user->id);
                }
            })
            ->firstOrFail();

        abort_if((float) $payment->amount <= 0, 422, 'The payment amount must be greater than zero.');

        $house = $payment->boardingHouse;
        abort_unless($house, 422, 'This bill is not connected to a boarding house.');
        $profile = $house->ownerProfile ?: $house->owner?->ownerProfile;
        $credentials = $this->paymongo->credentials($profile);
        if (! $credentials['enabled'] || blank($credentials['secret_key'])) {
            return back()->with('error', 'PayMongo is not fully configured for this property yet. Please contact the property owner.');
        }

        $reservation = Reservation::query()
            ->where('user_id', $user->id)
            ->where('boarding_house_id', $house->id)
            ->whereNotIn('status', ['cancelled', 'canceled', 'rejected', 'expired'])
            ->latest('id')
            ->first();

        if ($reservation && ! $this->reservations->canProcessPayment($reservation)) {
            return redirect()->route('user.reservations.index')
                ->with('error', 'This reservation has expired and can no longer be paid.');
        }

        $existing = PaymongoCheckout::query()
            ->where('user_id', $user->id)
            ->where('payment_id', $payment->id)
            ->where('status', 'pending')
            ->whereNotNull('checkout_url')
            ->latest('id')
            ->first();
        if ($existing) {
            return redirect()->away($existing->checkout_url);
        }

        $checkout = PaymongoCheckout::create([
            'user_id' => $user->id,
            'tenant_id' => $tenant?->id,
            'owner_id' => $profile?->user_id ?: $house->owner_id,
            'boarding_house_id' => $house->id,
            'reservation_id' => $reservation?->id,
            'payment_id' => $payment->id,
            'reference_number' => 'BM-PM-'.Str::upper(Str::random(20)),
            'amount' => $payment->amount,
            'currency' => 'PHP',
            'status' => 'pending',
        ]);

        try {
            $session = $this->paymongo->createCheckoutSession($credentials['secret_key'], [
                'line_items' => [[
                    'currency' => 'PHP',
                    'amount' => (int) round((float) $payment->amount * 100),
                    'name' => Str::limit(($payment->payment_type ?: 'Rent').' - '.$house->name, 120, ''),
                    'quantity' => 1,
                ]],
                'payment_method_types' => config('services.paymongo.payment_methods'),
                'success_url' => URL::temporarySignedRoute('user.paymongo.return', now()->addHours(24), ['checkout' => $checkout]),
                'cancel_url' => route('user.paymongo.cancel', $checkout),
                'description' => 'BoardMatch payment for '.$house->name,
                'reference_number' => $checkout->reference_number,
                'send_email_receipt' => true,
                'billing' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'metadata' => [
                    'boardmatch_checkout_id' => (string) $checkout->id,
                    'payment_id' => (string) $payment->id,
                    'user_id' => (string) $user->id,
                ],
            ]);

            $checkout->update([
                'checkout_session_id' => $session['id'],
                'checkout_url' => Arr::get($session, 'attributes.checkout_url'),
                'raw_payload' => $session,
            ]);

            return redirect()->away($checkout->checkout_url);
        } catch (Throwable $exception) {
            report($exception);
            $checkout->update(['status' => 'failed']);

            return back()->with('error', $exception->getMessage());
        }
    }

    public function legacyConfirm(Request $request): RedirectResponse
    {
        $this->reservations->expireStaleReservations();
        $user = $request->user();
        abort_unless($user?->isUser(), 403);

        $reservation = $this->reservations->relevantReservationForUser($user->id);
        if ($reservation && ! $this->reservations->canProcessPayment($reservation)) {
            return redirect()->route('user.reservations.index')
                ->with('error', 'This reservation has expired and can no longer be paid.');
        }

        return redirect()->route('user.payments.index')
            ->with('error', 'Direct payment confirmation has been disabled. Please use PayMongo secure checkout.');
    }

    public function returned(Request $request, PaymongoCheckout $checkout): RedirectResponse
    {
        abort_unless((int) $checkout->user_id === (int) $request->user()?->id, 403);
        if ($checkout->status === 'paid') {
            return $this->paidRedirect($checkout);
        }

        $profile = OwnerProfile::where('user_id', $checkout->owner_id)->first();
        $credentials = $this->paymongo->credentials($profile);

        try {
            $session = $this->paymongo->retrieveCheckoutSession($credentials['secret_key'], $checkout->checkout_session_id);
            if ($this->paymongo->isPaid($session)) {
                $checkout = $this->payments->settle($checkout, $session, $this->paymongo);

                return $this->paidRedirect($checkout);
            }
        } catch (Throwable $exception) {
            report($exception);
        }

        return redirect()->route('user.payments.index')
            ->with('success', 'PayMongo is still confirming your payment. This page will update after verification.');
    }

    public function cancel(Request $request, PaymongoCheckout $checkout): RedirectResponse
    {
        abort_unless((int) $checkout->user_id === (int) $request->user()?->id, 403);

        return redirect()->route('user.payments.index')->with('error', 'PayMongo checkout was cancelled. Your bill remains unpaid.');
    }

    public function webhook(Request $request): JsonResponse
    {
        $rawBody = $request->getContent();
        $payload = json_decode($rawBody, true);
        if (! is_array($payload)) {
            return response()->json(['message' => 'Invalid JSON.'], 400);
        }

        $eventResource = Arr::get($payload, 'data', []);
        $event = Arr::get($eventResource, 'attributes', []);
        $eventType = Arr::get($event, 'type') ?: Arr::get($eventResource, 'type');
        $session = Arr::get($event, 'data') ?: Arr::get($eventResource, 'data', []);
        if (! is_array($session)) {
            return response()->json(['message' => 'Invalid event data.'], 400);
        }
        $sessionId = Arr::get($session, 'id');
        $reference = Arr::get($session, 'attributes.reference_number');
        if (blank($sessionId) && blank($reference)) {
            return response()->json(['message' => 'Checkout reference missing.'], 400);
        }
        $checkout = PaymongoCheckout::query()
            ->when($sessionId, fn ($query) => $query->where('checkout_session_id', $sessionId))
            ->when(! $sessionId && $reference, fn ($query) => $query->where('reference_number', $reference))
            ->first();

        if (! $checkout) {
            return response()->json(['message' => 'Checkout not found.'], 404);
        }

        $profile = OwnerProfile::where('user_id', $checkout->owner_id)->first();
        $secret = $this->paymongo->credentials($profile)['webhook_secret'];
        $liveMode = (bool) (Arr::get($event, 'livemode') ?? Arr::get($eventResource, 'livemode') ?? Arr::get($session, 'attributes.livemode', false));
        if (blank($secret) || ! $this->paymongo->verifyWebhookSignature(
            $rawBody,
            (string) $request->header('Paymongo-Signature'),
            $secret,
            $liveMode,
        )) {
            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        if ($eventType === 'checkout_session.payment.paid') {
            try {
                $this->payments->settle($checkout, $session, $this->paymongo);
            } catch (Throwable $exception) {
                report($exception);

                return response()->json(['message' => 'Payment could not be settled.'], 422);
            }
        }

        return response()->json(['received' => true]);
    }

    private function paidRedirect(PaymongoCheckout $checkout): RedirectResponse
    {
        return redirect()->route('user.payments.index')
            ->with('payment_confirmed', true)
            ->with('payment_ref', $checkout->reference_number)
            ->with('payment_method_label', 'PayMongo')
            ->with('success', 'Payment verified successfully through PayMongo.');
    }
}
