<x-layouts.dashboard>
<x-user.shell>
@php
    $popularCategories = [
        [
            'title' => 'Reservations',
            'description' => 'Track reservation steps, request changes, and understand approval or cancellation updates.',
            'icon' => 'home',
            'faq_index' => 1,
            'search' => 'reservations booking reserve cancel approval status room',
        ],
        [
            'title' => 'Payments',
            'description' => 'Review payment concerns, pending confirmations, transaction records, and receipt issues.',
            'icon' => 'credit-card',
            'faq_index' => 2,
            'search' => 'payments transactions receipt pending payment proof confirmation',
        ],
        [
            'title' => 'Messages',
            'description' => 'Get help with owner conversations, inquiries, response times, and support threads.',
            'icon' => 'chat',
            'faq_index' => 4,
            'search' => 'messages inquiries support conversation owners response',
        ],
        [
            'title' => 'Matchmaking',
            'description' => 'Understand recommendations, adjust preferences, and improve your boarding house matches.',
            'icon' => 'sparkles',
            'faq_index' => 0,
            'search' => 'matchmaking recommendations preferences budget location amenities',
        ],
        [
            'title' => 'Account & Security',
            'description' => 'Update profile details, fix account access issues, and manage privacy or password settings.',
            'icon' => 'shield-check',
            'faq_index' => 6,
            'search' => 'account security password profile privacy verification',
        ],
        [
            'title' => 'Technical Issues',
            'description' => 'Report bugs, broken pages, upload errors, or search problems affecting your experience.',
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

    $quickActions = [
        [
            'title' => 'Submit Ticket',
            'description' => 'Send a detailed support request with attachments and issue context.',
            'icon' => 'ticket',
            'tone' => 'blue',
            'action' => 'form',
            'concern' => '',
        ],
        [
            'title' => 'Contact Support',
            'description' => 'Open your messages area to continue support-related conversations.',
            'icon' => 'lifebuoy',
            'tone' => 'emerald',
            'href' => route('user.messages.index'),
        ],
        [
            'title' => 'Report Issue',
            'description' => 'Jump straight to the form with Technical Problem already selected.',
            'icon' => 'bug',
            'tone' => 'amber',
            'action' => 'form',
            'concern' => 'Technical Problem',
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

    $supportChips = [
        'Reservation status',
        'Pending payment',
        'Technical problem',
        'Messages',
        'Matchmaking',
        'Profile settings',
    ];

    $contactItems = [
        [
            'label' => 'Email Support',
            'value' => 'support@boardmatch.com',
            'subtext' => 'Best for detailed concerns and attachments.',
            'icon' => 'envelope',
        ],
        [
            'label' => 'Support Messages',
            'value' => 'Open Messages',
            'subtext' => 'Continue ongoing conversations inside BoardMatch.',
            'icon' => 'chat',
            'href' => route('user.messages.index'),
        ],
        [
            'label' => 'Office Hours',
            'value' => 'Monday to Friday, 8:00 AM to 5:00 PM',
            'subtext' => 'Local business hours for active support handling.',
            'icon' => 'clock',
        ],
        [
            'label' => 'Typical Response',
            'value' => '24 to 48 hours',
            'subtext' => 'Most requests receive an update within two business days.',
            'icon' => 'bolt',
        ],
    ];

    $supportTips = [
        'Search the FAQs first to find quick answers before creating a new ticket.',
        'Attach a screenshot for bugs, upload failures, or unexpected page behavior.',
        'Include reservation, payment, or listing details when your issue is account-specific.',
        'Use Messages if your concern is tied to an existing conversation with an owner or manager.',
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
            'clock' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2.25" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
            'check-badge' => '<path stroke-linecap="round" stroke-linejoin="round" d="m9 12.75 2.25 2.25L15 9.75" /><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3.582c.318-.59.94-.959 1.614-.959h1.636c.674 0 1.296.369 1.614.959l.384.713c.228.423.661.699 1.14.728l.807.048c.668.04 1.257.45 1.53 1.062l.33.741c.195.44.611.753 1.08.814l.794.103c.657.085 1.197.553 1.401 1.183l.228.706c.146.451.484.815.912.983l.674.263c.607.237 1.006.821 1.006 1.473v1.498c0 .652-.399 1.236-1.006 1.473l-.674.263a1.875 1.875 0 0 0-.912.983l-.228.706a1.875 1.875 0 0 1-1.4 1.183l-.795.103a1.875 1.875 0 0 0-1.08.814l-.33.741a1.875 1.875 0 0 1-1.53 1.062l-.807.048a1.875 1.875 0 0 0-1.14.728l-.384.713a1.875 1.875 0 0 1-1.614.959h-1.636a1.875 1.875 0 0 1-1.614-.959l-.384-.713a1.875 1.875 0 0 0-1.14-.728l-.807-.048a1.875 1.875 0 0 1-1.53-1.062l-.33-.741a1.875 1.875 0 0 0-1.08-.814l-.794-.103A1.875 1.875 0 0 1 2.47 15.83l-.228-.706a1.875 1.875 0 0 0-.912-.983l-.674-.263A1.875 1.875 0 0 1 0 12.405v-1.498c0-.652.399-1.236 1.006-1.473l.674-.263c.428-.168.766-.532.912-.983l.228-.706A1.875 1.875 0 0 1 4.22 6.299l.794-.103c.469-.061.885-.374 1.08-.814l.33-.741A1.875 1.875 0 0 1 7.954 3.58l.807-.048c.479-.029.912-.305 1.14-.728l.384-.713Z" />',
            'calendar-days' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 8.25h18M4.5 5.25h15A1.5 1.5 0 0 1 21 6.75v12A1.5 1.5 0 0 1 19.5 20.25h-15A1.5 1.5 0 0 1 3 18.75v-12a1.5 1.5 0 0 1 1.5-1.5Z" />',
            'ticket' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6.75v10.5m-9-10.5v10.5m-1.5-9h12A1.5 1.5 0 0 1 19.5 9.75v1.125a2.625 2.625 0 1 0 0 5.25v1.125A1.5 1.5 0 0 1 18 18.75H6A1.5 1.5 0 0 1 4.5 17.25v-1.125a2.625 2.625 0 1 0 0-5.25V9.75A1.5 1.5 0 0 1 6 8.25Z" />',
            'lifebuoy' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 7.5h.75A2.25 2.25 0 0 1 19.5 9.75v4.5A2.25 2.25 0 0 1 17.25 16.5h-.75m0-9a4.5 4.5 0 1 0-9 0m9 0a4.5 4.5 0 1 1-9 0m9 0V6.75A2.25 2.25 0 0 0 14.25 4.5h-4.5A2.25 2.25 0 0 0 7.5 6.75v.75m9 9v.75A2.25 2.25 0 0 1 14.25 19.5h-4.5A2.25 2.25 0 0 1 7.5 17.25v-.75m9 0a4.5 4.5 0 1 1-9 0" />',
            'bug' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 8.25h10.5m-10.5 7.5h10.5M9 4.5l.75 1.5m4.5-1.5-.75 1.5m-6.75 3v6.75A3.75 3.75 0 0 0 10.5 19.5h3a3.75 3.75 0 0 0 3.75-3.75V9a3.75 3.75 0 0 0-3.75-3.75h-3A3.75 3.75 0 0 0 6.75 9Zm-2.25 1.5h2.25m10.5 0h2.25m-15 3h2.25m10.5 0h2.25" />',
            'envelope' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21.75 7.5v9A2.25 2.25 0 0 1 19.5 18.75h-15A2.25 2.25 0 0 1 2.25 16.5v-9A2.25 2.25 0 0 1 4.5 5.25h15A2.25 2.25 0 0 1 21.75 7.5Z" /><path stroke-linecap="round" stroke-linejoin="round" d="m3 7.5 8.295 5.53a1.5 1.5 0 0 0 1.41 0L21 7.5" />',
            'bolt' => '<path stroke-linecap="round" stroke-linejoin="round" d="m13.5 3.75-7.5 8.25h4.5l-1.5 8.25 7.5-8.25h-4.5l1.5-8.25Z" />',
            'magnifying-glass' => '<path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />',
            'paper-clip' => '<path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 1 1-6.364-6.364l8.4-8.4a3 3 0 1 1 4.243 4.243l-8.402 8.4a1.5 1.5 0 0 1-2.121-2.12l7.106-7.107" />',
            default => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2.25" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
        };
    };

    $statToneClasses = static function (string $tone): array {
        return match ($tone) {
            'amber' => [
                'card' => 'border-amber-100 bg-amber-50/80 dark:border-amber-400/20 dark:bg-amber-400/10',
                'icon' => 'bg-amber-100 text-amber-700 ring-1 ring-amber-200 dark:bg-amber-400/15 dark:text-amber-300 dark:ring-amber-400/20',
                'label' => 'text-amber-700 dark:text-amber-300',
            ],
            'emerald' => [
                'card' => 'border-emerald-100 bg-emerald-50/80 dark:border-emerald-400/20 dark:bg-emerald-400/10',
                'icon' => 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-400/15 dark:text-emerald-300 dark:ring-emerald-400/20',
                'label' => 'text-emerald-700 dark:text-emerald-300',
            ],
            'slate' => [
                'card' => 'border-slate-200 bg-slate-50/90 dark:border-slate-700 dark:bg-slate-800/60',
                'icon' => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700',
                'label' => 'text-slate-700 dark:text-slate-200',
            ],
            default => [
                'card' => 'border-blue-100 bg-blue-50/80 dark:border-blue-400/20 dark:bg-blue-400/10',
                'icon' => 'bg-blue-100 text-blue-700 ring-1 ring-blue-200 dark:bg-blue-400/15 dark:text-blue-300 dark:ring-blue-400/20',
                'label' => 'text-blue-700 dark:text-blue-300',
            ],
        };
    };

    $actionToneClasses = static function (string $tone): array {
        return match ($tone) {
            'emerald' => [
                'wrap' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/20',
                'button' => 'border-emerald-200 text-emerald-700 hover:bg-emerald-50 dark:border-emerald-400/20 dark:text-emerald-300 dark:hover:bg-emerald-400/10',
            ],
            'amber' => [
                'wrap' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200 dark:bg-amber-400/10 dark:text-amber-300 dark:ring-amber-400/20',
                'button' => 'border-amber-200 text-amber-700 hover:bg-amber-50 dark:border-amber-400/20 dark:text-amber-300 dark:hover:bg-amber-400/10',
            ],
            default => [
                'wrap' => 'bg-blue-50 text-blue-700 ring-1 ring-blue-200 dark:bg-blue-400/10 dark:text-blue-300 dark:ring-blue-400/20',
                'button' => 'border-blue-200 text-blue-700 hover:bg-blue-50 dark:border-blue-400/20 dark:text-blue-300 dark:hover:bg-blue-400/10',
            ],
        };
    };

    $statusToneClasses = static function (?string $status): array {
        $normalized = strtolower(trim((string) $status));

        return match (true) {
            in_array($normalized, ['resolved', 'closed', 'completed'], true) => [
                'pill' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/20',
                'dot' => 'bg-emerald-500',
            ],
            in_array($normalized, ['in progress', 'processing', 'reviewing', 'open'], true) => [
                'pill' => 'bg-blue-50 text-blue-700 ring-1 ring-blue-200 dark:bg-blue-400/10 dark:text-blue-300 dark:ring-blue-400/20',
                'dot' => 'bg-blue-500',
            ],
            in_array($normalized, ['pending', 'pending review'], true) => [
                'pill' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200 dark:bg-amber-400/10 dark:text-amber-300 dark:ring-amber-400/20',
                'dot' => 'bg-amber-500',
            ],
            in_array($normalized, ['rejected', 'cancelled', 'failed'], true) => [
                'pill' => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200 dark:bg-rose-400/10 dark:text-rose-300 dark:ring-rose-400/20',
                'dot' => 'bg-rose-500',
            ],
            default => [
                'pill' => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700',
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
                setQuery(value) {
                    this.query = value;
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
    class="space-y-4"
>
    <x-user.page-header
        eyebrow="Support"
        title="Help Center"
        subtitle="Search help topics, explore FAQs, review recent support activity, and contact the BoardMatch team from one place."
    >
        <x-slot:actions>
            <button
                type="button"
                @click="openForm('')"
                class="inline-flex h-9 items-center justify-center gap-2 rounded-xl bg-[#2563eb] px-3.5 text-xs font-semibold text-white shadow-sm transition hover:bg-[#1d4ed8]"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    {!! $iconPath('ticket') !!}
                </svg>
                Submit Ticket
            </button>
            <a
                href="{{ route('user.messages.index') }}"
                class="inline-flex h-9 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    {!! $iconPath('chat') !!}
                </svg>
                Contact Support
            </a>
        </x-slot:actions>
    </x-user.page-header>

    <section class="overflow-hidden rounded-[1.35rem] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="bg-[radial-gradient(circle_at_top_left,_rgba(37,99,235,0.16),_transparent_18rem)] px-4 py-4 sm:px-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <p class="text-[0.68rem] font-extrabold uppercase tracking-[0.18em] text-[#2563eb] dark:text-blue-300">Support Hub</p>
                    <h2 class="mt-1.5 text-[1.5rem] font-black tracking-tight text-slate-950 dark:text-white">Find answers fast and keep support organized.</h2>
                    <p class="mt-1.5 text-sm leading-6 text-slate-500 dark:text-slate-400">Use search to narrow FAQs and categories, or jump straight into a ticket when you need direct help.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-[11px] font-semibold text-blue-700 ring-1 ring-blue-100 dark:bg-blue-400/10 dark:text-blue-300 dark:ring-blue-400/20">
                        <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                        Average reply: 24 to 48 hours
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold text-slate-600 ring-1 ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700">
                        Student account support
                    </span>
                </div>
            </div>

            <div class="mt-4 rounded-[1.15rem] border border-slate-200/90 bg-white/90 p-3 shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-900/90">
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        {!! $iconPath('magnifying-glass') !!}
                    </svg>
                    <input
                        x-model="query"
                        type="search"
                        class="ui-input h-10 pl-10 pr-11 text-sm"
                        placeholder="Search help topics, reservation concerns, payments, messages, or technical issues..."
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

                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($supportChips as $chip)
                        <button
                            type="button"
                            @click="setQuery(@js($chip))"
                            class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-blue-400/20 dark:hover:bg-blue-400/10 dark:hover:text-blue-300"
                        >
                            {{ $chip }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    @if (session('success'))
        <div class="rounded-[1.1rem] border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($supportStats as $stat)
            @php($tone = $statToneClasses($stat['tone']))
            <article class="bm-stat-card {{ $tone['card'] }} p-3.5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.12em] {{ $tone['label'] }}">{{ $stat['label'] }}</p>
                        <p class="mt-2 text-[1.5rem] font-black leading-none text-slate-950 dark:text-white">{{ $stat['value'] }}</p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $tone['icon'] }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            {!! $iconPath($stat['icon']) !!}
                        </svg>
                    </span>
                </div>
                <p class="mt-2 text-[12px] leading-5 text-slate-500 dark:text-slate-400">{{ $stat['caption'] }}</p>
            </article>
        @endforeach
    </section>

    <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_320px]">
        <main class="space-y-4">
            <section class="ui-card p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-bold text-slate-950 dark:text-white">Quick Actions</h2>
                        <p class="mt-1 text-[13px] text-slate-500 dark:text-slate-400">Start a support task in one step.</p>
                    </div>
                </div>

                <div class="mt-4 grid gap-3 md:grid-cols-3">
                    @foreach ($quickActions as $action)
                        @php($tone = $actionToneClasses($action['tone']))
                        <article class="rounded-[1.1rem] border border-slate-200 bg-white p-3.5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                            <div class="flex items-start gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $tone['wrap'] }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        {!! $iconPath($action['icon']) !!}
                                    </svg>
                                </span>
                                <div class="min-w-0">
                                    <h3 class="text-sm font-bold text-slate-950 dark:text-white">{{ $action['title'] }}</h3>
                                    <p class="mt-1 text-[12px] leading-5 text-slate-500 dark:text-slate-400">{{ $action['description'] }}</p>
                                </div>
                            </div>

                            <div class="mt-3">
                                @if (($action['action'] ?? null) === 'form')
                                    <button
                                        type="button"
                                        @click="openForm(@js($action['concern']))"
                                        class="inline-flex h-8 items-center justify-center rounded-lg border px-3 text-[11px] font-semibold transition {{ $tone['button'] }}"
                                    >
                                        Open
                                    </button>
                                @else
                                    <a
                                        href="{{ $action['href'] }}"
                                        class="inline-flex h-8 items-center justify-center rounded-lg border px-3 text-[11px] font-semibold transition {{ $tone['button'] }}"
                                    >
                                        Open
                                    </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="ui-card p-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-bold text-slate-950 dark:text-white">Popular Help Categories</h2>
                        <p class="mt-1 text-[13px] text-slate-500 dark:text-slate-400">Browse the most common support topics and jump to related answers.</p>
                    </div>
                    <button
                        type="button"
                        @click="jumpTo('support-request')"
                        class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-[11px] font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        Create Ticket
                    </button>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($popularCategories as $category)
                        <button
                            type="button"
                            x-show="matchesSearch(@js($category['search'].' '.$category['title'].' '.$category['description']))"
                            @click="jumpTo('faq-section', {{ $category['faq_index'] }})"
                            class="group rounded-[1.1rem] border border-slate-200 bg-white p-3.5 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:bg-blue-50/40 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-400/20 dark:hover:bg-blue-400/5"
                        >
                            <div class="flex items-start gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-700 ring-1 ring-blue-100 dark:bg-blue-400/10 dark:text-blue-300 dark:ring-blue-400/20">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        {!! $iconPath($category['icon']) !!}
                                    </svg>
                                </span>
                                <div class="min-w-0">
                                    <h3 class="text-sm font-bold text-slate-950 dark:text-white">{{ $category['title'] }}</h3>
                                    <p class="mt-1 text-[12px] leading-5 text-slate-500 dark:text-slate-400">{{ $category['description'] }}</p>
                                    <span class="mt-3 inline-flex items-center gap-1 text-[11px] font-semibold text-blue-700 dark:text-blue-300">
                                        View related FAQs
                                        <svg class="h-3.5 w-3.5 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6" />
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </button>
                    @endforeach
                </div>
            </section>

            <section id="faq-section" class="ui-card overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-4 py-3.5 dark:border-slate-800">
                    <div>
                        <h2 class="text-sm font-bold text-slate-950 dark:text-white">Frequently Asked Questions</h2>
                        <p class="mt-1 text-[13px] text-slate-500 dark:text-slate-400">Fast answers for the most common support concerns.</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        {{ count($faqs) }} FAQs
                    </span>
                </div>

                <div class="divide-y divide-slate-200 dark:divide-slate-800">
                    @foreach ($faqs as $index => $faq)
                        <article
                            x-show="matchesSearch(@js($faq['search'].' '.$faq['question'].' '.$faq['answer']))"
                            class="px-4 py-3.5"
                        >
                            <button
                                type="button"
                                @click="toggleFaq({{ $index }})"
                                class="flex w-full items-start justify-between gap-4 text-left"
                            >
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-950 dark:text-white">{{ $faq['question'] }}</p>
                                    <p class="mt-1 text-[12px] font-medium uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500">{{ str_replace('-', ' ', $faq['topic']) }}</p>
                                </div>
                                <span
                                    class="mt-0.5 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-500 transition dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                    :class="openFaq === {{ $index }} ? 'rotate-180 border-blue-200 text-blue-600 dark:border-blue-400/20 dark:text-blue-300' : ''"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                                    </svg>
                                </span>
                            </button>

                            <div
                                x-show="openFaq === {{ $index }}"
                                x-transition.opacity.duration.150ms
                                x-cloak
                                class="pt-3"
                            >
                                <p class="text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $faq['answer'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="ui-card overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-4 py-3.5 dark:border-slate-800">
                    <div>
                        <h2 class="text-sm font-bold text-slate-950 dark:text-white">Recent Support Requests</h2>
                        <p class="mt-1 text-[13px] text-slate-500 dark:text-slate-400">Review your latest submitted concerns and current status.</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        {{ $recentSupportRequests->count() }} recent
                    </span>
                </div>

                <div class="divide-y divide-slate-200 dark:divide-slate-800">
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
                            class="px-4 py-3.5"
                        >
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-sm font-bold text-slate-950 dark:text-white">{{ $supportRequest->subject }}</h3>
                                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-[0.12em] {{ $statusTone['pill'] }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $statusTone['dot'] }}"></span>
                                            {{ $supportRequest->status ?: 'Pending' }}
                                        </span>
                                    </div>
                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-[11px] text-slate-400 dark:text-slate-500">
                                        <span>Request #SR-{{ str_pad((string) $supportRequest->id, 5, '0', STR_PAD_LEFT) }}</span>
                                        <span class="text-slate-300 dark:text-slate-600">-</span>
                                        <span>{{ $supportRequest->concern_type }}</span>
                                        <span class="text-slate-300 dark:text-slate-600">-</span>
                                        <span>{{ optional($supportRequest->created_at)->format('M d, Y h:i A') }}</span>
                                    </div>
                                    <p class="mt-2 text-[13px] leading-6 text-slate-500 dark:text-slate-400">{{ \Illuminate\Support\Str::limit($supportRequest->message, 180) }}</p>
                                </div>

                                <div class="flex shrink-0 items-center gap-2">
                                    @if ($supportRequest->screenshot)
                                        <span class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 text-[11px] font-semibold text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                {!! $iconPath('paper-clip') !!}
                                            </svg>
                                            Attachment
                                        </span>
                                    @endif
                                    <span class="inline-flex h-8 items-center rounded-lg bg-slate-100 px-2.5 text-[11px] font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                        {{ optional($supportRequest->created_at)->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="px-4 py-10 text-center">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    {!! $iconPath('document-text') !!}
                                </svg>
                            </div>
                            <h3 class="mt-3 text-base font-bold text-slate-950 dark:text-white">No support requests yet</h3>
                            <p class="mx-auto mt-2 max-w-lg text-[13px] leading-6 text-slate-500 dark:text-slate-400">When you submit a support ticket, its latest status and summary will appear here.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section id="support-request" class="ui-card p-4 sm:p-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-950 dark:text-white">Still Need Help?</h2>
                        <p class="mt-1 text-[13px] leading-6 text-slate-500 dark:text-slate-400">Send a support request and include as much detail as possible so the team can respond faster.</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700 ring-1 ring-blue-100 dark:bg-blue-400/10 dark:text-blue-300 dark:ring-blue-400/20">
                        Ticket form
                    </span>
                </div>

                <form method="POST" action="{{ route('user.help-center.store') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                    @csrf

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">
                            Full Name
                            <input
                                name="full_name"
                                value="{{ old('full_name', $tenant->name ?? '') }}"
                                required
                                class="ui-input mt-1 text-sm"
                            >
                            @error('full_name')
                                <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>
                            @enderror
                        </label>

                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">
                            Email Address
                            <input
                                name="email"
                                type="email"
                                value="{{ old('email', $tenant->email ?? '') }}"
                                required
                                class="ui-input mt-1 text-sm"
                            >
                            @error('email')
                                <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>
                            @enderror
                        </label>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">
                            Concern Type
                            <div class="relative mt-1">
                                <select
                                    name="concern_type"
                                    x-model="supportForm.concernType"
                                    required
                                    class="ui-input appearance-none pr-10 text-sm"
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
                                <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>
                            @enderror
                        </label>

                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">
                            Subject
                            <input
                                name="subject"
                                value="{{ old('subject') }}"
                                required
                                class="ui-input mt-1 text-sm"
                                placeholder="Summarize your concern"
                            >
                            @error('subject')
                                <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>
                            @enderror
                        </label>
                    </div>

                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">
                        Message
                        <textarea
                            name="message"
                            rows="5"
                            required
                            class="ui-input mt-1 resize-y py-3 text-sm"
                            placeholder="Describe what happened, what you expected, and any reservation, payment, or listing details that can help."
                        >{{ old('message') }}</textarea>
                        @error('message')
                            <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">
                        Attach Screenshot Optional
                        <input
                            name="screenshot"
                            type="file"
                            accept=".jpg,.jpeg,.png,.pdf"
                            class="mt-1 block w-full rounded-xl border border-dashed border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:file:bg-blue-400/10 dark:file:text-blue-300"
                        >
                        @error('screenshot')
                            <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>
                        @enderror
                    </label>

                    <div class="flex flex-col gap-3 border-t border-slate-200 pt-4 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-[12px] leading-5 text-slate-400 dark:text-slate-500">Supported files: JPG, PNG, PDF. Maximum upload size: 2MB.</p>
                        <button
                            type="submit"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-[#2563eb] px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1d4ed8]"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                {!! $iconPath('ticket') !!}
                            </svg>
                            Submit Request
                        </button>
                    </div>
                </form>
            </section>
        </main>

        <aside class="space-y-4">
            <section class="ui-card p-4">
                <div class="flex items-start gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-700 ring-1 ring-blue-100 dark:bg-blue-400/10 dark:text-blue-300 dark:ring-blue-400/20">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            {!! $iconPath('lifebuoy') !!}
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-sm font-bold text-slate-950 dark:text-white">Help & Contact</h2>
                        <p class="mt-1 text-[13px] leading-5 text-slate-500 dark:text-slate-400">Reach the right channel based on the type of support you need.</p>
                    </div>
                </div>

                <div class="mt-4 space-y-3">
                    @foreach ($contactItems as $item)
                        <div class="rounded-[1rem] border border-slate-200 bg-slate-50/70 p-3 dark:border-slate-800 dark:bg-slate-800/40">
                            <div class="flex items-start gap-3">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white text-slate-600 ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-300 dark:ring-slate-700">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        {!! $iconPath($item['icon']) !!}
                                    </svg>
                                </span>
                                <div class="min-w-0">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500">{{ $item['label'] }}</p>
                                    @if (isset($item['href']))
                                        <a href="{{ $item['href'] }}" class="mt-1 block text-sm font-semibold text-slate-950 transition hover:text-blue-700 dark:text-white dark:hover:text-blue-300">{{ $item['value'] }}</a>
                                    @elseif (\Illuminate\Support\Str::contains($item['value'], '@'))
                                        <a href="mailto:{{ $item['value'] }}" class="mt-1 block text-sm font-semibold text-slate-950 transition hover:text-blue-700 dark:text-white dark:hover:text-blue-300">{{ $item['value'] }}</a>
                                    @else
                                        <p class="mt-1 text-sm font-semibold text-slate-950 dark:text-white">{{ $item['value'] }}</p>
                                    @endif
                                    <p class="mt-1 text-[12px] leading-5 text-slate-500 dark:text-slate-400">{{ $item['subtext'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="ui-card p-4">
                <div class="flex items-start gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-700 ring-1 ring-amber-100 dark:bg-amber-400/10 dark:text-amber-300 dark:ring-amber-400/20">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            {!! $iconPath('bolt') !!}
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-sm font-bold text-slate-950 dark:text-white">Before You Submit</h2>
                        <p class="mt-1 text-[13px] leading-5 text-slate-500 dark:text-slate-400">A little detail upfront helps support move faster.</p>
                    </div>
                </div>

                <ul class="mt-4 space-y-3">
                    @foreach ($supportTips as $tip)
                        <li class="flex gap-3 text-[13px] leading-6 text-slate-500 dark:text-slate-400">
                            <span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-blue-500"></span>
                            <span>{{ $tip }}</span>
                        </li>
                    @endforeach
                </ul>

                <button
                    type="button"
                    @click="openForm('Technical Problem')"
                    class="mt-4 inline-flex h-9 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        {!! $iconPath('bug') !!}
                    </svg>
                    Report a Technical Problem
                </button>
            </section>
        </aside>
    </div>
</div>
</x-user.shell>
</x-layouts.dashboard>
