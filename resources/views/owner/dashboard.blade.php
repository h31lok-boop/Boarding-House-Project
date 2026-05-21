@php
    $r = fn ($name, $params = [], $fallback = null) => \Illuminate\Support\Facades\Route::has($name)
        ? route($name, $params)
        : ($fallback ?? url()->current());

    $icon = function (string $name, string $class = 'h-5 w-5'): string {
        return match ($name) {
            'menu' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>',
            'bell' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>',
            'message' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H8l-4 4V6a1 1 0 0 1 1-1Z"/><path d="M8 9h8M8 12h5"/></svg>',
            'chevron' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>',
            'calendar' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16"/></svg>',
            'building' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 21V7.5L12 3l8 4.5V21"/><path d="M9 21v-4h6v4"/><path d="M8 10h.01M12 10h.01M16 10h.01"/></svg>',
            'bed' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 11h16v8H4z"/><path d="M4 11V7a2 2 0 0 1 2-2h5a2 2 0 0 1 2 2v4"/><path d="M20 11V8a2 2 0 0 0-2-2h-3"/><path d="M4 19v2M20 19v2"/></svg>',
            'users' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/></svg>',
            'chat' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H9l-5 4V6a1 1 0 0 1 1-1Z"/><path d="M7 9h10M7 12h6"/></svg>',
            'shield' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 19 7v5c0 4.5-2.7 7.9-7 9-4.3-1.1-7-4.5-7-9V7l7-4Z"/><path d="m9 12 2 2 4-4"/></svg>',
            'info' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 10v6M12 7h.01"/></svg>',
            'alert' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 10 18H2L12 3Z"/><path d="M12 9v5M12 17h.01"/></svg>',
            'document' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 3h7l5 5v13H7z"/><path d="M14 3v6h6M10 13h6M10 17h4"/></svg>',
            'plus' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14M5 12h14"/></svg>',
            'check' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m5 12 4 4L19 6"/></svg>',
            'x' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/></svg>',
            'reply' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m10 9-5 5 5 5"/><path d="M5 14h10a5 5 0 0 1 5 5v1"/></svg>',
            'user' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>',
            'settings' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.8 1.8 0 0 0 .36 2l.05.05a2.1 2.1 0 0 1-2.97 2.97l-.05-.05a1.8 1.8 0 0 0-2-.36 1.8 1.8 0 0 0-1.09 1.65V21a2.1 2.1 0 0 1-4.2 0v-.08a1.8 1.8 0 0 0-1.09-1.65 1.8 1.8 0 0 0-2 .36l-.05.05a2.1 2.1 0 0 1-2.97-2.97l.05-.05a1.8 1.8 0 0 0 .36-2A1.8 1.8 0 0 0 2.15 13H2a2.1 2.1 0 0 1 0-4.2h.08a1.8 1.8 0 0 0 1.65-1.09 1.8 1.8 0 0 0-.36-2l-.05-.05a2.1 2.1 0 0 1 2.97-2.97l.05.05a1.8 1.8 0 0 0 2 .36A1.8 1.8 0 0 0 9.43 1.45V1.4a2.1 2.1 0 0 1 4.2 0v.08a1.8 1.8 0 0 0 1.09 1.65 1.8 1.8 0 0 0 2-.36l.05-.05a2.1 2.1 0 0 1 2.97 2.97l-.05.05a1.8 1.8 0 0 0-.36 2A1.8 1.8 0 0 0 20.85 8.8H21a2.1 2.1 0 0 1 0 4.2h-.08A1.8 1.8 0 0 0 19.4 15Z"/></svg>',
            'logout' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>',
            default => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/></svg>',
        };
    };

    $toneClasses = [
        'blue' => 'bg-blue-100 text-blue-600',
        'green' => 'bg-emerald-100 text-emerald-600',
        'orange' => 'bg-orange-100 text-orange-600',
        'purple' => 'bg-purple-100 text-purple-600',
        'teal' => 'bg-teal-100 text-teal-600',
        'red' => 'bg-red-100 text-red-600',
    ];

    $routes = [
        'listings' => $r('admin.listings', [], $r('owner.boarding-houses')),
        'createListing' => $r('admin.listings', [], $r('owner.boarding-houses')).'?modal=add',
        'rooms' => $r('admin.rooms', [], $r('owner.rooms')),
        'roomsAvailability' => $r('admin.rooms', [], $r('owner.rooms')).'?focus=availability',
        'availableRooms' => $r('admin.rooms', [], $r('owner.rooms')).'?status=available',
        'occupiedRooms' => $r('admin.rooms', [], $r('owner.rooms')).'?status=occupied',
        'inquiries' => $r('admin.inquiries.index', [], $r('owner.inquiries.index')),
        'pendingInquiries' => $r('admin.inquiries.index', [], $r('owner.inquiries.index')).'?status=pending',
        'messages' => $r('admin.messages', [], $r('owner.messages', [], $r('admin.inquiries.index'))),
        'compliance' => $r('admin.compliance.index', [], $r('owner.compliance.index')),
        'submitCompliance' => $r('admin.compliance.index', [], $r('owner.compliance.index')).'?modal=submit',
        'profile' => $r('admin.profile', [], $r('owner.profile')),
        'settings' => $r('admin.settings', [], $r('owner.settings', [], $r('admin.profile'))),
    ];

    $stats = [
        ['label' => 'Total Listings', 'value' => '3', 'href' => $routes['listings'], 'link' => 'View all listings', 'icon' => 'building', 'tone' => 'blue'],
        ['label' => 'Available Rooms', 'value' => '12', 'href' => $routes['availableRooms'], 'link' => 'View rooms', 'icon' => 'bed', 'tone' => 'green'],
        ['label' => 'Occupied Rooms', 'value' => '18', 'href' => $routes['occupiedRooms'], 'link' => 'View rooms', 'icon' => 'users', 'tone' => 'orange'],
        ['label' => 'Pending Inquiries', 'value' => '7', 'href' => $routes['pendingInquiries'], 'link' => 'View inquiries', 'icon' => 'chat', 'tone' => 'purple'],
        ['label' => 'Compliance Status', 'value' => 'Approved', 'href' => $routes['compliance'], 'link' => 'View details', 'icon' => 'shield', 'tone' => 'teal'],
    ];

    $inquiries = [
        ['id' => 1, 'name' => 'Maria Santos', 'room' => 'Single Room', 'date' => 'May 25, 2025', 'status' => 'Pending', 'contact' => '0917 123 4567', 'email' => 'maria.santos@gmail.com', 'message' => 'Good day! I would like to inquire if the single room is still available for next school year. Thank you!'],
        ['id' => 2, 'name' => 'John Reyes', 'room' => 'Double Room', 'date' => 'May 20, 2025', 'status' => 'Pending', 'contact' => '0921 987 6543', 'email' => 'john.reyes@email.com', 'message' => 'Is the double room still available, and what requirements should I prepare?'],
        ['id' => 3, 'name' => 'Angelica Gomez', 'room' => 'Bed Space', 'date' => 'May 30, 2025', 'status' => 'Pending', 'contact' => '0906 555 3322', 'email' => 'angelica.gomez@gmail.com', 'message' => 'I would like to know if a bed space will be available by the end of May.'],
        ['id' => 4, 'name' => 'Mark Dela Cruz', 'room' => 'Single Room', 'date' => 'May 18, 2025', 'status' => 'Confirmed', 'contact' => '0915 444 2211', 'email' => 'mark.delacruz@email.com', 'message' => 'Thank you for confirming my room reservation.'],
    ];

    $messages = [
        ['id' => 1, 'name' => 'Maria Santos', 'text' => 'Good day! I would like to inquire...', 'fullText' => 'Good day! I would like to inquire if the single room A-101 is still available for next school year. Thank you!', 'time' => '10:34 AM', 'count' => 1, 'initials' => 'MS'],
        ['id' => 2, 'name' => 'John Reyes', 'text' => 'Is the room still available?', 'fullText' => 'Is the room still available? I am planning to move in next week if possible.', 'time' => 'Yesterday', 'count' => 2, 'initials' => 'JR'],
        ['id' => 3, 'name' => 'Angelica Gomez', 'text' => 'Thank you!', 'fullText' => 'Thank you! I can visit tomorrow morning for viewing.', 'time' => 'May 15', 'count' => 1, 'initials' => 'AG'],
    ];

    $notifications = [
        ['id' => 1, 'icon' => 'info', 'tone' => 'blue', 'text' => 'Your boarding house "Green Haven" has been approved by admin review.', 'time' => '2 hours ago', 'type' => 'Approval', 'read' => false, 'href' => $routes['listings']],
        ['id' => 2, 'icon' => 'alert', 'tone' => 'red', 'text' => 'New inquiry from Maria Santos', 'time' => '3 hours ago', 'type' => 'Inquiry', 'read' => false, 'href' => $routes['inquiries']],
        ['id' => 3, 'icon' => 'document', 'tone' => 'orange', 'text' => 'Your document "Fire Safety Certificate" will expire on June 10, 2025.', 'time' => '1 day ago', 'type' => 'Document expiration', 'read' => false, 'href' => $routes['compliance']],
    ];

    $actions = [
        ['label' => 'Add New Listing', 'href' => $routes['createListing'], 'icon' => 'plus', 'tone' => 'blue'],
        ['label' => 'Update Availability', 'href' => $routes['roomsAvailability'], 'icon' => 'check', 'tone' => 'green'],
        ['label' => 'View Inquiries', 'href' => $routes['inquiries'], 'icon' => 'chat', 'tone' => 'purple'],
        ['label' => 'Submit Requirements', 'href' => $routes['submitCompliance'], 'icon' => 'document', 'tone' => 'orange'],
    ];
