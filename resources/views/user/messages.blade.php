<x-layouts.dashboard>
<x-user.shell>
@php
    $avatarFor = function (string $name, string $background = '2563eb'): string {
        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&background='.$background.'&color=fff&size=96&bold=true';
    };

    $imageFor = function ($house, int $index): string {
        $path = $house?->images?->first()?->image_path
            ?? $house?->featured_image
            ?? $house?->exterior_image
            ?? null;

        if ($path) {
            return \Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '/'])
                ? $path
                : \Illuminate\Support\Facades\Storage::url($path);
        }

        $tones = ['dbeafe/2563eb', 'dcfce7/16a34a', 'fef3c7/d97706', 'fce7f3/be185d', 'ede9fe/7c3aed'];

        return 'https://placehold.co/320x220/'.$tones[$index % count($tones)].'?text=BoardMatch';
    };

    $categories = ['bookings', 'payments', 'support'];
    $roleFor = fn (int $index) => $index % 3 === 2 ? 'Property Manager' : 'Property Owner';
    $responseFor = fn (int $index) => $index % 2 === 0 ? 'Responds within 1 hour' : 'Usually replies today';
    $reservationUrl = route('user.reservations.index');
    $paymentsUrl = route('user.payments.index');
    $defaultHouse = $houses->first();
    $defaultDetailsUrl = $defaultHouse ? route('user.boarding-houses.show', $defaultHouse) : '#';

    $threads = $messages->getCollection()->values()->map(function ($message, int $index) use ($avatarFor, $imageFor, $categories, $roleFor, $responseFor, $reservationUrl, $paymentsUrl, $defaultHouse) {
        $house = $message->boardingHouse;
        $ownerName = $house?->owner?->name ?? ['Maria Santos', 'James Lorenzo', 'Liza Bautista', 'Ryan Cruz'][$index % 4];
        $property = $house?->name ?? 'BoardMatch Support';
        $category = $categories[$index % count($categories)];
        $location = $house?->full_address ?? $house?->address ?? 'Baguio City';
        $roomType = ['Private Room', 'Shared Room', 'Studio Type'][$index % 3];
        $rent = 'PHP '.number_format(3200 + ($index * 300)).' / month';
        $time = optional($message->created_at)->isToday()
            ? optional($message->created_at)->format('h:i A')
            : optional($message->created_at)->format('M d');
        $timeFull = optional($message->created_at)->format('h:i A') ?: '10:24 AM';
        $detailsUrl = $house ? route('user.boarding-houses.show', $house) : route('user.messages.index');
        $unread = $index < 2 ? 2 - $index : 0;
        $status = ucfirst((string) ($message->status ?: 'Reservation Pending'));

        return [
            'id' => $message->id,
            'category' => $category,
            'house_id' => $house?->id ?: $defaultHouse?->id,
            'owner_name' => $ownerName,
            'owner_role' => $roleFor($index),
            'property' => $property,
            'location' => $location,
            'room_type' => $roomType,
            'monthly_rent' => $rent,
            'booking_status' => $status,
            'message' => $message->message ?: 'Thank you for your inquiry. The room is still available.',
            'time' => $time ?: '10:24 AM',
            'time_full' => $timeFull,
            'avatar' => $avatarFor($ownerName),
            'house_image' => $imageFor($house, $index),
            'unread' => $unread,
            'online' => in_array($index, [0, 3, 5], true),
            'response_time' => $responseFor($index),
            'details_url' => $detailsUrl,
            'reservation_url' => $reservationUrl,
            'payments_url' => $paymentsUrl,
            'profile_url' => $detailsUrl,
            'system' => $category === 'payments'
                ? ['title' => 'Payment Reminder', 'body' => 'Deposit Required: PHP 3,000', 'meta' => 'Due Date: June 25, 2026', 'button' => 'Pay Now', 'url' => $paymentsUrl]
                : ['title' => 'Reservation Update', 'body' => 'Reservation submitted for owner review.', 'meta' => 'Next step: wait for confirmation', 'button' => 'View Reservation', 'url' => $reservationUrl],
            'progress' => [
                ['label' => 'Inquiry Sent', 'state' => 'done'],
                ['label' => 'Owner Replied', 'state' => $unread > 0 ? 'done' : 'done'],
                ['label' => 'Reservation Submitted', 'state' => 'done'],
                ['label' => 'Payment Pending', 'state' => 'current'],
                ['label' => 'Move-in Confirmed', 'state' => 'upcoming'],
            ],
        ];
    });

    if ($threads->isEmpty()) {
        $threads = collect([
            [
                'id' => 1,
                'category' => 'bookings',
                'house_id' => $defaultHouse?->id,
                'owner_name' => 'Maria Santos',
                'owner_role' => 'Property Owner',
                'property' => 'Sunrise Student Boarding House',
                'location' => 'Baguio City',
                'room_type' => 'Private Room',
                'monthly_rent' => 'PHP 3,500 / month',
                'booking_status' => 'Reservation Pending',
                'message' => 'Thank you for your inquiry. The room is still available.',
                'time' => '10:24 AM',
                'time_full' => '10:24 AM',
                'avatar' => $avatarFor('Maria Santos'),
                'house_image' => 'https://placehold.co/320x220/dbeafe/2563eb?text=Sunrise',
                'unread' => 2,
                'online' => true,
                'response_time' => 'Responds within 1 hour',
                'details_url' => $defaultDetailsUrl,
                'reservation_url' => $reservationUrl,
                'payments_url' => $paymentsUrl,
                'profile_url' => $defaultDetailsUrl,
                'system' => ['title' => 'Reservation Approved', 'body' => 'Your reservation has been confirmed.', 'meta' => 'Prepare your deposit to secure the room.', 'button' => 'View Reservation', 'url' => $reservationUrl],
                'progress' => [
                    ['label' => 'Inquiry Sent', 'state' => 'done'],
                    ['label' => 'Owner Replied', 'state' => 'done'],
                    ['label' => 'Reservation Submitted', 'state' => 'done'],
                    ['label' => 'Payment Pending', 'state' => 'current'],
                    ['label' => 'Move-in Confirmed', 'state' => 'upcoming'],
                ],
            ],
            [
                'id' => 2,
                'category' => 'payments',
                'house_id' => $defaultHouse?->id,
                'owner_name' => 'James Lorenzo',
                'owner_role' => 'Property Owner',
                'property' => 'Greenview Boarding House',
                'location' => 'Digos City',
                'room_type' => 'Shared Room',
                'monthly_rent' => 'PHP 4,000 / month',
                'booking_status' => 'Payment Pending',
                'message' => 'Please upload the deposit receipt so we can verify your reservation.',
                'time' => 'Yesterday',
                'time_full' => '04:18 PM',
                'avatar' => $avatarFor('James Lorenzo', '16a34a'),
                'house_image' => 'https://placehold.co/320x220/dcfce7/16a34a?text=Greenview',
                'unread' => 1,
                'online' => false,
                'response_time' => 'Usually replies today',
                'details_url' => $defaultDetailsUrl,
                'reservation_url' => $reservationUrl,
                'payments_url' => $paymentsUrl,
                'profile_url' => $defaultDetailsUrl,
                'system' => ['title' => 'Payment Reminder', 'body' => 'Deposit Required: PHP 3,000', 'meta' => 'Due Date: June 25, 2026', 'button' => 'Pay Now', 'url' => $paymentsUrl],
                'progress' => [
                    ['label' => 'Inquiry Sent', 'state' => 'done'],
                    ['label' => 'Owner Replied', 'state' => 'done'],
                    ['label' => 'Reservation Submitted', 'state' => 'done'],
                    ['label' => 'Payment Pending', 'state' => 'current'],
                    ['label' => 'Move-in Confirmed', 'state' => 'upcoming'],
                ],
            ],
            [
                'id' => 3,
                'category' => 'support',
                'house_id' => $defaultHouse?->id,
                'owner_name' => 'BoardMatch Support',
                'owner_role' => 'Support Team',
                'property' => 'Account Assistance',
                'location' => 'BoardMatch Help Desk',
                'room_type' => 'Support Ticket',
                'monthly_rent' => 'No rent linked',
                'booking_status' => 'Open',
                'message' => 'We received your request and will help you update your reservation details.',
                'time' => 'Jun 18',
                'time_full' => '09:30 AM',
                'avatar' => $avatarFor('BoardMatch Support', '0f172a'),
                'house_image' => 'https://placehold.co/320x220/e0f2fe/0284c7?text=Support',
                'unread' => 0,
                'online' => true,
                'response_time' => 'Support replies within 2 hours',
                'details_url' => $defaultDetailsUrl,
                'reservation_url' => $reservationUrl,
                'payments_url' => $paymentsUrl,
                'profile_url' => $defaultDetailsUrl,
                'system' => ['title' => 'Support Ticket Opened', 'body' => 'Your concern has been routed to the support team.', 'meta' => 'Ticket type: Reservation Support', 'button' => 'Help Center', 'url' => route('user.help-center.index')],
                'progress' => [
                    ['label' => 'Inquiry Sent', 'state' => 'done'],
                    ['label' => 'Owner Replied', 'state' => 'done'],
                    ['label' => 'Reservation Submitted', 'state' => 'upcoming'],
                    ['label' => 'Payment Pending', 'state' => 'upcoming'],
                    ['label' => 'Move-in Confirmed', 'state' => 'upcoming'],
                ],
            ],
        ]);
    }

    $categoryCounts = [
        'all' => $threads->count(),
        'unread' => $threads->sum('unread'),
        'bookings' => $threads->where('category', 'bookings')->count(),
        'payments' => $threads->where('category', 'payments')->count(),
        'support' => $threads->where('category', 'support')->count(),
        'archived' => 0,
    ];

    $totalConversations = $messages->total() ?: $threads->count();
    $unreadMessages = $threads->sum('unread');
