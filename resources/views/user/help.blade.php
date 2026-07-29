<x-layouts.dashboard>
<x-user.shell>
@php
    $popularCategories = [
        [
            'title' => 'Reservations',
            'description' => 'Track steps, request changes, and approval updates.',
            'icon' => 'home',
            'faq_index' => 1,
            'search' => 'reservations booking reserve cancel approval status room',
        ],
        [
            'title' => 'Payments',
            'description' => 'Payment concerns, confirmations, and receipts.',
            'icon' => 'credit-card',
            'faq_index' => 2,
            'search' => 'payments transactions receipt pending payment proof confirmation',
        ],
        [
            'title' => 'Messages',
            'description' => 'Owner conversations, inquiries, and replies.',
            'icon' => 'chat',
            'faq_index' => 4,
            'search' => 'messages inquiries support conversation owners response',
        ],
        [
            'title' => 'Matchmaking',
            'description' => 'Recommendations and preference tuning.',
            'icon' => 'sparkles',
            'faq_index' => 0,
            'search' => 'matchmaking recommendations preferences budget location amenities',
        ],
        [
            'title' => 'Account & Security',
            'description' => 'Profile details, access, and passwords.',
            'icon' => 'shield-check',
            'faq_index' => 6,
            'search' => 'account security password profile privacy verification',
        ],
        [
            'title' => 'Technical Issues',
            'description' => 'Bugs, broken pages, and upload errors.',
            'icon' => 'wrench',
            'faq_index' => 8,
            'search' => 'technical issue bug error upload loading broken search',
        ],
    ];

    $faqs = [
        [
            'topic' => 'matchmaking',
            'question' => 'How does BoardMatch recommend boarding houses?',
            'answer' => 'BoardMatch uses your selected preferences such as location, rental budget, room type, amenities, and lifestyle details to recommend boarding houses that fit your needs more closely.',
            'search' => 'matchmaking recommend recommendations preferences budget location amenities',
        ],
        [
            'topic' => 'reservations',
            'question' => 'How do I reserve a boarding house?',
            'answer' => 'Open a listing from Find Boarding Houses or Matchmaking, review the room details, and use the reserve or inquire action. The request will then appear on your Reservations page for tracking.',
            'search' => 'reserve reservation boarding house request reservations page track',
        ],
        [
            'topic' => 'payments',
            'question' => 'Where can I review my payment records?',
            'answer' => 'You can review your payment history from the Transactions page, where you can see status, amount, dates, and related boarding house details.',
            'search' => 'payments transactions records history amount status',
        ],
        [
            'topic' => 'payments',
            'question' => 'What does a pending payment status mean?',
            'answer' => 'A pending payment usually means the payment or reservation is still being reviewed by the boarding house owner or an administrator before it is fully confirmed.',
            'search' => 'pending payment status meaning review confirmation',
        ],
        [
            'topic' => 'messages',
            'question' => 'How can I message a property owner or support?',
            'answer' => 'Go to Messages and open the related conversation. You can ask about room availability, rental rules, viewing schedules, or continue an existing support-related thread there.',
            'search' => 'messages property owner support conversation inquiry',
        ],
        [
            'topic' => 'matchmaking',
            'question' => 'Can I change my matchmaking preferences later?',
            'answer' => 'Yes. Update your preferences any time to refine budget, location, room type, amenities, and lifestyle details. Your matchmaking results can change after those updates.',
            'search' => 'change preferences matchmaking update budget location room type',
        ],
        [
            'topic' => 'account-security',
            'question' => 'How do I update my profile information?',
            'answer' => 'Visit Profile Settings to edit your personal details, contact information, password, and account preferences in one place.',
            'search' => 'profile update account settings personal details password',
        ],
        [
            'topic' => 'account-security',
            'question' => 'Why is my profile completeness not at 100%?',
            'answer' => 'Your profile may still be missing information like a photo, contact details, lifestyle or preference data, or other optional account information used across BoardMatch.',
            'search' => 'profile completeness missing photo contact details lifestyle',
        ],
        [
            'topic' => 'technical',
            'question' => 'What should I include when I report a technical issue?',
            'answer' => 'Include the page or feature affected, what happened, what you expected instead, and a screenshot if possible. That helps support investigate much faster.',
            'search' => 'technical issue report screenshot bug page feature',
        ],
    ];

    $concernTypes = [
        'Account Problem',
        'Reservation Concern',
        'Payment Concern',
        'Boarding House Inquiry',
        'Matchmaking Issue',
        'Technical Problem',
        'Other Concern',
    ];

    $iconPath = static function (string $icon): string {
        return match ($icon) {
            'home' => '<path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955a1.125 1.125 0 0 1 1.591 0L21.75 12M4.5 9.75v9A1.5 1.5 0 0 0 6 20.25h3.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v4.875H18a1.5 1.5 0 0 0 1.5-1.5v-9" />',
            'credit-card' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M3.75 6h16.5A1.5 1.5 0 0 1 21.75 7.5v9A1.5 1.5 0 0 1 20.25 18h-16.5A1.5 1.5 0 0 1 2.25 16.5v-9A1.5 1.5 0 0 1 3.75 6Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 14.25h3m6-1.5h2.25" />',
            'chat' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3h6m-8.25 8.25 1.447-2.894A1.125 1.125 0 0 1 7.705 16h8.545A2.25 2.25 0 0 0 18.5 13.75V6.25A2.25 2.25 0 0 0 16.25 4H7.75A2.25 2.25 0 0 0 5.5 6.25v10.928a.375.375 0 0 0 .75.168Z" />',
            'sparkles' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18l-.813-2.096a2.25 2.25 0 0 0-1.341-1.341L4.75 13.75l2.096-.813a2.25 2.25 0 0 0 1.341-1.341L9 9.5l.813 2.096a2.25 2.25 0 0 0 1.341 1.341l2.096.813-2.096.813a2.25 2.25 0 0 0-1.341 1.341ZM18.25 8.25 18 9l-.25-.75A1.875 1.875 0 0 0 16.5 7l.75-.25L18 6l.25.75A1.875 1.875 0 0 0 19.5 8l-.75.25-.75.75ZM16.894 20.567 16.5 21.75l-.394-1.183a1.875 1.875 0 0 0-1.173-1.173l-1.183-.394 1.183-.394a1.875 1.875 0 0 0 1.173-1.173l.394-1.183.394 1.183a1.875 1.875 0 0 0 1.173 1.173l1.183.394-1.183.394a1.875 1.875 0 0 0-1.173 1.173Z" />',
            'shield-check' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m6 2.25c0 5.246-3.438 9.69-8.25 11.174C7.938 21.69 4.5 17.246 4.5 12V5.741c0-.534.355-1.003.868-1.142l7.5-2.143a1.125 1.125 0 0 1 .764 0l7.5 2.143c.513.147.868.608.868 1.142V12Z" />',
            'wrench' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.42 6.36a6 6 0 1 0 8.22 8.22l-3.12-3.12a1.5 1.5 0 0 1-.44-1.06V7.5a1.5 1.5 0 0 0-1.5-1.5h-2.9a1.5 1.5 0 0 1-1.06-.44L7.5 2.44" /><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 19.5 4.286-4.286" />',
            'document-text' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25V11.625a3.375 3.375 0 0 0-3.375-3.375H13.5A1.125 1.125 0 0 1 12.375 7.125V4.5m0 0L18 10.125m-5.625-5.625H7.875A1.875 1.875 0 0 0 6 6.375v11.25c0 1.036.84 1.875 1.875 1.875h8.25A1.875 1.875 0 0 0 18 17.625V10.125" /><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 12.75h4.5m-4.5 3h4.5" />',
            'ticket' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6.75v10.5m-9-10.5v10.5m-1.5-9h12A1.5 1.5 0 0 1 19.5 9.75v1.125a2.625 2.625 0 1 0 0 5.25v1.125A1.5 1.5 0 0 1 18 18.75H6A1.5 1.5 0 0 1 4.5 17.25v-1.125a2.625 2.625 0 1 0 0-5.25V9.75A1.5 1.5 0 0 1 6 8.25Z" />',
            'magnifying-glass' => '<path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />',
            'paper-clip' => '<path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 1 1-6.364-6.364l8.4-8.4a3 3 0 1 1 4.243 4.243l-8.402 8.4a1.5 1.5 0 0 1-2.121-2.12l7.106-7.107" />',
            default => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2.25" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
        };
    };

    $statusToneClasses = static function (?string $status): array {
        $normalized = strtolower(trim((string) $status));

        return match (true) {
            in_array($normalized, ['resolved', 'closed', 'completed'], true) => [
                'pill' => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200/70 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/20',
                'dot' => 'bg-emerald-500',
            ],
            in_array($normalized, ['in progress', 'processing', 'reviewing', 'open'], true) => [
                'pill' => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200/70 dark:bg-blue-400/10 dark:text-blue-300 dark:ring-blue-400/20',
                'dot' => 'bg-blue-500',
            ],
            in_array($normalized, ['pending', 'pending review'], true) => [
                'pill' => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200/70 dark:bg-amber-400/10 dark:text-amber-300 dark:ring-amber-400/20',
                'dot' => 'bg-amber-500',
            ],
            in_array($normalized, ['rejected', 'cancelled', 'failed'], true) => [
                'pill' => 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200/70 dark:bg-rose-400/10 dark:text-rose-300 dark:ring-rose-400/20',
                'dot' => 'bg-rose-500',
            ],
            default => [
                'pill' => 'bg-slate-100 text-slate-700 ring-1 ring-inset ring-slate-200/70 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700',
                'dot' => 'bg-slate-400',
            ],
        };
    };
