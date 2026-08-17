<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PaymentReceipt;
use App\Services\ReservationLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentReceiptController extends Controller
{
    public function __construct(
        private readonly ReservationLifecycleService $reservationLifecycleService,
    ) {}

    public function show(Request $request, PaymentReceipt $receipt)
    {
        $this->authorizeReceiptAccess($request, $receipt);

        if (! $receipt->receipt_path) {
            return $this->print($request, $receipt);
        }

        abort_unless(Storage::disk('public')->exists($receipt->receipt_path), 404);

        return response()->file(Storage::disk('public')->path($receipt->receipt_path));
    }

    public function print(Request $request, PaymentReceipt $receipt)
    {
        $this->authorizeReceiptAccess($request, $receipt);
        abort_unless($receipt->status === PaymentReceipt::STATUS_APPROVED, 403, 'This receipt is not approved for printing yet.');
        $receipt->loadMissing('user', 'payment.boardingHouse', 'booking.boardingHouse', 'booking.room.boardingHouse');

        return view('user.payment-receipt', compact('receipt'));
    }

    public function download(Request $request, PaymentReceipt $receipt): StreamedResponse
    {
        $this->authorizeReceiptAccess($request, $receipt);

        abort_unless($receipt->receipt_path, 404);
        abort_unless(Storage::disk('public')->exists($receipt->receipt_path), 404);

        return Storage::disk('public')->download(
            $receipt->receipt_path,
            $receipt->original_filename ?: basename($receipt->receipt_path)
        );
    }

    private function authorizeReceiptAccess(Request $request, PaymentReceipt $receipt): void
    {
        $user = $request->user();
        $receipt->loadMissing('booking.room.boardingHouse', 'booking.boardingHouse', 'payment.boardingHouse');
        $ownerCanView = $user?->isStrictOwner()
            && (int) ($receipt->booking?->room?->boardingHouse?->owner_id
                ?? $receipt->booking?->boardingHouse?->owner_id
                ?? $receipt->payment?->boardingHouse?->owner_id) === (int) $user->id;

        abort_unless(
            $user && ($user->isSuperAdmin() || $ownerCanView || (int) $receipt->user_id === (int) $user->id),
            403
        );
    }

}
