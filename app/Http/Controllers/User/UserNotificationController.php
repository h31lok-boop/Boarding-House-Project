<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserNotificationController extends Controller
{
    private const TYPE_GROUPS = [
        'reservations' => ['reservation', 'Reservation Update', 'reservation_update'],
        'payments' => ['payment', 'Payment Confirmation'],
        'messages' => ['message', 'New Message'],
        'inquiries' => ['inquiry', 'Inquiry Response', 'inquiry_reply'],
        'matchmaking' => ['matchmaking', 'Matchmaking Recommendation'],
        'system' => ['system', 'System Alert', 'admin_notice', 'admin notice'],
    ];

    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user && $user->isUser(), 403);

        $filter = $request->query('filter', 'all');
        $search = trim((string) $request->query('q', ''));
        $sort = $request->query('sort', 'newest');
        $allowedFilters = ['all', 'unread', 'reservations', 'payments', 'messages', 'inquiries', 'matchmaking', 'system'];
        $allowedSorts = ['newest', 'oldest', 'unread'];

        if (! in_array($filter, $allowedFilters, true)) {
            $filter = 'all';
        }

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'newest';
        }

        $query = $this->baseQueryForUser($user->id);
        $this->applyFilter($query, $filter);
        $this->applySearch($query, $search);
        $this->applySort($query, $sort);

        $notifications = $query->paginate(12)->withQueryString();
        $summaryCards = $this->summaryCards($user->id);

        return view('user.notifications', [
            'notifications' => $notifications,
            'filter' => $filter,
            'search' => $search,
            'sort' => $sort,
            'unreadCount' => $summaryCards['unread']['count'],
            'totalCount' => $summaryCards['all']['count'],
            'summaryCards' => $summaryCards,
            'filters' => [
                'all' => 'All',
                'unread' => 'Unread',
                'reservations' => 'Reservations',
                'payments' => 'Payments',
                'messages' => 'Messages',
                'inquiries' => 'Inquiries',
                'matchmaking' => 'Matchmaking',
                'system' => 'System',
            ],
            'sortOptions' => [
                'newest' => 'Newest First',
                'oldest' => 'Oldest First',
                'unread' => 'Unread First',
            ],
        ]);
    }

    public function markAsRead(Request $request, string $id): RedirectResponse
    {
        $notification = $this->notificationForUser($request, $id);
        $this->duplicateGroupFor($notification)->update([
            'is_read' => true,
            'read_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $user->isUser(), 403);

        UserNotification::query()
            ->where('user_id', $user->id)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function destroy(Request $request, string $id): RedirectResponse
    {
        $notification = $this->notificationForUser($request, $id);
        $this->duplicateGroupFor($notification)->delete();

        return back()->with('success', 'Notification deleted successfully.');
    }

    public function clearAll(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $user->isUser(), 403);

        UserNotification::query()
            ->where('user_id', $user->id)
            ->delete();

        return redirect()
            ->route('user.notifications.index')
            ->with('success', 'All notifications cleared.');
    }

    private function notificationForUser(Request $request, string $id): UserNotification
    {
        $user = $request->user();
        abort_unless($user && $user->isUser(), 403);

        return UserNotification::query()
            ->where('user_id', $user->id)
            ->whereKey($id)
            ->firstOrFail();
    }

    private function baseQueryForUser(int $userId)
    {
        return UserNotification::query()
            ->where('user_id', $userId)
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('notifications as newer_notification')
                    ->whereColumn('newer_notification.user_id', 'notifications.user_id')
                    ->whereColumn('newer_notification.id', '>', 'notifications.id')
                    ->whereRaw(
                        "(
                            (
                                notifications.reference_id IS NOT NULL
                                AND newer_notification.reference_id = notifications.reference_id
                                AND COALESCE(newer_notification.type, '') = COALESCE(notifications.type, '')
                            )
                            OR
                            (
                                notifications.reference_id IS NULL
                                AND newer_notification.reference_id IS NULL
                                AND COALESCE(newer_notification.type, '') = COALESCE(notifications.type, '')
                                AND COALESCE(newer_notification.title, '') = COALESCE(notifications.title, '')
                                AND COALESCE(newer_notification.message, '') = COALESCE(notifications.message, '')
                            )
                        )"
                    );
            });
    }

    private function applyFilter($query, string $filter): void
    {
        if ($filter === 'unread') {
            $query->unread();
            return;
        }

        if (isset(self::TYPE_GROUPS[$filter])) {
            $query->whereIn('type', self::TYPE_GROUPS[$filter]);
        }
    }

    private function applySearch($query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $query->where(function ($searchQuery) use ($search): void {
            $searchQuery
                ->where('title', 'like', '%'.$search.'%')
                ->orWhere('message', 'like', '%'.$search.'%')
                ->orWhere('type', 'like', '%'.$search.'%');
        });
    }

    private function applySort($query, string $sort): void
    {
        match ($sort) {
            'oldest' => $query->oldest('created_at'),
            'unread' => $query
                ->orderByRaw('CASE WHEN read_at IS NULL THEN 0 ELSE 1 END')
                ->latest('created_at'),
            default => $query->latest('created_at'),
        };
    }

    private function summaryCards(int $userId): array
    {
        return [
            'all' => [
                'label' => 'All Notifications',
                'filter' => 'all',
                'count' => $this->baseQueryForUser($userId)->count(),
            ],
            'unread' => [
                'label' => 'Unread',
                'filter' => 'unread',
                'count' => $this->baseQueryForUser($userId)->unread()->count(),
            ],
            'reservations' => [
                'label' => 'Reservations',
                'filter' => 'reservations',
                'count' => $this->countByGroup($userId, 'reservations'),
            ],
            'payments' => [
                'label' => 'Payments',
                'filter' => 'payments',
                'count' => $this->countByGroup($userId, 'payments'),
            ],
            'messages' => [
                'label' => 'Messages',
                'filter' => 'messages',
                'count' => $this->countByGroup($userId, 'messages'),
            ],
            'system' => [
                'label' => 'System Alerts',
                'filter' => 'system',
                'count' => $this->countByGroup($userId, 'system'),
            ],
        ];
    }

    private function countByGroup(int $userId, string $group): int
    {
        return $this->baseQueryForUser($userId)
            ->whereIn('type', self::TYPE_GROUPS[$group])
            ->count();
    }

    private function duplicateGroupFor(UserNotification $notification)
    {
        $query = UserNotification::query()
            ->where('user_id', $notification->user_id)
            ->where('type', $notification->type);

        if ($notification->reference_id) {
            return $query->where('reference_id', $notification->reference_id);
        }

        return $query
            ->whereNull('reference_id')
            ->where('title', $notification->title)
            ->where('message', $notification->message);
    }
}