@endphp

@once
    <script>
        window.messageCenter = window.messageCenter || function (config) {
            return {
                conversations: config.conversations || [],
                counts: config.counts || {},
                quickReplies: config.quickReplies || [],
                activeId: null,
                category: 'all',
                search: '',
                mobileListOpen: false,
                progressOpen: false,
                emojiOpen: false,
                messageBody: '',
                attachment: { name: '', size: '', type: '' },
                init() {
                    this.activeId = this.conversations[0]?.id || null;
                },
                get active() {
                    return this.conversations.find((conversation) => conversation.id === this.activeId) || this.conversations[0] || null;
                },
                get unreadTotal() {
                    return this.conversations.reduce((total, conversation) => total + Number(conversation.unread || 0), 0);
                },
                get filteredConversations() {
                    const term = this.search.trim().toLowerCase();

                    return this.conversations.filter((conversation) => {
                        const matchesCategory = this.category === 'all'
                            || (this.category === 'unread' && Number(conversation.unread || 0) > 0)
                            || conversation.category === this.category;

                        const haystack = [
                            conversation.owner_name,
                            conversation.property,
                            conversation.message,
                            conversation.location,
                        ].join(' ').toLowerCase();

                        return matchesCategory && (!term || haystack.includes(term));
                    });
                },
                countFor(category) {
                    return this.counts[category] || 0;
                },
                setActive(conversation) {
                    this.activeId = conversation.id;
                    this.mobileListOpen = false;
                    this.progressOpen = false;
                    this.$nextTick(() => {
                        this.$refs.messageInput?.focus();
                    });
                },
                useQuickReply(reply) {
                    this.messageBody = reply;
                    this.$nextTick(() => this.$refs.messageInput?.focus());
                },
                addEmoji(emoji) {
                    this.messageBody = `${this.messageBody}${emoji}`;
                    this.emojiOpen = false;
                    this.$nextTick(() => this.$refs.messageInput?.focus());
                },
                shareLocation() {
                    const locationText = 'Sharing my preferred move-in location near campus.';
                    this.messageBody = this.messageBody
                        ? `${this.messageBody} ${locationText}`
                        : locationText;
                    this.$nextTick(() => this.$refs.messageInput?.focus());
                },
                handleAttachment(event) {
                    const file = event.target.files?.[0];
                    if (!file) return;
                    this.attachment = {
                        name: file.name,
                        type: file.type || 'File',
                        size: this.formatBytes(file.size),
                    };
                },
                clearAttachment() {
                    this.attachment = { name: '', size: '', type: '' };
                    if (this.$refs.attachmentInput) {
                        this.$refs.attachmentInput.value = '';
                    }
                },
                formatBytes(bytes) {
                    if (!bytes) return '0 B';
                    const units = ['B', 'KB', 'MB'];
                    const index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
                    return `${(bytes / Math.pow(1024, index)).toFixed(index === 0 ? 0 : 1)} ${units[index]}`;
                },
            };
        };
    </script>
