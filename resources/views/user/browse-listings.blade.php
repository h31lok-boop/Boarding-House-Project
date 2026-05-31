<x-layouts.dashboard>
<x-user.shell>
@php
    $detailUrl = \Illuminate\Support\Facades\Route::has('user.browse.show') ? route('user.browse') : url()->current();
    $mapUrl = \Illuminate\Support\Facades\Route::has('map.user.listings')
        ? route('map.user.listings')
        : 'https://www.openstreetmap.org/#map=14/6.7440/125.3550';

    $listings = [
        [
            'name'     => 'Sunrise Student Boarding House',
            'price'    => '2,800',
            'location' => 'Zone 1, Digos City, Davao del Sur',
            'room'     => 'Private Room',
            'rating'   => '4.6',
            'reviews'  => 32,
            'photos'   => 12,
            'image'    => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=700&q=80',
            'icons'    => ['wifi', 'water', 'key', 'lock'],
        ],
        [
            'name'     => 'Greenleaf Bedspace and Dorm',
            'price'    => '3,200',
            'location' => 'Poblacion, Digos City, Davao del Sur',
            'room'     => 'Bedspace',
            'rating'   => '4.4',
            'reviews'  => 28,
            'photos'   => 10,
            'image'    => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=700&q=80',
            'icons'    => ['wifi', 'water', 'key', 'share'],
        ],
        [
            'name'     => 'Haven Point Boarding Residence',
            'price'    => '5,200',
            'location' => 'Dawis Norte, Digos City, Davao del Sur',
            'room'     => 'Private Room',
            'rating'   => '4.7',
            'reviews'  => 41,
            'photos'   => 15,
            'image'    => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=700&q=80',
            'icons'    => ['air', 'water', 'key', 'lock'],
        ],
        [
            'name'     => 'Berchby Loft',
            'price'    => '5,500',
            'location' => 'Poblacion, Digos City, Davao del Sur',
            'room'     => 'Studio Unit',
            'rating'   => '4.5',
            'reviews'  => 19,
            'photos'   => 8,
            'image'    => 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=700&q=80',
            'icons'    => ['wifi', 'water', 'key', 'lock', 'share'],
        ],
        [
            'name'     => 'Suburban Sanctuary',
            'price'    => '3,200',
            'location' => 'Mahayahay, Digos City, Davao del Sur',
            'room'     => 'Single Room',
            'rating'   => '4.3',
            'reviews'  => 16,
            'photos'   => 9,
            'image'    => 'https://images.unsplash.com/photo-1595526114035-0d45ed16cfbf?auto=format&fit=crop&w=700&q=80',
            'icons'    => ['wifi', 'water', 'key', 'lock'],
        ],
        [
            'name'     => "Kecai's Place",
            'price'    => '2,700',
            'location' => 'Aplaya, Digos City, Davao del Sur',
            'room'     => 'Single Room',
            'rating'   => '4.2',
            'reviews'  => 23,
            'photos'   => 11,
            'image'    => 'https://images.unsplash.com/photo-1484154218962-a197022b5858?auto=format&fit=crop&w=700&q=80',
            'icons'    => ['wifi', 'water', 'key', 'lock'],
        ],
        [
            'name'     => 'Summers Cozy Studio',
            'price'    => '4,500',
            'location' => 'Zone 2, Digos City, Davao del Sur',
            'room'     => 'Studio Unit',
            'rating'   => '4.5',
            'reviews'  => 30,
            'photos'   => 7,
            'image'    => 'https://images.unsplash.com/photo-1560185007-c5ca9d2c014d?auto=format&fit=crop&w=700&q=80',
            'icons'    => ['wifi', 'water', 'key', 'lock'],
        ],
        [
            'name'     => 'Summer Hub',
            'price'    => '2,000',
            'location' => 'Zone 4, Digos City, Davao del Sur',
            'room'     => 'Shared Room',
            'rating'   => '4.1',
            'reviews'  => 18,
            'photos'   => 6,
            'image'    => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=700&q=80',
            'icons'    => ['wifi', 'water', 'key', 'lock', 'share'],
        ],
    ];

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
        'expand'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 3h6v6M14 10l7-7M9 21H3v-6m7-1-7 7"/>',
    ];