@endphp

@once
    <script>
        function helpHub(config = {}) {
            return {
                query: '',
                openFaq: config.initialFaq ?? 0,
                hasErrors: Boolean(config.hasErrors),
                supportForm: {
                    concernType: config.initialConcernType ?? '',
                },
                init() {
                    if (this.hasErrors) {
                        this.$nextTick(() => this.scrollTo('support-request'));
                    }
                },
                normalized(value) {
                    return (value || '').toString().toLowerCase().trim();
                },
                matchesSearch(text) {
                    const query = this.normalized(this.query);
                    return query === '' || this.normalized(text).includes(query);
                },
                clearQuery() {
                    this.query = '';
                },
                toggleFaq(index) {
                    this.openFaq = this.openFaq === index ? null : index;
                },
                jumpTo(target, faqIndex = null) {
                    if (faqIndex !== null) {
                        this.openFaq = faqIndex;
                    }

                    this.$nextTick(() => this.scrollTo(target));
                },
                openForm(concernType = '') {
                    if (concernType) {
                        this.supportForm.concernType = concernType;
                    }

                    this.$nextTick(() => this.scrollTo('support-request'));
                },
                scrollTo(id) {
                    document.getElementById(id)?.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start',
                    });
                },
            };
        }
    </script>
@endonce