@endonce

<div
    x-data="messageCenter({
        conversations: {{ \Illuminate\Support\Js::from($threads->values()->all()) }},
        counts: {{ \Illuminate\Support\Js::from($categoryCounts) }},
        quickReplies: {{ \Illuminate\Support\Js::from([
            'Is the room still available?',
            'Can I schedule a viewing?',
            'Is WiFi included?',
            'Thank you',
            'What documents are required?',
            'How much is the deposit?',
        ]) }}
    })"
    x-init="init()"
    class="space-y-4"
>
    <x-user.page-header
        eyebrow="Inbox"
        title="Messages"
        subtitle="Communicate with boarding house owners, managers, and support."
    >
        <x-slot:actions>
            <span class="inline-flex items-center rounded-full bg-[#2563eb]/10 px-2 py-0.5 text-xs font-semibold text-[#2563eb] dark:bg-blue-400/10 dark:text-blue-300">
                <span x-text="unreadTotal"></span>
                <span class="ml-1">unread</span>
            </span>
            <span class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                <span x-text="conversations.length"></span>
                <span class="ml-1">conversations</span>
            </span>
            <button type="button" @click="mobileListOpen = true" class="inline-flex items-center gap-2 rounded-xl bg-[#2563eb] px-3 py-2 text-xs font-semibold text-white shadow-sm lg:hidden">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75h10.5m-10.5 4.5h10.5M4.875 9.75h.008v.008h-.008V9.75Zm0 4.5h.008v.008h-.008v-.008Z" />
                </svg>
                Conversations
            </button>
        </x-slot:actions>
    </x-user.page-header>

    <div class="grid gap-4 lg:grid-cols-[minmax(270px,0.78fr)_minmax(0,1.35fr)] xl:grid-cols-[minmax(280px,0.78fr)_minmax(0,1.35fr)_minmax(250px,0.72fr)]">
        <aside class="hidden min-h-[660px] overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:flex lg:flex-col">
            @include('user.messages-panel', ['mobile' => false])
        </aside>

        <section class="min-h-[660px] overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <template x-if="active">
                <div class="flex h-full min-h-[660px] flex-col">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-4 py-3 dark:border-slate-800">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="relative shrink-0">
                                <img :src="active.avatar" :alt="active.owner_name" class="h-10 w-10 rounded-full border border-slate-200 object-cover dark:border-slate-700">
                                <span x-show="active.online" class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white bg-emerald-500 dark:border-slate-900"></span>
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-950 dark:text-white" x-text="active.owner_name"></p>
                                <div class="mt-0.5 flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                    <span x-text="active.owner_role"></span>
                                    <span class="hidden sm:inline">-</span>
                                    <span x-text="active.online ? 'Online' : 'Away'"></span>
                                    <span class="hidden sm:inline">-</span>
                                    <span x-text="active.response_time"></span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <a :href="active.details_url" class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-200 px-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">View Listing</a>
                            <a :href="active.reservation_url" class="hidden h-8 items-center justify-center rounded-lg border border-slate-200 px-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800 sm:inline-flex">View Reservation</a>
                            <a :href="active.profile_url" class="hidden h-8 items-center justify-center rounded-lg border border-slate-200 px-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800 sm:inline-flex">View Profile</a>
                            <button type="button" @click="progressOpen = ! progressOpen" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 xl:hidden">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5M3.75 12h16.5M3.75 18.75h16.5" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-800">
                        <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-800/40 sm:flex-row sm:items-center">
                            <img :src="active.house_image" :alt="active.property" class="h-20 w-full rounded-lg border border-slate-200 object-cover dark:border-slate-700 sm:w-28">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-950 dark:text-white" x-text="active.property"></p>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400" x-text="active.location"></p>
                                    </div>
                                    <span class="inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700 ring-1 ring-amber-200 dark:bg-amber-400/10 dark:text-amber-300 dark:ring-amber-400/20" x-text="active.booking_status"></span>
                                </div>
                                <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-600 dark:text-slate-300">
                                    <span class="rounded-full bg-white px-2 py-1 ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-700" x-text="active.room_type"></span>
                                    <span class="rounded-full bg-white px-2 py-1 ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-700" x-text="active.monthly_rent"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div x-show="progressOpen" class="border-b border-slate-200 px-4 py-3 dark:border-slate-800 xl:hidden">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-800/40">
                            <p class="text-xs font-semibold text-slate-950 dark:text-white">Booking Progress</p>
                            <div class="mt-3 grid gap-2 sm:grid-cols-5">
                                <template x-for="step in active.progress" :key="step.label">
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[10px] font-bold"
                                            :class="step.state === 'done' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300' : (step.state === 'current' ? 'bg-[#2563eb] text-white' : 'bg-slate-200 text-slate-500 dark:bg-slate-700 dark:text-slate-300')">
                                            <span x-show="step.state === 'done'" aria-hidden="true">&check;</span>
                                            <span x-show="step.state === 'current'">...</span>
                                        </span>
                                        <span class="text-slate-600 dark:text-slate-300" x-text="step.label"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="min-h-0 flex-1 overflow-y-auto bg-slate-50/70 px-4 py-4 dark:bg-slate-950/30">
                        <div class="mx-auto max-w-3xl space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="h-px flex-1 bg-slate-200 dark:bg-slate-800"></div>
                                <span class="text-[11px] font-semibold text-slate-400">Today</span>
                                <div class="h-px flex-1 bg-slate-200 dark:bg-slate-800"></div>
                            </div>

                            <div class="flex justify-start">
                                <div class="max-w-[80%] rounded-2xl rounded-bl-md bg-white px-4 py-2.5 text-sm leading-6 text-slate-700 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-800">
                                    <p x-text="'Hi, this is ' + active.owner_name + '. Thanks for reaching out about ' + active.property + '.'"></p>
                                    <p class="mt-1 text-[11px] text-slate-400" x-text="active.time_full"></p>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <div class="max-w-[80%] rounded-2xl rounded-br-md bg-[#2563eb] px-4 py-2.5 text-sm leading-6 text-white shadow-sm">
                                    <p x-text="active.message"></p>
                                    <p class="mt-1 text-right text-[11px] text-blue-100">Delivered</p>
                                </div>
                            </div>

                            <div class="rounded-xl border border-blue-100 bg-blue-50 p-3 dark:border-blue-400/20 dark:bg-blue-400/10">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-blue-900 dark:text-blue-100" x-text="active.system.title"></p>
                                        <p class="mt-1 text-xs text-blue-700 dark:text-blue-200" x-text="active.system.body"></p>
                                        <p class="mt-0.5 text-xs text-blue-600/80 dark:text-blue-200/80" x-text="active.system.meta"></p>
                                    </div>
                                    <a :href="active.system.url" class="inline-flex h-8 items-center justify-center rounded-lg bg-[#2563eb] px-3 text-xs font-semibold text-white shadow-sm" x-text="active.system.button"></a>
                                </div>
                            </div>

                            <div class="flex justify-start items-end gap-2">
                                <img :src="active.avatar" :alt="active.owner_name" class="h-7 w-7 rounded-full object-cover">
                                <div class="max-w-[80%] rounded-2xl rounded-bl-md bg-white px-4 py-2.5 text-sm leading-6 text-slate-700 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-800">
                                    <p>Thank you for your inquiry. I can help with viewing schedules, payment details, and move-in requirements.</p>
                                    <p class="mt-1 text-[11px] text-slate-400" x-text="active.time_full"></p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 text-xs text-slate-400">
                                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                <span x-text="active.owner_name + ' is typing...'"></span>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-200 bg-white px-4 py-3 dark:border-slate-800 dark:bg-slate-900">
                        <div class="mb-3 flex gap-2 overflow-x-auto pb-1">
                            <template x-for="reply in quickReplies" :key="reply">
                                <button type="button" @click="useQuickReply(reply)" class="shrink-0 rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-[#2563eb] hover:text-[#2563eb] dark:border-slate-700 dark:text-slate-300">
                                    <span x-text="reply"></span>
                                </button>
                            </template>
                        </div>

                        <template x-if="attachment.name">
                            <div class="mb-3 flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs dark:border-slate-800 dark:bg-slate-800/40">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-700 dark:text-slate-200" x-text="attachment.name"></p>
                                    <p class="text-slate-500 dark:text-slate-400" x-text="attachment.size"></p>
                                </div>
                                <button type="button" @click="clearAttachment()" class="font-semibold text-red-600 dark:text-red-300">Remove</button>
                            </div>
                        </template>

                        <div x-show="emojiOpen" x-cloak @click.outside="emojiOpen = false" class="mb-3 flex w-fit gap-1 rounded-xl border border-slate-200 bg-white p-2 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                            <template x-for="emoji in ['🙂', '👍', '🙏', '📅', '🏠', '💬']" :key="emoji">
                                <button type="button" @click="addEmoji(emoji)" class="flex h-8 w-8 items-center justify-center rounded-lg text-base transition hover:bg-slate-100 dark:hover:bg-slate-800">
                                    <span x-text="emoji"></span>
                                </button>
                            </template>
                        </div>

                        <form method="POST" action="{{ route('user.messages.store') }}" enctype="multipart/form-data" class="flex items-end gap-2">
                            @csrf
                            <input type="hidden" name="boarding_house_id" :value="active.house_id">
                            <input x-ref="attachmentInput" type="file" name="attachment" class="sr-only" accept="image/*,.pdf,.doc,.docx" @change="handleAttachment($event)">
                            <button type="button" @click="emojiOpen = ! emojiOpen" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                                <span class="text-sm font-bold">:)</span>
                            </button>
                            <button type="button" @click="$refs.attachmentInput.click()" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94a3 3 0 1 1 4.243 4.243L8.552 18.32a1.5 1.5 0 1 1-2.121-2.121l7.81-7.81" />
                                </svg>
                            </button>
                            <button type="button" @click="$refs.attachmentInput.click()" class="hidden h-9 shrink-0 items-center justify-center rounded-lg border border-slate-200 px-2.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 sm:inline-flex">Receipt</button>
                            <button type="button" @click="shareLocation()" class="hidden h-9 shrink-0 items-center justify-center rounded-lg border border-slate-200 px-2.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 md:inline-flex">Location</button>
                            <textarea
                                x-model="messageBody"
                                x-ref="messageInput"
                                name="message"
                                required
                                rows="1"
                                @keydown.enter.exact.prevent="$refs.sendButton.click()"
                                class="max-h-32 min-h-9 flex-1 resize-none rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#2563eb] focus:bg-white focus:ring-2 focus:ring-[#2563eb]/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                                placeholder="Type your message..."
                            ></textarea>
                            <button x-ref="sendButton" type="submit" :disabled="!active?.house_id" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#2563eb] text-white shadow-sm transition hover:bg-[#1d4ed8] disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500 dark:disabled:bg-slate-700 dark:disabled:text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.126A59.77 59.77 0 0 1 21.485 12 59.77 59.77 0 0 1 3.27 20.876L6 12Zm0 0h7.5" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </template>

            <template x-if="!active">
                <div class="flex min-h-[660px] items-center justify-center p-8 text-center">
                    <div>
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-xl bg-[#2563eb]/10 text-[#2563eb] dark:bg-blue-400/10 dark:text-blue-300">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm3.75 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm3.75 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12c0 4.556-4.03 8.25-9 8.25a9.77 9.77 0 0 1-2.555-.337 5.972 5.972 0 0 1-4.035 1.057 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                            </svg>
                        </div>
                        <h2 class="mt-4 text-base font-semibold text-slate-950 dark:text-white">Select a Conversation</h2>
                        <p class="mt-2 max-w-sm text-sm text-slate-500 dark:text-slate-400">Choose a conversation to start messaging with a boarding house owner.</p>
                    </div>
                </div>
            </template>
        </section>

        <aside class="hidden min-h-[660px] rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 xl:block">
            <template x-if="active">
                <div>
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Booking Progress</h2>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Track the conversation outcome.</p>
                        </div>
                        <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700 ring-1 ring-amber-200 dark:bg-amber-400/10 dark:text-amber-300 dark:ring-amber-400/20" x-text="active.booking_status"></span>
                    </div>

                    <div class="mt-5 space-y-4">
                        <template x-for="step in active.progress" :key="step.label">
                            <div class="flex gap-3">
                                <div class="flex flex-col items-center">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold"
                                        :class="step.state === 'done' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300' : (step.state === 'current' ? 'bg-[#2563eb] text-white' : 'bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500')">
                                        <span x-show="step.state === 'done'" aria-hidden="true">&check;</span>
                                        <span x-show="step.state === 'current'">...</span>
                                    </span>
                                    <span class="mt-1 h-7 w-px bg-slate-200 last:hidden dark:bg-slate-800"></span>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-100" x-text="step.label"></p>
                                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400" x-text="step.state === 'done' ? 'Completed' : (step.state === 'current' ? 'In progress' : 'Waiting')"></p>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-800/40">
                        <h3 class="text-xs font-semibold text-slate-950 dark:text-white">Quick Actions</h3>
                        <div class="mt-3 grid gap-2">
                            <a :href="active.details_url" class="inline-flex h-8 items-center justify-center rounded-lg bg-white px-3 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50 dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-700">View Listing</a>
                            <a :href="active.reservation_url" class="inline-flex h-8 items-center justify-center rounded-lg bg-white px-3 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50 dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-700">View Reservation</a>
                            <a :href="active.payments_url" class="inline-flex h-8 items-center justify-center rounded-lg bg-[#2563eb] px-3 text-xs font-semibold text-white transition hover:bg-[#1d4ed8]">Go to Payments</a>
                        </div>
                    </div>
                </div>
            </template>
        </aside>
    </div>

    <div x-show="mobileListOpen" x-cloak class="fixed inset-0 z-50 lg:hidden">
        <div class="absolute inset-0 bg-slate-950/60" @click="mobileListOpen = false"></div>
        <aside class="absolute inset-y-0 left-0 flex w-full max-w-sm flex-col overflow-hidden bg-white shadow-xl dark:bg-slate-900">
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 dark:border-slate-800">
                <p class="text-sm font-semibold text-slate-950 dark:text-white">Conversations</p>
                <button type="button" @click="mobileListOpen = false" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            @include('user.messages-panel', ['mobile' => true])
        </aside>
    </div>
</div>

</x-user.shell>
</x-layouts.dashboard>
