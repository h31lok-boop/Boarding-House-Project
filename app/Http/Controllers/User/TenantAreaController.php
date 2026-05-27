<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BoardingHouse;
use App\Models\Inquiry;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class TenantAreaController extends Controller
{
    public function reservations(Request $request)
    {
        $tenant = $this->tenant($request);

        $reservations = Reservation::with(['boardingHouse', 'room'])
            ->where('user_id', $tenant->id)
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('user.reservations', compact('reservations'));
    }

    public function cancelReservation(Request $request, Reservation $reservation)
    {
        $tenant = $this->tenant($request);
        abort_unless((int) $reservation->user_id === (int) $tenant->id, 403);

        if (in_array(strtolower((string) $reservation->status), ['confirmed', 'cancelled'], true)) {
            return back()->with('error', 'This reservation can no longer be cancelled from your account.');
        }

        $reservation->update([
            'status' => 'cancelled',
            'notes' => trim(($reservation->notes ? $reservation->notes."\n" : '').'Cancelled by tenant on '.now()->format('M d, Y h:i A')),
        ]);

        return back()->with('success', 'Reservation cancelled.');
    }

    public function payments(Request $request)
    {
        $tenant = $this->tenant($request);
        $hasTenantPayments = Schema::hasTable('tenants') && Schema::hasColumn('payments', 'tenant_id');
        $hasUserPayments = Schema::hasColumn('payments', 'user_id');

        $payments = Payment::with(['tenant.user', 'boardingHouse'])
            ->where(function ($query) use ($hasTenantPayments, $hasUserPayments, $tenant) {
                if ($hasTenantPayments) {
                    $query->whereHas('tenant', fn ($tenantQuery) => $tenantQuery->where('user_id', $tenant->id));
                }

                if ($hasUserPayments) {
                    $query->orWhere('user_id', $tenant->id);
                }

                if (! $hasTenantPayments && ! $hasUserPayments) {
                    $query->whereRaw('1 = 0');
                }
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('user.payments', compact('payments'));
    }

    public function messages(Request $request)
    {
        $tenant = $this->tenant($request);

        $messages = Inquiry::with('boardingHouse')
            ->where('user_id', $tenant->id)
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->query('q').'%';
                $query->where(function ($search) use ($term) {
                    $search->where('message', 'like', $term)
                        ->orWhereHas('boardingHouse', fn ($house) => $house->where('name', 'like', $term));
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $houses = $this->approvedHouses();

        return view('user.messages', compact('messages', 'houses'));
    }

    public function storeMessage(Request $request)
    {
        $tenant = $this->tenant($request);

        $data = $request->validate([
            'boarding_house_id' => ['required', 'exists:boarding_houses,id'],
            'message' => ['required', 'string', 'max:1200'],
        ]);

        $message = [
            'user_id' => $tenant->id,
            'boarding_house_id' => $data['boarding_house_id'],
            'message' => $data['message'],
            'status' => 'pending',
        ];

        if (Schema::hasColumn('inquiries', 'priority')) {
            $message['priority'] = 'normal';
        }

        Inquiry::create($message);

        return back()->with('success', 'Message sent to the owner.');
    }

    public function reviews(Request $request)
    {
        $tenant = $this->tenant($request);

        $reviews = Review::with('boardingHouse')
            ->where('user_id', $tenant->id)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $houses = $this->approvedHouses();

        return view('user.reviews', compact('reviews', 'houses'));
    }

    public function storeReview(Request $request)
    {
        $tenant = $this->tenant($request);

        $data = $request->validate([
            'boarding_house_id' => ['required', 'exists:boarding_houses,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1200'],
        ]);

        $review = [
            'user_id' => $tenant->id,
            'boarding_house_id' => $data['boarding_house_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ];

        if (Schema::hasColumn('reviews', 'overall_rating')) {
            $review['overall_rating'] = $data['rating'];
        }

        if (Schema::hasColumn('reviews', 'status')) {
            $review['status'] = 'pending';
        }

        Review::create($review);

        return back()->with('success', 'Review submitted.');
    }

    public function updateReview(Request $request, Review $review)
    {
        $tenant = $this->tenant($request);
        abort_unless((int) $review->user_id === (int) $tenant->id, 403);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1200'],
        ]);

        $reviewUpdate = [
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ];

        if (Schema::hasColumn('reviews', 'overall_rating')) {
            $reviewUpdate['overall_rating'] = $data['rating'];
        }

        if (Schema::hasColumn('reviews', 'status')) {
            $reviewUpdate['status'] = 'pending';
        }

        $review->forceFill($reviewUpdate)->save();

        return back()->with('success', 'Review updated.');
    }

    public function destroyReview(Request $request, Review $review)
    {
        $tenant = $this->tenant($request);
        abort_unless((int) $review->user_id === (int) $tenant->id, 403);

        $review->delete();

        return back()->with('success', 'Review deleted.');
    }

    private function tenant(Request $request)
    {
        $tenant = $request->user();
        abort_unless($tenant && $tenant->isUser(), 403);

        return $tenant;
    }

    private function approvedHouses()
    {
        return BoardingHouse::query()
            ->when(
                Schema::hasColumn('boarding_houses', 'approval_status') || Schema::hasColumn('boarding_houses', 'status'),
                function ($query) {
                    $query->where(function ($statusQuery) {
                        if (Schema::hasColumn('boarding_houses', 'approval_status')) {
                            $statusQuery->where('approval_status', 'approved');
                        }

                        if (Schema::hasColumn('boarding_houses', 'status')) {
                            $method = Schema::hasColumn('boarding_houses', 'approval_status') ? 'orWhere' : 'where';
                            $statusQuery->{$method}('status', 'approved');
                        }
                    });
                }
            )
            ->when(Schema::hasColumn('boarding_houses', 'is_active'), fn ($query) => $query->where('is_active', true))
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
