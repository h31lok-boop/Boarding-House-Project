<x-layouts.dashboard>
<x-admin.shell>
    @php
        $supportOverview = collect($supportStats)->values();

        $popularCategories = [
            [
                'title' => 'Reservations',
                'description' => 'Approvals, cancellations, move-in timelines, and booking exceptions.',
                'icon' => 'reservations',
                'faq_index' => 0,
                'search' => 'reservations booking approval cancellation move-in calendar',
            ],
            [
                'title' => 'Payments',
                'description' => 'Payment posting, receipt verification, billing issues, and transaction follow-ups.',
                'icon' => 'payments',
                'faq_index' => 1,
                'search' => 'payments receipts billing transactions payout verification',
            ],
            [
                'title' => 'Tenants',
                'description' => 'Tenant records, active stays, move-outs, occupancy concerns, and profile support.',
                'icon' => 'tenants',
                'faq_index' => 2,
                'search' => 'tenants residents profiles occupancy move-out records',
            ],
            [
                'title' => 'Listings',
                'description' => 'Boarding house edits, room availability, amenities, pricing, and visibility.',
                'icon' => 'boarding-house',
                'faq_index' => 3,
                'search' => 'listings boarding house rooms availability amenities pricing',
            ],
            [
                'title' => 'Messages',
                'description' => 'Inbox organization, inquiry follow-ups, and communication response guidance.',
                'icon' => 'messages',
                'faq_index' => 4,
                'search' => 'messages inbox chat inquiries replies communication',
            ],
            [
                'title' => 'Account & Security',
                'description' => 'Workspace access, owner settings, profile updates, and security checkups.',
                'icon' => 'settings',
                'faq_index' => 5,
                'search' => 'account settings security profile password access',
            ],
        ];

        $faqItems = [
            [
                'category' => 'Reservations',
                'question' => 'How do I handle reservations waiting for approval?',
                'answer' => 'Open the reservation queue to review move-in dates, room details, and tenant notes. From there you can approve, update, or follow up before confirming the booking.',
                'search' => 'reservation pending approval queue booking tenant move-in',
            ],
            [
                'category' => 'Payments',
                'question' => 'Where should I review payment concerns and receipt verification?',
                'answer' => 'Use Payments for transaction history and the payment verification workspace for receipt review. That gives you the fastest path for unresolved balances or proof-of-payment issues.',
                'search' => 'payment receipt verification balance transaction proof of payment',
            ],
            [
                'category' => 'Tenants',
                'question' => 'How can I update tenant records or resolve occupancy mismatches?',
                'answer' => 'Open the tenant directory to review active stays, profile details, and housing records. If the issue affects a reservation or move-out, cross-check the reservation status before making changes.',
                'search' => 'tenant records occupancy profile active stay move-out directory',
            ],
            [
                'category' => 'Listings',
                'question' => 'What is the best place to update listing details or room availability?',
                'answer' => 'Go to Listings or Boarding Houses to manage property details, then update room-level availability and amenities from the room management views so inventory stays accurate.',
                'search' => 'listing details room availability boarding house amenities inventory',
            ],
            [
                'category' => 'Messages',
                'question' => 'How do I stay on top of messages and tenant inquiries?',
                'answer' => 'Use the messages inbox for conversation threads and keep notifications enabled for urgent follow-ups. Responding from one place helps prevent missed inquiries and duplicate replies.',
                'search' => 'messages inbox tenant inquiries replies notifications conversation',
            ],
            [
                'category' => 'Account & Security',
                'question' => 'Where can I update owner settings or review account security?',
                'answer' => 'Profile and security settings live in the account settings area. Review your contact details, password, and security preferences there to keep the owner workspace current.',
                'search' => 'account settings security password owner profile contact details',
            ],
            [
                'category' => 'Escalations',
                'question' => 'What should I include when I contact BoardMatch support?',
                'answer' => 'Share the affected workflow, property or tenant details, payment references if relevant, screenshots, and the outcome you expected. That context shortens triage time considerably.',
                'search' => 'support escalation screenshots payment reference property tenant issue',
            ],
        ];

        $quickActions = [
            [
                'title' => 'Open Reservation Queue',
                'description' => 'Review bookings that need approval, updates, or follow-up.',
                'href' => route('admin.reservations'),
                'icon' => 'reservations',
                'tone' => 'blue',
            ],
            [
                'title' => 'Check Payments',
                'description' => 'Inspect transactions, receipts, and balance-related concerns.',
                'href' => route('admin.payments'),
                'icon' => 'payments',
                'tone' => 'amber',
            ],
            [
                'title' => 'Manage Listings',
                'description' => 'Update property visibility, amenities, and room availability.',
                'href' => route('admin.boarding-houses'),
                'icon' => 'boarding-house',
                'tone' => 'emerald',
            ],
            [
                'title' => 'Review Messages',
                'description' => 'Jump into owner conversations and inquiry follow-ups.',
                'href' => route('admin.messages'),
                'icon' => 'messages',
                'tone' => 'slate',
            ],
        ];

        $resourceLinks = [
            [
                'title' => 'Tenant Directory',
                'description' => 'Check profiles, active stays, and occupancy details.',
                'href' => route('admin.tenants.index'),
                'icon' => 'tenants',
            ],
            [
                'title' => 'Payment Verification',
                'description' => 'Review submitted receipts and approval decisions.',
                'href' => route('admin.payment-receipts.index'),
                'icon' => 'payments',
            ],
            [
                'title' => 'Owner Reports',
                'description' => 'Open reports for occupancy, revenue, and performance trends.',
                'href' => route('admin.reports.index'),
                'icon' => 'reports',
            ],
            [
                'title' => 'Notifications',
                'description' => 'View system alerts, reminders, and operational updates.',
                'href' => route('admin.notifications.index'),
                'icon' => 'notifications',
            ],
            [
                'title' => 'Workspace Search',
                'description' => 'Search tenants, listings, reservations, and payments quickly.',
                'href' => route('admin.search', ['query' => 'support']),
                'icon' => 'search',
            ],
            [
                'title' => 'Account Settings',
                'description' => 'Update profile details and security preferences.',
                'href' => route('admin.settings.index'),
                'icon' => 'settings',
            ],
        ];

        $supportChips = ['Reservations', 'Payments', 'Tenants', 'Listings', 'Messages', 'System status'];

        $contactWidgets = [
            [
                'label' => 'Email Support',
                'value' => 'support@boardmatch.ph',
                'description' => 'Best for blocking issues that need screenshots, references, or detailed context.',
                'href' => 'mailto:support@boardmatch.ph?subject=BoardMatch%20Owner%20Portal%20Support',
            ],
            [
                'label' => 'First Response SLA',
                'value' => 'Within 2 business hours',
                'description' => 'Priority owner requests are triaged during active support coverage windows.',
            ],
            [
                'label' => 'Support Hours',
                'value' => 'Monday to Friday, 8:00 AM to 6:00 PM',
                'description' => 'BoardMatch owner operations support follows local business hours.',
            ],
        ];

        $supportChecklist = [
            'Include the reservation, payment, property, or tenant involved.',
            'Add screenshots when the issue involves unexpected UI behavior.',
            'Note what you expected to happen and what happened instead.',
            'Mention deadlines when move-ins, payouts, or listing visibility are affected.',
        ];

        $toneClasses = static function (string $tone): array {
            return match ($tone) {
                'emerald' => [
                    'icon' => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
                    'pill' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
                    'border' => 'border-emerald-100',
                ],
                'amber' => [
                    'icon' => 'bg-amber-50 text-amber-600 ring-amber-100',
                    'pill' => 'bg-amber-50 text-amber-700 ring-amber-100',
                    'border' => 'border-amber-100',
                ],
                'slate' => [
                    'icon' => 'bg-slate-100 text-slate-600 ring-slate-200',
                    'pill' => 'bg-slate-100 text-slate-700 ring-slate-200',
                    'border' => 'border-slate-200',
                ],
                default => [
                    'icon' => 'bg-blue-50 text-blue-600 ring-blue-100',
                    'pill' => 'bg-blue-50 text-blue-700 ring-blue-100',
                    'border' => 'border-blue-100',
                ],
            };
        };

        $statusToneClasses = static function (?string $status): array {
            $normalized = strtolower(trim((string) $status));

            return match (true) {
                in_array($normalized, ['resolved', 'closed', 'completed'], true) => [
                    'pill' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
                    'dot' => 'bg-emerald-500',
                ],
                in_array($normalized, ['in progress', 'processing', 'reviewing', 'open'], true) => [
                    'pill' => 'bg-blue-50 text-blue-700 ring-blue-100',
                    'dot' => 'bg-blue-500',
                ],
                in_array($normalized, ['pending', 'pending review'], true) => [
                    'pill' => 'bg-amber-50 text-amber-700 ring-amber-100',
                    'dot' => 'bg-amber-500',
                ],
                in_array($normalized, ['rejected', 'cancelled', 'failed'], true) => [
                    'pill' => 'bg-rose-50 text-rose-700 ring-rose-100',
                    'dot' => 'bg-rose-500',
                ],
                default => [
                    'pill' => 'bg-slate-100 text-slate-700 ring-slate-200',
                    'dot' => 'bg-slate-400',
                ],
            };
        };
    @endphp

    @once
        <script>
            function ownerSupportDashboard(config = {}) {
                return {
                    query: '',
                    openFaq: 0,
                    faqs: Array.isArray(config.faqs) ? config.faqs : [],
                    normalized(value) {
                        return (value || '').toString().toLowerCase().trim();
                    },
                    matchesSearch(text) {
                        const query = this.normalized(this.query);

                        if (! query) {
                            return true;
                        }

                        return this.normalized(text).includes(query);
                    },
                    faqVisible(faq) {
                        if (! faq) {
                            return false;
                        }

                        return this.matchesSearch([faq.category, faq.question, faq.answer, faq.search].join(' '));
                    },
                    filteredFaqCount() {
                        return this.faqs.filter((faq) => this.faqVisible(faq)).length;
                    },
                    openTopic(topic, index = 0) {
                        this.query = topic;
                        this.openFaq = index;
                        this.scrollTo('faq-section');
                    },
                    selectQuery(value, target = 'faq-section') {
                        this.query = value;
                        this.scrollTo(target);
                    },
                    clearSearch() {
                        this.query = '';
                    },
                    scrollTo(id) {
                        this.$nextTick(() => {
                            const element = document.getElementById(id);

                            if (element) {
                                element.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            }
                        });
                    },
                };
            }
        </script>
    @endonce

    <div x-data="ownerSupportDashboard({ faqs: @js($faqItems) })" class="space-y-3 text-slate-950">
        <section class="overflow-hidden rounded-[1.4rem] border border-slate-200 bg-[radial-gradient(circle_at_top_right,rgba(59,130,246,0.16),transparent_34%),linear-gradient(180deg,#ffffff_0%,#f8fbff_100%)] shadow-[0_18px_38px_rgba(15,23,42,0.06)]">
            <div class="grid gap-4 px-4 py-4 lg:grid-cols-[minmax(0,1.2fr)_minmax(300px,0.8fr)] xl:px-5 xl:py-5">
                <div class="min-w-0">
                    <div class="inline-flex items-center gap-3 rounded-2xl border border-blue-100 bg-white/90 px-3 py-2 shadow-sm shadow-blue-100/40">
                        <img src="{{ asset('images/boardmatch-mark.svg') }}" alt="" class="h-8 w-8 rounded-xl bg-blue-600 p-1.5">
                        <div class="leading-tight">
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-blue-600">BoardMatch Owner Portal</p>
                            <p class="text-[13px] font-semibold text-slate-700">Professional property management support</p>
                        </div>
                    </div>

                    <div class="mt-3 max-w-3xl">
                        <h1 class="text-[1.55rem] font-black tracking-tight text-slate-950 sm:text-[1.85rem]">Owner Support Dashboard</h1>
                        <p class="mt-1.5 text-sm leading-6 text-slate-600 sm:text-[14px]">Fast answers, operational visibility, and direct support pathways for reservations, payments, tenants, listings, and messages.</p>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($supportChips as $chip)
                            <button
                                type="button"
                                @click="selectQuery(@js($chip))"
                                class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"
                            >
                                {{ $chip }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-[1.25rem] border border-slate-200 bg-white/92 p-3.5 shadow-sm shadow-slate-200/80">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Search the owner help center</p>
                            <p class="mt-1 text-[13px] text-slate-600">Find FAQs, workflows, and support activity without leaving the page.</p>
                        </div>
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 ring-1 ring-blue-100">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                            </svg>
                        </span>
                    </div>

                    <label class="relative mt-3.5 block">
                        <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                            </svg>
                        </span>
                        <input
                            x-model="query"
                            type="search"
                            placeholder="Search reservations, payments, listings, messages..."
                            class="h-10 w-full rounded-2xl border border-slate-200 bg-slate-50 pl-10 pr-12 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                        >
                        <button
                            x-cloak
                            x-show="query"
                            type="button"
                            @click="clearSearch()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full bg-slate-200 px-2 py-1 text-[11px] font-bold uppercase tracking-[0.12em] text-slate-600 transition hover:bg-slate-300"
                        >
                            Clear
                        </button>
                    </label>

                    <div class="mt-3.5 grid gap-2.5 sm:grid-cols-2">
                        <div class="rounded-2xl border border-blue-100 bg-blue-50/70 p-3">
                            <p class="text-[11px] font-bold uppercase tracking-[0.12em] text-blue-700">Suggested search</p>
                            <p class="mt-1 text-[13px] font-semibold text-slate-900">Use property names, tenant names, or issue types to narrow results quickly.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-3">
                            <p class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-500">FAQ matches</p>
                            <p class="mt-1 text-[13px] font-semibold text-slate-900"><span x-text="filteredFaqCount()"></span> topics available</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section>
            <div class="mb-2.5 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-[1rem] font-black tracking-tight text-slate-950">Support Overview</h2>
                    <p class="mt-0.5 text-[11px] text-slate-500">Operational support signals for the owner workspace.</p>
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($supportOverview as $stat)
                    @php($tone = $toneClasses($stat['tone']))
                    <article class="rounded-[1.2rem] border {{ $tone['border'] }} bg-white p-3.5 shadow-sm shadow-slate-200/70">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">{{ $stat['label'] }}</p>
                                <p class="mt-1.5 text-[1.55rem] font-black tracking-tight text-slate-950">{{ $stat['value'] }}</p>
                                <p class="mt-1 text-[12px] leading-5 text-slate-500">{{ $stat['caption'] }}</p>
                            </div>
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl ring-1 {{ $tone['icon'] }}">
                                @if ($stat['icon'] === 'document-text')
                                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25V11.625a3.375 3.375 0 0 0-3.375-3.375H13.5A1.125 1.125 0 0 1 12.375 7.125V4.5m0 0L18 10.125m-5.625-5.625H7.875A1.875 1.875 0 0 0 6 6.375v11.25c0 1.036.84 1.875 1.875 1.875h8.25A1.875 1.875 0 0 0 18 17.625V10.125" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 12.75h4.5m-4.5 3h4.5" />
                                    </svg>
                                @elseif ($stat['icon'] === 'clock')
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2.25" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                @elseif ($stat['icon'] === 'check-badge')
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 12.75 2.25 2.25L15 9.75" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3.582c.318-.59.94-.959 1.614-.959h1.636c.674 0 1.296.369 1.614.959l.384.713c.228.423.661.699 1.14.728l.807.048c.668.04 1.257.45 1.53 1.062l.33.741c.195.44.611.753 1.08.814l.794.103c.657.085 1.197.553 1.401 1.183l.228.706c.146.451.484.815.912.983l.674.263c.607.237 1.006.821 1.006 1.473v1.498c0 .652-.399 1.236-1.006 1.473l-.674.263a1.875 1.875 0 0 0-.912.983l-.228.706a1.875 1.875 0 0 1-1.4 1.183l-.795.103a1.875 1.875 0 0 0-1.08.814l-.33.741a1.875 1.875 0 0 1-1.53 1.062l-.807.048a1.875 1.875 0 0 0-1.14.728l-.384.713a1.875 1.875 0 0 1-1.614.959h-1.636a1.875 1.875 0 0 1-1.614-.959l-.384-.713a1.875 1.875 0 0 0-1.14-.728l-.807-.048a1.875 1.875 0 0 1-1.53-1.062l-.33-.741a1.875 1.875 0 0 0-1.08-.814l-.794-.103A1.875 1.875 0 0 1 2.47 15.83l-.228-.706a1.875 1.875 0 0 0-.912-.983l-.674-.263A1.875 1.875 0 0 1 0 12.405v-1.498c0-.652.399-1.236 1.006-1.473l.674-.263c.428-.168.766-.532.912-.983l.228-.706A1.875 1.875 0 0 1 4.22 6.299l.794-.103c.469-.061.885-.374 1.08-.814l.33-.741A1.875 1.875 0 0 1 7.954 3.58l.807-.048c.479-.029.912-.305 1.14-.728l.384-.713Z" />
                                    </svg>
                                @else
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 8.25h18M4.5 5.25h15A1.5 1.5 0 0 1 21 6.75v12A1.5 1.5 0 0 1 19.5 20.25h-15A1.5 1.5 0 0 1 3 18.75v-12a1.5 1.5 0 0 1 1.5-1.5Z" />
                                    </svg>
                                @endif
                            </span>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <div class="grid gap-4 xl:grid-cols-[minmax(0,1.18fr)_360px]">
            <main class="space-y-4">
                <section class="rounded-[1.45rem] border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/70">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-lg font-black tracking-tight text-slate-950">Popular Help Categories</h2>
                            <p class="mt-1 text-sm text-slate-500">Jump to the workflows owners use most often.</p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 ring-1 ring-blue-100">
                            Fast navigation
                        </span>
                    </div>

                    <div class="mt-4 grid gap-3 md:grid-cols-2 2xl:grid-cols-3">
                        @foreach ($popularCategories as $category)
                            <button
                                type="button"
                                @click="openTopic(@js($category['title']), {{ $category['faq_index'] }})"
                                x-show="matchesSearch(@js($category['search'].' '.$category['title'].' '.$category['description']))"
                                class="group rounded-[1.3rem] border border-slate-200 bg-slate-50/70 p-4 text-left transition hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-50/70 hover:shadow-sm"
                            >
                                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-blue-600 ring-1 ring-slate-200 transition group-hover:ring-blue-100">
                                    <span class="h-5 w-5">
                                        @include('components.sidebar.partials.admin-icon', ['name' => $category['icon']])
                                    </span>
                                </span>
                                <h3 class="mt-3 text-sm font-black text-slate-950">{{ $category['title'] }}</h3>
                                <p class="mt-1 text-[13px] leading-6 text-slate-500">{{ $category['description'] }}</p>
                                <span class="mt-3 inline-flex items-center gap-1 text-xs font-bold text-blue-700">
                                    Explore help
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" />
                                    </svg>
                                </span>
                            </button>
                        @endforeach
                    </div>
                </section>

                <section id="faq-section" class="rounded-[1.45rem] border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/70">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <h2 class="text-lg font-black tracking-tight text-slate-950">Searchable FAQ Section</h2>
                            <p class="mt-1 text-sm text-slate-500">Owner-specific answers for daily operational support.</p>
                        </div>

                        <div class="w-full lg:max-w-sm">
                            <label class="relative block">
                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                                    </svg>
                                </span>
                                <input
                                    x-model="query"
                                    type="search"
                                    placeholder="Search FAQs"
                                    class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 pl-9 pr-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                >
                            </label>
                            <p class="mt-2 text-xs text-slate-400">Showing <span x-text="filteredFaqCount()"></span> help topics.</p>
                        </div>
                    </div>

                    <div class="mt-4 space-y-2">
                        @foreach ($faqItems as $faq)
                            <article
                                x-show="faqVisible(faqs[{{ $loop->index }}])"
                                class="overflow-hidden rounded-[1.2rem] border border-slate-200"
                            >
                                <button
                                    type="button"
                                    @click="openFaq = openFaq === {{ $loop->index }} ? -1 : {{ $loop->index }}"
                                    class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left transition hover:bg-slate-50"
                                >
                                    <div class="min-w-0">
                                        <p class="text-[11px] font-bold uppercase tracking-[0.12em] text-blue-600">{{ $faq['category'] }}</p>
                                        <h3 class="mt-1 text-sm font-bold text-slate-950">{{ $faq['question'] }}</h3>
                                    </div>
                                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition" :class="openFaq === {{ $loop->index }} ? 'rotate-180 border-blue-200 text-blue-600' : ''">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                                        </svg>
                                    </span>
                                </button>

                                <div
                                    x-cloak
                                    x-show="openFaq === {{ $loop->index }}"
                                    x-transition.opacity.duration.150ms
                                    class="border-t border-slate-200 px-4 py-3"
                                >
                                    <p class="text-sm leading-6 text-slate-600">{{ $faq['answer'] }}</p>
                                </div>
                            </article>
                        @endforeach

                        <div
                            x-cloak
                            x-show="query && filteredFaqCount() === 0"
                            class="rounded-[1.2rem] border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center"
                        >
                            <p class="text-sm font-bold text-slate-900">No FAQ matches found</p>
                            <p class="mt-1 text-xs leading-5 text-slate-500">Try a broader term like reservations, payments, tenants, listings, or messages.</p>
                        </div>
                    </div>
                </section>

                <section class="rounded-[1.45rem] border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/70">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-lg font-black tracking-tight text-slate-950">Recent Support Activity</h2>
                            <p class="mt-1 text-sm text-slate-500">Latest support requests and platform follow-up signals.</p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                            {{ $recentSupportRequests->count() }} recent items
                        </span>
                    </div>

                    <div class="mt-4 space-y-3">
                        @forelse ($recentSupportRequests as $supportRequest)
                            @php($statusTone = $statusToneClasses($supportRequest->status))
                            <article
                                x-show="matchesSearch(@js(implode(' ', [
                                    (string) $supportRequest->subject,
                                    (string) $supportRequest->concern_type,
                                    (string) $supportRequest->message,
                                    (string) $supportRequest->status,
                                    (string) $supportRequest->full_name,
                                ])))"
                                class="rounded-[1.2rem] border border-slate-200 bg-slate-50/70 px-4 py-3.5"
                            >
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="text-sm font-black text-slate-950">{{ $supportRequest->subject }}</h3>
                                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.12em] ring-1 {{ $statusTone['pill'] }}">
                                                <span class="h-1.5 w-1.5 rounded-full {{ $statusTone['dot'] }}"></span>
                                                {{ $supportRequest->status ?: 'Pending' }}
                                            </span>
                                        </div>

                                        <div class="mt-1 flex flex-wrap items-center gap-2 text-[11px] text-slate-400">
                                            <span>Request #SR-{{ str_pad((string) $supportRequest->id, 5, '0', STR_PAD_LEFT) }}</span>
                                            <span>-</span>
                                            <span>{{ $supportRequest->concern_type }}</span>
                                            <span>-</span>
                                            <span>{{ $supportRequest->full_name }}</span>
                                            <span>-</span>
                                            <span>{{ optional($supportRequest->created_at)->format('M d, Y h:i A') }}</span>
                                        </div>

                                        <p class="mt-2 text-[13px] leading-6 text-slate-500">{{ \Illuminate\Support\Str::limit($supportRequest->message, 170) }}</p>
                                    </div>

                                    <div class="flex shrink-0 items-center gap-2">
                                        @if ($supportRequest->screenshot)
                                            <span class="inline-flex h-8 items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-2.5 text-[11px] font-semibold text-slate-600">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 1 1-6.364-6.364l8.4-8.4a3 3 0 1 1 4.243 4.243l-8.402 8.4a1.5 1.5 0 0 1-2.121-2.12l7.106-7.107" />
                                                </svg>
                                                Attachment
                                            </span>
                                        @endif

                                        <span class="inline-flex h-8 items-center rounded-xl bg-white px-2.5 text-[11px] font-semibold text-slate-600 ring-1 ring-slate-200">
                                            {{ optional($supportRequest->created_at)->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-[1.2rem] border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center">
                                <p class="text-sm font-bold text-slate-900">No recent support activity</p>
                                <p class="mt-1 text-xs leading-5 text-slate-500">Support requests will appear here once owners or tenants submit them.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </main>

            <aside class="space-y-4">
                <section class="rounded-[1.45rem] border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/70">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-black tracking-tight text-slate-950">Quick Actions</h2>
                            <p class="mt-1 text-sm text-slate-500">Open the owner tools you need most.</p>
                        </div>
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 ring-1 ring-blue-100">
                            <span class="h-5 w-5">
                                @include('components.sidebar.partials.admin-icon', ['name' => 'support'])
                            </span>
                        </span>
                    </div>

                    <div class="mt-4 grid gap-2.5">
                        @foreach ($quickActions as $action)
                            @php($tone = $toneClasses($action['tone']))
                            <a href="{{ $action['href'] }}" class="group flex items-center justify-between gap-3 rounded-[1.15rem] border border-slate-200 px-3 py-3 transition hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-50/60">
                                <span class="inline-flex min-w-0 items-center gap-3">
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl ring-1 {{ $tone['icon'] }}">
                                        <span class="h-5 w-5">
                                            @include('components.sidebar.partials.admin-icon', ['name' => $action['icon']])
                                        </span>
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block text-sm font-black text-slate-950">{{ $action['title'] }}</span>
                                        <span class="mt-0.5 block text-xs leading-5 text-slate-500">{{ $action['description'] }}</span>
                                    </span>
                                </span>
                                <svg class="h-4 w-4 shrink-0 text-slate-400 transition group-hover:text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7" />
                                </svg>
                            </a>
                        @endforeach
                    </div>
                </section>

                <section class="rounded-[1.45rem] border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/70">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-black tracking-tight text-slate-950">Resource Links</h2>
                            <p class="mt-1 text-sm text-slate-500">Reference pages that support day-to-day owner operations.</p>
                        </div>
                    </div>

                    <div class="mt-4 space-y-2.5">
                        @foreach ($resourceLinks as $resource)
                            <a
                                href="{{ $resource['href'] }}"
                                x-show="matchesSearch(@js($resource['title'].' '.$resource['description']))"
                                class="flex items-start gap-3 rounded-[1.15rem] border border-slate-200 bg-slate-50/70 px-3 py-3 transition hover:border-blue-200 hover:bg-blue-50/60"
                            >
                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white text-blue-600 ring-1 ring-slate-200">
                                    <span class="h-5 w-5">
                                        @include('components.sidebar.partials.admin-icon', ['name' => $resource['icon']])
                                    </span>
                                </span>
                                <span class="min-w-0">
                                    <span class="block text-sm font-black text-slate-950">{{ $resource['title'] }}</span>
                                    <span class="mt-1 block text-xs leading-5 text-slate-500">{{ $resource['description'] }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </section>

                <section id="system-status" class="rounded-[1.45rem] border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/70">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-black tracking-tight text-slate-950">System Status</h2>
                            <p class="mt-1 text-sm text-slate-500">Support signals derived from recent owner-facing activity.</p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 ring-1 ring-blue-100">
                            Updated daily
                        </span>
                    </div>

                    <div class="mt-4 space-y-3">
                        @foreach ($systemStatus as $status)
                            @php($tone = $toneClasses($status['tone']))
                            <div
                                x-show="matchesSearch(@js($status['label'].' '.$status['state'].' '.$status['summary']))"
                                class="rounded-[1.15rem] border border-slate-200 bg-slate-50/70 p-3"
                            >
                                <div class="flex items-start gap-3">
                                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $tone['icon'] }}">
                                        <span class="h-5 w-5">
                                            @include('components.sidebar.partials.admin-icon', ['name' => $status['icon']])
                                        </span>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="text-sm font-black text-slate-950">{{ $status['label'] }}</p>
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.12em] ring-1 {{ $tone['pill'] }}">{{ $status['state'] }}</span>
                                        </div>
                                        <p class="mt-1 text-xs leading-5 text-slate-500">{{ $status['summary'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section id="contact-support" class="rounded-[1.45rem] border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/70">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-black tracking-tight text-slate-950">Contact Support</h2>
                            <p class="mt-1 text-sm text-slate-500">Direct channels for professional owner support.</p>
                        </div>
                    </div>

                    <div class="mt-4 space-y-3">
                        @foreach ($contactWidgets as $widget)
                            <div class="rounded-[1.15rem] border border-slate-200 bg-slate-50/70 p-3">
                                <p class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400">{{ $widget['label'] }}</p>
                                @if (isset($widget['href']))
                                    <a href="{{ $widget['href'] }}" class="mt-1 block text-sm font-black text-slate-950 transition hover:text-blue-700">{{ $widget['value'] }}</a>
                                @else
                                    <p class="mt-1 text-sm font-black text-slate-950">{{ $widget['value'] }}</p>
                                @endif
                                <p class="mt-1 text-xs leading-5 text-slate-500">{{ $widget['description'] }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                        <a href="mailto:support@boardmatch.ph?subject=BoardMatch%20Owner%20Portal%20Support" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                            Email Support
                        </a>
                        <a href="{{ route('admin.notifications.index') }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                            View Alerts
                        </a>
                    </div>
                </section>

                <section class="rounded-[1.45rem] border border-slate-200 bg-white p-4 shadow-sm shadow-slate-200/70">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-black tracking-tight text-slate-950">Support Prep</h2>
                            <p class="mt-1 text-sm text-slate-500">A stronger request usually means a faster response.</p>
                        </div>
                    </div>

                    <ul class="mt-4 space-y-3">
                        @foreach ($supportChecklist as $item)
                            <li class="flex gap-3 text-[13px] leading-6 text-slate-600">
                                <span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-blue-500"></span>
                                <span>{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            </aside>
        </div>
    </div>
</x-admin.shell>
</x-layouts.dashboard>
