@php
    $showPageHeader = $showPageHeader ?? true;

    $iconPaths = [
        'bell' => '<path d="M15 17H9m9-5a6 6 0 1 0-12 0c0 3-1.5 4-2 5h16c-.5-1-2-2-2-5Z"/><path d="M10 20a2 2 0 0 0 4 0"/>',
        'question' => '<path d="M9.5 9a2.5 2.5 0 1 1 4 2c-1 .7-1.5 1.2-1.5 2.5"/><path d="M12 17h.01"/><circle cx="12" cy="12" r="9"/>',
        'mail' => '<path d="M4 5h16v14H4z"/><path d="m4 7 8 6 8-6"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'check' => '<circle cx="12" cy="12" r="9"/><path d="m8.5 12.2 2.3 2.3 4.7-5"/>',
        'x-circle' => '<circle cx="12" cy="12" r="9"/><path d="M9 9l6 6M15 9l-6 6"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'download' => '<path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/>',
        'reply' => '<path d="m10 9-5 5 5 5"/><path d="M5 14h10a5 5 0 0 0 5-5V7"/>',
        'eye' => '<path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/>',
        'more' => '<path d="M12 12h.01M19 12h.01M5 12h.01"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'chevron-left' => '<path d="m15 18-6-6 6-6"/>',
        'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
        'x' => '<path d="M6 6l12 12M18 6 6 18"/>',
        'phone' => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19 19 0 0 1-8.3-3 18.7 18.7 0 0 1-5.8-5.8 19 19 0 0 1-3-8.3A2 2 0 0 1 4.7 2h3a2 2 0 0 1 2 1.7l.4 2.7a2 2 0 0 1-.6 1.8L8.2 9.5a15 15 0 0 0 6.3 6.3l1.3-1.3a2 2 0 0 1 1.8-.6l2.7.4a2 2 0 0 1 1.7 2Z"/>',
        'calendar' => '<path d="M7 3v4M17 3v4"/><path d="M4 8h16"/><path d="M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Z"/>',
        'send' => '<path d="m4 12 16-8-5 16-3-7-8-1Z"/><path d="m12 13 8-9"/>',
        'paperclip' => '<path d="m21.4 11.6-8.5 8.5a6 6 0 1 1-8.5-8.5l9.2-9.2a4 4 0 1 1 5.7 5.7l-9.2 9.2a2 2 0 1 1-2.8-2.8l8.5-8.5"/>',
        'smile' => '<circle cx="12" cy="12" r="9"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><path d="M9 9h.01M15 9h.01"/>',
        'note' => '<path d="M6 3h9l3 3v15H6z"/><path d="M15 3v4h4"/><path d="M9 12h6M9 16h6"/>',
    ];

    $uiIcon = fn ($name, $class = 'h-5 w-5') => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8">'.$iconPaths[$name].'</svg>';

    $statusClasses = [
        'New' => 'bg-blue-100 text-blue-700 ring-blue-200',
        'Pending' => 'bg-amber-100 text-amber-700 ring-amber-200',
        'Accepted' => 'bg-violet-100 text-violet-700 ring-violet-200',
        'Confirmed' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
        'Declined' => 'bg-rose-100 text-rose-700 ring-rose-200',
    ];

    $stats = [
        ['label' => 'New Inquiries', 'value' => '6', 'description' => 'New requests', 'icon' => 'mail', 'iconClass' => 'bg-blue-100 text-blue-600 ring-blue-200'],
        ['label' => 'Pending Inquiries', 'value' => '4', 'description' => 'Awaiting response', 'icon' => 'clock', 'iconClass' => 'bg-amber-100 text-amber-600 ring-amber-200'],
        ['label' => 'Confirmed Inquiries', 'value' => '8', 'description' => 'Confirmed bookings', 'icon' => 'check', 'iconClass' => 'bg-emerald-100 text-emerald-600 ring-emerald-200'],
        ['label' => 'Declined Inquiries', 'value' => '3', 'description' => 'Not accepted', 'icon' => 'x-circle', 'iconClass' => 'bg-rose-100 text-rose-600 ring-rose-200'],
    ];

    $inquiryRows = [
        ['name' => 'Maria Santos', 'phone' => '0917 123 4567', 'email' => 'maria.santos@gmail.com', 'room' => 'Single Room, Room A-101', 'moveIn' => 'May 20, 2026', 'status' => 'New', 'last' => '10 minutes ago', 'initials' => 'MS'],
        ['name' => 'John Reyes', 'phone' => '0921 987 6543', 'email' => 'john.reyes@email.com', 'room' => 'Double Room, Room B-204', 'moveIn' => 'May 25, 2026', 'status' => 'Pending', 'last' => '2 hours ago', 'initials' => 'JR'],
        ['name' => 'Angelica Gomez', 'phone' => '0906 555 3322', 'email' => 'angelica.gomez@gmail.com', 'room' => 'Bed Space, Room C-110', 'moveIn' => 'May 18, 2026', 'status' => 'Confirmed', 'last' => '1 day ago', 'initials' => 'AG'],
        ['name' => 'Mark Dela Cruz', 'phone' => '0915 444 2211', 'email' => 'mark.delacruz@email.com', 'room' => 'Single Room, Room A-102', 'moveIn' => 'May 22, 2026', 'status' => 'Accepted', 'last' => '1 day ago', 'initials' => 'MD'],
        ['name' => 'Reynalyn Cruz', 'phone' => '0932 888 7766', 'email' => 'reynalyn.cruz@gmail.com', 'room' => 'Shared Room, Room D-301', 'moveIn' => 'May 30, 2026', 'status' => 'Declined', 'last' => '2 days ago', 'initials' => 'RC'],
    ];

    $selectedInquiry = $inquiryRows[0];
