<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentReceiptStoreRequest;
use App\Models\Booking;
use App\Models\PaymentReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentReceiptController extends Controller
{
    public function store(PaymentReceiptStoreRequest $request)
    {
        $user = $request->user();
        $file = $request->file('receipt');
        $path = $file?->store('payment-receipts', 'public');
        $bookingId = $request->input('booking_id') ?: $this->latestBookingIdFor($user->id);

        $receipt = PaymentReceipt::create([
            'user_id' => $user->id,
            'booking_id' => $bookingId,
            'payment_method' => $request->input('payment_method'),
            'amount' => $request->input('amount'),
            'reference_number' => $request->input('reference_number'),
            'transaction_id' => $request->input('transaction_id'),
            'payment_date' => $request->input('payment_date'),
            'receipt_path' => $path,
            'original_filename' => $file?->getClientOriginalName(),
            'mime_type' => $file?->getMimeType(),
            'file_size' => $file?->getSize(),
            'notes' => $request->input('notes'),
            'status' => PaymentReceipt::STATUS_PENDING_REVIEW,
        ]);

        $this->notifyAdmins($receipt);

        return redirect()
            ->route('user.payments.index')
            ->with('success', 'Payment proof submitted successfully.');
    }

    public function show(Request $request, PaymentReceipt $receipt)
    {
        $this->authorizeReceiptAccess($request, $receipt);

        abort_unless($receipt->receipt_path, 404);
        abort_unless(Storage::disk('public')->exists($receipt->receipt_path), 404);

        return response()->file(Storage::disk('public')->path($receipt->receipt_path));
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

    public function destroy(Request $request, PaymentReceipt $receipt)
    {
        abort_unless((int) $receipt->user_id === (int) $request->user()->id, 403);

        if ($receipt->status !== PaymentReceipt::STATUS_PENDING_REVIEW) {
            return back()->with('error', 'Only receipts pending review can be deleted.');
        }

        if ($receipt->receipt_path) {
            Storage::disk('public')->delete($receipt->receipt_path);
        }

        $receipt->delete();

        return back()->with('success', 'Payment receipt deleted.');
    }

    private function latestBookingIdFor(int $userId): ?int
    {
        if (! Schema::hasTable('bookings') || ! Schema::hasColumn('bookings', 'user_id')) {
            return null;
        }

        return Booking::where('user_id', $userId)->latest()->value('id');
    }

    private function authorizeReceiptAccess(Request $request, PaymentReceipt $receipt): void
    {
        $user = $request->user();

        abort_unless($user && ($user->isAdmin() || (int) $receipt->user_id === (int) $user->id), 403);
    }

    private function notifyAdmins(PaymentReceipt $receipt): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        $admins = DB::table('users')
            ->whereIn(DB::raw('LOWER(role)'), ['admin', 'owner'])
            ->get(['id']);

        foreach ($admins as $admin) {
            DB::table('notifications')->updateOrInsert(
                [
                    'user_id' => $admin->id,
                    'type' => 'payments',
                    'reference_id' => 'payment-receipt-'.$receipt->id,
                ],
                [
                    'title' => 'Payment proof submitted',
                    'message' => 'A student submitted a payment proof for verification.',
                    'data' => json_encode(['payment_receipt_id' => $receipt->id]),
                    'is_read' => false,
                    'read_at' => null,
                    'updated_at' => now(),
                ],
            );
        }
    }
}