@endphp

<style>
    /* ── Base ─────────────────────────────────────────── */
    .bm-finder { color: #111827; font-family: Manrope, "Segoe UI", sans-serif; }
    .bm-finder * { box-sizing: border-box; }
    .bm-icon { width: 18px; height: 18px; flex: 0 0 auto; }

    /* ── Breadcrumb ───────────────────────────────────── */
    .bm-crumbs {
        display: flex; align-items: center; gap: 8px;
        color: #64748b; font-size: 13px; font-weight: 600;
        margin-bottom: 18px;
    }
    .bm-crumbs a { color: #64748b; text-decoration: none; }
    .bm-crumbs a:hover { color: #5b36f5; }

    /* ── Page heading ─────────────────────────────────── */
    .bm-page-heading { margin-bottom: 20px; }
    .bm-page-heading h1 {
        margin: 0; color: #0f172a;
        font-size: 28px; font-weight: 800; line-height: 1.15;
    }
    .bm-page-heading p {
        margin: 6px 0 0; color: #64748b; font-size: 14px; line-height: 1.5;
    }

    /* ── Search card ──────────────────────────────────── */
    .bm-search-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px 18px;
        box-shadow: 0 2px 12px rgba(15,23,42,.06);
    }
    .bm-search-box {
        position: relative;
        margin-bottom: 14px;
    }
    .bm-search-box svg {
        position: absolute; left: 13px; top: 50%;
        transform: translateY(-50%); color: #94a3b8;
    }
    .bm-search-box input {
        width: 100%; height: 40px;
        border: 1px solid #e2e8f0; border-radius: 8px;
        padding: 0 14px 0 42px;
        color: #0f172a; font-size: 13px; font-family: inherit;
        outline: none; background: #fff;
        transition: border-color .15s, box-shadow .15s;
    }
    .bm-search-box input:focus {
        border-color: #7556ff;
        box-shadow: 0 0 0 3px rgba(117,86,255,.12);
    }

    /* ── Filter row ───────────────────────────────────── */
    .bm-filter-row {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
    }
    .bm-filter-control {
        position: relative;
        display: flex; flex-direction: column; justify-content: center; gap: 2px;
        height: 52px;
        border: 1px solid #e2e8f0; border-radius: 8px;
        background: #fff; padding: 8px 34px 8px 12px;
        cursor: pointer; transition: border-color .15s;
    }
    .bm-filter-control:hover { border-color: #a78bfa; }
    .bm-filter-control span {
        color: #94a3b8; font-size: 10px; font-weight: 700;
        text-transform: uppercase; letter-spacing: .04em;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .bm-filter-control select {
        width: 100%; border: 0; padding: 0;
        background: transparent; appearance: none;
        color: #111827; font-size: 12px; font-weight: 700;
        font-family: inherit; outline: none; cursor: pointer;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .bm-filter-control svg {
        position: absolute; right: 10px; top: 50%;
        transform: translateY(-50%);
        color: #94a3b8; width: 14px; height: 14px; pointer-events: none;
    }

    /* ── Two-column layout ────────────────────────────── */
    .bm-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 300px;
        gap: 22px;
        align-items: start;
        margin-top: 20px;
    }

    /* ── Results header ───────────────────────────────── */
    .bm-results-head {
        display: flex; align-items: center;
        justify-content: space-between; gap: 12px;
        margin: 0 0 14px;
    }
    .bm-results-count {
        margin: 0; color: #475569; font-size: 13px; font-weight: 700;
    }
    .bm-results-count strong { color: #111827; }
    .bm-results-controls { display: flex; align-items: center; gap: 8px; }

    /* ── View toggle ──────────────────────────────────── */
    .bm-view-toggle {
        display: inline-flex; overflow: hidden;
        border: 1px solid #e2e8f0; border-radius: 8px; background: #fff;
    }
    .bm-view-toggle button {
        display: inline-flex; align-items: center; gap: 6px;
        height: 34px; padding: 0 12px;
        border: 0; background: #fff; color: #64748b;
        font-size: 12px; font-weight: 700; font-family: inherit;
        cursor: pointer; transition: background .12s, color .12s;
    }
    .bm-view-toggle button + button { border-left: 1px solid #e2e8f0; }
    .bm-view-toggle button.is-active { color: #5b36f5; background: #f5f3ff; }
    .bm-view-toggle button:hover:not(.is-active) { background: #f8fafc; }

    /* ── Mobile filter toggle ─────────────────────────── */
    .bm-mobile-filter-btn {
        display: none; align-items: center; gap: 6px;
        height: 34px; padding: 0 12px;
        border: 1px solid #e2e8f0; border-radius: 8px;
        background: #fff; color: #475569;
        font-size: 12px; font-weight: 700; font-family: inherit;
        cursor: pointer; white-space: nowrap;
        transition: border-color .15s, color .15s;
    }
    .bm-mobile-filter-btn:hover,
    .bm-mobile-filter-btn.is-open { border-color: #7a5cff; color: #5b36f5; background: #f5f3ff; }

    /* ── Card grid ────────────────────────────────────── */
    .bm-card-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    /* ── Listing card — equal height via flex ─────────── */
    .bm-listing-card {
        display: flex; flex-direction: column;
        overflow: hidden;
        border: 1px solid #e4e9f1; border-radius: 10px;
        background: #fff;
        box-shadow: 0 2px 10px rgba(15,23,42,.06);
        transition: box-shadow .18s, transform .18s;
    }
    .bm-listing-card:hover {
        box-shadow: 0 8px 28px rgba(15,23,42,.12);
        transform: translateY(-2px);
    }

    /* ── Card image ───────────────────────────────────── */
    .bm-card-media {
        position: relative;
        aspect-ratio: 16 / 10;
        flex: 0 0 auto;
        background: linear-gradient(135deg, #e2e8f0, #f8fafc);
        overflow: hidden;
    }
    .bm-card-media img {
        width: 100%; height: 100%; object-fit: cover; display: block;
        transition: transform .3s ease;
    }
    .bm-listing-card:hover .bm-card-media img { transform: scale(1.03); }
    .bm-photo-count {
        position: absolute; left: 10px; bottom: 9px;
        display: inline-flex; align-items: center; gap: 4px;
        height: 24px; padding: 0 8px;
        border-radius: 5px; color: #fff;
        background: rgba(17,24,39,.72);
        font-size: 11px; font-weight: 800;
    }
    .bm-heart {
        position: absolute; right: 9px; top: 9px;
        display: inline-flex; width: 34px; height: 34px;
        align-items: center; justify-content: center;
        border: 1px solid rgba(226,232,240,.9);
        border-radius: 8px; background: rgba(255,255,255,.9);
        color: #94a3b8; cursor: pointer;
        transition: color .15s, border-color .15s, background .15s;
    }
    .bm-heart:hover { color: #ff4b2b; border-color: #ff4b2b; }
    .bm-heart.is-saved { color: #ff4b2b; border-color: #ff4b2b; }

    /* ── Card body — flex column so footer sticks ─────── */
    .bm-card-body {
        flex: 1;
        display: flex; flex-direction: column;
        padding: 14px 14px 14px;
    }

    .bm-card-title {
        margin: 0; color: #111827;
        font-size: 14px; line-height: 1.35; font-weight: 800;
        display: -webkit-box;
        -webkit-line-clamp: 2; -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 38px;
    }

    /* ── Price ────────────────────────────────────────── */
    .bm-price-row {
        margin-top: 10px;
        display: flex; align-items: baseline; gap: 4px;
        color: #64748b; font-size: 12px; font-weight: 600;
    }
    .bm-price-row strong {
        font-size: 18px; font-weight: 900; color: #e8380d; line-height: 1;
    }

    /* ── Location ─────────────────────────────────────── */
    .bm-location {
        display: flex; align-items: center; gap: 5px;
        margin-top: 7px; color: #64748b; font-size: 12px;
        overflow: hidden; white-space: nowrap; text-overflow: ellipsis;
    }
    .bm-location svg { flex: 0 0 auto; }

    /* ── Room chip ────────────────────────────────────── */
    .bm-room-chip {
        display: inline-flex; align-self: flex-start;
        margin-top: 10px;
        border-radius: 5px; background: #f1f5f9;
        color: #475569; padding: 3px 9px;
        font-size: 11px; font-weight: 700;
        border: 1px solid #e2e8f0;
    }

    /* ── Rating + amenity icons ───────────────────────── */
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
    .bm-rating svg { color: #f59e0b; fill: #f59e0b; stroke: #f59e0b; }
    .bm-amenity-icons {
        display: inline-flex; align-items: center; gap: 7px;
        color: #94a3b8; flex-wrap: nowrap;
    }
    .bm-amenity-icons svg { width: 13px; height: 13px; }

    /* ── Card actions — always at the card bottom ─────── */
    .bm-card-actions {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 8px;
        margin-top: auto;
        padding-top: 14px;
        border-top: 1px solid #f1f5f9;
    }

    .bm-details-btn,
    .bm-save-btn {
        display: inline-flex; align-items: center; justify-content: center;
        height: 36px; border-radius: 7px;
        font-size: 12px; font-weight: 800; font-family: inherit;
        line-height: 1; white-space: nowrap; text-decoration: none !important;
        cursor: pointer; transition: background .15s, border-color .15s, color .15s;
    }
    .bm-details-btn {
        border: 1.5px solid #7a5cff; color: #5b36f5; background: #fff;
        padding: 0 14px;
    }
    .bm-details-btn:hover { background: #f5f3ff; }
    .bm-save-btn {
        border: 1.5px solid #e2e8f0; color: #475569; background: #fff;
        gap: 5px; padding: 0 12px;
    }
    .bm-save-btn:hover { border-color: #a78bfa; color: #5b36f5; background: #f5f3ff; }
    .bm-save-btn.is-saved { color: #5b36f5; border-color: #a78bfa; background: #f5f3ff; }

    /* ── Pagination ───────────────────────────────────── */
    .bm-pagination-row {
        display: flex; align-items: center;
        justify-content: space-between; flex-wrap: wrap;
        gap: 12px; margin-top: 20px;
    }
    .bm-pagination {
        display: inline-flex; align-items: center; gap: 6px;
    }
    .bm-page-btn {
        min-width: 34px; height: 34px; padding: 0 4px;
        border: 1px solid #e2e8f0; border-radius: 7px;
        background: #fff; color: #475569;
        font-size: 13px; font-weight: 700; font-family: inherit;
        cursor: pointer; transition: background .12s, border-color .12s, color .12s;
    }
    .bm-page-btn:hover:not(.is-plain) { border-color: #a78bfa; color: #5b36f5; background: #f5f3ff; }
    .bm-page-btn.is-active { border-color: #ff5a3c; color: #ff4b2b; background: #fff7f4; }
    .bm-page-btn.is-plain { border-color: transparent; background: transparent; color: #94a3b8; }
    .bm-page-size { position: relative; }
    .bm-page-size select {
        height: 34px; min-width: 110px; appearance: none;
        border: 1px solid #e2e8f0; border-radius: 7px;
        background: #fff; color: #475569;
        font-size: 12px; font-weight: 700; font-family: inherit;
        padding: 0 32px 0 12px; outline: none; cursor: pointer;
    }
    .bm-page-size svg {
        position: absolute; right: 10px; top: 50%;
        transform: translateY(-50%);
        color: #94a3b8; pointer-events: none;
    }

    /* ── Sidebar ──────────────────────────────────────── */
    .bm-sidebar { display: flex; flex-direction: column; gap: 14px; }
    .bm-side-panel {
        border: 1px solid #e7ebf2; border-radius: 10px;
        background: #fff;
        box-shadow: 0 2px 12px rgba(15,23,42,.05);
        padding: 16px 18px;
    }
    .bm-panel-title {
        display: flex; align-items: center;
        justify-content: space-between; margin-bottom: 14px;
    }
    .bm-panel-title h2 {
        margin: 0; color: #111827; font-size: 14px; font-weight: 900;
    }
    .bm-panel-title button {
        border: 0; background: transparent;
        color: #ff4b2b; font-size: 12px; font-weight: 800;
        font-family: inherit; cursor: pointer;
    }
    .bm-panel-title button:hover { color: #cc2d14; }

    /* ── Map: OpenStreetMap iframe ────────────────────── */
    .bm-map-wrap {
        position: relative;
        border-radius: 8px; overflow: hidden;
        border: 1px solid #e2e8f0;
    }
    .bm-map-wrap iframe {
        display: block;
        width: 100%; height: 210px; border: 0;
    }
    .bm-map-footer {
        display: flex; align-items: center; justify-content: space-between;
        margin-top: 10px;
    }
    .bm-map-footer-label {
        display: flex; align-items: center; gap: 6px;
        color: #475569; font-size: 12px; font-weight: 700;
    }
    .bm-map-footer-label svg { color: #5b36f5; }
    .bm-map-link {
        color: #5b36f5; text-decoration: none;
        font-size: 12px; font-weight: 800;
    }
    .bm-map-link:hover { text-decoration: underline; }

    /* ── Search summary ───────────────────────────────── */
    .bm-summary-list { display: flex; flex-direction: column; gap: 12px; }
    .bm-summary-row {
        display: flex; align-items: center;
        justify-content: space-between; gap: 10px;
    }
    .bm-summary-label {
        display: inline-flex; align-items: center; gap: 10px;
        color: #334155; font-size: 12px; font-weight: 800; min-width: 0;
        white-space: nowrap;
    }
    .bm-summary-label .bm-icon { color: #5b36f5; flex-shrink: 0; }
    .bm-summary-value {
        color: #64748b; font-size: 12px; font-weight: 700;
        text-align: right; min-width: 0;
        overflow: hidden; white-space: nowrap; text-overflow: ellipsis;
    }
    .bm-edit-search {
        width: 100%; height: 36px; margin-top: 16px;
        border: 1.5px solid #a78bfa; border-radius: 7px;
        background: #fff; color: #5b36f5;
        font-size: 12px; font-weight: 900; font-family: inherit;
        cursor: pointer; transition: background .15s;
    }
    .bm-edit-search:hover { background: #f5f3ff; }

    /* ── Amenity list ─────────────────────────────────── */
    .bm-amenity-list { display: flex; flex-direction: column; gap: 12px; }
    .bm-amenity-row {
        display: flex; align-items: center;
        justify-content: space-between; gap: 10px;
    }
    .bm-amenity-name {
        display: inline-flex; align-items: center; gap: 10px;
        color: #334155; font-size: 12px; font-weight: 800;
        min-width: 0;
    }
    .bm-amenity-name .bm-icon { color: #5b36f5; flex-shrink: 0; }
    .bm-count-pill {
        min-width: 32px; height: 24px;
        display: inline-flex; align-items: center; justify-content: center;
        border: 1px solid #e2e8f0; border-radius: 999px;
        background: #f8fafc; color: #64748b;
        font-size: 11px; font-weight: 800; flex-shrink: 0;
    }
    .bm-view-amenities {
        display: inline-flex; margin-top: 16px;
        color: #5b36f5; text-decoration: none;
        font-size: 12px; font-weight: 900;
    }
    .bm-view-amenities:hover { text-decoration: underline; }

    /* ── Safety banner ────────────────────────────────── */
    .bm-safety {
        display: flex; align-items: center;
        justify-content: space-between; gap: 20px;
        margin-top: 24px; padding: 14px 20px;
        border: 1px solid #e7ebf2; border-radius: 10px;
        background: #fff;
        box-shadow: 0 2px 10px rgba(15,23,42,.04);
    }
    .bm-safety-main { display: flex; align-items: center; gap: 14px; min-width: 0; }
    .bm-shield {
        display: inline-flex; width: 40px; height: 40px; flex: 0 0 40px;
        align-items: center; justify-content: center;
        border-radius: 8px; background: #fff2ed; color: #ff4b2b;
    }
    .bm-safety strong { display: block; color: #111827; font-size: 13px; font-weight: 900; }
    .bm-safety p { margin: 0; }
    .bm-safety span { color: #64748b; font-size: 12px; }
    .bm-safety-cta {
        display: inline-flex; align-items: center; gap: 16px;
        color: #64748b; font-size: 12px; font-weight: 700;
        white-space: nowrap; flex-shrink: 0;
    }
    .bm-safety-cta a {
        color: #ff4b2b; text-decoration: none; font-weight: 900;
        padding: 7px 14px; border: 1.5px solid #ff4b2b;
        border-radius: 7px; background: #fff;
        transition: background .15s;
    }
    .bm-safety-cta a:hover { background: #fff7f4; }

    /* ── Divider between label+value in summary ───────── */
    .bm-summary-sep {
        flex: 1; height: 1px; background: #f1f5f9; margin: 0 4px;
    }

    /* ── Responsive: ≤ 1400px: sidebar 280px ─────────── */
    @media (max-width: 1400px) {
        .bm-layout { grid-template-columns: minmax(0, 1fr) 280px; }
        .bm-card-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    /* ── Responsive: ≤ 1200px: sidebar below, 3 panels ── */
    @media (max-width: 1200px) {
        .bm-layout { grid-template-columns: 1fr; }
        .bm-sidebar { flex-direction: row; flex-wrap: wrap; }
        .bm-sidebar .bm-side-panel { flex: 1 1 260px; min-width: 220px; }
        .bm-card-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .bm-mobile-filter-btn { display: inline-flex; }
        .bm-filter-row { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }

    /* ── Responsive: ≤ 900px ─────────────────────────── */
    @media (max-width: 900px) {
        .bm-card-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .bm-filter-row { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .bm-safety { gap: 14px; }
        .bm-safety-cta { flex-direction: column; align-items: flex-start; gap: 8px; white-space: normal; }
        .bm-pagination-row { justify-content: center; }
    }

    /* ── Responsive: ≤ 640px ─────────────────────────── */
    @media (max-width: 640px) {
        .bm-page-heading h1 { font-size: 22px; }
        .bm-search-card { padding: 12px; }
        .bm-card-grid { grid-template-columns: 1fr; }
        .bm-filter-row { grid-template-columns: 1fr 1fr; }
        .bm-sidebar { flex-direction: column; }
        .bm-sidebar .bm-side-panel { flex: none; }
        .bm-results-head { flex-direction: column; align-items: flex-start; gap: 8px; }
        .bm-results-controls { width: 100%; justify-content: space-between; }
        .bm-card-actions { grid-template-columns: 1fr auto; }
        .bm-pagination-row { flex-direction: column; align-items: center; gap: 10px; }
        .bm-page-size select { min-width: 130px; }
        .bm-safety { flex-direction: column; padding: 14px 16px; gap: 12px; }
        .bm-safety-cta { width: 100%; }
        .bm-layout { margin-top: 14px; }
    }

    /* ── Responsive: ≤ 480px ─────────────────────────── */
    @media (max-width: 480px) {
        .bm-filter-row { grid-template-columns: 1fr 1fr; }
        .bm-card-title { font-size: 13px; }
        .bm-price-row strong { font-size: 16px; }
        .bm-map-wrap iframe { height: 180px; }
    }
</style>

<div class="bm-finder" x-data="boardingHouseFinder()">

    {{-- Breadcrumb --}}
    <nav class="bm-crumbs" aria-label="Breadcrumb">
        <a href="{{ route('user.dashboard') }}">Dashboard</a>
        <svg class="bm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/>
        </svg>
        <span>Find Boarding Houses</span>
    </nav>

    {{-- Page heading --}}
    <header class="bm-page-heading">
        <h1>Find Boarding Houses</h1>
        <p>Discover and compare boarding houses in Digos City, Davao del Sur that fit your preferences and budget.</p>
    </header>

    {{-- Two-column layout --}}
    <div class="bm-layout">

        {{-- ── Left: main content ───────────────────── --}}
        <main>

            {{-- Search & filters --}}
            <section class="bm-search-card" aria-label="Search filters">
                <div class="bm-search-box">
                    <svg class="bm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        {!! $iconPaths['search'] !!}
                    </svg>
                    <input x-model="search" type="search"
                           placeholder="Search by name, barangay, or keyword…">
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
                        <select>
                            <option>Recommended</option>
                            <option>Lowest Price</option>
                            <option>Highest Rated</option>
                            <option>Newest</option>
                        </select>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">{!! $iconPaths['chevron'] !!}</svg>
                    </label>
                </div>
            </section>

            {{-- Results bar --}}
            <div class="bm-results-head">
                <p class="bm-results-count"><strong>42</strong> boarding houses found in Digos City</p>
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
                @foreach($listings as $listing)
                    <article class="bm-listing-card">
                        {{-- Image --}}
                        <div class="bm-card-media">
                            <img src="{{ $listing['image'] }}"
                                 alt="{{ $listing['name'] }}"
                                 loading="lazy"
                                 onerror="this.style.display='none'">
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
                                <strong>₱{{ $listing['price'] }}</strong>
                                <span>/ month</span>
                            </div>

                            <div class="bm-location">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex:0 0 auto">{!! $iconPaths['location'] !!}</svg>
                                {{ $listing['location'] }}
                            </div>

                            <span class="bm-room-chip">{{ $listing['room'] }}</span>

                            <div class="bm-card-meta">
                                <span class="bm-rating">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.5">{!! $iconPaths['star'] !!}</svg>
                                    {{ $listing['rating'] }}
                                    <span style="color:#94a3b8;font-weight:600">({{ $listing['reviews'] }})</span>
                                </span>
                                <span class="bm-amenity-icons" aria-label="Amenities">
                                    @foreach(array_slice($listing['icons'], 0, 4) as $icon)
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPaths[$icon] !!}</svg>
                                    @endforeach
                                </span>
                            </div>

                            {{-- Buttons — pushed to bottom by margin-top:auto on card-actions --}}
                            <div class="bm-card-actions">
                                <a class="bm-details-btn" href="{{ $detailUrl }}">View Details</a>
                                <button class="bm-save-btn" type="button"
                                        :class="{ 'is-saved': isSaved('{{ $listing['name'] }}') }"
                                        @click="toggleSave('{{ $listing['name'] }}')">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPaths['bookmark'] !!}</svg>
                                    Save
                                </button>
                            </div>
                        </div>
                    </article>
                @endforeach
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
                        <span class="bm-summary-value">Digos City, Davao del Sur</span>
                    </div>
                    <div class="bm-summary-row">
                        <span class="bm-summary-label">
                            <svg class="bm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPaths['home'] !!}</svg>
                            Room Type
                        </span>
                        <span class="bm-summary-value">Private Room</span>
                    </div>
                    <div class="bm-summary-row">
                        <span class="bm-summary-label">
                            <svg class="bm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPaths['spark'] !!}</svg>
                            Amenities
                        </span>
                        <span class="bm-summary-value">Wi-Fi, Kitchen</span>
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

    {{-- Safety banner --}}
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
            <a href="{{ route('user.recommendations') }}">Request a listing</a>
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
