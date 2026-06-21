<x-layouts.dashboard>
<x-user.shell>
@php
    $quickHelpCards = [
        [
            'topic' => 'getting-started',
            'title' => 'Getting Started',
            'body' => 'Learn how to use BoardMatch, update your profile, and set your boarding house preferences.',
        ],
        [
            'topic' => 'find-boarding-houses',
            'title' => 'Find Boarding Houses',
            'body' => 'Browse available boarding houses, view room details, check prices, and compare locations.',
        ],
        [
            'topic' => 'matchmaking',
            'title' => 'Matchmaking Guide',
            'body' => 'Understand how BoardMatch recommends boarding houses based on your budget, location, lifestyle, and preferences.',
        ],
        [
            'topic' => 'reservations',
            'title' => 'Reservations',
            'body' => 'Learn how to reserve a room, check your reservation status, and cancel or update a request.',
        ],
        [
            'topic' => 'payments',
            'title' => 'Payments and Transactions',
            'body' => 'View payment methods, confirm payments, check transaction history, and track payment status.',
        ],
        [
            'topic' => 'messages',
            'title' => 'Messages',
            'body' => 'Communicate with boarding house owners or managers about inquiries, room availability, and viewing schedules.',
        ],
        [
            'topic' => 'account-security',
            'title' => 'Account and Security',
            'body' => 'Update your password, manage your profile, enable security options, and protect your account.',
        ],
    ];

    $faqs = [
        [
            'topic' => 'matchmaking',
            'question' => 'How does BoardMatch recommend boarding houses?',
            'answer' => 'BoardMatch uses your selected preferences such as preferred location, rental budget, room type, amenities, and lifestyle information to suggest boarding houses that best match your needs.',
        ],
        [
            'topic' => 'reservations',
            'question' => 'How do I reserve a boarding house?',
            'answer' => 'Go to Find Boarding Houses or Matchmaking, select a boarding house, view its details, then click Reserve or Inquire. Your reservation will appear in the Reservations page.',
        ],
        [
            'topic' => 'payments',
            'question' => 'Where can I see my payments?',
            'answer' => 'You can view your payment records in the Transactions page. This includes payment status, amount paid, date, and boarding house details.',
        ],
        [
            'topic' => 'payments',
            'question' => 'What does "Pending" payment mean?',
            'answer' => 'Pending means your payment or reservation is still waiting for confirmation from the owner or admin.',
        ],
        [
            'topic' => 'messages',
            'question' => 'How can I message a property owner?',
            'answer' => 'Open the Messages page and select the conversation connected to your inquiry or reservation. You can ask about room availability, rent, rules, or viewing schedules.',
        ],
        [
            'topic' => 'matchmaking',
            'question' => 'Can I change my preferences?',
            'answer' => 'Yes. Go to My Preferences and update your budget, location, room type, amenities, and lifestyle details. Your matchmaking results may change based on your updated information.',
        ],
        [
            'topic' => 'account-security',
            'question' => 'How do I update my profile?',
            'answer' => 'Go to Profile Settings. From there, you can update your personal information, contact details, profile photo, password, and security settings.',
        ],
        [
            'topic' => 'account-security',
            'question' => 'Why is my profile completeness not 100%?',
            'answer' => 'Your profile may still be missing information such as profile photo, contact details, personal information, or optional government ID.',
        ],
    ];

    $guides = [
        [
            'topic' => 'getting-started',
            'title' => 'Getting Started',
            'body' => 'Start by completing your profile, adding contact details, and setting your preferred location, room type, amenities, and budget. These details help BoardMatch personalize your account experience.',
        ],
        [
            'topic' => 'find-boarding-houses',
            'title' => 'Find Boarding Houses',
            'body' => 'Use Find Boarding Houses to browse listings, inspect room information, compare prices, review locations, and decide which properties are worth contacting or reserving.',
        ],
        [
            'topic' => 'matchmaking',
            'title' => 'Matchmaking Guide',
            'body' => 'Matchmaking uses your budget, location, room type, amenities, lifestyle, and preference details to surface boarding houses that better fit your needs.',
        ],
        [
            'topic' => 'reservations',
            'title' => 'Reservations',
            'body' => 'After choosing a boarding house, reserve or inquire from the listing page. Track pending, approved, cancelled, and updated reservation requests from Reservations.',
        ],
        [
            'topic' => 'payments',
            'title' => 'Payments and Transactions',
            'body' => 'Use Payments to manage payment methods and confirm payments. Use Transactions to review payment status, dates, amounts, and related boarding house records.',
        ],
        [
            'topic' => 'messages',
            'title' => 'Messages',
            'body' => 'Use Messages to ask owners or managers about availability, rental rules, viewing schedules, requirements, and updates related to your inquiries.',
        ],
        [
            'topic' => 'account-security',
            'title' => 'Account and Security',
            'body' => 'Use Profile Settings to keep your personal details current, update your password, manage security options, and protect your account information.',
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

    $tips = [
        'Keep your profile information complete to get better boarding house recommendations.',
        'Always check the boarding house details before making a reservation.',
        'Use Messages to confirm availability before visiting the property.',
        'Always review your Transactions page after making a payment.',
        'Do not share your password or payment information with anyone.',
    ];
@endphp

<div class="space-y-6">
    <div class="space-y-4">
        <x-user.page-header
            eyebrow="Support"
            title="Help Center"
            subtitle="Find answers, guides, and support for using your BoardMatch Student Housing account."
        >
            <x-slot:actions>
                <a href="#support-request" data-scroll-target="#support-request" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700">
                    Submit Request
                </a>
            </x-slot:actions>
        </x-user.page-header>

        <div class="relative">
            <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.2-5.2m1.7-4.3a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="search" data-help-search class="ui-input pl-11" placeholder="Search help topics, FAQs, payments, reservations, or messages...">
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4" data-help-cards>
        @foreach ($quickHelpCards as $index => $card)
            <button type="button"
                    data-help-card
                    data-topic="{{ $card['topic'] }}"
                    data-target="#guide-{{ $card['topic'] }}"
                    data-help-text="{{ Str::lower($card['title'].' '.$card['body']) }}"
                    class="ui-card p-5 text-left transition hover:-translate-y-0.5 hover:border-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-sm font-bold text-indigo-600">
                    {{ $index + 1 }}
                </div>
                <h2 class="text-sm font-bold text-gray-900">{{ $card['title'] }}</h2>
                <p class="mt-2 text-sm leading-6 ui-muted">{{ $card['body'] }}</p>
            </button>
        @endforeach
    </section>

    <p data-help-empty class="hidden rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
        No help topic found. Try another keyword.
    </p>

    <div class="grid gap-6 xl:grid-cols-[1fr_360px]">
        <div class="space-y-6">
            <section class="ui-card overflow-hidden" data-guide-section>
                <div class="border-b ui-border px-6 py-4">
                    <h2 class="text-base font-bold text-gray-900">Topic Guides</h2>
                </div>
                <div class="grid gap-4 p-6 md:grid-cols-2">
                    @foreach ($guides as $guide)
                        <article id="guide-{{ $guide['topic'] }}"
                                 data-help-guide
                                 data-topic="{{ $guide['topic'] }}"
                                 data-help-text="{{ Str::lower($guide['title'].' '.$guide['body']) }}"
                                 class="rounded-xl border border-slate-200 bg-white p-4 transition">
                            <h3 class="text-sm font-bold text-gray-900">{{ $guide['title'] }}</h3>
                            <p class="mt-2 text-sm leading-6 ui-muted">{{ $guide['body'] }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="ui-card overflow-hidden">
                <div class="border-b ui-border px-6 py-4">
                    <h2 class="text-base font-bold text-gray-900">Frequently Asked Questions</h2>
                </div>
                <div class="divide-y ui-border">
                    @foreach ($faqs as $faq)
                        <details class="group px-6 py-4"
                                 data-help-faq
                                 data-topic="{{ $faq['topic'] }}"
                                 data-help-text="{{ Str::lower($faq['question']) }}">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-sm font-semibold text-gray-900">
                                <span>{{ $faq['question'] }}</span>
                                <svg data-faq-arrow class="h-4 w-4 shrink-0 text-gray-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </summary>
                            <p class="mt-3 text-sm leading-6 ui-muted">{{ $faq['answer'] }}</p>
                        </details>
                    @endforeach
                </div>
            </section>

            <section id="support-request" data-support-request data-has-errors="{{ $errors->any() ? 'true' : 'false' }}" class="ui-card p-6">
                <div>
                    <h2 class="text-base font-bold text-gray-900">Still Need Help?</h2>
                    <p class="mt-1 text-sm ui-muted">If you cannot find the answer you need, send a support request and our team will assist you as soon as possible.</p>
                </div>

                <form method="POST" action="{{ route('user.help-center.store') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                    @csrf
                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="block text-sm font-medium text-gray-700">
                            Full Name
                            <input name="full_name" value="{{ old('full_name', $tenant->name ?? '') }}" required class="mt-1 ui-input text-sm">
                            @error('full_name') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
                        </label>
                        <label class="block text-sm font-medium text-gray-700">
                            Email Address
                            <input name="email" type="email" value="{{ old('email', $tenant->email ?? '') }}" required class="mt-1 ui-input text-sm">
                            @error('email') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
                        </label>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="block text-sm font-medium text-gray-700">
                            Concern Type
                            <select name="concern_type" required class="mt-1 ui-input text-sm">
                                <option value="">Select concern type</option>
                                @foreach ($concernTypes as $type)
                                    <option value="{{ $type }}" @selected(old('concern_type') === $type)>{{ $type }}</option>
                                @endforeach
                            </select>
                            @error('concern_type') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
                        </label>
                        <label class="block text-sm font-medium text-gray-700">
                            Subject
                            <input name="subject" value="{{ old('subject') }}" required class="mt-1 ui-input text-sm">
                            @error('subject') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
                        </label>
                    </div>

                    <label class="block text-sm font-medium text-gray-700">
                        Message
                        <textarea name="message" rows="5" required class="mt-1 ui-input text-sm resize-y" placeholder="Describe your concern...">{{ old('message') }}</textarea>
                        @error('message') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
                    </label>

                    <label class="block text-sm font-medium text-gray-700">
                        Attach Screenshot optional
                        <input name="screenshot" type="file" accept=".jpg,.jpeg,.png,.pdf" class="mt-1 block w-full rounded-lg border border-dashed border-slate-300 bg-white px-3 py-2 text-sm text-gray-600 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100">
                        @error('screenshot') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
                    </label>

                    <div class="flex justify-end">
                        <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            Submit Request
                        </button>
                    </div>
                </form>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="ui-card p-6">
                <h2 class="text-base font-bold text-gray-900">Contact Information</h2>
                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="font-semibold text-gray-900">Email Support</dt>
                        <dd class="mt-1 ui-muted">support@boardmatch.com</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-900">Office Hours</dt>
                        <dd class="mt-1 ui-muted">Monday to Friday, 8:00 AM to 5:00 PM</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-900">Response Time</dt>
                        <dd class="mt-1 ui-muted">Usually within 24 to 48 hours.</dd>
                    </div>
                </dl>
            </section>

            <section class="ui-card p-6">
                <h2 class="text-base font-bold text-gray-900">Helpful Tips</h2>
                <ul class="mt-4 space-y-3">
                    @foreach ($tips as $tip)
                        <li class="flex gap-3 text-sm leading-6 ui-muted">
                            <span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-indigo-500"></span>
                            <span>{{ $tip }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>
        </aside>
    </div>
</div>

<script>
(() => {
    const searchInput = document.querySelector('[data-help-search]');
    const cards = [...document.querySelectorAll('[data-help-card]')];
    const guides = [...document.querySelectorAll('[data-help-guide]')];
    const faqs = [...document.querySelectorAll('[data-help-faq]')];
    const emptyState = document.querySelector('[data-help-empty]');
    const guideSection = document.querySelector('[data-guide-section]');
    const supportSection = document.querySelector('[data-support-request]');

    const normalize = (value) => (value || '').toLowerCase().trim();

    const scrollToElement = (selectorOrElement) => {
        const target = typeof selectorOrElement === 'string'
            ? document.querySelector(selectorOrElement)
            : selectorOrElement;

        if (!target) {
            return;
        }

        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    const setSelectedTopic = (topic) => {
        cards.forEach((card) => {
            card.classList.toggle('ring-2', card.dataset.topic === topic);
            card.classList.toggle('ring-indigo-400', card.dataset.topic === topic);
            card.classList.toggle('border-indigo-200', card.dataset.topic === topic);
        });

        guides.forEach((guide) => {
            guide.classList.toggle('ring-2', guide.dataset.topic === topic);
            guide.classList.toggle('ring-indigo-300', guide.dataset.topic === topic);
            guide.classList.toggle('border-indigo-300', guide.dataset.topic === topic);
            guide.classList.toggle('bg-indigo-50', guide.dataset.topic === topic);
        });
    };

    const filterItems = () => {
        const query = normalize(searchInput?.value);
        let visibleCount = 0;

        [...cards, ...guides, ...faqs].forEach((item) => {
            const matches = query === '' || normalize(item.dataset.helpText).includes(query);
            item.classList.toggle('hidden', !matches);

            if (matches && (item.hasAttribute('data-help-card') || item.hasAttribute('data-help-faq'))) {
                visibleCount += 1;
            }

            if (!matches && item instanceof HTMLDetailsElement) {
                item.open = false;
            }
        });

        const visibleGuides = guides.some((guide) => !guide.classList.contains('hidden'));
        guideSection?.classList.toggle('hidden', query !== '' && !visibleGuides);
        emptyState?.classList.toggle('hidden', query === '' || visibleCount > 0 || visibleGuides);
    };

    cards.forEach((card) => {
        card.addEventListener('click', () => {
            setSelectedTopic(card.dataset.topic);
            scrollToElement(card.dataset.target);

            const relatedFaq = faqs.find((faq) => faq.dataset.topic === card.dataset.topic);
            if (relatedFaq) {
                faqs.forEach((faq) => {
                    faq.open = faq === relatedFaq;
                });
            }
        });
    });

    faqs.forEach((faq) => {
        faq.addEventListener('toggle', () => {
            if (!faq.open) {
                return;
            }

            faqs.forEach((otherFaq) => {
                if (otherFaq !== faq) {
                    otherFaq.open = false;
                }
            });
        });
    });

    document.querySelectorAll('[data-scroll-target]').forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            scrollToElement(trigger.dataset.scrollTarget);
        });
    });

    searchInput?.addEventListener('input', filterItems);
    filterItems();

    if (supportSection?.dataset.hasErrors === 'true') {
        window.setTimeout(() => scrollToElement(supportSection), 50);
    }
})();
</script>
</x-user.shell>
</x-layouts.dashboard>