<div
    x-data="helpHub({
        hasErrors: @js($errors->any()),
        initialConcernType: @js(old('concern_type')),
        initialFaq: 0,
    })"
    x-init="init()"
    class="mx-auto w-full max-w-4xl space-y-5"
>
    {{-- Page header --}}
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Help Center</h1>
            <p class="mt-0.5 text-[13px] text-slate-500 dark:text-slate-400">Find answers, browse FAQs, and reach the BoardMatch team.</p>
        </div>
        <button
            type="button"
            @click="openForm('')"
            class="inline-flex h-9 shrink-0 items-center justify-center gap-2 rounded-lg bg-[#2563eb] px-4 text-[13px] font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-[#1d4ed8] hover:shadow-md hover:shadow-blue-600/25 focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-200"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                {!! $iconPath('ticket') !!}
            </svg>
            Submit Ticket
        </button>
    </header>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    {{-- Search --}}
    <div class="relative">
        <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            {!! $iconPath('magnifying-glass') !!}
        </svg>
        <input
            x-model="query"
            type="search"
            class="h-12 w-full rounded-2xl border border-slate-200 bg-white pl-11 pr-11 text-sm text-slate-700 shadow-sm shadow-slate-200/50 outline-none transition hover:border-slate-300 focus:border-blue-400 focus:ring-4 focus:ring-blue-100 placeholder:text-slate-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:shadow-none dark:hover:border-slate-600"
            placeholder="Search FAQs, categories, or your support requests…"
        >
        <button
            type="button"
            x-show="query"
            x-cloak
            @click="clearQuery()"
            class="absolute right-3 top-1/2 inline-flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200"
            aria-label="Clear search"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- Help categories --}}
    <section>
        <h2 class="text-sm font-bold text-slate-900 dark:text-white">Browse by Category</h2>
        <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($popularCategories as $category)
                <button
                    type="button"
                    x-show="matchesSearch(@js($category['search'].' '.$category['title'].' '.$category['description']))"
                    @click="jumpTo('faq-section', {{ $category['faq_index'] }})"
                    class="group flex items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4 text-left shadow-sm shadow-slate-200/40 transition duration-150 hover:-translate-y-0.5 hover:border-blue-200 hover:bg-gradient-to-br hover:from-blue-50/60 hover:to-white hover:shadow-md hover:shadow-blue-100/60 focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:shadow-none dark:hover:border-blue-400/20 dark:hover:from-blue-400/10 dark:hover:to-slate-900"
                >
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-50 to-blue-100/60 text-blue-600 transition group-hover:from-blue-100 group-hover:to-blue-50 dark:from-blue-400/10 dark:to-blue-400/5 dark:text-blue-300">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            {!! $iconPath($category['icon']) !!}
                        </svg>
                    </span>
                    <span class="min-w-0">
                        <span class="block text-[13px] font-bold text-slate-900 transition group-hover:text-blue-700 dark:text-white dark:group-hover:text-blue-300">{{ $category['title'] }}</span>
                        <span class="mt-0.5 block text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $category['description'] }}</span>
                    </span>
                </button>
            @endforeach
        </div>
    </section>

    {{-- FAQs --}}
    <section id="faq-section" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-slate-200/50 dark:border-slate-800 dark:bg-slate-900 dark:shadow-none">
        <div class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-transparent px-5 py-3.5 dark:border-slate-800 dark:from-slate-800/40">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Frequently Asked Questions</h2>
            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400">{{ count($faqs) }} answers</span>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-slate-800">
            @foreach ($faqs as $index => $faq)
                <article x-show="matchesSearch(@js($faq['search'].' '.$faq['question'].' '.$faq['answer']))">
                    <button
                        type="button"
                        @click="toggleFaq({{ $index }})"
                        class="flex w-full items-center justify-between gap-4 px-5 py-3.5 text-left transition hover:bg-slate-50/70 focus:outline-none focus-visible:bg-blue-50/50 focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-blue-200 dark:hover:bg-slate-800/50 dark:focus-visible:bg-blue-400/5"
                        :aria-expanded="openFaq === {{ $index }}"
                    >
                        <span class="text-[13px] font-semibold text-slate-800 dark:text-slate-100">{{ $faq['question'] }}</span>
                        <svg
                            class="h-4 w-4 shrink-0 text-slate-400 transition"
                            :class="openFaq === {{ $index }} ? 'rotate-180 text-blue-600 dark:text-blue-300' : ''"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                        </svg>
                    </button>

                    <div
                        x-show="openFaq === {{ $index }}"
                        x-transition.opacity.duration.150ms
                        x-cloak
                        class="px-5 pb-4"
                    >
                        <p class="text-[13px] leading-6 text-slate-500 dark:text-slate-400">{{ $faq['answer'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    {{-- Recent support requests --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-slate-200/50 dark:border-slate-800 dark:bg-slate-900 dark:shadow-none">
        <div class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-transparent px-5 py-3.5 dark:border-slate-800 dark:from-slate-800/40">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Recent Support Requests</h2>
            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400">{{ $recentSupportRequests->count() }} recent</span>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse ($recentSupportRequests as $supportRequest)
                @php($statusTone = $statusToneClasses($supportRequest->status))
                <article
                    x-show="matchesSearch(@js(
                        implode(' ', [
                            (string) $supportRequest->subject,
                            (string) $supportRequest->concern_type,
                            (string) $supportRequest->status,
                            (string) $supportRequest->message,
                        ])
                    ))"
                    class="px-5 py-3.5 transition hover:bg-slate-50/60 dark:hover:bg-slate-800/40"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-[13px] font-bold text-slate-900 dark:text-white">{{ $supportRequest->subject }}</h3>
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-[10px] font-bold {{ $statusTone['pill'] }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $statusTone['dot'] }}"></span>
                                    {{ $supportRequest->status ?: 'Pending' }}
                                </span>
                                @if ($supportRequest->screenshot)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-slate-400">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            {!! $iconPath('paper-clip') !!}
                                        </svg>
                                        Attachment
                                    </span>
                                @endif
                            </div>
                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                                #SR-{{ str_pad((string) $supportRequest->id, 5, '0', STR_PAD_LEFT) }} · {{ $supportRequest->concern_type }} · {{ optional($supportRequest->created_at)->format('M d, Y') }}
                            </p>
                            <p class="mt-1.5 text-[13px] leading-5 text-slate-500 dark:text-slate-400">{{ \Illuminate\Support\Str::limit($supportRequest->message, 140) }}</p>
                        </div>
                        <span class="shrink-0 text-[11px] font-medium text-slate-400">{{ optional($supportRequest->created_at)->diffForHumans(null, true) }}</span>
                    </div>
                </article>
            @empty
                <div class="px-5 py-10 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            {!! $iconPath('document-text') !!}
                        </svg>
                    </div>
                    <p class="mt-3 text-sm font-bold text-slate-900 dark:text-white">No support requests yet</p>
                    <p class="mt-1 text-[13px] text-slate-500 dark:text-slate-400">Submitted tickets and their status will appear here.</p>
                </div>
            @endforelse
        </div>
    </section>

    {{-- Submit ticket --}}
    <section id="support-request" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm shadow-slate-200/50 dark:border-slate-800 dark:bg-slate-900 dark:shadow-none">
        <div class="border-b border-slate-100 bg-gradient-to-r from-blue-50/60 to-transparent px-5 py-3.5 dark:border-slate-800 dark:from-blue-400/5">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Submit a Ticket</h2>
            <p class="mt-0.5 text-xs text-slate-400 dark:text-slate-500">Include as much detail as possible so the team can respond faster. Typical reply: 24–48 hours.</p>
        </div>

        <form method="POST" action="{{ route('user.help-center.store') }}" enctype="multipart/form-data" class="space-y-4 px-5 py-5">
            @csrf

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300">
                    Full Name
                    <input
                        name="full_name"
                        value="{{ old('full_name', $tenant->name ?? '') }}"
                        required
                        class="ui-input mt-1 text-sm font-normal"
                    >
                    @error('full_name')
                        <span class="mt-1 block text-xs font-medium text-rose-600">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300">
                    Email Address
                    <input
                        name="email"
                        type="email"
                        value="{{ old('email', $tenant->email ?? '') }}"
                        required
                        class="ui-input mt-1 text-sm font-normal"
                    >
                    @error('email')
                        <span class="mt-1 block text-xs font-medium text-rose-600">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300">
                    Concern Type
                    <div class="relative mt-1">
                        <select
                            name="concern_type"
                            x-model="supportForm.concernType"
                            required
                            class="ui-input appearance-none pr-10 text-sm font-normal"
                        >
                            <option value="">Select concern type</option>
                            @foreach ($concernTypes as $type)
                                <option value="{{ $type }}" @selected(old('concern_type') === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                        <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                        </svg>
                    </div>
                    @error('concern_type')
                        <span class="mt-1 block text-xs font-medium text-rose-600">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300">
                    Subject
                    <input
                        name="subject"
                        value="{{ old('subject') }}"
                        required
                        class="ui-input mt-1 text-sm font-normal"
                        placeholder="Summarize your concern"
                    >
                    @error('subject')
                        <span class="mt-1 block text-xs font-medium text-rose-600">{{ $message }}</span>
                    @enderror
                </label>
            </div>

            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300">
                Message
                <textarea
                    name="message"
                    rows="4"
                    required
                    class="ui-input mt-1 resize-y py-3 text-sm font-normal"
                    placeholder="Describe what happened, what you expected, and any reservation, payment, or listing details that can help."
                >{{ old('message') }}</textarea>
                @error('message')
                    <span class="mt-1 block text-xs font-medium text-rose-600">{{ $message }}</span>
                @enderror
            </label>

            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300">
                Screenshot <span class="font-normal text-slate-400">(optional — JPG, PNG, or PDF, max 2MB)</span>
                <input
                    name="screenshot"
                    type="file"
                    accept=".jpg,.jpeg,.png,.pdf"
                    class="mt-1 block w-full rounded-xl border border-dashed border-slate-300 bg-white px-3 py-2.5 text-sm font-normal text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:file:bg-blue-400/10 dark:file:text-blue-300"
                >
                @error('screenshot')
                    <span class="mt-1 block text-xs font-medium text-rose-600">{{ $message }}</span>
                @enderror
            </label>

            <div class="flex justify-end border-t border-slate-100 pt-4 dark:border-slate-800">
                <button
                    type="submit"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-[#2563eb] px-5 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-[#1d4ed8] hover:shadow-md hover:shadow-blue-600/25 focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-200"
                >
                    Submit Request
                </button>
            </div>
        </form>
    </section>
</div>
</x-user.shell>
</x-layouts.dashboard>