@endphp

<div
    id="inquiries-management"
    x-data="{
        modalType: null,
        selectedInquiry: null,
        search: '',
        status: 'All Status',
        statusOpen: false,
        matches(name, email, phone, status) {
            const query = this.search.toLowerCase().trim();
            const haystack = `${name} ${email} ${phone}`.toLowerCase();
            return (this.status === 'All Status' || this.status === status) && (! query || haystack.includes(query));
        },
        openInquiryModal(type, inquiry) {
            this.modalType = type;
            this.selectedInquiry = inquiry;
        },
        closeInquiryModal() {
            this.modalType = null;
        },
        badgeClass(status) {
            return {
                'New': 'bg-blue-100 text-blue-700 ring-blue-200',
                'Pending': 'bg-amber-100 text-amber-700 ring-amber-200',
                'Accepted': 'bg-violet-100 text-violet-700 ring-violet-200',
                'Confirmed': 'bg-emerald-100 text-emerald-700 ring-emerald-200',
                'Declined': 'bg-rose-100 text-rose-700 ring-rose-200',
            }[status] || 'bg-slate-100 text-slate-700 ring-slate-200';
        }
    }"
    @keydown.escape.window="closeInquiryModal()"
    class="space-y-6"
>
    @if ($showPageHeader)
        <section class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Inquiries</h1>
                <p class="mt-1 text-sm text-slate-600 sm:text-base">Manage student inquiries and booking requests.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="button" class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50" aria-label="Notifications">
                    {!! $uiIcon('bell', 'h-5 w-5') !!}
                    <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[11px] font-bold text-white">3</span>
                </button>
                <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50" aria-label="Help">
                    {!! $uiIcon('question', 'h-5 w-5') !!}
                </button>
                <button type="button" class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-left shadow-sm">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-700 text-xs font-bold text-white">JD</span>
                    <span class="leading-tight">
                        <span class="block text-sm font-semibold text-slate-950">Juan Dela Cruz</span>
                        <span class="block text-xs text-slate-500">Owner</span>
                    </span>
                    <span class="text-slate-500">{!! $uiIcon('chevron-down', 'h-4 w-4') !!}</span>
                </button>
            </div>
        </section>
    @endif

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $stat)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full ring-1 {{ $stat['iconClass'] }}">
                        {!! $uiIcon($stat['icon'], 'h-7 w-7') !!}
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-600">{{ $stat['label'] }}</p>
                        <p class="mt-1 text-2xl font-bold text-slate-950">{{ $stat['value'] }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $stat['description'] }}</p>
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    <section class="grid gap-6">
        <div class="min-w-0 rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-200 p-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="grid gap-3 sm:grid-cols-[minmax(260px,1fr)_170px] lg:min-w-[560px]">
                    <label class="relative block">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">{!! $uiIcon('search', 'h-5 w-5') !!}</span>
                        <input x-model.debounce.150ms="search" type="search" placeholder="Search by student name, email or contact..." class="h-11 w-full rounded-xl border-slate-200 bg-white pl-11 pr-4 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500">
                    </label>

                    <div class="relative" @click.outside="statusOpen = false">
                        <button type="button" @click="statusOpen = ! statusOpen" class="flex h-11 w-full items-center justify-between rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-800 shadow-sm transition hover:bg-slate-50">
                            <span x-text="status">All Status</span>
                            <span class="text-slate-500">{!! $uiIcon('chevron-down', 'h-4 w-4') !!}</span>
                        </button>
                        <div x-show="statusOpen" x-transition style="display: none;" class="absolute left-0 top-[calc(100%+0.35rem)] z-30 w-full overflow-hidden rounded-xl border border-slate-200 bg-white py-2 shadow-xl">
                            @foreach (['All Status', 'New', 'Pending', 'Accepted', 'Declined', 'Confirmed'] as $option)
                                <button type="button" @click="status = @js($option); statusOpen = false" class="flex w-full items-center justify-between px-4 py-2.5 text-left text-sm text-slate-800 transition hover:bg-slate-50">
                                    <span>{{ $option }}</span>
                                    <span x-show="status === @js($option)" class="text-blue-700">{!! $uiIcon('check', 'h-4 w-4') !!}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <button type="button" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                    {!! $uiIcon('download', 'h-4 w-4') !!}
                    <span>Export</span>
                </button>
            </div>

            <div class="hidden overflow-x-auto lg:block">
                <table class="min-w-[980px] w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Student</th>
                            <th class="px-5 py-4">Requested Room</th>
                            <th class="px-5 py-4">Move-in Date</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Last Message</th>
                            <th class="px-5 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach ($inquiryRows as $row)
                            <tr x-show="matches(@js($row['name']), @js($row['email']), @js($row['phone']), @js($row['status']))" class="transition hover:bg-slate-50/80 {{ $loop->first ? 'bg-blue-50/60 shadow-[inset_4px_0_0_#2563eb]' : '' }}">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700">{{ $row['initials'] }}</span>
                                        <span class="min-w-0">
                                            <span class="block font-semibold text-slate-950">{{ $row['name'] }}</span>
                                            <span class="block text-xs text-slate-500">{{ $row['phone'] }}</span>
                                            <span class="block text-xs text-slate-500">{{ $row['email'] }}</span>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-slate-700">{{ $row['room'] }}</td>
                                <td class="px-5 py-4 text-slate-700">{{ $row['moveIn'] }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-lg px-3 py-1 text-xs font-bold ring-1 {{ $statusClasses[$row['status']] }}">{{ $row['status'] }}</span>
                                </td>
                                <td class="px-5 py-4 text-slate-700">{{ $row['last'] }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" @click="openInquiryModal('reply', @js($row))" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" title="Reply to inquiry">{!! $uiIcon('reply', 'h-4 w-4') !!}</button>
                                        <button type="button" @click="openInquiryModal('details', @js($row))" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" title="View inquiry details">{!! $uiIcon('eye', 'h-4 w-4') !!}</button>
                                        <button type="button" @click="openInquiryModal('actions', @js($row))" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 transition hover:border-slate-300 hover:bg-slate-50" title="More actions">{!! $uiIcon('more', 'h-4 w-4') !!}</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="grid gap-4 p-4 lg:hidden">
                @foreach ($inquiryRows as $row)
                    <article x-show="matches(@js($row['name']), @js($row['email']), @js($row['phone']), @js($row['status']))" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700">{{ $row['initials'] }}</span>
                                <div class="min-w-0">
                                    <h3 class="font-semibold text-slate-950">{{ $row['name'] }}</h3>
                                    <p class="text-sm text-slate-600">{{ $row['room'] }}</p>
                                </div>
                            </div>
                            <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-bold ring-1 {{ $statusClasses[$row['status']] }}">{{ $row['status'] }}</span>
                        </div>
                        <div class="mt-3 grid gap-1 text-sm text-slate-600">
                            <p>{{ $row['phone'] }} | {{ $row['email'] }}</p>
                            <p>Move-in: {{ $row['moveIn'] }}</p>
                            <p>Last message: {{ $row['last'] }}</p>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <button type="button" @click="openInquiryModal('reply', @js($row))" class="inline-flex h-9 items-center gap-2 rounded-lg border border-slate-200 px-3 text-sm font-semibold text-slate-700">Reply</button>
                            <button type="button" @click="openInquiryModal('details', @js($row))" class="inline-flex h-9 items-center gap-2 rounded-lg border border-slate-200 px-3 text-sm font-semibold text-slate-700">Details</button>
                            <button type="button" @click="openInquiryModal('actions', @js($row))" class="inline-flex h-9 items-center gap-2 rounded-lg border border-slate-200 px-3 text-sm font-semibold text-slate-700">More</button>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="flex flex-col gap-4 border-t border-slate-200 p-4 lg:flex-row lg:items-center lg:justify-between">
                <p class="text-sm text-slate-600">Showing 1 to 5 of 25 inquiries</p>
                <div class="flex flex-wrap items-center gap-3">
                    <nav class="flex items-center gap-2" aria-label="Pagination">
                        <button type="button" class="inline-flex h-9 items-center gap-1 rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-600 hover:bg-slate-50">{!! $uiIcon('chevron-left', 'h-4 w-4') !!}<span class="hidden sm:inline">Previous</span></button>
                        <button type="button" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg bg-blue-700 px-3 text-sm font-bold text-white">1</button>
                        <button type="button" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-700 hover:bg-slate-50">2</button>
                        <button type="button" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-700 hover:bg-slate-50">3</button>
                        <button type="button" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-700 hover:bg-slate-50">5</button>
                        <button type="button" class="inline-flex h-9 items-center gap-1 rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-600 hover:bg-slate-50"><span class="hidden sm:inline">Next</span>{!! $uiIcon('chevron-right', 'h-4 w-4') !!}</button>
                    </nav>
                    <label class="relative block">
                        <select class="h-10 appearance-none rounded-xl border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option>5 / page</option>
                        </select>
                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-500">{!! $uiIcon('chevron-down', 'h-4 w-4') !!}</span>
                    </label>
                </div>
            </div>
        </div>

    </section>

    <div x-show="modalType" x-transition.opacity style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4" @click.self="closeInquiryModal()">
        <div class="max-h-[85vh] w-full overflow-hidden rounded-2xl bg-white shadow-2xl" :class="['accept','decline','confirm'].includes(modalType) ? 'max-w-lg' : 'max-w-4xl'">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-950" x-text="modalType === 'reply' ? 'Reply to Inquiry' : modalType === 'actions' ? 'Inquiry Actions' : modalType === 'notes' ? 'Internal Notes' : modalType === 'reservation' ? 'Convert to Reservation' : modalType === 'accept' ? 'Accept Inquiry?' : modalType === 'decline' ? 'Decline Inquiry?' : modalType === 'confirm' ? 'Mark as Confirmed?' : 'Inquiry Details'"></h2>
                    <p class="text-sm text-slate-500" x-text="selectedInquiry?.name"></p>
                </div>
                <button type="button" @click="closeInquiryModal()" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-900">{!! $uiIcon('x', 'h-5 w-5') !!}</button>
            </div>

            <div class="max-h-[calc(85vh-138px)] overflow-y-auto p-6">
                <div class="mb-5 flex flex-wrap items-center gap-3 text-sm">
                    <span class="inline-flex rounded-lg px-3 py-1 text-xs font-bold ring-1" :class="badgeClass(selectedInquiry?.status)" x-text="selectedInquiry?.status"></span>
                    <span class="inline-flex items-center gap-1 text-slate-600">{!! $uiIcon('phone', 'h-4 w-4') !!} <span x-text="selectedInquiry?.phone"></span></span>
                    <span class="inline-flex items-center gap-1 text-slate-600">{!! $uiIcon('mail', 'h-4 w-4') !!} <span x-text="selectedInquiry?.email"></span></span>
                </div>

                <div x-show="modalType === 'details' || modalType === 'actions'" class="space-y-5 text-sm">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        <div><dt class="font-semibold text-slate-700">Requested Room</dt><dd class="mt-1 text-slate-900" x-text="selectedInquiry?.room"></dd></div>
                        <div><dt class="font-semibold text-slate-700">Preferred Move-in Date</dt><dd class="mt-1 text-slate-900" x-text="selectedInquiry?.moveIn"></dd></div>
                        <div><dt class="font-semibold text-slate-700">Last Message</dt><dd class="mt-1 text-slate-900" x-text="selectedInquiry?.last"></dd></div>
                    </dl>
                    <div>
                        <p class="font-semibold text-slate-700">Message</p>
                        <p class="mt-1 rounded-2xl bg-slate-50 p-4 leading-6 text-slate-700">Good day! I would like to inquire if the selected room is still available for next school year. Thank you!</p>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-950">Conversation</h3>
                        <div class="mt-3 space-y-3">
                            <div class="rounded-2xl bg-slate-100 p-3">
                                <div class="flex items-center justify-between gap-3"><p class="font-semibold text-slate-900" x-text="selectedInquiry?.name"></p><p class="text-xs text-slate-500">Today, 9:30 AM</p></div>
                                <p class="mt-2 leading-6 text-slate-700">Good day! I would like to inquire if the room is still available for next school year. Thank you!</p>
                            </div>
                            <div class="ml-6 rounded-2xl bg-blue-50 p-3">
                                <div class="flex items-center justify-between gap-3"><p class="font-semibold text-blue-900">You</p><p class="text-xs text-blue-600">Today, 9:40 AM</p></div>
                                <p class="mt-2 leading-6 text-blue-900">Good day! Yes, the room is still available. Would you like me to reserve it for you?</p>
                            </div>
                        </div>
                    </div>
                    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                        <button type="button" @click="modalType = 'accept'" class="rounded-xl bg-emerald-600 px-3 py-2 text-sm font-bold text-white hover:bg-emerald-700">Accept Inquiry</button>
                        <button type="button" @click="modalType = 'decline'" class="rounded-xl bg-rose-600 px-3 py-2 text-sm font-bold text-white hover:bg-rose-700">Decline Inquiry</button>
                        <button type="button" @click="modalType = 'confirm'" class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-bold text-emerald-700 hover:bg-emerald-100">Mark Confirmed</button>
                        <button type="button" @click="modalType = 'reservation'" class="rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-bold text-blue-700 hover:bg-blue-100">Convert</button>
                    </div>
                </div>

                <div x-show="modalType === 'reply'" class="space-y-4">
                    <textarea rows="6" placeholder="Type your reply..." class="w-full resize-none rounded-2xl border-slate-200 text-sm text-slate-800 placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500"></textarea>
                    <div class="flex items-center gap-2 text-slate-500">
                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg hover:bg-slate-100">{!! $uiIcon('paperclip', 'h-4 w-4') !!}</button>
                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg hover:bg-slate-100">{!! $uiIcon('smile', 'h-4 w-4') !!}</button>
                    </div>
                </div>

                <div x-show="modalType === 'notes'" class="space-y-3">
                    <textarea rows="5" class="w-full resize-none rounded-xl border-slate-200 bg-white text-sm text-slate-800 focus:border-blue-500 focus:ring-blue-500">Student is interested. Follow up on May 17. Prefers morning viewing.</textarea>
                    <p class="text-xs text-slate-500">Saved by you, 3 hours ago</p>
                </div>

                <div x-show="modalType === 'reservation'" class="grid gap-4 sm:grid-cols-2">
                    <label><span class="text-sm font-semibold text-slate-700">Student</span><input type="text" class="mt-1 h-11 w-full rounded-xl border-slate-200 text-sm" :value="selectedInquiry?.name"></label>
                    <label><span class="text-sm font-semibold text-slate-700">Room</span><input type="text" class="mt-1 h-11 w-full rounded-xl border-slate-200 text-sm" :value="selectedInquiry?.room"></label>
                    <label><span class="text-sm font-semibold text-slate-700">Move-in Date</span><input type="text" class="mt-1 h-11 w-full rounded-xl border-slate-200 text-sm" :value="selectedInquiry?.moveIn"></label>
                    <label><span class="text-sm font-semibold text-slate-700">Reservation Status</span><select class="mt-1 h-11 w-full rounded-xl border-slate-200 text-sm"><option>Reserved</option><option>Pending Payment</option></select></label>
                </div>

                <div x-show="['accept','decline','confirm'].includes(modalType)" class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-700">
                    <p x-text="modalType === 'accept' ? 'Accept this inquiry and notify the student?' : modalType === 'decline' ? 'Decline this inquiry? You can still keep the conversation history.' : 'Mark this inquiry as confirmed?'"></p>
                </div>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-200 px-6 py-4 sm:flex-row sm:justify-end">
                <button type="button" @click="closeInquiryModal()" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                <button x-show="modalType === 'reply'" type="button" @click="closeInquiryModal()" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800">{!! $uiIcon('send', 'h-4 w-4') !!} Send Reply</button>
                <button x-show="modalType === 'notes'" type="button" @click="closeInquiryModal()" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800">Save Note</button>
                <button x-show="modalType === 'accept'" type="button" @click="closeInquiryModal()" class="inline-flex h-10 items-center justify-center rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700">Accept Inquiry</button>
                <button x-show="modalType === 'decline'" type="button" @click="closeInquiryModal()" class="inline-flex h-10 items-center justify-center rounded-xl bg-rose-600 px-4 text-sm font-semibold text-white hover:bg-rose-700">Decline Inquiry</button>
                <button x-show="modalType === 'confirm'" type="button" @click="closeInquiryModal()" class="inline-flex h-10 items-center justify-center rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700">Mark Confirmed</button>
                <button x-show="modalType === 'reservation'" type="button" @click="closeInquiryModal()" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800">Convert to Reservation</button>
                <button x-show="modalType === 'details' || modalType === 'actions'" type="button" @click="modalType = 'reply'" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800">Reply</button>
                <button x-show="modalType === 'details' || modalType === 'actions'" type="button" @click="modalType = 'notes'" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Internal Notes</button>
            </div>
        </div>
    </div>
</div>
