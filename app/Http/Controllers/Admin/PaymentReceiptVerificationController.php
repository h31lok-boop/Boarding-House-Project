<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class PaymentReceiptVerificationController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $receipts = PaymentReceipt::with(['user', 'booking.room.boardingHouse'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.payment-verification', compact('receipts'));
    }

    public function approve(Request $request, PaymentReceipt $receipt)
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $receipt->forceFill([
            'status' => PaymentReceipt::STATUS_APPROVED,
            'rejection_reason' => null,
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
        ])->save();

        $this->notifyStudent($receipt, 'Your payment has been approved.', 'Your payment proof has been approved.');

        return back()->with('success', 'Payment receipt approved.');
    }

    public function reject(Request $request, PaymentReceipt $receipt)
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

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
