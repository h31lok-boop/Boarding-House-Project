<!DOCTYPE html>
<html lang="en" class="scroll-smooth" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DSSC Boarding House System</title>
    <x-theme-init />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="landing-page min-h-screen overflow-x-hidden">
@php
    $r = fn (string $name, array $params = [], ?string $fallback = null) => \Illuminate\Support\Facades\Route::has($name)
        ? route($name, $params)
        : ($fallback ?? '#');

    $loginUrl = \Illuminate\Support\Facades\Route::has('login')
        ? route('login')
        : $r('auth.choice', [], '#contact');
    $registerUrl = \Illuminate\Support\Facades\Route::has('register') ? route('register') : null;
    $browseUrl = $r('tenant.boarding-houses', [], $r('user.boarding-houses.index', [], '#listings'));

    $navItems = [
        ['label' => 'Home', 'href' => '#home'],
        ['label' => 'Features', 'href' => '#features'],
        ['label' => 'Listings', 'href' => '#listings'],
        ['label' => 'Gallery', 'href' => '#gallery'],
        ['label' => 'Testimonials', 'href' => '#testimonials'],
        ['label' => 'Contact', 'href' => '#contact'],
    ];

    $features = [
        ['title' => 'Verified Boarding Houses', 'description' => 'Browse listings reviewed for student-friendly stay requirements.', 'tone' => 'blue', 'icon' => 'shield'],
        ['title' => 'Easy Search', 'description' => 'Filter by location, price, amenities, and room availability.', 'tone' => 'purple', 'icon' => 'search'],
        ['title' => 'Safe & Secure', 'description' => 'Find places with clear owner details and safety information.', 'tone' => 'emerald', 'icon' => 'lock'],
        ['title' => 'Direct Inquiry', 'description' => 'Send questions to owners before applying or reserving.', 'tone' => 'orange', 'icon' => 'message'],
        ['title' => 'Room Availability', 'description' => 'Check available rooms, bed spaces, and status updates.', 'tone' => 'teal', 'icon' => 'calendar'],
        ['title' => 'Student Friendly', 'description' => 'Designed around DSSC students looking for reliable housing.', 'tone' => 'sky', 'icon' => 'users'],
    ];

    $listings = [
        [
            'name' => 'MetroNest Boarding Hub',
            'location' => 'Purok 5, Goma, Digos City',
            'price' => 'PHP 6,000 to PHP 7,200/month',
            'rating' => '4.8',
            'status' => 'Available',
            'amenities' => ['WiFi', 'Study Area', 'CCTV'],
            'image' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=900&q=80',
        ],
        [
            'name' => 'Casa Digos Boarding Stay',
            'location' => 'Purok 6, Igpit, Digos City',
            'price' => 'PHP 3,500 to PHP 4,700/month',
            'rating' => '4.6',
            'status' => 'Available',
            'amenities' => ['Kitchen', 'Laundry', 'Parking'],
            'image' => 'https://images.unsplash.com/photo-1560185007-c5ca9d2c014d?auto=format&fit=crop&w=900&q=80',
        ],
        [
            'name' => 'Sunrise Student Boarding House',
            'location' => 'Purok 1, Aplaya, Digos City',
            'price' => 'PHP 2,800 to PHP 4,000/month',
            'rating' => '4.7',
            'status' => 'Few slots left',
            'amenities' => ['Near Campus', 'Water', 'Security'],
            'image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=900&q=80',
        ],
    ];

    $steps = [
        ['title' => 'Search', 'description' => 'Find listings near your school, workplace, or preferred barangay.'],
        ['title' => 'Compare', 'description' => 'Review prices, amenities, ratings, and room availability.'],
        ['title' => 'Inquire', 'description' => 'Message owners and ask about rules, schedules, and requirements.'],
        ['title' => 'Move In', 'description' => 'Reserve a room and track your application from your dashboard.'],
    ];

    $gallery = [
        ['src' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=900&q=80', 'label' => 'Clean shared rooms'],
        ['src' => 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=900&q=80', 'label' => 'Private bed spaces'],
        ['src' => 'https://images.unsplash.com/photo-1554995207-c18c203602cb?auto=format&fit=crop&w=900&q=80', 'label' => 'Common study areas'],
        ['src' => 'https://images.unsplash.com/photo-1484154218962-a197022b5858?auto=format&fit=crop&w=900&q=80', 'label' => 'Shared kitchen access'],
    ];

    $testimonials = [
        ['initials' => 'MS', 'name' => 'Maria Santos', 'rating' => '5.0', 'comment' => 'The system helped me compare locations quickly and contact the owner before reserving.'],
        ['initials' => 'JR', 'name' => 'John Reyes', 'rating' => '4.9', 'comment' => 'Saved listings and clear room details made it easier to choose a boarding house.'],
        ['initials' => 'AL', 'name' => 'Ana Lopez', 'rating' => '4.8', 'comment' => 'I liked seeing availability and safety details before sending my application.'],
    ];

    $icon = function (string $name, string $class = 'h-5 w-5') {
        $icons = [
            'home' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 11.5 12 5l8 6.5V20a1 1 0 0 1-1 1h-5v-6h-4v6H5a1 1 0 0 1-1-1v-8.5Z"/></svg>',
            'shield' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3 5 6v5.5c0 4.2 2.7 7.9 7 9.5 4.3-1.6 7-5.3 7-9.5V6l-7-3Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m8.5 12 2.3 2.3 4.7-5"/></svg>',
            'search' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="7" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m20 20-3.5-3.5"/></svg>',
            'lock' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="5" y="10" width="14" height="10" rx="2" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10V8a4 4 0 1 1 8 0v2"/></svg>',
            'message' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 6.5A2.5 2.5 0 0 1 7.5 4h9A2.5 2.5 0 0 1 19 6.5v6A2.5 2.5 0 0 1 16.5 15H9l-4 3V6.5Z"/></svg>',
            'calendar' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="4" y="5" width="16" height="15" rx="2" stroke-width="1.8"/><path stroke-linecap="round" stroke-width="1.8" d="M8 3v4M16 3v4M7 11h10"/></svg>',
            'users' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="9" cy="8" r="3.5" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.5 20a5.5 5.5 0 0 1 11 0"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11a3 3 0 1 0 0-6M17 20h3.5a4.5 4.5 0 0 0-5-4.5"/></svg>',
            'star' => '<svg class="'.$class.'" viewBox="0 0 20 20" fill="currentColor"><path d="m10 1.5 2.5 5.2 5.7.8-4.1 4 1 5.7-5.1-2.7-5.1 2.7 1-5.7-4.1-4 5.7-.8L10 1.5Z"/></svg>',
            'arrow' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 12h14M13 6l6 6-6 6"/></svg>',
            'menu' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M4 7h16M4 12h16M4 17h16"/></svg>',
            'x' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6l12 12M18 6 6 18"/></svg>',
            'sun' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="4" stroke-width="1.8"/><path stroke-linecap="round" stroke-width="1.8" d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>',
            'moon' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 14.4A7.5 7.5 0 0 1 9.6 3 9 9 0 1 0 21 14.4Z"/></svg>',
            'heart' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m12 20-1.8-1.7C5.8 14.3 3 11.7 3 8.5A4.5 4.5 0 0 1 7.5 4c1.7 0 3.3.8 4.5 2.1A6 6 0 0 1 16.5 4 4.5 4.5 0 0 1 21 8.5c0 3.2-2.8 5.8-7.2 9.8L12 20Z"/></svg>',
        ];

        return $icons[$name] ?? $icons['home'];
    };
@endphp

<header class="sticky top-0 z-50 px-4 py-3 sm:px-6 lg:px-8">
    <div class="landing-nav mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-3">
        <a href="#home" class="flex min-w-0 items-center gap-3">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-white/15 bg-slate-950 text-white">
                {!! $icon('home', 'h-5 w-5') !!}
            </span>
            <span class="min-w-0 leading-tight">
                <span class="block truncate text-sm font-extrabold tracking-wide text-slate-950">DSSC BOARDING</span>
                <span class="block truncate text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">HOUSE SYSTEM</span>
            </span>
        </a>

        <nav class="hidden items-center gap-1 lg:flex">
            @foreach ($navItems as $item)
                <a href="{{ $item['href'] }}" class="rounded-xl px-3.5 py-2 text-sm font-bold text-slate-600 transition hover:bg-slate-100 hover:text-slate-950">{{ $item['label'] }}</a>
            @endforeach
        </nav>

        <div class="hidden items-center gap-2 lg:flex">
            <x-theme-toggle class="landing-icon-button" />
            <a href="{{ $loginUrl }}" class="landing-button landing-button-secondary">Sign In</a>
            @if ($registerUrl)
                <a href="{{ $registerUrl }}" class="landing-button landing-button-primary">Register</a>
            @endif
        </div>

        <button type="button" id="landing-menu-button" class="landing-icon-button lg:hidden" aria-label="Open navigation" aria-expanded="false">
            <span data-menu-open>{!! $icon('menu', 'h-5 w-5') !!}</span>
            <span data-menu-close class="hidden">{!! $icon('x', 'h-5 w-5') !!}</span>
        </button>
    </div>

    <div id="landing-mobile-menu" class="landing-nav mx-auto mt-3 hidden max-w-7xl p-3 lg:hidden">
        <nav class="grid gap-1">
            @foreach ($navItems as $item)
                <a href="{{ $item['href'] }}" class="rounded-xl px-3 py-2 text-sm font-bold text-slate-700 hover:bg-slate-100">{{ $item['label'] }}</a>
            @endforeach
        </nav>
        <div class="mt-3 grid gap-2 border-t border-slate-200 pt-3 sm:grid-cols-3">
            <x-theme-toggle class="landing-button landing-button-secondary justify-center" show-label prefix="Theme:" />
            <a href="{{ $loginUrl }}" class="landing-button landing-button-secondary justify-center">Sign In</a>
            @if ($registerUrl)
                <a href="{{ $registerUrl }}" class="landing-button landing-button-primary justify-center">Register</a>
            @endif
        </div>
    </div>
</header>

<main>
    <section id="home" class="px-4 pb-14 pt-6 sm:px-6 lg:px-8 lg:pb-20">
        <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[minmax(0,1.05fr)_minmax(420px,0.95fr)] lg:items-center">
            <div class="space-y-7">
                <span class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-bold text-emerald-700">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Now accepting reservations for 2026
                </span>

                <div class="space-y-4">
                    <h1 class="max-w-3xl text-4xl font-black tracking-tight text-slate-950 sm:text-5xl lg:text-6xl">
                        Find Your Perfect Boarding House
                    </h1>
                    <p class="max-w-2xl text-base leading-7 text-slate-600 sm:text-lg">
                        Browse safe, verified, and student-friendly boarding houses near your school or workplace.
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <a href="{{ $browseUrl }}" class="landing-button landing-button-primary justify-center px-6 py-3">
                        Browse Listings
                        {!! $icon('arrow', 'h-4 w-4') !!}
                    </a>
                    <a href="{{ $loginUrl }}" class="landing-button landing-button-secondary justify-center px-6 py-3">Sign In</a>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    @foreach ([['value' => '500+', 'label' => 'Happy Residents'], ['value' => '98%', 'label' => 'Satisfaction'], ['value' => '15+', 'label' => 'Years Experience']] as $stat)
                        <article class="landing-card p-4">
                            <p class="text-2xl font-black text-slate-950">{{ $stat['value'] }}</p>
                            <p class="mt-1 text-sm font-semibold text-slate-500">{{ $stat['label'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>

            <div class="landing-hero-media">
                <img src="https://images.unsplash.com/photo-1560185007-5f0bb1866cab?auto=format&fit=crop&w=1200&q=85" alt="Modern boarding house room" class="h-full min-h-[360px] w-full rounded-2xl object-cover shadow-2xl">
                <article class="landing-card absolute bottom-4 left-4 right-4 p-4 sm:left-auto sm:w-80">
                    <div class="flex gap-3">
                        <img src="{{ $listings[0]['image'] }}" alt="MetroNest Boarding Hub" class="h-20 w-24 shrink-0 rounded-xl object-cover">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-black text-slate-950">MetroNest Boarding Hub</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">Purok 5, Goma</p>
                            <div class="mt-2 flex items-center justify-between gap-2">
                                <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700">Available</span>
                                <span class="inline-flex items-center gap-1 text-sm font-bold text-amber-600">{!! $icon('star', 'h-4 w-4') !!} 4.8</span>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section id="features" class="landing-section">
        <div class="landing-section-header">
            <span class="landing-eyebrow">Features</span>
            <h2>Why Choose DSSC Boarding?</h2>
            <p>Everything tenants and owners need in one consistent boarding house system.</p>
        </div>

        <div class="mx-auto grid max-w-7xl gap-4 px-4 sm:grid-cols-2 sm:px-6 lg:grid-cols-3 lg:px-8">
            @foreach ($features as $feature)
                <article class="landing-card p-5">
                    <span class="landing-feature-icon landing-feature-{{ $feature['tone'] }}">
                        {!! $icon($feature['icon'], 'h-6 w-6') !!}
                    </span>
                    <h3 class="mt-5 text-lg font-black text-slate-950">{{ $feature['title'] }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $feature['description'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section id="listings" class="landing-section bg-white/60">
        <div class="landing-section-header">
            <span class="landing-eyebrow">Featured Listings</span>
            <h2>Boarding Houses Near You</h2>
            <p>Preview popular listings using the same card style tenants see inside their dashboard.</p>
        </div>

        <div class="mx-auto grid max-w-7xl gap-5 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
            @foreach ($listings as $listing)
                <article class="landing-card overflow-hidden">
                    <img src="{{ $listing['image'] }}" alt="{{ $listing['name'] }}" class="h-48 w-full object-cover">
                    <div class="space-y-4 p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="text-lg font-black text-slate-950">{{ $listing['name'] }}</h3>
                                <p class="mt-1 text-sm font-semibold text-slate-500">{{ $listing['location'] }}</p>
                            </div>
                            <button type="button" class="landing-icon-button shrink-0" aria-label="Save {{ $listing['name'] }}">
                                {!! $icon('heart', 'h-5 w-5') !!}
                            </button>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">{{ $listing['status'] }}</span>
                            <span class="inline-flex items-center gap-1 text-sm font-bold text-amber-600">{!! $icon('star', 'h-4 w-4') !!} {{ $listing['rating'] }}</span>
                        </div>

                        <p class="text-sm font-black text-slate-800">{{ $listing['price'] }}</p>

                        <div class="flex flex-wrap gap-2">
                            @foreach ($listing['amenities'] as $amenity)
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">{{ $amenity }}</span>
                            @endforeach
                        </div>

                        <a href="{{ $browseUrl }}?q={{ urlencode($listing['name']) }}" class="landing-button landing-button-primary w-full justify-center">View Details</a>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="landing-section">
        <div class="landing-section-header">
            <span class="landing-eyebrow">How It Works</span>
            <h2>From Search To Move In</h2>
            <p>Simple steps for finding and managing a boarding house application.</p>
        </div>

        <div class="mx-auto grid max-w-7xl gap-4 px-4 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8">
            @foreach ($steps as $index => $step)
                <article class="landing-card p-5">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-700 text-sm font-black text-white">{{ $index + 1 }}</span>
                    <h3 class="mt-5 text-lg font-black text-slate-950">{{ $step['title'] }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $step['description'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section id="gallery" class="landing-section landing-gallery-section animate-on-scroll fade-up bg-white/60">
        <div class="landing-section-header">
            <span class="landing-eyebrow animate-on-scroll scale-in" style="--animation-delay: 100ms;">Gallery</span>
            <h2 class="animate-on-scroll fade-up" style="--animation-delay: 200ms;">Clean, Student-Friendly Spaces</h2>
            <p class="animate-on-scroll fade-up" style="--animation-delay: 300ms;">Rounded image cards keep the public page aligned with the dashboard design.</p>
        </div>

        <div class="mx-auto grid max-w-7xl gap-4 px-4 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8">
            @foreach ($gallery as $item)
                <button
                    type="button"
                    class="landing-card landing-gallery-card animate-on-scroll stagger-card overflow-hidden text-left"
                    style="--animation-delay: {{ ($loop->iteration) * 100 }}ms;"
                    data-gallery-index="{{ $loop->index }}"
                    aria-label="View {{ $item['label'] }}"
                >
                    <span class="landing-gallery-image">
                        <img src="{{ $item['src'] }}" alt="{{ $item['label'] }}" class="h-56 w-full object-cover">
                        <span class="landing-gallery-overlay">
                            <span class="landing-gallery-view">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" />
                                    <circle cx="12" cy="12" r="3" stroke-width="1.8" />
                                </svg>
                                View Photo
                            </span>
                        </span>
                    </span>
                    <span class="block p-4 text-sm font-bold text-slate-700">{{ $item['label'] }}</span>
                </button>
            @endforeach
        </div>
    </section>

    <div id="gallery-modal" class="landing-gallery-modal hidden" role="dialog" aria-modal="true" aria-labelledby="gallery-modal-title">
        <div class="landing-gallery-modal-panel">
            <button type="button" id="gallery-modal-close" class="landing-gallery-modal-close" aria-label="Close image preview">
                {!! $icon('x', 'h-5 w-5') !!}
            </button>

            <button type="button" id="gallery-modal-prev" class="landing-gallery-modal-arrow landing-gallery-modal-prev" aria-label="Previous image">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m15 18-6-6 6-6" />
                </svg>
            </button>

            <img id="gallery-modal-image" src="" alt="" class="landing-gallery-modal-image">

            <button type="button" id="gallery-modal-next" class="landing-gallery-modal-arrow landing-gallery-modal-next" aria-label="Next image">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m9 18 6-6-6-6" />
                </svg>
            </button>

            <div class="landing-gallery-modal-caption">
                <h3 id="gallery-modal-title"></h3>
            </div>
        </div>
    </div>

    <section id="testimonials" class="landing-section">
        <div class="landing-section-header">
            <span class="landing-eyebrow">Testimonials</span>
            <h2>Trusted By Students</h2>
            <p>Review cards use the same clean structure as the account dashboards.</p>
        </div>

        <div class="mx-auto grid max-w-7xl gap-5 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
            @foreach ($testimonials as $testimonial)
                <article class="landing-card p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-sm font-black text-blue-700">{{ $testimonial['initials'] }}</span>
                            <div>
                                <h3 class="font-black text-slate-950">{{ $testimonial['name'] }}</h3>
                                <span class="mt-1 inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700">Verified Tenant</span>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 text-sm font-black text-amber-600">{!! $icon('star', 'h-4 w-4') !!} {{ $testimonial['rating'] }}</span>
                    </div>
                    <p class="mt-5 text-sm leading-6 text-slate-600">{{ $testimonial['comment'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="px-4 py-8 sm:px-6 lg:px-8">
        <div class="landing-cta mx-auto flex max-w-7xl flex-col gap-5 rounded-2xl p-6 text-white shadow-2xl sm:p-8 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-2xl font-black sm:text-3xl">Ready to find your next boarding house?</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-white/75">Create an account or browse listings to start comparing safe, verified options.</p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="{{ $browseUrl }}" class="landing-button bg-white text-blue-800 hover:bg-slate-100">Browse Listings</a>
                @if ($registerUrl)
                    <a href="{{ $registerUrl }}" class="landing-button bg-emerald-500 text-white hover:bg-emerald-600">Create Account</a>
                @endif
            </div>
        </div>
    </section>

    <section id="contact" class="landing-section">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-[0.9fr_1.1fr] lg:px-8">
            <div>
                <span class="landing-eyebrow">Contact</span>
                <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">Need help finding a boarding house?</h2>
                <p class="mt-4 text-sm leading-6 text-slate-600 sm:text-base">Send a message to DSSC Boarding support for questions about listings, reservations, or account access.</p>
                <div class="mt-6 space-y-3 text-sm font-semibold text-slate-600">
                    <p>DSSC Boarding House System</p>
                    <p>Digos City, Davao del Sur</p>
                    <p>support@dsscboarding.local</p>
                </div>
            </div>

            <form id="landing-contact-form" class="landing-card space-y-4 p-5 sm:p-6">
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="text-sm font-bold text-slate-700">Full Name</span>
                        <input type="text" name="name" required class="landing-input mt-2" placeholder="Juan Student">
                    </label>
                    <label class="block">
                        <span class="text-sm font-bold text-slate-700">Email Address</span>
                        <input type="email" name="email" required class="landing-input mt-2" placeholder="juan@example.com">
                    </label>
                </div>
                <label class="block">
                    <span class="text-sm font-bold text-slate-700">Message</span>
                    <textarea name="message" required rows="5" class="landing-input mt-2 resize-none" placeholder="Tell us what you need help with."></textarea>
                </label>
                <p id="landing-contact-status" class="hidden rounded-xl bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700">Message sent. Support will review your request.</p>
                <button type="submit" class="landing-button landing-button-primary w-full justify-center py-3">Send Message</button>
            </form>
        </div>
    </section>
</main>

<footer class="landing-footer mt-10">
    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 md:grid-cols-2 lg:grid-cols-4 lg:px-8">
        <div>
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl border border-white/15 bg-white/10 text-white">{!! $icon('home', 'h-5 w-5') !!}</span>
                <div>
                    <p class="font-black">DSSC Boarding</p>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-white/55">House System</p>
                </div>
            </div>
            <p class="mt-4 text-sm leading-6 text-white/65">A clean public entry point for tenants, owners, and boarding house support workflows.</p>
        </div>

        <div>
            <h3 class="font-black">Quick Links</h3>
            <div class="mt-4 grid gap-2 text-sm text-white/65">
                @foreach ($navItems as $item)
                    <a href="{{ $item['href'] }}" class="hover:text-white">{{ $item['label'] }}</a>
                @endforeach
            </div>
        </div>

        <div>
            <h3 class="font-black">Account Links</h3>
            <div class="mt-4 grid gap-2 text-sm text-white/65">
                <a href="{{ $loginUrl }}" class="hover:text-white">Sign In</a>
                @if ($registerUrl)
                    <a href="{{ $registerUrl }}" class="hover:text-white">Create Account</a>
                @endif
                <a href="{{ $browseUrl }}" class="hover:text-white">Browse Listings</a>
            </div>
        </div>

        <div>
            <h3 class="font-black">Support</h3>
            <div class="mt-4 grid gap-2 text-sm text-white/65">
                <a href="#contact" class="hover:text-white">Contact Support</a>
                <a href="#features" class="hover:text-white">How It Works</a>
                <a href="#listings" class="hover:text-white">Featured Listings</a>
            </div>
        </div>
    </div>
    <div class="border-t border-white/10 px-4 py-5 text-center text-sm font-semibold text-white/50">
        &copy; 2026 DSSC Boarding House System. All rights reserved.
    </div>
</footer>

<script>
    const menuButton = document.getElementById('landing-menu-button');
    const mobileMenu = document.getElementById('landing-mobile-menu');
    const openIcon = document.querySelector('[data-menu-open]');
    const closeIcon = document.querySelector('[data-menu-close]');
    const contactForm = document.getElementById('landing-contact-form');
    const contactStatus = document.getElementById('landing-contact-status');
    const galleryItems = @json($gallery);
    const animatedItems = document.querySelectorAll('.animate-on-scroll');
    const galleryCards = document.querySelectorAll('[data-gallery-index]');
    const galleryModal = document.getElementById('gallery-modal');
    const galleryModalImage = document.getElementById('gallery-modal-image');
    const galleryModalTitle = document.getElementById('gallery-modal-title');
    const galleryModalClose = document.getElementById('gallery-modal-close');
    const galleryModalPrev = document.getElementById('gallery-modal-prev');
    const galleryModalNext = document.getElementById('gallery-modal-next');
    let activeGalleryIndex = 0;

    menuButton?.addEventListener('click', () => {
        const isOpen = ! mobileMenu.classList.contains('hidden');
        mobileMenu.classList.toggle('hidden', isOpen);
        openIcon.classList.toggle('hidden', ! isOpen);
        closeIcon.classList.toggle('hidden', isOpen);
        menuButton.setAttribute('aria-expanded', String(! isOpen));
    });

    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener('click', () => {
            mobileMenu?.classList.add('hidden');
            openIcon?.classList.remove('hidden');
            closeIcon?.classList.add('hidden');
            menuButton?.setAttribute('aria-expanded', 'false');
        });
    });

    if ('IntersectionObserver' in window) {
        const animationObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (! entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, {
            threshold: 0.16,
            rootMargin: '0px 0px -8% 0px',
        });

        animatedItems.forEach((item) => animationObserver.observe(item));
    } else {
        animatedItems.forEach((item) => item.classList.add('is-visible'));
    }

    function renderGalleryModal(index) {
        if (! galleryItems.length || ! galleryModal || ! galleryModalImage || ! galleryModalTitle) {
            return;
        }

        activeGalleryIndex = (index + galleryItems.length) % galleryItems.length;
        const item = galleryItems[activeGalleryIndex];
        galleryModalImage.src = item.src;
        galleryModalImage.alt = item.label;
        galleryModalTitle.textContent = item.label;
    }

    function openGalleryModal(index) {
        renderGalleryModal(index);
        galleryModal?.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        galleryModalClose?.focus();
    }

    function closeGalleryModal() {
        galleryModal?.classList.add('hidden');
        document.body.style.overflow = '';
    }

    galleryCards.forEach((card) => {
        card.addEventListener('click', () => {
            openGalleryModal(Number(card.dataset.galleryIndex || 0));
        });
    });

    galleryModalClose?.addEventListener('click', closeGalleryModal);
    galleryModalPrev?.addEventListener('click', () => renderGalleryModal(activeGalleryIndex - 1));
    galleryModalNext?.addEventListener('click', () => renderGalleryModal(activeGalleryIndex + 1));
    galleryModal?.addEventListener('click', (event) => {
        if (event.target === galleryModal) {
            closeGalleryModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (galleryModal?.classList.contains('hidden')) {
            return;
        }

        if (event.key === 'Escape') {
            closeGalleryModal();
        }

        if (event.key === 'ArrowLeft') {
            renderGalleryModal(activeGalleryIndex - 1);
        }

        if (event.key === 'ArrowRight') {
            renderGalleryModal(activeGalleryIndex + 1);
        }
    });

    contactForm?.addEventListener('submit', (event) => {
        event.preventDefault();

        if (! contactForm.checkValidity()) {
            contactForm.reportValidity();
            return;
        }

        contactStatus.classList.remove('hidden');
        contactForm.reset();

        window.setTimeout(() => {
            contactStatus.classList.add('hidden');
        }, 3500);
    });
</script>
</body>
</html>