@endphp

<x-layouts.caretaker>
    <x-owner.shell :show-header="false">
        <div
            class="space-y-5"
            x-data="{
                routes: @js($routes),
                recentInquiries: @js($inquiries),
                recentMessages: @js($messages),
                notifications: @js($notifications),
                selectedInquiry: null,
                selectedMessage: null,
                selectedNotification: null,
                activeModal: null,
                selectedDate: '2025-05-16',
                selectedDateLabel: 'May 16, 2025',
                profileOpen: false,
                replyText: '',
                toast: '',
                toastTimer: null,
                get notificationBadge() {
                    return this.notifications.filter((notification) => ! notification.read).length;
                },
                get messageBadge() {
                    return this.recentMessages.filter((message) => Number(message.count || 0) > 0).length;
                },
                openModal(type) {
                    this.profileOpen = false;
                    this.activeModal = type;
                },
                closeModal() {
                    this.activeModal = null;
                    this.replyText = '';
                },
                openInquiry(inquiry) {
                    this.selectedInquiry = { ...inquiry };
                    this.openModal('inquiryDetails');
                },
                setInquiryStatus(status) {
                    if (! this.selectedInquiry) return;
                    this.recentInquiries = this.recentInquiries.map((inquiry) => inquiry.id === this.selectedInquiry.id ? { ...inquiry, status } : inquiry);
                    this.selectedInquiry.status = status;
                    this.showToast(status === 'Accepted' ? 'Inquiry accepted.' : status === 'Declined' ? 'Inquiry declined.' : 'Inquiry marked as confirmed.');
                },
                openInquiryReply() {
                    this.replyText = '';
                    this.openModal('replyInquiry');
                },
                sendInquiryReply() {
                    if (! this.replyText.trim()) {
                        this.showToast('Reply message cannot be empty.');
                        return;
                    }
                    this.closeModal();
                    this.showToast('Reply sent.');
                },
                openMessage(message) {
                    this.selectedMessage = { ...message };
                    this.openModal('messagePreview');
                },
                openMessageReply() {
                    this.replyText = '';
                    this.openModal('replyMessage');
                },
                sendMessageReply() {
                    if (! this.replyText.trim()) {
                        this.showToast('Reply message cannot be empty.');
                        return;
                    }
                    this.markMessageRead(false);
                    this.closeModal();
                    this.showToast('Reply sent.');
                },
                markMessageRead(showFeedback = true) {
                    if (! this.selectedMessage) return;
                    this.recentMessages = this.recentMessages.map((message) => message.id === this.selectedMessage.id ? { ...message, count: 0 } : message);
                    this.selectedMessage.count = 0;
                    if (showFeedback) this.showToast('Message marked as read.');
                },
                openNotification(notification) {
                    this.selectedNotification = { ...notification };
                    this.openModal('notificationDetails');
                },
                markNotificationRead(showFeedback = true) {
                    if (! this.selectedNotification) return;
                    this.notifications = this.notifications.map((notification) => notification.id === this.selectedNotification.id ? { ...notification, read: true } : notification);
                    this.selectedNotification.read = true;
                    if (showFeedback) this.showToast('Notification marked as read.');
                },
                markAllNotificationsRead() {
                    this.notifications = this.notifications.map((notification) => ({ ...notification, read: true }));
                    if (this.selectedNotification) this.selectedNotification.read = true;
                    this.showToast('Notifications marked as read.');
                },
                updateDate() {
                    const date = new Date(`${this.selectedDate}T00:00:00`);
                    this.selectedDateLabel = date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
                    this.closeModal();
                    this.showToast('Date updated.');
                },
                goTo(url) {
                    window.location.href = url;
                },
                showToast(message) {
                    this.toast = message;
                    clearTimeout(this.toastTimer);
                    this.toastTimer = setTimeout(() => this.toast = '', 2500);
                },
                inquiryBadgeClass(status) {
                    return {
                        Pending: 'bg-amber-100 text-amber-700',
                        Accepted: 'bg-violet-100 text-violet-700',
                        Declined: 'bg-rose-100 text-rose-700',
                        Confirmed: 'bg-emerald-100 text-emerald-700',
                    }[status] || 'bg-blue-100 text-blue-700';
                }
            }"
            @keydown.escape.window="closeModal(); profileOpen = false"
        >
            <section class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                <button type="button" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50" aria-label="Open menu">
                    {!! $icon('menu', 'h-5 w-5') !!}
                </button>

                <div class="ml-auto flex items-center gap-3">
                    <button type="button" @click="openModal('notifications')" class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50" aria-label="Notifications">
                        {!! $icon('bell', 'h-5 w-5') !!}
                        <span x-show="notificationBadge > 0" x-text="notificationBadge" class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold leading-none text-white"></span>
                    </button>
                    <button type="button" @click="openModal('messages')" class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50" aria-label="Messages">
                        {!! $icon('message', 'h-5 w-5') !!}
                        <span x-show="messageBadge > 0" x-text="messageBadge" class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold leading-none text-white"></span>
                    </button>
                    <div class="relative" @click.outside="profileOpen = false">
                        <button type="button" @click="profileOpen = ! profileOpen" class="flex min-w-0 items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900 shadow-sm transition hover:bg-slate-50" aria-haspopup="menu" :aria-expanded="profileOpen.toString()">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-200 text-xs font-bold text-slate-700">JD</span>
                            <span class="hidden min-w-0 leading-tight sm:block">
                                <span class="block truncate text-sm font-semibold">Juan Dela Cruz</span>
                                <span class="block truncate text-xs text-slate-500">Admin</span>
                            </span>
                            <span class="hidden text-slate-400 sm:inline-flex">{!! $icon('chevron', 'h-4 w-4') !!}</span>
                        </button>
                        <div x-show="profileOpen" x-transition style="display: none;" class="absolute right-0 z-40 mt-2 w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white py-2 text-sm text-slate-700 shadow-2xl">
                            <a href="{{ $routes['profile'] }}" class="flex items-center gap-3 px-4 py-2.5 font-semibold hover:bg-slate-50">{!! $icon('user', 'h-4 w-4') !!} View Profile</a>
                            <a href="{{ $routes['settings'] }}" class="flex items-center gap-3 px-4 py-2.5 font-semibold hover:bg-slate-50">{!! $icon('settings', 'h-4 w-4') !!} Settings</a>
                            <button type="button" @click="openModal('logoutConfirm')" class="flex w-full items-center gap-3 border-t border-slate-100 px-4 py-2.5 text-left font-semibold text-rose-700 hover:bg-rose-50">{!! $icon('logout', 'h-4 w-4') !!} Logout</button>
                        </div>
                    </div>
                </div>
            </section>

            <section class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Dashboard</h1>
                    <p class="mt-1 text-sm text-slate-500">Welcome back, Juan! Here's what's happening with your properties.</p>
                </div>
                <button type="button" @click="openModal('datePicker')" class="inline-flex items-center gap-2 self-start rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 sm:self-auto">
                    {!! $icon('calendar', 'h-4 w-4') !!}
                    <span x-text="selectedDateLabel">May 16, 2025</span>
                </button>
            </section>

            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                @foreach ($stats as $card)
                    <a href="{{ $card['href'] }}" class="rounded-[14px] border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                                <p class="mt-3 text-2xl font-bold tracking-tight text-slate-900">{{ $card['value'] }}</p>
                            </div>
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $toneClasses[$card['tone']] }}">
                                {!! $icon($card['icon'], 'h-5 w-5') !!}
                            </span>
                        </div>
                        <p class="mt-4 border-t border-slate-100 pt-4 text-sm font-semibold text-blue-600">{{ $card['link'] }}</p>
                    </a>
                @endforeach
            </section>

            <section class="grid gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(340px,0.85fr)]">
                <div class="rounded-[14px] border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between gap-4 p-5">
                        <h2 class="text-base font-bold text-slate-900">Recent Inquiries</h2>
                        <a href="{{ $r('admin.inquiries.index', [], $r('owner.inquiries.index')) }}" class="text-sm font-semibold text-blue-600">View All</a>
                    </div>
                    <div class="overflow-x-auto border-t border-slate-100">
                        <table class="min-w-[720px] w-full text-sm">
                            <thead>
                                <tr class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                                    <th class="px-5 py-3">Student Name</th>
                                    <th class="px-5 py-3">Room Requested</th>
                                    <th class="px-5 py-3">Move-in Date</th>
                                    <th class="px-5 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="row in recentInquiries" :key="row.id">
                                    <tr @click="openInquiry(row)" class="cursor-pointer text-slate-700 transition hover:bg-slate-50">
                                        <td class="px-5 py-3 font-medium text-slate-900" x-text="row.name"></td>
                                        <td class="px-5 py-3" x-text="row.room"></td>
                                        <td class="px-5 py-3" x-text="row.date"></td>
                                        <td class="px-5 py-3">
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold" :class="inquiryBadgeClass(row.status)" x-text="row.status"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-[14px] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-base font-bold text-slate-900">Recent Messages</h2>
                        <a href="{{ $r('admin.messages', [], $r('owner.messages', [], $r('admin.inquiries.index'))) }}" class="text-sm font-semibold text-blue-600">View All</a>
                    </div>
                    <div class="mt-4 divide-y divide-slate-100">
                        <template x-for="message in recentMessages" :key="message.id">
                            <button type="button" @click="openMessage(message)" class="flex w-full items-center gap-3 py-3 text-left transition hover:bg-slate-50">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-200 text-xs font-bold text-slate-700" x-text="message.initials"></span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="truncate text-sm font-bold text-slate-900" x-text="message.name"></p>
                                        <p class="shrink-0 text-xs text-slate-400" x-text="message.time"></p>
                                    </div>
                                    <p class="mt-1 truncate text-sm text-slate-500" x-text="message.text"></p>
                                </div>
                                <span x-show="Number(message.count) > 0" class="flex h-6 min-w-6 items-center justify-center rounded-full bg-emerald-500 px-2 text-xs font-bold text-white" x-text="message.count"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </section>

            <section class="grid gap-5 xl:grid-cols-[minmax(0,1.1fr)_minmax(340px,0.9fr)]">
                <div class="rounded-[14px] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-base font-bold text-slate-900">Notifications</h2>
                        <button type="button" @click="openModal('notifications')" class="text-sm font-semibold text-blue-600">View All</button>
                    </div>
                    <div class="mt-4 divide-y divide-slate-100">
                        <template x-for="notification in notifications" :key="notification.id">
                            <button type="button" @click="openNotification(notification)" class="flex w-full items-center gap-3 py-3 text-left transition hover:bg-slate-50">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl" :class="{ 'bg-blue-100 text-blue-600': notification.tone === 'blue', 'bg-red-100 text-red-600': notification.tone === 'red', 'bg-orange-100 text-orange-600': notification.tone === 'orange' }">
                                    <span x-show="notification.icon === 'info'">{!! $icon('info', 'h-4 w-4') !!}</span>
                                    <span x-show="notification.icon === 'alert'">{!! $icon('alert', 'h-4 w-4') !!}</span>
                                    <span x-show="notification.icon === 'document'">{!! $icon('document', 'h-4 w-4') !!}</span>
                                </span>
                                <p class="min-w-0 flex-1 text-sm font-medium leading-5 text-slate-800" :class="notification.read ? 'text-slate-500' : 'text-slate-900'" x-text="notification.text"></p>
                                <span x-show="! notification.read" class="h-2 w-2 rounded-full bg-blue-600"></span>
                                <p class="shrink-0 text-xs text-slate-500" x-text="notification.time"></p>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="rounded-[14px] border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-base font-bold text-slate-900">Quick Actions</h2>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach ($actions as $action)
                            <a href="{{ $action['href'] }}" class="flex min-h-[112px] cursor-pointer flex-col items-center justify-center gap-3 rounded-[14px] border border-slate-200 bg-white p-4 text-center shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $toneClasses[$action['tone']] }}">
                                    {!! $icon($action['icon'], 'h-6 w-6') !!}
                                </span>
                                <span class="text-sm font-bold leading-5 text-slate-800">{{ $action['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>

            <div x-show="toast" x-transition style="display: none;" class="fixed bottom-6 right-6 z-50 rounded-xl bg-slate-950 px-4 py-3 text-sm font-semibold text-white shadow-2xl" x-text="toast"></div>

            <div x-show="activeModal" x-transition.opacity style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm" @click.self="closeModal()">
                <div class="flex max-h-[85vh] w-full flex-col overflow-hidden rounded-2xl bg-white shadow-2xl" :class="activeModal === 'logoutConfirm' ? 'max-w-md' : activeModal === 'datePicker' ? 'max-w-sm' : 'max-w-3xl'">
                    <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5">
                        <div>
                            <h2 class="text-lg font-bold text-slate-950" x-text="activeModal === 'inquiryDetails' ? 'Inquiry Details' : activeModal === 'replyInquiry' ? 'Reply to Inquiry' : activeModal === 'messagePreview' ? 'Message Preview' : activeModal === 'replyMessage' ? 'Reply to Message' : activeModal === 'notifications' ? 'Notifications' : activeModal === 'notificationDetails' ? 'Notification Details' : activeModal === 'messages' ? 'Recent Messages' : activeModal === 'datePicker' ? 'Select Date' : 'Confirm Logout'"></h2>
                            <p class="mt-1 text-sm text-slate-500" x-show="activeModal === 'inquiryDetails' || activeModal === 'replyInquiry'" x-text="selectedInquiry?.name"></p>
                            <p class="mt-1 text-sm text-slate-500" x-show="activeModal === 'messagePreview' || activeModal === 'replyMessage'" x-text="selectedMessage?.name"></p>
                        </div>
                        <button type="button" @click="closeModal()" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-900" aria-label="Close modal">
                            {!! $icon('x', 'h-5 w-5') !!}
                        </button>
                    </div>

                    <div class="overflow-y-auto px-6 py-5">
                        <div x-show="activeModal === 'inquiryDetails'" class="space-y-5 text-sm">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-xl font-bold text-slate-950" x-text="selectedInquiry?.name"></p>
                                    <p class="mt-1 text-slate-500" x-text="selectedInquiry?.email"></p>
                                </div>
                                <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-bold" :class="inquiryBadgeClass(selectedInquiry?.status)" x-text="selectedInquiry?.status"></span>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div><p class="font-semibold text-slate-700">Room Requested</p><p class="mt-1 text-slate-900" x-text="selectedInquiry?.room"></p></div>
                                <div><p class="font-semibold text-slate-700">Move-in Date</p><p class="mt-1 text-slate-900" x-text="selectedInquiry?.date"></p></div>
                                <div><p class="font-semibold text-slate-700">Contact Number</p><p class="mt-1 text-slate-900" x-text="selectedInquiry?.contact"></p></div>
                                <div><p class="font-semibold text-slate-700">Email</p><p class="mt-1 text-blue-700" x-text="selectedInquiry?.email"></p></div>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="font-semibold text-slate-700">Message</p>
                                <p class="mt-2 leading-6 text-slate-700" x-text="selectedInquiry?.message"></p>
                            </div>
                        </div>

                        <div x-show="activeModal === 'replyInquiry' || activeModal === 'replyMessage'" class="space-y-4">
                            <textarea x-model="replyText" rows="5" class="w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" :placeholder="activeModal === 'replyInquiry' ? 'Type your inquiry reply...' : 'Type your message reply...'"></textarea>
                            <p class="text-xs text-slate-500">Reply message cannot be empty.</p>
                        </div>

                        <div x-show="activeModal === 'messagePreview'" class="space-y-5 text-sm">
                            <div class="flex items-start gap-4">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-slate-200 text-sm font-bold text-slate-700" x-text="selectedMessage?.initials"></span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <p class="text-xl font-bold text-slate-950" x-text="selectedMessage?.name"></p>
                                        <p class="text-xs font-semibold text-slate-500" x-text="selectedMessage?.time"></p>
                                    </div>
                                    <div class="mt-2 flex items-center gap-2">
                                        <span x-show="Number(selectedMessage?.count || 0) > 0" class="rounded-full bg-emerald-500 px-2 py-0.5 text-xs font-bold text-white" x-text="selectedMessage?.count + ' unread'"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="font-semibold text-slate-700">Full Message</p>
                                <p class="mt-2 leading-6 text-slate-700" x-text="selectedMessage?.fullText"></p>
                            </div>
                        </div>

                        <div x-show="activeModal === 'notifications'" class="space-y-3">
                            <template x-for="notification in notifications" :key="notification.id">
                                <button type="button" @click="openNotification(notification)" class="flex w-full items-center gap-3 rounded-2xl border border-slate-200 p-4 text-left transition hover:bg-slate-50">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl" :class="{ 'bg-blue-100 text-blue-600': notification.tone === 'blue', 'bg-red-100 text-red-600': notification.tone === 'red', 'bg-orange-100 text-orange-600': notification.tone === 'orange' }">
                                        <span x-show="notification.icon === 'info'">{!! $icon('info', 'h-4 w-4') !!}</span>
                                        <span x-show="notification.icon === 'alert'">{!! $icon('alert', 'h-4 w-4') !!}</span>
                                        <span x-show="notification.icon === 'document'">{!! $icon('document', 'h-4 w-4') !!}</span>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm font-semibold text-slate-900" x-text="notification.text"></span>
                                        <span class="mt-1 block text-xs text-slate-500" x-text="notification.time + ' - ' + notification.type"></span>
                                    </span>
                                    <span class="rounded-full px-2 py-0.5 text-xs font-bold" :class="notification.read ? 'bg-slate-100 text-slate-600' : 'bg-blue-100 text-blue-700'" x-text="notification.read ? 'Read' : 'Unread'"></span>
                                </button>
                            </template>
                        </div>

                        <div x-show="activeModal === 'notificationDetails'" class="space-y-5 text-sm">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="font-semibold text-slate-700">Notification</p>
                                <p class="mt-2 leading-6 text-slate-800" x-text="selectedNotification?.text"></p>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div><p class="font-semibold text-slate-700">Time</p><p class="mt-1 text-slate-900" x-text="selectedNotification?.time"></p></div>
                                <div><p class="font-semibold text-slate-700">Type</p><p class="mt-1 text-slate-900" x-text="selectedNotification?.type"></p></div>
                                <div><p class="font-semibold text-slate-700">Status</p><p class="mt-1 text-slate-900" x-text="selectedNotification?.read ? 'Read' : 'Unread'"></p></div>
                            </div>
                        </div>

                        <div x-show="activeModal === 'messages'" class="divide-y divide-slate-100">
                            <template x-for="message in recentMessages" :key="message.id">
                                <button type="button" @click="openMessage(message)" class="flex w-full items-center gap-3 py-3 text-left transition hover:bg-slate-50">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-200 text-xs font-bold text-slate-700" x-text="message.initials"></span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm font-bold text-slate-900" x-text="message.name"></span>
                                        <span class="mt-1 block truncate text-sm text-slate-500" x-text="message.text"></span>
                                    </span>
                                    <span x-show="Number(message.count) > 0" class="rounded-full bg-emerald-500 px-2 py-0.5 text-xs font-bold text-white" x-text="message.count"></span>
                                </button>
                            </template>
                        </div>

                        <div x-show="activeModal === 'datePicker'" class="space-y-4">
                            <label class="block">
                                <span class="text-sm font-semibold text-slate-700">Dashboard Date</span>
                                <input x-model="selectedDate" type="date" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </label>
                            <p class="text-sm text-slate-500">Date filtering is local until backend reporting filters are connected.</p>
                        </div>

                        <div x-show="activeModal === 'logoutConfirm'" class="rounded-2xl bg-rose-50 p-4 text-sm text-rose-800">
                            Are you sure you want to log out?
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-200 px-6 py-4 sm:flex-row sm:justify-end">
                        <button type="button" @click="closeModal()" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Close</button>
                        <button x-show="activeModal === 'inquiryDetails'" type="button" @click="openInquiryReply()" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-blue-200 px-4 text-sm font-semibold text-blue-700 transition hover:bg-blue-50">{!! $icon('reply', 'h-4 w-4') !!} Reply</button>
                        <button x-show="activeModal === 'inquiryDetails'" type="button" @click="setInquiryStatus('Accepted')" class="inline-flex h-10 items-center justify-center rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white transition hover:bg-emerald-700">Accept</button>
                        <button x-show="activeModal === 'inquiryDetails'" type="button" @click="setInquiryStatus('Declined')" class="inline-flex h-10 items-center justify-center rounded-xl bg-rose-600 px-4 text-sm font-semibold text-white transition hover:bg-rose-700">Decline</button>
                        <button x-show="activeModal === 'inquiryDetails'" type="button" @click="setInquiryStatus('Confirmed')" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white transition hover:bg-blue-800">Mark as Confirmed</button>
                        <button x-show="activeModal === 'replyInquiry'" type="button" @click="sendInquiryReply()" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white transition hover:bg-blue-800">Send Reply</button>
                        <button x-show="activeModal === 'messagePreview'" type="button" @click="openMessageReply()" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-blue-200 px-4 text-sm font-semibold text-blue-700 transition hover:bg-blue-50">{!! $icon('reply', 'h-4 w-4') !!} Reply</button>
                        <button x-show="activeModal === 'messagePreview'" type="button" @click="markMessageRead()" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Mark as Read</button>
                        <button x-show="activeModal === 'messagePreview'" type="button" @click="goTo(routes.messages)" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white transition hover:bg-blue-800">Open Conversation</button>
                        <button x-show="activeModal === 'replyMessage'" type="button" @click="sendMessageReply()" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white transition hover:bg-blue-800">Send Reply</button>
                        <button x-show="activeModal === 'notifications'" type="button" @click="markAllNotificationsRead()" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Mark All as Read</button>
                        <button x-show="activeModal === 'notificationDetails'" type="button" @click="markNotificationRead()" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Mark as Read</button>
                        <button x-show="activeModal === 'notificationDetails'" type="button" @click="goTo(selectedNotification?.href)" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white transition hover:bg-blue-800">View Related Page</button>
                        <button x-show="activeModal === 'datePicker'" type="button" @click="updateDate()" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white transition hover:bg-blue-800">Update Date</button>
                        <button x-show="activeModal === 'logoutConfirm'" type="submit" form="owner-dashboard-logout-form" class="inline-flex h-10 items-center justify-center rounded-xl bg-rose-600 px-4 text-sm font-semibold text-white transition hover:bg-rose-700">Logout</button>
                    </div>
                </div>
            </div>

            <form id="owner-dashboard-logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
                @csrf
            </form>
        </div>
    </x-owner.shell>
</x-layouts.caretaker>
