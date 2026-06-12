<x-layouts.dashboard>
<x-user.shell>
@php
    $r = function (string $name, array $params = [], ?string $fallback = null) {
        if (\Illuminate\Support\Facades\Route::has($name)) {
            return route($name, $params);
        }
        return $fallback ?? url()->current();
    };

    $tenant      = auth()->user();
    $displayName = trim((string) ($tenant?->name ?: 'User'));
    $firstName   = trim(explode(' ', $displayName)[0] ?? 'User') ?: 'User';
    $avatarLetter = strtoupper(substr($firstName, 0, 1)) ?: 'U';

    $mapUrl    = \Illuminate\Support\Facades\Route::has('map.user.listings')
        ? route('map.user.listings')
        : 'https://www.openstreetmap.org/#map=14/6.7440/125.3550';

    $imageUrl = function (?string $path) {
        if (! $path) {
            return asset('images/boarding-house-placeholder.svg');
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return asset('storage/'.$path);
    };

    $propertyTypeLabel = fn (?string $type) => match ($type) {
        'dormitory' => 'Dormitory',
        'apartment' => 'Apartment / Studio',
        'bedspace' => 'Bedspace',
        'other' => 'Transient / Resort',
        default => 'Private Room',
    };

    $amenityIcon = function (string $name) {
        $normalized = \Illuminate\Support\Str::lower($name);

        return match (true) {
            \Illuminate\Support\Str::contains($normalized, 'wifi') || \Illuminate\Support\Str::contains($normalized, 'wi-fi') => 'wifi',
            \Illuminate\Support\Str::contains($normalized, 'water') => 'water',
            \Illuminate\Support\Str::contains($normalized, 'air') => 'air',
            \Illuminate\Support\Str::contains($normalized, 'kitchen') => 'kitchen',
            \Illuminate\Support\Str::contains($normalized, 'laundry') => 'laundry',
            \Illuminate\Support\Str::contains($normalized, 'parking') => 'parking',
            \Illuminate\Support\Str::contains($normalized, 'cctv') || \Illuminate\Support\Str::contains($normalized, 'security') => 'lock',
            default => 'key',
        };
    };

    $houseCollection = isset($houses) && method_exists($houses, 'getCollection')
        ? $houses->getCollection()
        : collect($houses ?? []);

    $listings = $houseCollection->map(function ($house) use ($imageUrl, $propertyTypeLabel, $amenityIcon) {
        $primaryImage = $house->images->firstWhere('is_primary', true)
            ?? $house->images->sortBy('sort_order')->first()
            ?? $house->images->first();
        $price = $house->display_price
            ?? $house->rooms->where('price', '>', 0)->min('price')
            ?? $house->roomCategories->where('monthly_rate', '>', 0)->min('monthly_rate')
            ?? (($house->price ?? 0) > 0 ? (float) $house->price : null)
            ?? (($house->monthly_payment ?? 0) > 0 ? (float) $house->monthly_payment : null);
        $room = $house->roomCategories->first()?->name ?: $propertyTypeLabel($house->property_type);
        $icons = $house->amenities
            ->pluck('name')
            ->map(fn ($name) => $amenityIcon((string) $name))
            ->unique()
            ->take(4)
            ->values()
            ->all();

        return [
            'id' => $house->id,
            'name' => $house->name,
            'price' => $price !== null ? number_format((float) $price) : 'Ask owner',
            'price_label' => $price !== null ? '₱'.number_format((float) $price) : 'Ask owner',
            'location' => collect([
                $house->barangay?->barangay_name,
                $house->city?->city_name,
                $house->province?->province_name ?? 'Davao del Sur',
            ])->filter()->implode(', ') ?: ($house->full_address ?: ($house->address ?: 'Location not available')),
            'room' => $room,
            'rating' => $house->reviews_avg_rating ? number_format((float) $house->reviews_avg_rating, 1) : 'N/A',
            'reviews' => (int) ($house->reviews_count ?? 0),
            'photos' => max((int) $house->images->count(), 1),
            'image' => $imageUrl($primaryImage?->image_path ?: ($house->featured_image ?: ($house->exterior_image ?: $house->room_image))),
            'icons' => $icons ?: ['wifi', 'water', 'key', 'lock'],
            'url' => route('user.boarding-houses.show', $house),
        ];
    })->values();

    $resultCount = isset($houses) && method_exists($houses, 'total')
        ? $houses->total()
        : $listings->count();

    $popularAmenities = [
        ['name' => 'Wi-Fi',            'count' => 42, 'icon' => 'wifi'],
        ['name' => 'Kitchen',          'count' => 38, 'icon' => 'kitchen'],
        ['name' => 'Laundry',          'count' => 35, 'icon' => 'laundry'],
        ['name' => 'Air Conditioning', 'count' => 28, 'icon' => 'air'],
        ['name' => 'Parking',          'count' => 22, 'icon' => 'parking'],
    ];

    $iconPaths = [
        'search'   => '<path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.2-5.2m1.7-5.05a6.75 6.75 0 1 1-13.5 0 6.75 6.75 0 0 1 13.5 0Z"/>',
        'chevron'  => '<path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>',
        'grid'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 4h6v6H4V4Zm10 0h6v6h-6V4ZM4 14h6v6H4v-6Zm10 0h6v6h-6v-6Z"/>',
        'list'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
        'filter'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M7 12h10m-7 6h4"/>',
        'heart'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21.2l7.8-7.8 1-1a5.5 5.5 0 0 0 0-7.8Z"/>',
        'camera'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 8h3l1.4-2h7.2L17 8h3v10H4V8Zm8 7.2a3.2 3.2 0 1 0 0-6.4 3.2 3.2 0 0 0 0 6.4Z"/>',
        'bookmark' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 4h12v16l-6-3-6 3V4Z"/>',
        'star'     => '<path stroke-linecap="round" stroke-linejoin="round" d="m12 3 2.6 5.3 5.9.8-4.2 4.1 1 5.8-5.3-2.8L6.7 19l1-5.8-4.2-4.1 5.9-.8L12 3Z"/>',
        'wifi'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.5 8.5a15 15 0 0 1 19 0M5.8 11.8a10 10 0 0 1 12.4 0M9.2 15.2a5 5 0 0 1 5.6 0M12 18h.01"/>',
        'water'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3s5 5.2 5 9a5 5 0 0 1-10 0c0-3.8 5-9 5-9Z"/>',
        'key'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 7a4 4 0 1 1-2.5 7.1L6 20.5H3.5V18H6v-2.5h2.5l2.4-2.4A4 4 0 0 1 15 7Z"/>',
        'lock'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 10h12v10H6V10Zm3 0V7a3 3 0 1 1 6 0v3"/>',
        'share'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M18 8a3 3 0 1 0-2.8-4H15a3 3 0 0 0 .2 1L8.8 9a3 3 0 1 0 0 6l6.4 4a3 3 0 1 0 .9-1.5L9.7 13.5a3.1 3.1 0 0 0 0-3L16.1 6.5A3 3 0 0 0 18 8Z"/>',
        'kitchen'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 3v8m4-8v8m4-8v8M5 11h12v10H5V11Zm14-7v17"/>',
        'laundry'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 4h14v16H5V4Zm4 3h.01M12 15a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/>',
        'air'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M5 7l14 10M19 7 5 17"/>',
        'parking'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 20V4h6a4.5 4.5 0 0 1 0 9H8"/>',
        'location' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-4.8 7-11a7 7 0 1 0-14 0c0 6.2 7 11 7 11Zm0-8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>',
        'budget'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m3-9.5c-.6-1-1.6-1.5-3-1.5-1.7 0-3 .9-3 2s1.3 2 3 2 3 .9 3 2-1.3 2-3 2c-1.4 0-2.5-.5-3.1-1.5"/>',
        'home'     => '<path stroke-linecap="round" stroke-linejoin="round" d="m3 11 9-8 9 8v9h-6v-5H9v5H3v-9Z"/>',
        'spark'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3l2.1 6 6 2.1-6 2.1-2.1 6-2.1-6-6-2.1 6-2.1L12 3Z"/>',
        'sort'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 6h12M4 12h8M4 18h5m9-8v10m0 0 3-3m-3 3-3-3"/>',
        'shield'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3 5 6v5c0 4.6 2.8 8.4 7 10 4.2-1.6 7-5.4 7-10V6l-7-3Zm-2.2 9 1.5 1.5 3.2-3.6"/>',
        'bell'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a3 3 0 1 1-5.714 0"/>',
        'user'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a8.25 8.25 0 0 1 15 0"/>',
        'settings' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19 12a7 7 0 0 0-.1-1.2l2-1.5-2-3.5-2.4 1a7 7 0 0 0-2-1.2L14.2 3h-4.4l-.3 2.6a7 7 0 0 0-2 1.2l-2.4-1-2 3.5 2 1.5A7 7 0 0 0 5 12c0 .4 0 .8.1 1.2l-2 1.5 2 3.5 2.4-1a7 7 0 0 0 2 1.2l.3 2.6h4.4l.3-2.6a7 7 0 0 0 2-1.2l2.4 1 2-3.5-2-1.5c.1-.4.1-.8.1-1.2Z"/>',
        'card'     => '<rect x="3" y="6" width="18" height="14" rx="2"/><path stroke-linecap="round" d="M3 10h18M7 15h4"/>',
        'logout'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M10 5H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4M14 16l4-4-4-4M18 12H9"/>',
        'chevron-down' => '<path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/>',
    ];
@endphp

<style>
    /* ── Base ──────────────────────────────────────────── */
    .bm-finder { color: #0F172A; }
    .bm-finder * { box-sizing: border-box; }
    .bm-icon { width: 18px; height: 18px; flex: 0 0 auto; }

    /* ── Page heading ──────────────────────────────────── */
    .bm-page-heading { margin-bottom: 20px; }
    .bm-page-heading h1 {
        margin: 0; color: #0F172A;
        font-size: 26px; font-weight: 800; line-height: 1.2;
    }
    .bm-page-heading p {
        margin: 6px 0 0; color: #64748B; font-size: 14px; line-height: 1.6;
    }

    /* ── Search card ───────────────────────────────────── */
    .bm-search-card {
        background: #fff;
        border: 1px solid #E5E7EB;
        border-radius: 20px;
        padding: 18px 20px;
        box-shadow: 0 8px 24px rgba(15,23,42,.06);
        margin-bottom: 20px;
    }
    .bm-search-box {
        position: relative;
        margin-bottom: 14px;
    }
    .bm-search-box svg {
        position: absolute; left: 14px; top: 50%;
        transform: translateY(-50%); color: #94A3B8;
    }
    .bm-search-box input {
        width: 100%; height: 42px;
        border: 1px solid #E5E7EB; border-radius: 12px;
        padding: 0 16px 0 46px;
        color: #0F172A; font-size: 14px; font-family: inherit;
        outline: none; background: #F8FAFC;
        transition: border-color .15s, box-shadow .15s, background .15s;
    }
    .bm-search-box input:focus {
        border-color: #2563EB;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(37,99,235,.10);
    }

    /* ── Filter row ────────────────────────────────────── */
    .bm-filter-row {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
    }
    .bm-filter-control {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-height: 62px;
        border: 1px solid #E5E7EB;
        border-radius: 14px;
        background: #F9FAFB;
        padding: 10px 40px 10px 14px;
        cursor: pointer;
        transition: border-color .15s, background .15s, box-shadow .15s;
    }
    .bm-filter-control:hover { border-color: #93C5FD; background: #fff; }
    .bm-filter-control:focus-within { border-color: #2563EB; background: #fff; box-shadow: 0 0 0 3px rgba(37,99,235,.10); }
    .bm-filter-control span {
        display: block;
        margin-bottom: 4px;
        color: #94A3B8;
        font-size: 10px;
        font-weight: 700;
        line-height: 1.2;
        text-transform: uppercase;
        letter-spacing: .08em;
        white-space: normal;
        overflow: visible;
        text-overflow: clip;
    }
    .bm-filter-control select {
        width: 100%;
        min-width: 0;
        border: 0;
        padding: 0;
        background: transparent;
        appearance: none;
        color: #0F172A;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.35;
        font-family: inherit;
        outline: none;
        cursor: pointer;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .bm-filter-control svg {
        position: absolute; right: 14px; top: 50%;
        transform: translateY(-50%);
        color: #94A3B8; width: 14px; height: 14px; pointer-events: none;
    }

    /* ── Two-column layout ─────────────────────────────── */
    .bm-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 300px;
        gap: 20px;
        align-items: start;
    }

    /* ── Results header ────────────────────────────────── */
    .bm-results-head {
        display: flex; align-items: center;
        justify-content: space-between; gap: 12px;
        margin: 0 0 16px;
    }
    .bm-results-count {
        margin: 0; color: #64748B; font-size: 13px; font-weight: 600;
    }
    .bm-results-count strong { color: #0F172A; font-size: 14px; }
    .bm-results-controls { display: flex; align-items: center; gap: 8px; }

    /* ── View toggle ───────────────────────────────────── */
    .bm-view-toggle {
        display: inline-flex; overflow: hidden;
        border: 1px solid #E5E7EB; border-radius: 10px; background: #fff;
    }
    .bm-view-toggle button {
        display: inline-flex; align-items: center; gap: 6px;
        height: 36px; padding: 0 13px;
        border: 0; background: #fff; color: #64748B;
        font-size: 12px; font-weight: 700; font-family: inherit;
        cursor: pointer; transition: background .12s, color .12s;
    }
    .bm-view-toggle button + button { border-left: 1px solid #E5E7EB; }
    .bm-view-toggle button.is-active { color: #2563EB; background: #EFF6FF; }
    .bm-view-toggle button:hover:not(.is-active) { background: #F8FAFC; }

    /* ── Mobile filter toggle ──────────────────────────── */
    .bm-mobile-filter-btn {
        display: none; align-items: center; gap: 6px;
        height: 36px; padding: 0 13px;
        border: 1px solid #E5E7EB; border-radius: 10px;
        background: #fff; color: #64748B;
        font-size: 12px; font-weight: 700; font-family: inherit;
        cursor: pointer; white-space: nowrap;
        transition: border-color .15s, color .15s, background .15s;
    }
    .bm-mobile-filter-btn:hover,
    .bm-mobile-filter-btn.is-open { border-color: #2563EB; color: #2563EB; background: #EFF6FF; }

    /* ── Card grid ─────────────────────────────────────── */
    .bm-card-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    /* ── Listing card ──────────────────────────────────── */
    .bm-listing-card {
        display: flex; flex-direction: column;
        overflow: hidden;
        border: 1px solid #E5E7EB; border-radius: 16px;
        background: #fff;
        box-shadow: 0 2px 12px rgba(15,23,42,.05);
        transition: box-shadow .2s, transform .2s;
    }
    .bm-listing-card:hover {
        box-shadow: 0 10px 30px rgba(15,23,42,.10);
        transform: translateY(-3px);
    }

    /* ── Card image ────────────────────────────────────── */
    .bm-card-media {
        position: relative;
        aspect-ratio: 16 / 10;
        flex: 0 0 auto;
        background: linear-gradient(135deg, #E5E7EB, #F8FAFC);
        overflow: hidden;
    }
    .bm-card-media img {
        width: 100%; height: 100%; object-fit: cover; display: block;
        transition: transform .35s ease;
    }
    .bm-listing-card:hover .bm-card-media img { transform: scale(1.04); }
    .bm-photo-count {
        position: absolute; left: 10px; bottom: 10px;
        display: inline-flex; align-items: center; gap: 4px;
        height: 26px; padding: 0 10px;
        border-radius: 8px; color: #fff;
        background: rgba(15,23,42,.70);
        font-size: 11px; font-weight: 700;
        backdrop-filter: blur(4px);
    }
    .bm-heart {
        position: absolute; right: 10px; top: 10px;
        display: inline-flex; width: 36px; height: 36px;
        align-items: center; justify-content: center;
        border: 1px solid rgba(255,255,255,.8);
        border-radius: 10px; background: rgba(255,255,255,.92);
        color: #94A3B8; cursor: pointer;
        transition: color .15s, border-color .15s, background .15s;
    }
    .bm-heart:hover { color: #EF4444; border-color: #FCA5A5; background: #FFF1F2; }
    .bm-heart.is-saved { color: #EF4444; border-color: #FCA5A5; background: #FFF1F2; }

    /* ── Card body ─────────────────────────────────────── */
    .bm-card-body {
        flex: 1;
        display: flex; flex-direction: column;
        padding: 16px;
    }

    .bm-card-title {
        margin: 0; color: #0F172A;
        font-size: 14px; line-height: 1.4; font-weight: 800;
        display: -webkit-box;
        -webkit-line-clamp: 2; -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 40px;
    }

    /* ── Price ─────────────────────────────────────────── */
    .bm-price-row {
        margin-top: 10px;
        display: flex; align-items: baseline; gap: 4px;
        color: #64748B; font-size: 12px; font-weight: 600;
    }
    .bm-price-row strong {
        font-size: 18px; font-weight: 900; color: #F97316; line-height: 1;
    }

    /* ── Location ──────────────────────────────────────── */
    .bm-location {
        display: flex; align-items: center; gap: 5px;
        margin-top: 7px; color: #64748B; font-size: 12px;
        overflow: hidden; white-space: nowrap; text-overflow: ellipsis;
    }
    .bm-location svg { flex: 0 0 auto; }

    /* ── Room chip ─────────────────────────────────────── */
    .bm-room-chip {
        display: inline-flex; align-self: flex-start;
        margin-top: 10px;
        border-radius: 8px; background: #EFF6FF;
        color: #2563EB; padding: 3px 10px;
        font-size: 11px; font-weight: 700;
        border: 1px solid #DBEAFE;
    }

    /* ── Rating + amenity icons ────────────────────────── */
    .bm-card-meta {
        margin-top: 10px;
        display: flex; align-items: center;
        justify-content: space-between; gap: 8px;
    }
    .bm-rating {
        display: inline-flex; align-items: center; gap: 4px;
        color: #334155; font-size: 11px; font-weight: 700;
        min-width: 0; flex-shrink: 0;
    }
    .bm-rating svg { color: #F59E0B; fill: #F59E0B; stroke: #F59E0B; }
    .bm-amenity-icons {
        display: inline-flex; align-items: center; gap: 6px;
        color: #94A3B8; flex-wrap: nowrap;
    }
    .bm-amenity-icons svg { width: 14px; height: 14px; }

    /* ── Card actions ──────────────────────────────────── */
    .bm-card-actions {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 8px;
        margin-top: auto;
        padding-top: 14px;
        border-top: 1px solid #F1F5F9;
    }

    .bm-details-btn,
    .bm-save-btn {
        display: inline-flex; align-items: center; justify-content: center;
        height: 38px; border-radius: 10px;
        font-size: 12px; font-weight: 700; font-family: inherit;
        line-height: 1; white-space: nowrap; text-decoration: none !important;
        cursor: pointer; transition: background .15s, border-color .15s, color .15s;
    }
    .bm-details-btn {
        border: 1.5px solid #2563EB; color: #2563EB; background: #fff;
        padding: 0 16px;
    }
    .bm-details-btn:hover { background: #EFF6FF; }
    .bm-save-btn {
        border: 1.5px solid #E5E7EB; color: #64748B; background: #fff;
        gap: 5px; padding: 0 13px;
    }
    .bm-save-btn:hover { border-color: #93C5FD; color: #2563EB; background: #EFF6FF; }
    .bm-save-btn.is-saved { color: #2563EB; border-color: #93C5FD; background: #EFF6FF; }

    /* ── Pagination ────────────────────────────────────── */
    .bm-pagination-row {
        display: flex; align-items: center;
        justify-content: space-between; flex-wrap: wrap;
        gap: 12px; margin-top: 24px;
    }
    .bm-pagination {
        display: inline-flex; align-items: center; gap: 6px;
    }
    .bm-page-btn {
        min-width: 36px; height: 36px; padding: 0 4px;
        border: 1px solid #E5E7EB; border-radius: 10px;
        background: #fff; color: #64748B;
        font-size: 13px; font-weight: 700; font-family: inherit;
        cursor: pointer; transition: background .12s, border-color .12s, color .12s;
    }
    .bm-page-btn:hover:not(.is-plain) { border-color: #93C5FD; color: #2563EB; background: #EFF6FF; }
    .bm-page-btn.is-active { border-color: #2563EB; color: #2563EB; background: #EFF6FF; font-weight: 900; }
    .bm-page-btn.is-plain { border-color: transparent; background: transparent; color: #94A3B8; }
    .bm-page-size { position: relative; }
    .bm-page-size select {
        height: 36px; min-width: 120px; appearance: none;
        border: 1px solid #E5E7EB; border-radius: 10px;
        background: #fff; color: #64748B;
        font-size: 12px; font-weight: 700; font-family: inherit;
        padding: 0 32px 0 14px; outline: none; cursor: pointer;
        transition: border-color .15s;
    }
    .bm-page-size select:focus { border-color: #2563EB; }
    .bm-page-size svg {
        position: absolute; right: 10px; top: 50%;
        transform: translateY(-50%);
        color: #94A3B8; pointer-events: none;
    }

    /* ── Sidebar ───────────────────────────────────────── */
    .bm-sidebar { display: flex; flex-direction: column; gap: 16px; }
    .bm-side-panel {
        border: 1px solid #E5E7EB; border-radius: 16px;
        background: #fff;
        box-shadow: 0 2px 12px rgba(15,23,42,.04);
        padding: 18px 20px;
    }
    .bm-panel-title {
        display: flex; align-items: center;
        justify-content: space-between; margin-bottom: 16px;
    }
    .bm-panel-title h2 {
        margin: 0; color: #0F172A; font-size: 14px; font-weight: 800;
    }
    .bm-panel-title button {
        border: 0; background: transparent;
        color: #EF4444; font-size: 12px; font-weight: 700;
        font-family: inherit; cursor: pointer; transition: color .15s;
    }
    .bm-panel-title button:hover { color: #DC2626; }

    /* ── Map panel ─────────────────────────────────────── */
    .bm-map-wrap {
        position: relative;
        border-radius: 12px; overflow: hidden;
        border: 1px solid #E5E7EB;
    }
    .bm-map-wrap iframe {
        display: block;
        width: 100%; height: 200px; border: 0;
    }
    .bm-map-footer {
        display: flex; align-items: center; justify-content: space-between;
        margin-top: 12px;
    }
    .bm-map-footer-label {
        display: flex; align-items: center; gap: 6px;
        color: #64748B; font-size: 12px; font-weight: 600;
    }
    .bm-map-footer-label svg { color: #2563EB; }
    .bm-map-link {
        color: #2563EB; text-decoration: none;
        font-size: 12px; font-weight: 700; transition: color .15s;
    }
    .bm-map-link:hover { color: #1D4ED8; text-decoration: underline; }

    /* ── Search summary ────────────────────────────────── */
    .bm-summary-list { display: flex; flex-direction: column; gap: 12px; }
    .bm-summary-row {
        display: flex; align-items: center;
        justify-content: space-between; gap: 10px;
    }
    .bm-summary-label {
        display: inline-flex; align-items: center; gap: 10px;
        color: #334155; font-size: 12px; font-weight: 700;
        min-width: 0; white-space: nowrap; flex-shrink: 0;
    }
    .bm-summary-label .bm-icon { color: #2563EB; flex-shrink: 0; }
    .bm-summary-value {
        color: #64748B; font-size: 12px; font-weight: 600;
        text-align: right; min-width: 0;
        overflow: hidden; white-space: nowrap; text-overflow: ellipsis;
    }
    .bm-edit-search {
        width: 100%; height: 38px; margin-top: 16px;
        border: 1.5px solid #2563EB; border-radius: 10px;
        background: #fff; color: #2563EB;
        font-size: 12px; font-weight: 800; font-family: inherit;
        cursor: pointer; transition: background .15s;
    }
    .bm-edit-search:hover { background: #EFF6FF; }

    /* ── Amenity list ──────────────────────────────────── */
    .bm-amenity-list { display: flex; flex-direction: column; gap: 10px; }
    .bm-amenity-row {
        display: flex; align-items: center;
        justify-content: space-between; gap: 10px;
    }
    .bm-amenity-name {
        display: inline-flex; align-items: center; gap: 10px;
        color: #334155; font-size: 12px; font-weight: 700; min-width: 0;
    }
    .bm-amenity-name .bm-icon { color: #2563EB; flex-shrink: 0; }
    .bm-count-pill {
        min-width: 34px; height: 24px;
        display: inline-flex; align-items: center; justify-content: center;
        border: 1px solid #E5E7EB; border-radius: 999px;
        background: #F8FAFC; color: #64748B;
        font-size: 11px; font-weight: 700; flex-shrink: 0;
    }
    .bm-view-amenities {
        display: inline-flex; margin-top: 14px;
        color: #2563EB; text-decoration: none;
        font-size: 12px; font-weight: 700; transition: color .15s;
    }
    .bm-view-amenities:hover { color: #1D4ED8; text-decoration: underline; }

    /* ── Safety banner ─────────────────────────────────── */
    .bm-safety {
        display: flex; align-items: center;
        justify-content: space-between; gap: 20px;
        margin-top: 24px; padding: 18px 22px;
        border: 1px solid #E5E7EB; border-radius: 16px;
        background: #fff;
        box-shadow: 0 2px 10px rgba(15,23,42,.04);
    }
    .bm-safety-main { display: flex; align-items: center; gap: 16px; min-width: 0; }
    .bm-shield {
        display: inline-flex; width: 42px; height: 42px; flex: 0 0 42px;
        align-items: center; justify-content: center;
        border-radius: 12px; background: #FFF7ED; color: #F97316;
    }
    .bm-safety strong { display: block; color: #0F172A; font-size: 13px; font-weight: 800; }
    .bm-safety p { margin: 0; }
    .bm-safety span { color: #64748B; font-size: 12px; }
    .bm-safety-cta {
        display: inline-flex; align-items: center; gap: 16px;
        color: #64748B; font-size: 12px; font-weight: 600;
        white-space: nowrap; flex-shrink: 0;
    }
    .bm-safety-cta a {
        color: #2563EB; text-decoration: none; font-weight: 800;
        padding: 8px 16px; border: 1.5px solid #2563EB;
        border-radius: 10px; background: #fff;
        transition: background .15s, color .15s;
    }
    .bm-safety-cta a:hover { background: #EFF6FF; }

    /* ── Responsive: ≤ 1400px ──────────────────────────── */
    @media (max-width: 1400px) {
        .bm-layout { grid-template-columns: minmax(0, 1fr) 280px; }
        .bm-card-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    /* ── Responsive: ≤ 1200px ──────────────────────────── */
    @media (max-width: 1200px) {
        .bm-layout { grid-template-columns: 1fr; }
        .bm-sidebar { flex-direction: row; flex-wrap: wrap; }
        .bm-sidebar .bm-side-panel { flex: 1 1 260px; min-width: 220px; }
        .bm-card-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .bm-mobile-filter-btn { display: inline-flex; }
        .bm-filter-row { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    /* ── Responsive: ≤ 900px ───────────────────────────── */
    @media (max-width: 900px) {
        .bm-card-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .bm-filter-row { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .bm-safety { gap: 14px; }
        .bm-safety-cta { flex-direction: column; align-items: flex-start; gap: 8px; white-space: normal; }
        .bm-pagination-row { justify-content: center; }
    }

    /* ── Responsive: ≤ 640px ───────────────────────────── */
    @media (max-width: 640px) {
        .bm-page-heading h1 { font-size: 22px; }
        .bm-search-card { padding: 14px; border-radius: 16px; }
        .bm-card-grid { grid-template-columns: 1fr; }
        .bm-filter-row { grid-template-columns: 1fr; gap: 10px; }
        .bm-sidebar { flex-direction: column; }
        .bm-sidebar .bm-side-panel { flex: none; }
        .bm-results-head { flex-direction: column; align-items: flex-start; gap: 8px; }
        .bm-results-controls { width: 100%; justify-content: space-between; }
        .bm-pagination-row { flex-direction: column; align-items: center; gap: 10px; }
        .bm-page-size select { min-width: 130px; }
        .bm-safety { flex-direction: column; padding: 16px; gap: 12px; }
        .bm-safety-cta { width: 100%; }
    }

    /* ── Responsive: ≤ 480px ───────────────────────────── */
    @media (max-width: 480px) {
        .bm-filter-row { grid-template-columns: 1fr; }
        .bm-card-title { font-size: 13px; }
        .bm-price-row strong { font-size: 16px; }
        .bm-map-wrap iframe { height: 170px; }
    }
</style>

<div class="bm-finder" x-data="boardingHouseFinder()" @keydown.escape.window="profileOpen = false">

    {{-- ============================================================
         TOP HEADER
    ============================================================ --}}
    @if(request()->routeIs('user.dashboard'))
        <div style="margin-bottom:20px">
            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-[#E5E7EB] bg-white px-5 py-3.5 shadow-sm">

            {{-- Breadcrumb --}}
            <nav class="hidden shrink-0 items-center gap-2 text-xs font-semibold text-[#64748B] sm:flex" aria-label="Breadcrumb">
                <a href="{{ $r('user.dashboard') }}" class="text-[#2563EB] transition hover:text-[#1D4ED8]">Dashboard</a>
                <svg class="h-3 w-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/>
                </svg>
                <span>Find Boarding Houses</span>
            </nav>

            {{-- Search --}}
            <form method="GET" action="{{ $r('user.boarding-houses.index') }}" class="relative min-w-0 flex-1">
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-[#94A3B8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <circle cx="10.5" cy="10.5" r="6.5"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16 16 4 4"/>
                </svg>
                <input
                    name="q"
                    type="search"
                    placeholder="Search anything..."
                    class="h-10 w-full rounded-xl border border-[#E5E7EB] bg-[#F8FAFC] pl-10 pr-4 text-sm text-[#0F172A] outline-none transition placeholder:text-slate-400 focus:border-[#2563EB] focus:bg-white focus:ring-4 focus:ring-blue-100"
                >
            </form>

            {{-- Notification + Profile --}}
            <div class="flex shrink-0 items-center gap-2">

                {{-- Bell --}}
                <button type="button"
                    class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-[#E5E7EB] bg-white text-[#64748B] shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-[#2563EB]"
                    aria-label="Notifications">
                    <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        {!! $iconPaths['bell'] !!}
                    </svg>
                    <span class="absolute -right-0.5 -top-0.5 h-2.5 w-2.5 rounded-full border-2 border-white bg-red-500"></span>
                </button>

                {{-- Profile --}}
                <div class="relative">
                    <button
                        type="button"
                        @click="profileOpen = !profileOpen"
                        :aria-expanded="profileOpen"
                        class="flex items-center gap-2.5 rounded-xl border border-[#E5E7EB] bg-white px-3 py-2 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/50"
                        aria-haspopup="menu"
                    >
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#2563EB] text-sm font-bold text-white">{{ $avatarLetter }}</span>
                        <span class="hidden text-left sm:block">
                            <span class="block text-sm font-semibold leading-tight text-[#0F172A]">{{ $firstName }}</span>
                            <span class="block text-[11px] leading-tight text-[#64748B]">Tenant</span>
                        </span>
                        <svg class="h-4 w-4 text-[#94A3B8] transition-transform duration-200" :class="profileOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            {!! $iconPaths['chevron-down'] !!}
                        </svg>
                    </button>

                    <div
                        x-cloak
                        x-show="profileOpen"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95 translate-y-1"
                        @click.outside="profileOpen = false"
                        class="absolute right-0 z-50 mt-2 w-56 overflow-hidden rounded-2xl border border-[#E5E7EB] bg-white shadow-xl shadow-slate-900/10"
                        role="menu"
                    >
                        <div class="border-b border-[#E5E7EB] px-4 py-3">
                            <p class="text-sm font-semibold text-[#0F172A]">{{ $displayName }}</p>
                            <p class="truncate text-xs text-[#64748B]">{{ $tenant?->email ?? 'tenant@boardmatch.local' }}</p>
                        </div>
                        <div class="p-1.5">
                            <a href="{{ $r('user.preferences.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-[#0F172A] transition hover:bg-blue-50 hover:text-[#2563EB]" role="menuitem">
                                <svg class="h-4 w-4 shrink-0 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">{!! $iconPaths['user'] !!}</svg>
                                My Profile
                            </a>
                            <a href="{{ $r('user.settings.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-[#0F172A] transition hover:bg-blue-50 hover:text-[#2563EB]" role="menuitem">
                                <svg class="h-4 w-4 shrink-0 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">{!! $iconPaths['settings'] !!}</svg>
                                Account Settings
                            </a>
                            <a href="{{ $r('user.payments.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-[#0F172A] transition hover:bg-blue-50 hover:text-[#2563EB]" role="menuitem">
                                <svg class="h-4 w-4 shrink-0 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">{!! $iconPaths['card'] !!}</svg>
                                Billing &amp; Payments
                            </a>
                            <div class="my-1 border-t border-[#E5E7EB]"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm text-red-600 transition hover:bg-red-50" role="menuitem">
                                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">{!! $iconPaths['logout'] !!}</svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
            </div>
        </div>
    @endif

    {{-- ============================================================
         PAGE TITLE
    ============================================================ --}}
    <header class="bm-page-heading">
        <h1>Find Boarding Houses</h1>
        <p>Discover and compare boarding houses in Digos City, Davao del Sur that fit your preferences and budget.</p>
    </header>

    {{-- ============================================================
         SEARCH & FILTER CARD
    ============================================================ --}}
    <section class="bm-search-card" aria-label="Search filters">
        <div class="bm-search-box">
            <svg class="bm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                {!! $iconPaths['search'] !!}
            </svg>
            <input x-model="search" type="search"
                   placeholder="Search by name, barangay, or keyword...">
        </div>

        <div class="bm-filter-row">
            <label class="bm-filter-control">
                <span>Budget</span>
                <select>
                    <option>₱3,000 – ₱6,000</option>
                    <option>Under ₱3,000</option>
                    <option>₱6,000 – ₱9,000</option>
                    <option>Above ₱9,000</option>
                </select>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">{!! $iconPaths['chevron'] !!}</svg>
            </label>

            <label class="bm-filter-control">
                <span>Location / Barangay</span>
                <select>
                    <option>All Barangays</option>
                    <option>Poblacion</option>
                    <option>Zone 1</option>
                    <option>Zone 2</option>
                    <option>Zone 3</option>
                    <option>Zone 4</option>
                    <option>Zone 5</option>
                    <option>Aplaya</option>
                    <option>Mahayahay</option>
                    <option>Dawis Norte</option>
                </select>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">{!! $iconPaths['chevron'] !!}</svg>
            </label>

            <label class="bm-filter-control">
                <span>Room Type</span>
                <select>
                    <option>All Types</option>
                    <option>Private Room</option>
                    <option>Shared Room</option>
                    <option>Studio Unit</option>
                    <option>Bedspace</option>
                </select>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">{!! $iconPaths['chevron'] !!}</svg>
            </label>

            <label class="bm-filter-control">
                <span>Amenities</span>
                <select>
                    <option>Any Amenity</option>
                    <option>Wi-Fi + Kitchen</option>
                    <option>Air Conditioning</option>
                    <option>Parking</option>
                </select>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">{!! $iconPaths['chevron'] !!}</svg>
            </label>

            <label class="bm-filter-control">
                <span>Sort By</span>
                <select onchange="const url = new URL(window.location.href); url.searchParams.set('sort', this.value); window.location.href = url.toString();">
                    <option value="recommended" @selected(request('sort') === 'recommended')>Recommended</option>
                    <option value="price_asc" @selected(request('sort') === 'price_asc')>Lowest Price</option>
                    <option value="rating" @selected(request('sort') === 'rating')>Highest Rated</option>
                    <option value="newest" @selected(request('sort', 'newest') === 'newest')>Newest</option>
                </select>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">{!! $iconPaths['chevron'] !!}</svg>
            </label>
        </div>
    </section>

    @if(!empty($recommendationNotice))
        <div class="mb-4 rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm font-semibold text-[#2563EB]">
            {{ $recommendationNotice }}
            <a href="{{ $r('user.preferences.index') }}" class="ml-1 underline">Complete Preferences</a>
        </div>
    @endif

    {{-- ============================================================
         MAIN LAYOUT: Listings (left) + Sidebar (right)
    ============================================================ --}}
    <div class="bm-layout">

        {{-- ── Left: listings ──────────────────────── --}}
        <main>

            {{-- Results bar --}}
            <div class="bm-results-head">
                <p class="bm-results-count"><strong>{{ $resultCount }}</strong> boarding {{ \Illuminate\Support\Str::plural('house', $resultCount) }} found in Digos City</p>
                <div class="bm-results-controls">
                    <button type="button"
                            class="bm-mobile-filter-btn"
                            :class="{ 'is-open': showFilters }"
                            @click="showFilters = !showFilters"
                            aria-label="Toggle filters">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPaths['filter'] !!}</svg>
                        <span x-text="showFilters ? 'Hide Filters' : 'Filters'">Filters</span>
                    </button>
                    <div class="bm-view-toggle" role="group" aria-label="View mode">
                        <button type="button" :class="{ 'is-active': view === 'grid' }" @click="view = 'grid'">
                            <svg class="bm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPaths['grid'] !!}</svg>
                            Grid
                        </button>
                        <button type="button" :class="{ 'is-active': view === 'list' }" @click="view = 'list'">
                            <svg class="bm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPaths['list'] !!}</svg>
                            List
                        </button>
                    </div>
                </div>
            </div>

            {{-- Card grid --}}
            <div class="bm-card-grid">
                @forelse($listings as $listing)
                    <article class="bm-listing-card">
                        {{-- Image --}}
                        <div class="bm-card-media">
                            <img src="{{ $listing['image'] }}"
                                 alt="{{ $listing['name'] }}"
                                 loading="lazy"
                                 onerror="this.onerror=null;this.style.display='none'">
                            <span class="bm-photo-count">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPaths['camera'] !!}</svg>
                                {{ $listing['photos'] }}
                            </span>
                            <button class="bm-heart" type="button"
                                    :class="{ 'is-saved': isSaved('{{ $listing['name'] }}') }"
                                    @click="toggleSave('{{ $listing['name'] }}')"
                                    aria-label="Save {{ $listing['name'] }}">
                                <svg class="bm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPaths['heart'] !!}</svg>
                            </button>
                        </div>

                        {{-- Body --}}
                        <div class="bm-card-body">
                            <h2 class="bm-card-title">{{ $listing['name'] }}</h2>

                            <div class="bm-price-row">
                                <strong>{{ $listing['price_label'] }}</strong>
                                @if($listing['price'] !== 'Ask owner')
                                    <span>/ month</span>
                                @endif
                            </div>

                            <div class="bm-location">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex:0 0 auto">{!! $iconPaths['location'] !!}</svg>
                                <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $listing['location'] }}</span>
                            </div>

                            <span class="bm-room-chip">{{ $listing['room'] }}</span>

                            <div class="bm-card-meta">
                                <span class="bm-rating">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.5">{!! $iconPaths['star'] !!}</svg>
                                    {{ $listing['rating'] }}
                                    <span style="color:#94A3B8;font-weight:600">({{ $listing['reviews'] }})</span>
                                </span>
                                <span class="bm-amenity-icons" aria-label="Amenities">
                                    @foreach(array_slice($listing['icons'], 0, 4) as $icon)
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPaths[$icon] !!}</svg>
                                    @endforeach
                                </span>
                            </div>

                            <div class="bm-card-actions">
                                <a class="bm-details-btn" href="{{ $listing['url'] }}">View Details</a>
                                <button class="bm-save-btn" type="button"
                                        :class="{ 'is-saved': isSaved('{{ $listing['name'] }}') }"
                                        @click="toggleSave('{{ $listing['name'] }}')">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPaths['bookmark'] !!}</svg>
                                    Save
                                </button>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-[#E5E7EB] bg-white p-6 text-center shadow-sm sm:col-span-2 lg:col-span-3">
                        <h2 class="text-base font-bold text-[#0F172A]">No boarding houses found</h2>
                        <p class="mt-2 text-sm text-[#64748B]">Try changing your search filters or check again later.</p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            <div class="bm-pagination-row">
                <nav class="bm-pagination" aria-label="Pagination">
                    <button class="bm-page-btn is-plain" type="button" aria-label="Previous">&#8249;</button>
                    <button class="bm-page-btn is-active" type="button">1</button>
                    <button class="bm-page-btn" type="button">2</button>
                    <button class="bm-page-btn" type="button">3</button>
                    <span class="bm-page-btn is-plain" aria-hidden="true">…</span>
                    <button class="bm-page-btn" type="button">6</button>
                    <button class="bm-page-btn is-plain" type="button" aria-label="Next">&#8250;</button>
                </nav>
                <label class="bm-page-size">
                    <select aria-label="Results per page">
                        <option>8 per page</option>
                        <option>12 per page</option>
                        <option>24 per page</option>
                    </select>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">{!! $iconPaths['chevron'] !!}</svg>
                </label>
            </div>

        </main>

        {{-- ── Right: sidebar ───────────────────────── --}}
        <aside class="bm-sidebar" aria-label="Sidebar"
               x-show="showFilters || isDesktop"
               x-transition:enter="transition ease-out duration-200"
               x-transition:enter-start="opacity-0 -translate-y-1"
               x-transition:enter-end="opacity-100 translate-y-0">

            {{-- Map Preview --}}
            <section class="bm-side-panel">
                <div class="bm-panel-title">
                    <h2>Map Preview</h2>
                </div>
                <div class="bm-map-wrap">
                    <iframe
                        src="https://www.openstreetmap.org/export/embed.html?bbox=125.3100%2C6.7150%2C125.4000%2C6.7750&layer=mapnik&marker=6.7440%2C125.3550"
                        title="Digos City, Davao del Sur"
                        loading="lazy"
                        allowfullscreen>
                    </iframe>
                </div>
                <div class="bm-map-footer">
                    <div class="bm-map-footer-label">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPaths['location'] !!}</svg>
                        Digos City, Davao del Sur
                    </div>
                    <a class="bm-map-link" href="{{ $mapUrl }}" target="_blank" rel="noopener">
                        View larger map ↗
                    </a>
                </div>
            </section>

            {{-- Search Summary --}}
            <section class="bm-side-panel">
                <div class="bm-panel-title">
                    <h2>Your Search Summary</h2>
                    <button type="button" @click="clearAll()">Clear All</button>
                </div>
                <div class="bm-summary-list">
                    <div class="bm-summary-row">
                        <span class="bm-summary-label">
                            <svg class="bm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPaths['budget'] !!}</svg>
                            Budget
                        </span>
                        <span class="bm-summary-value">₱3,000 – ₱6,000</span>
                    </div>
                    <div class="bm-summary-row">
                        <span class="bm-summary-label">
                            <svg class="bm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPaths['location'] !!}</svg>
                            Location
                        </span>
                        <span class="bm-summary-value">Digos City, D.d.S</span>
                    </div>
                    <div class="bm-summary-row">
                        <span class="bm-summary-label">
                            <svg class="bm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPaths['home'] !!}</svg>
                            Room Type
                        </span>
                        <span class="bm-summary-value">All Types</span>
                    </div>
                    <div class="bm-summary-row">
                        <span class="bm-summary-label">
                            <svg class="bm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPaths['spark'] !!}</svg>
                            Amenities
                        </span>
                        <span class="bm-summary-value">Any Amenity</span>
                    </div>
                    <div class="bm-summary-row">
                        <span class="bm-summary-label">
                            <svg class="bm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPaths['sort'] !!}</svg>
                            Sort By
                        </span>
                        <span class="bm-summary-value">Recommended</span>
                    </div>
                </div>
                <button class="bm-edit-search" type="button">Edit Search</button>
            </section>

            {{-- Popular Amenities --}}
            <section class="bm-side-panel">
                <div class="bm-panel-title">
                    <h2>Popular Amenities</h2>
                </div>
                <div class="bm-amenity-list">
                    @foreach($popularAmenities as $amenity)
                        <div class="bm-amenity-row">
                            <span class="bm-amenity-name">
                                <svg class="bm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPaths[$amenity['icon']] !!}</svg>
                                {{ $amenity['name'] }}
                            </span>
                            <span class="bm-count-pill">{{ $amenity['count'] }}</span>
                        </div>
                    @endforeach
                </div>
                <a class="bm-view-amenities" href="#">View all amenities →</a>
            </section>

        </aside>
    </div>

    {{-- ============================================================
         SAFETY BANNER
    ============================================================ --}}
    <section class="bm-safety" role="complementary" aria-label="Safety notice">
        <div class="bm-safety-main">
            <span class="bm-shield">
                <svg class="bm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPaths['shield'] !!}</svg>
            </span>
            <p>
                <strong>Your safety is our priority.</strong>
                <span>All boarding houses listed in Digos City are verified and regularly inspected for your peace of mind.</span>
            </p>
        </div>
        <div class="bm-safety-cta">
            <span>Can't find what you're looking for?</span>
            <a href="{{ $r('user.matchmaking.index') }}">Request a listing</a>
        </div>
    </section>

</div>

<script>
    function boardingHouseFinder() {
        return {
            search: '',
            view: 'grid',
            saved: [],
            showFilters: false,
            isDesktop: window.innerWidth >= 1200,
            profileOpen: false,

            init() {
                this.isDesktop   = window.innerWidth >= 1200;
                this.showFilters = this.isDesktop;

                const onResize = () => {
                    const wasDesktop = this.isDesktop;
                    this.isDesktop   = window.innerWidth >= 1200;
                    if (!wasDesktop && this.isDesktop)  this.showFilters = true;
                    if (wasDesktop  && !this.isDesktop) this.showFilters = false;
                };

                window.addEventListener('resize', onResize, { passive: true });
                this.$cleanup = () => window.removeEventListener('resize', onResize);
            },

            toggleSave(name) {
                if (this.saved.includes(name)) {
                    this.saved = this.saved.filter(i => i !== name);
                } else {
                    this.saved.push(name);
                }
            },

            isSaved(name) { return this.saved.includes(name); },
            clearAll()    { this.search = ''; },
        };
    }
</script>
</x-user.shell>
</x-layouts.dashboard>
