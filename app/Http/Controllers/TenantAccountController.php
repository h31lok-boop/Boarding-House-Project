<?php

namespace App\Http\Controllers;

use App\Http\Requests\TenantAccountUpdateRequest;
use App\Models\BoardingHouse;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TenantAccountController extends Controller
{
    public function show(Request $request): View
    {
        $tenant = $request->user();
        abort_unless($tenant && $tenant->isTenant(), 403);

        return view('tenant.account', [
            'tenant' => $tenant,
            'tenantStatus' => $tenant->is_active ? 'Approved' : 'Pending',
            'notificationPreferences' => $this->buildNotificationPreferences($tenant),
            'bookingDetails' => $this->buildBookingDetails($tenant),
            'billingHistory' => $this->buildBillingHistory($tenant),
        ]);
    }

    public function update(TenantAccountUpdateRequest $request): RedirectResponse
    {
        $tenant = $request->user();
        abort_unless($tenant && $tenant->isTenant(), 403);

        $validated = $request->validated();

        $tenant->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ]);

        if ($tenant->isDirty('email')) {
            $tenant->email_verified_at = null;
        }

        if (Schema::hasColumn('users', 'notify_payment_reminders')) {
            $tenant->notify_payment_reminders = $request->boolean('notify_payment_reminders');
        }

        if (Schema::hasColumn('users', 'notify_booking_updates')) {
            $tenant->notify_booking_updates = $request->boolean('notify_booking_updates');
        }

        if (Schema::hasColumn('users', 'notify_ticket_updates')) {
            $tenant->notify_ticket_updates = $request->boolean('notify_ticket_updates');
        }

        if ($request->boolean('profile_image_remove') && $tenant->profile_image) {
            Storage::disk('public')->delete($tenant->profile_image);
            $tenant->profile_image = null;
        }

        if ($request->hasFile('profile_image')) {
            if ($tenant->profile_image) {
                Storage::disk('public')->delete($tenant->profile_image);
            }

            $tenant->profile_image = $request->file('profile_image')->store('profile-images', 'public');
        }

        $tenant->save();

        return redirect()
            ->route('tenant.account')
            ->with('status', 'tenant-account-updated');
    }

    private function buildNotificationPreferences($tenant): array
    {
        return [
            'payment_reminders' => Schema::hasColumn('users', 'notify_payment_reminders')
                ? (bool) ($tenant->notify_payment_reminders ?? true)
                : true,
            'booking_updates' => Schema::hasColumn('users', 'notify_booking_updates')
                ? (bool) ($tenant->notify_booking_updates ?? true)
                : true,
            'ticket_updates' => Schema::hasColumn('users', 'notify_ticket_updates')
                ? (bool) ($tenant->notify_ticket_updates ?? true)
                : true,
        ];
    }

    private function buildBookingDetails($tenant): array
    {
        $details = [
            'boarding_house' => trim((string) ($tenant->institution_name ?? '')) ?: 'Not assigned',
            'room_number' => trim((string) ($tenant->room_number ?? '')) ?: 'Not assigned',
            'address' => '',
            'booking_reference' => null,
            'status' => $tenant->is_active ? 'Approved' : 'Pending',
            'move_in_date' => $tenant->move_in_date ? Carbon::parse($tenant->move_in_date)->format('M d, Y') : null,
        ];

        if ($details['boarding_house'] !== 'Not assigned') {
            $boardingHouse = BoardingHouse::query()
                ->whereRaw('LOWER(name) = ?', [strtolower($details['boarding_house'])])
                ->first(['name', 'address']);

            if ($boardingHouse) {
                $details['boarding_house'] = $boardingHouse->name;
                $details['address'] = trim((string) ($boardingHouse->address ?? ''));
            }
        }

        if (! Schema::hasTable('bookings')) {
            return $details;
        }

        $columns = Schema::getColumnListing('bookings');
        $tenantIdColumn = $this->firstAvailableColumn($columns, ['tenant_id', 'user_id']);
        $tenantEmailColumn = $this->firstAvailableColumn($columns, ['tenant_email', 'email']);
        $tenantNameColumn = $this->firstAvailableColumn($columns, ['tenant_name', 'name']);

        $query = DB::table('bookings as booking_item');
        if ($tenantIdColumn) {
            $query->where('booking_item.'.$tenantIdColumn, $tenant->id);
        } elseif ($tenantEmailColumn && $tenant->email) {
            $query->where('booking_item.'.$tenantEmailColumn, $tenant->email);
        } elseif ($tenantNameColumn && $tenant->name) {
            $query->where('booking_item.'.$tenantNameColumn, $tenant->name);
        } else {
            return $details;
        }

        $houseIdColumn = $this->firstAvailableColumn($columns, ['boarding_house_id']);
        if ($houseIdColumn && Schema::hasTable('boarding_houses')) {
            $query->leftJoin('boarding_houses as booking_house', 'booking_item.'.$houseIdColumn, '=', 'booking_house.id');
        }

        $roomIdColumn = $this->firstAvailableColumn($columns, ['room_id']);
        if ($roomIdColumn && Schema::hasTable('rooms')) {
            $query->leftJoin('rooms as booking_room', 'booking_item.'.$roomIdColumn, '=', 'booking_room.id');
        }

        $referenceColumn = $this->firstAvailableColumn($columns, ['booking_reference', 'reference_no', 'reference', 'booking_id']);
        $statusColumn = $this->firstAvailableColumn($columns, ['status']);
        $roomNumberColumn = $this->firstAvailableColumn($columns, ['room_number', 'room_no']);
        $moveInColumn = $this->firstAvailableColumn($columns, ['move_in_date', 'start_date', 'check_in_date']);

        $select = ['booking_item.id as booking_id'];
        if ($referenceColumn) {
            $select[] = 'booking_item.'.$referenceColumn.' as booking_reference';
        }
        if ($statusColumn) {
            $select[] = 'booking_item.'.$statusColumn.' as booking_status';
        }
        if ($roomNumberColumn) {
            $select[] = 'booking_item.'.$roomNumberColumn.' as booking_room_no';
        }
        if ($moveInColumn) {
            $select[] = 'booking_item.'.$moveInColumn.' as booking_move_in';
        }
        if ($houseIdColumn && Schema::hasTable('boarding_houses')) {
            if (Schema::hasColumn('boarding_houses', 'name')) {
                $select[] = 'booking_house.name as boarding_house_name';
            }
            if (Schema::hasColumn('boarding_houses', 'address')) {
                $select[] = 'booking_house.address as boarding_house_address';
            }
        }
        if ($roomIdColumn && Schema::hasTable('rooms') && Schema::hasColumn('rooms', 'room_no')) {
            $select[] = 'booking_room.room_no as joined_room_no';
        }

        $orderColumn = $this->firstAvailableColumn($columns, ['updated_at', 'created_at', 'id']) ?? 'id';
        if ($orderColumn === 'id') {
            $query->orderByDesc('booking_item.id');
        } else {
            $query->orderByDesc('booking_item.'.$orderColumn);
        }

        $booking = $query->first($select);
        if (! $booking) {
            return $details;
        }

        $houseName = trim((string) ($booking->boarding_house_name ?? ''));
        if ($houseName !== '') {
            $details['boarding_house'] = $houseName;
        }

        $roomNo = trim((string) ($booking->booking_room_no ?? $booking->joined_room_no ?? ''));
        if ($roomNo !== '') {
            $details['room_number'] = $roomNo;
        }

        $address = trim((string) ($booking->boarding_house_address ?? ''));
        if ($address !== '') {
            $details['address'] = $address;
        }

        $bookingStatus = trim((string) ($booking->booking_status ?? ''));
        if ($bookingStatus !== '') {
            $details['status'] = ucfirst(strtolower($bookingStatus));
        }

        $reference = trim((string) ($booking->booking_reference ?? ''));
        if ($reference !== '') {
            $details['booking_reference'] = $reference;
        } elseif (! empty($booking->booking_id)) {
            $details['booking_reference'] = '#'.(int) $booking->booking_id;
        }

        if (! empty($booking->booking_move_in)) {
            $details['move_in_date'] = Carbon::parse($booking->booking_move_in)->format('M d, Y');
        }

        return $details;
    }

    private function buildBillingHistory($tenant): array
    {
        $result = [
            'enabled' => false,
            'total_paid' => 0.0,
            'outstanding' => 0.0,
            'items' => [],
        ];

        if (! Schema::hasTable('payments')) {
            return $result;
        }

        $columns = Schema::getColumnListing('payments');
        $tenantIdColumn = $this->firstAvailableColumn($columns, ['tenant_id', 'user_id']);
        $tenantEmailColumn = $this->firstAvailableColumn($columns, ['tenant_email', 'email']);
        $tenantNameColumn = $this->firstAvailableColumn($columns, ['tenant_name', 'name']);

        $query = DB::table('payments as payment_item');
        if ($tenantIdColumn) {
            $query->where('payment_item.'.$tenantIdColumn, $tenant->id);
        } elseif ($tenantEmailColumn && $tenant->email) {
            $query->where('payment_item.'.$tenantEmailColumn, $tenant->email);
        } elseif ($tenantNameColumn && $tenant->name) {
            $query->where('payment_item.'.$tenantNameColumn, $tenant->name);
        } else {
            return $result;
        }

        $referenceColumn = $this->firstAvailableColumn($columns, ['payment_reference', 'reference_no', 'reference', 'transaction_id', 'invoice_no']);
        $statusColumn = $this->firstAvailableColumn($columns, ['status']);
        $amountColumn = $this->firstAvailableColumn($columns, ['amount']);
        $dueDateColumn = $this->firstAvailableColumn($columns, ['due_date', 'payment_date', 'created_at']);
        $paidDateColumn = $this->firstAvailableColumn($columns, ['paid_at', 'payment_date']);
        $receiptColumn = $this->firstAvailableColumn($columns, ['receipt_url', 'receipt_path', 'receipt', 'proof_of_payment', 'proof_image', 'attachment']);

        $select = ['payment_item.id as payment_id'];
        if ($referenceColumn) {
            $select[] = 'payment_item.'.$referenceColumn.' as payment_reference';
        }
        if ($statusColumn) {
            $select[] = 'payment_item.'.$statusColumn.' as payment_status';
        }
        if ($amountColumn) {
            $select[] = 'payment_item.'.$amountColumn.' as payment_amount';
        }
        if ($dueDateColumn) {
            $select[] = 'payment_item.'.$dueDateColumn.' as payment_due_date';
        }
        if ($paidDateColumn) {
            $select[] = 'payment_item.'.$paidDateColumn.' as payment_paid_date';
        }
        if ($receiptColumn) {
            $select[] = 'payment_item.'.$receiptColumn.' as payment_receipt';
        }

        $orderColumn = $this->firstAvailableColumn($columns, ['due_date', 'updated_at', 'created_at', 'id']) ?? 'id';
        if ($orderColumn === 'id') {
            $query->orderByDesc('payment_item.id');
        } else {
            $query->orderByDesc('payment_item.'.$orderColumn);
        }

        $rows = $query->limit(30)->get($select);
        if ($rows->isEmpty()) {
            return $result;
        }

        $result['enabled'] = true;
        $paidStatuses = ['paid', 'completed', 'settled'];
        $excludedStatuses = ['cancelled', 'canceled', 'void'];

        foreach ($rows as $row) {
            $status = strtolower(trim((string) ($row->payment_status ?? '')));
            $amount = (float) ($row->payment_amount ?? 0);

            if (in_array($status, $paidStatuses, true)) {
                $result['total_paid'] += $amount;
            } elseif (! in_array($status, $excludedStatuses, true)) {
                $result['outstanding'] += $amount;
            }

            $result['items'][] = [
                'id' => (int) ($row->payment_id ?? 0),
                'reference' => trim((string) ($row->payment_reference ?? '')) ?: '#'.(int) ($row->payment_id ?? 0),
                'amount' => $amount,
                'status' => $status !== '' ? ucfirst($status) : 'Pending',
                'due_date' => $this->formatDate($row->payment_due_date ?? null),
                'paid_date' => $this->formatDate($row->payment_paid_date ?? null),
                'receipt_url' => $this->resolveReceiptUrl($row->payment_receipt ?? null),
            ];
        }

        return $result;
    }

    private function firstAvailableColumn(array $columns, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (in_array($candidate, $columns, true)) {
                return $candidate;
            }
        }

        return null;
    }

    private function formatDate(mixed $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        return Carbon::parse($value)->format('M d, Y');
    }

    private function resolveReceiptUrl(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $path = trim($value);
        if (preg_match('/^https?:\/\//i', $path) === 1 || str_starts_with($path, '/')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
