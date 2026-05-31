<x-layouts.dashboard>
<x-user.shell>
@php
    $detailUrl = \Illuminate\Support\Facades\Route::has('user.browse.show') ? route('user.browse') : url()->current();
    $mapUrl = \Illuminate\Support\Facades\Route::has('map.user.listings')
        ? route('map.user.listings')
        : 'https://www.openstreetmap.org/#map=12/14.6760/121.0437';

    $listings = [
        [
            'name' => 'Sunrise Boarding House',
            'price' => '3,500',
            'location' => 'Quezon City, Metro Manila',
            'room' => 'Private Room',
            'rating' => '4.6',
            'reviews' => 32,
            'photos' => 12,
            'image' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=700&q=80',
            'icons' => ['wifi', 'water', 'key', 'lock'],
        ],
        [
            'name' => 'Greenview Residences',
            'price' => '4,000',
            'location' => 'Quezon City, Metro Manila',
            'room' => 'Private Room',
            'rating' => '4.4',
            'reviews' => 28,
            'photos' => 10,
            'image' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=700&q=80',
            'icons' => ['wifi', 'water', 'key', 'share'],
        ],
        [
            'name' => 'Haven Boarders',
            'price' => '3,800',
            'location' => 'Quezon City, Metro Manila',
            'room' => 'Shared Room',
            'rating' => '4.2',
            'reviews' => 19,
            'photos' => 15,
            'image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=700&q=80',
            'icons' => ['air', 'water', 'key'],
        ],
        [
            'name' => 'Comfort Living QC',
            'price' => '4,500',
            'location' => 'Quezon City, Metro Manila',
            'room' => 'Private Room',
            'rating' => '4.7',
            'reviews' => 41,
            'photos' => 8,
            'image' => 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=700&q=80',
            'icons' => ['wifi', 'water', 'key', 'lock', 'share'],
        ],
        [
            'name' => 'SafeHaven Boarding',
            'price' => '3,200',
            'location' => 'Quezon City, Metro Manila',
            'room' => 'Shared Room',
            'rating' => '4.1',
            'reviews' => 16,
            'photos' => 9,
            'image' => 'https://images.unsplash.com/photo-1595526114035-0d45ed16cfbf?auto=format&fit=crop&w=700&q=80',
            'icons' => ['wifi', 'water', 'key', 'lock'],
        ],
        [
            'name' => 'Maple Lodge',
            'price' => '3,600',
            'location' => 'Quezon City, Metro Manila',
            'room' => 'Private Room',
            'rating' => '4.3',
            'reviews' => 23,
            'photos' => 11,
            'image' => 'https://images.unsplash.com/photo-1484154218962-a197022b5858?auto=format&fit=crop&w=700&q=80',
            'icons' => ['wifi', 'water', 'key', 'lock'],
        ],
        [
            'name' => 'Tranquil Place',
            'price' => '4,200',
            'location' => 'Quezon City, Metro Manila',
            'room' => 'Private Room',
            'rating' => '4.5',
            'reviews' => 30,
            'photos' => 7,
            'image' => 'https://images.unsplash.com/photo-1560185007-c5ca9d2c014d?auto=format&fit=crop&w=700&q=80',
            'icons' => ['wifi', 'water', 'key', 'lock'],
        ],
        [
            'name' => 'Bright Stay',
            'price' => '3,900',
            'location' => 'Quezon City, Metro Manila',
            'room' => 'Shared Room',
            'rating' => '4.2',
            'reviews' => 18,
            'photos' => 6,
            'image' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=700&q=80',
            'icons' => ['wifi', 'water', 'key', 'lock', 'share'],
        ],
    ];

    $popularAmenities = [
        ['name' => 'Wi-Fi', 'count' => 42, 'icon' => 'wifi'],
        ['name' => 'Kitchen', 'count' => 38, 'icon' => 'kitchen'],
        ['name' => 'Laundry', 'count' => 35, 'icon' => 'laundry'],
        ['name' => 'Air Conditioning', 'count' => 28, 'icon' => 'air'],
        ['name' => 'Parking', 'count' => 22, 'icon' => 'parking'],
    ];

    $iconPaths = [
        'search' => '<path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.2-5.2m1.7-5.05a6.75 6.75 0 1 1-13.5 0 6.75 6.75 0 0 1 13.5 0Z"/>',
        'chevron' => '<path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>',
        'grid' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 4h6v6H4V4Zm10 0h6v6h-6V4ZM4 14h6v6H4v-6Zm10 0h6v6h-6v-6Z"/>',
        'list' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
        'filter' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M7 12h10m-7 6h4"/>',
        'heart' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21.2l7.8-7.8 1-1a5.5 5.5 0 0 0 0-7.8Z"/>',
        'camera' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 8h3l1.4-2h7.2L17 8h3v10H4V8Zm8 7.2a3.2 3.2 0 1 0 0-6.4 3.2 3.2 0 0 0 0 6.4Z"/>',
        'bookmark' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 4h12v16l-6-3-6 3V4Z"/>',
        'star' => '<path stroke-linecap="round" stroke-linejoin="round" d="m12 3 2.6 5.3 5.9.8-4.2 4.1 1 5.8-5.3-2.8L6.7 19l1-5.8-4.2-4.1 5.9-.8L12 3Z"/>',
        'wifi' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.5 8.5a15 15 0 0 1 19 0M5.8 11.8a10 10 0 0 1 12.4 0M9.2 15.2a5 5 0 0 1 5.6 0M12 18h.01"/>',
        'water' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3s5 5.2 5 9a5 5 0 0 1-10 0c0-3.8 5-9 5-9Z"/>',
        'key' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 7a4 4 0 1 1-2.5 7.1L6 20.5H3.5V18H6v-2.5h2.5l2.4-2.4A4 4 0 0 1 15 7Z"/>',
        'lock' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 10h12v10H6V10Zm3 0V7a3 3 0 1 1 6 0v3"/>',
        'share' => '<path stroke-linecap="round" stroke-linejoin="round" d="M18 8a3 3 0 1 0-2.8-4H15a3 3 0 0 0 .2 1L8.8 9a3 3 0 1 0 0 6l6.4 4a3 3 0 1 0 .9-1.5L9.7 13.5a3.1 3.1 0 0 0 0-3L16.1 6.5A3 3 0 0 0 18 8Z"/>',
        'kitchen' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 3v8m4-8v8m4-8v8M5 11h12v10H5V11Zm14-7v17"/>',
        'laundry' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 4h14v16H5V4Zm4 3h.01M12 15a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/>',
        'air' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M5 7l14 10M19 7 5 17"/>',
        'parking' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 20V4h6a4.5 4.5 0 0 1 0 9H8"/>',
        'location' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-4.8 7-11a7 7 0 1 0-14 0c0 6.2 7 11 7 11Zm0-8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>',
        'budget' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m3-9.5c-.6-1-1.6-1.5-3-1.5-1.7 0-3 .9-3 2s1.3 2 3 2 3 .9 3 2-1.3 2-3 2c-1.4 0-2.5-.5-3.1-1.5"/>',
        'home' => '<path stroke-linecap="round" stroke-linejoin="round" d="m3 11 9-8 9 8v9h-6v-5H9v5H3v-9Z"/>',
        'spark' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3l2.1 6 6 2.1-6 2.1-2.1 6-2.1-6-6-2.1 6-2.1L12 3Z"/>',
        'sort' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 6h12M4 12h8M4 18h5m9-8v10m0 0 3-3m-3 3-3-3"/>',
        'shield' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3 5 6v5c0 4.6 2.8 8.4 7 10 4.2-1.6 7-5.4 7-10V6l-7-3Zm-2.2 9 1.5 1.5 3.2-3.6"/>',
        'expand' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 3h6v6M14 10l7-7M9 21H3v-6m7-1-7 7"/>',
    ];
@endphp

<style>
    .bm-finder {
        color: #111827;
        font-family: Manrope, "Segoe UI", sans-serif;
    }

    .bm-finder * {
        box-sizing: border-box;
    }

    .bm-icon {
        width: 18px;
        height: 18px;
        flex: 0 0 auto;
    }

    .bm-crumbs {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 16px;
    }

    .bm-crumbs a {
        color: #64748b;
        text-decoration: none;
    }

    .bm-page-heading h1 {
        margin: 0;
        color: #0f172a;
        font-size: 30px;
        line-height: 1.12;
        font-weight: 800;
        letter-spacing: 0;
    }

    .bm-page-heading p {
        margin: 10px 0 0;
        color: #64748b;
        font-size: 15px;
    }

    .bm-search-card {
        margin-top: 0;
        background: #fff;
        border: 1px solid #e9edf3;
        border-radius: 10px;
        padding: 16px 18px;
        box-shadow: 0 18px 44px rgba(15, 23, 42, 0.045);
    }

    .bm-search-box {
        position: relative;
        margin-bottom: 14px;
    }

    .bm-search-box svg {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
    }

    .bm-search-box input {
        width: 100%;
        height: 36px;
        border: 1px solid #d9e0ea;
        border-radius: 8px;
        color: #0f172a;
        font-size: 13px;
        outline: none;
        padding: 0 14px 0 42px;
        transition: border-color 0.18s ease, box-shadow 0.18s ease;
    }

    .bm-search-box input:focus {
        border-color: #7556ff;
        box-shadow: 0 0 0 3px rgba(117, 86, 255, 0.12);
    }

    .bm-filter-row {
        display: grid;
        width: 100%;
        min-width: 0;
        grid-template-columns: repeat(auto-fit, minmax(135px, 1fr));
        gap: 12px;
        align-items: center;
    }

    .bm-filter-control {
        position: relative;
        display: grid;
        gap: 4px;
        min-width: 0;
        height: 50px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
        padding: 8px 34px 7px 12px;
    }

    .bm-filter-control span {
        color: #64748b;
        font-size: 10px;
        font-weight: 700;
    }

    .bm-filter-control select {
        width: 100%;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        border: 0;
        padding: 0;
        background: transparent;
        appearance: none;
        color: #111827;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.15;
        outline: none;
    }

    .bm-filter-control svg {
        position: absolute;
        right: 11px;
        bottom: 10px;
        color: #475569;
        width: 14px;
        height: 14px;
        pointer-events: none;
    }

    .bm-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 360px;
        gap: 32px;
        align-items: start;
        margin-top: 24px;
    }

    .bm-results-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin: 24px 0 14px;
    }

    .bm-results-head p {
        margin: 0;
        color: #475569;
        font-size: 13px;
        font-weight: 700;
    }

    .bm-view-toggle {
        display: inline-flex;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
    }

    .bm-view-toggle button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 34px;
        min-width: 82px;
        justify-content: center;
        border: 0;
        background: #fff;
        color: #334155;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
    }

    .bm-view-toggle button + button {
        border-left: 1px solid #edf1f5;
    }

    .bm-view-toggle button.is-active {
        color: #5b36f5;
        background: #fbfaff;
    }

    .bm-card-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px 18px;
    }

    .bm-listing-card {
        min-width: 0;
        overflow: hidden;
        border: 1px solid #e4e9f1;
        border-radius: 9px;
        background: #fff;
        box-shadow: 0 12px 26px rgba(15, 23, 42, 0.045);
    }

    .bm-card-media {
        position: relative;
        height: 128px;
        background: linear-gradient(135deg, #e2e8f0, #f8fafc);
        overflow: hidden;
    }

    .bm-card-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .bm-photo-count {
        position: absolute;
        left: 10px;
        bottom: 9px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        min-width: 38px;
        height: 24px;
        padding: 0 8px;
        border-radius: 5px;
        color: #fff;
        background: rgba(17, 24, 39, 0.78);
        font-size: 12px;
        font-weight: 800;
    }

    .bm-heart {
        position: absolute;
        right: 9px;
        top: 8px;
        display: inline-flex;
        width: 36px;
        height: 36px;
        align-items: center;
        justify-content: center;
        border: 1px solid #dfe5ee;
        border-radius: 9px;
        background: rgba(255, 255, 255, 0.94);
        color: #64748b;
        cursor: pointer;
    }

    .bm-heart.is-saved {
        color: #ff4b2b;
    }

    .bm-card-body {
        padding: 12px 12px 10px;
    }

    .bm-card-title {
        margin: 0;
        color: #111827;
        font-size: 15px;
        line-height: 1.2;
        font-weight: 800;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .bm-price-row {
        margin-top: 8px;
        color: #ff4b2b;
        font-size: 11px;
        font-weight: 700;
    }

    .bm-price-row strong {
        font-size: 15px;
        font-weight: 900;
    }

    .bm-location {
        margin-top: 8px;
        color: #64748b;
        font-size: 11px;
        line-height: 1.25;
    }

    .bm-room-chip {
        display: inline-flex;
        margin-top: 9px;
        border-radius: 5px;
        background: #f3f5f8;
        color: #64748b;
        padding: 3px 7px;
        font-size: 10px;
        font-weight: 700;
    }

    .bm-card-meta {
        margin-top: 11px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .bm-rating {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        color: #334155;
        font-size: 11px;
        font-weight: 600;
        min-width: 0;
    }

    .bm-rating svg {
        color: #f8a300;
        fill: #f8a300;
        stroke: #f8a300;
    }

    .bm-amenity-icons {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: #475569;
    }

    .bm-amenity-icons svg {
        width: 13px;
        height: 13px;
    }

    .bm-card-actions {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 76px;
        gap: 12px;
        margin-top: 12px;
    }

    .bm-details-btn,
    .bm-save-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 31px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 12px;
        font-weight: 800;
    }

    .bm-details-btn {
        border: 1px solid #7a5cff;
        color: #5b36f5;
        background: #fff;
    }

    .bm-save-btn {
        border: 1px solid #dce3ed;
        color: #111827;
        background: #fff;
        gap: 6px;
        cursor: pointer;
    }

    .bm-save-btn.is-saved {
        color: #5b36f5;
        border-color: #b8a8ff;
        background: #fbfaff;
    }

    .bm-pagination-row {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        align-items: center;
        gap: 16px;
        margin: 10px 0 0;
    }

    .bm-pagination {
        grid-column: 2;
        display: inline-flex;
        align-items: center;
        gap: 12px;
    }

    .bm-page-btn {
        min-width: 32px;
        height: 32px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        background: #fff;
        color: #334155;
        font-size: 13px;
        font-weight: 800;
    }

    .bm-page-btn.is-active {
        border-color: #ff5a3c;
        color: #ff4b2b;
        background: #fff7f4;
    }

    .bm-page-btn.is-plain {
        border-color: transparent;
        background: transparent;
        color: #64748b;
    }

    .bm-page-size {
        justify-self: end;
        position: relative;
    }

    .bm-page-size select {
        height: 34px;
        min-width: 108px;
        appearance: none;
        border: 1px solid #e2e8f0;
        border-radius: 7px;
        background: #fff;
        color: #334155;
        font-size: 12px;
        font-weight: 800;
        padding: 0 32px 0 12px;
        outline: none;
    }

    .bm-page-size svg {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        pointer-events: none;
    }

    .bm-sidebar {
        display: grid;
        gap: 10px;
    }

    .bm-side-panel {
        border: 1px solid #e7ebf2;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.045);
        padding: 18px;
    }

    .bm-panel-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
    }

    .bm-panel-title h2 {
        margin: 0;
        color: #111827;
        font-size: 15px;
        font-weight: 900;
    }

    .bm-panel-title button {
        border: 0;
        background: transparent;
        color: #ff4b2b;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
    }

    .bm-map {
        position: relative;
        height: 206px;
        overflow: hidden;
        border-radius: 8px;
        background:
            linear-gradient(26deg, transparent 45%, rgba(134, 197, 255, 0.5) 46%, rgba(134, 197, 255, 0.5) 49%, transparent 50%),
            linear-gradient(146deg, transparent 42%, rgba(255, 255, 255, 0.95) 43%, rgba(255, 255, 255, 0.95) 47%, transparent 48%),
            linear-gradient(85deg, transparent 48%, rgba(255, 255, 255, 0.9) 49%, rgba(255, 255, 255, 0.9) 51%, transparent 52%),
            linear-gradient(0deg, rgba(124, 198, 255, 0.28), rgba(246, 248, 252, 0.88));
    }

    .bm-map::before,
    .bm-map::after {
        content: "";
        position: absolute;
        inset: 0;
        background:
            repeating-linear-gradient(18deg, transparent 0 28px, rgba(196, 203, 213, 0.38) 29px 31px, transparent 32px 60px),
            repeating-linear-gradient(107deg, transparent 0 34px, rgba(196, 203, 213, 0.3) 35px 37px, transparent 38px 72px);
        opacity: 0.55;
    }

    .bm-map::after {
        background:
            radial-gradient(circle at 12% 30%, rgba(86, 202, 115, 0.26) 0 20px, transparent 21px),
            radial-gradient(circle at 77% 12%, rgba(86, 202, 115, 0.22) 0 22px, transparent 23px),
            radial-gradient(circle at 91% 52%, rgba(86, 202, 115, 0.18) 0 18px, transparent 19px);
        opacity: 1;
    }

    .bm-map-label {
        position: absolute;
        z-index: 2;
        color: #334155;
        font-size: 13px;
        font-weight: 900;
        letter-spacing: 0.08em;
        left: 37%;
        top: 48%;
    }

    .bm-map-place {
        position: absolute;
        z-index: 2;
        color: rgba(71, 85, 105, 0.72);
        font-size: 11px;
        font-weight: 700;
    }

    .bm-pin {
        position: absolute;
        z-index: 3;
        width: 22px;
        height: 22px;
        border-radius: 50% 50% 50% 0;
        background: #6c3df4;
        transform: rotate(-45deg);
        box-shadow: 0 7px 14px rgba(108, 61, 244, 0.24);
    }

    .bm-pin::after {
        content: "";
        position: absolute;
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #fff;
        left: 7px;
        top: 7px;
    }

    .bm-map-link {
        position: absolute;
        z-index: 4;
        left: 12px;
        bottom: 12px;
        color: #5b36f5;
        background: rgba(255, 255, 255, 0.92);
        border-radius: 6px;
        padding: 8px 12px;
        text-decoration: none;
        font-size: 12px;
        font-weight: 900;
    }

    .bm-map-expand {
        position: absolute;
        z-index: 4;
        right: 11px;
        bottom: 10px;
        display: inline-flex;
        width: 30px;
        height: 30px;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 7px;
        background: rgba(255, 255, 255, 0.92);
        color: #475569;
    }

    .bm-summary-list,
    .bm-amenity-list {
        display: grid;
        gap: 15px;
    }

    .bm-summary-row,
    .bm-amenity-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        color: #475569;
        font-size: 13px;
    }

    .bm-summary-label,
    .bm-amenity-name {
        display: inline-flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
        font-weight: 800;
        color: #334155;
    }

    .bm-summary-value {
        color: #64748b;
        font-weight: 700;
        text-align: right;
    }

    .bm-edit-search {
        width: 100%;
        height: 34px;
        margin-top: 20px;
        border: 1px solid #a38fff;
        border-radius: 7px;
        background: #fff;
        color: #5b36f5;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
    }

    .bm-count-pill {
        min-width: 34px;
        height: 26px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e7ebf2;
        border-radius: 999px;
        background: #fbfcfe;
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
    }

    .bm-view-amenities {
        display: inline-flex;
        margin-top: 20px;
        color: #5b36f5;
        text-decoration: none;
        font-size: 13px;
        font-weight: 900;
    }

    .bm-safety {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        min-height: 58px;
        margin-top: 26px;
        padding: 10px 24px;
        border: 1px solid #e7ebf2;
        border-radius: 9px;
        background: #fff;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.035);
    }

    .bm-safety-main {
        display: flex;
        align-items: center;
        gap: 16px;
        min-width: 0;
    }

    .bm-shield {
        display: inline-flex;
        width: 42px;
        height: 42px;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #fff2ed;
        color: #ff4b2b;
    }

    .bm-safety strong {
        display: block;
        color: #111827;
        font-size: 13px;
        font-weight: 900;
    }

    .bm-safety p {
        margin: 0;
    }

    .bm-safety span {
        color: #64748b;
        font-size: 12px;
    }

    .bm-safety-cta {
        display: inline-flex;
        align-items: center;
        gap: 24px;
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
        white-space: nowrap;
    }

    .bm-safety-cta a {
        color: #ff4b2b;
        text-decoration: none;
        font-weight: 900;
    }

    .bm-safety-cta span {
        color: #64748b;
        font-size: 13px;
    }

    @media (max-width: 1500px) {
        .bm-card-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .bm-filter-row {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

    }

    @media (max-width: 1200px) {
        .bm-layout {
            grid-template-columns: 1fr;
        }

        .bm-sidebar {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 900px) {
        .bm-card-grid,
        .bm-sidebar,
        .bm-filter-row {
            grid-template-columns: 1fr;
        }

        .bm-search-card {
            padding: 16px;
        }

        .bm-results-head,
        .bm-safety {
            align-items: stretch;
            flex-direction: column;
        }

        .bm-pagination-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        .bm-page-size {
            justify-self: auto;
        }

        .bm-safety-cta {
            justify-content: space-between;
        }
    }
</style>

<div class="bm-finder" x-data="boardingHouseFinder()">
    <nav class="bm-crumbs" aria-label="Breadcrumb">
        <a href="{{ route('user.dashboard') }}">Dashboard</a>
        <svg class="bm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/>
        </svg>
        <span>Find Boarding Houses</span>
    </nav>

    <header class="bm-page-heading">
        <h1>Find Boarding Houses</h1>
        <p>Discover and compare boarding houses that fit your preferences and budget.</p>
    </header>

    <div class="bm-layout">
        <main>
            <section class="bm-search-card" aria-label="Search filters">
                <div class="bm-search-box">
                    <svg class="bm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        {!! $iconPaths['search'] !!}
                    </svg>
                    <input x-model="search" type="search" placeholder="Search by boarding house name, location, or keyword...">
                </div>

                <div class="bm-filter-row">
                    <label class="bm-filter-control">
                        <span>Budget</span>
                        <select>
                            <option>&#8369;3,000 - &#8369;6,000</option>
                            <option>Under &#8369;3,000</option>
                            <option>&#8369;6,000 - &#8369;9,000</option>
                            <option>Above &#8369;9,000</option>
                        </select>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">{!! $iconPaths['chevron'] !!}</svg>
                    </label>

                    <label class="bm-filter-control">
                        <span>Location</span>
                        <select>
                            <option>Quezon City</option>
                            <option>Manila</option>
                            <option>Makati</option>
                            <option>Pasig</option>
                        </select>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">{!! $iconPaths['chevron'] !!}</svg>
                    </label>

                    <label class="bm-filter-control">
                        <span>Room Type</span>
                        <select>
                            <option>Private Room</option>
                            <option>Shared Room</option>
                            <option>Studio</option>
                            <option>Dormitory</option>
                        </select>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">{!! $iconPaths['chevron'] !!}</svg>
                    </label>

                    <label class="bm-filter-control">
                        <span>Amenities</span>
                        <select>
                            <option>Select amenities</option>
                            <option>Wi-Fi, Kitchen, Laundry</option>
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

            <div class="bm-results-head">
                <p>42 boarding houses found</p>
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

            <div class="bm-card-grid">
                @foreach($listings as $listing)
                    <article class="bm-listing-card">
                        <div class="bm-card-media">
                            <img src="{{ $listing['image'] }}" alt="{{ $listing['name'] }}" loading="lazy" onerror="this.style.display='none'">
                            <span class="bm-photo-count">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPaths['camera'] !!}</svg>
                                {{ $listing['photos'] }}
                            </span>
                            <button class="bm-heart" type="button" :class="{ 'is-saved': isSaved('{{ $listing['name'] }}') }" @click="toggleSave('{{ $listing['name'] }}')" aria-label="Save {{ $listing['name'] }}">
                                <svg class="bm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPaths['heart'] !!}</svg>
                            </button>
                        </div>

                        <div class="bm-card-body">
                            <h2 class="bm-card-title">{{ $listing['name'] }}</h2>
                            <div class="bm-price-row"><strong>&#8369;{{ $listing['price'] }}</strong> / month</div>
                            <div class="bm-location">{{ $listing['location'] }}</div>
                            <span class="bm-room-chip">{{ $listing['room'] }}</span>

                            <div class="bm-card-meta">
                                <span class="bm-rating">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2">{!! $iconPaths['star'] !!}</svg>
                                    {{ $listing['rating'] }} ({{ $listing['reviews'] }} reviews)
                                </span>
                                <span class="bm-amenity-icons" aria-label="Amenities">
                                    @foreach($listing['icons'] as $icon)
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPaths[$icon] !!}</svg>
                                    @endforeach
                                </span>
                            </div>

                            <div class="bm-card-actions">
                                <a class="bm-details-btn" href="{{ $detailUrl }}">View Details</a>
                                <button class="bm-save-btn" type="button" :class="{ 'is-saved': isSaved('{{ $listing['name'] }}') }" @click="toggleSave('{{ $listing['name'] }}')">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPaths['bookmark'] !!}</svg>
                                    Save
                                </button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="bm-pagination-row">
                <div class="bm-pagination" aria-label="Pagination">
                    <button class="bm-page-btn is-plain" type="button" aria-label="Previous page">&#8249;</button>
                    <button class="bm-page-btn is-active" type="button">1</button>
                    <button class="bm-page-btn" type="button">2</button>
                    <button class="bm-page-btn" type="button">3</button>
                    <span class="bm-page-btn is-plain">...</span>
                    <button class="bm-page-btn" type="button">6</button>
                    <button class="bm-page-btn is-plain" type="button" aria-label="Next page">&#8250;</button>
                </div>

                <label class="bm-page-size">
                    <select aria-label="Results per page">
                        <option>8 per page</option>
                        <option>12 per page</option>
                        <option>24 per page</option>
                    </select>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">{!! $iconPaths['chevron'] !!}</svg>
                </label>
            </div>
        </main>

        <aside class="bm-sidebar" aria-label="Search sidebar">
            <section class="bm-side-panel">
                <div class="bm-panel-title">
                    <h2>Map Preview</h2>
                </div>
                <div class="bm-map">
                    <span class="bm-map-place" style="left: 3%; top: 18%;">Baesa</span>
                    <span class="bm-map-place" style="left: 55%; top: 17%;">North</span>
                    <span class="bm-map-place" style="left: 74%; top: 61%;">Project 8</span>
                    <span class="bm-map-place" style="left: 86%; top: 72%;">Cubao</span>
                    <span class="bm-map-label">QUEZON CITY</span>
                    <span class="bm-pin" style="left: 23%; top: 39%;"></span>
                    <span class="bm-pin" style="left: 39%; top: 18%;"></span>
                    <span class="bm-pin" style="left: 63%; top: 19%;"></span>
                    <span class="bm-pin" style="left: 45%; top: 60%;"></span>
                    <span class="bm-pin" style="left: 86%; top: 56%;"></span>
                    <a class="bm-map-link" href="{{ $mapUrl }}">View larger map</a>
                    <a class="bm-map-expand" href="{{ $mapUrl }}" aria-label="Expand map">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPaths['expand'] !!}</svg>
                    </a>
                </div>
            </section>

            <section class="bm-side-panel">
                <div class="bm-panel-title">
                    <h2>Your Search Summary</h2>
                    <button type="button" @click="clearAll()">Clear All</button>
                </div>
                <div class="bm-summary-list">
                    <div class="bm-summary-row">
                        <span class="bm-summary-label"><svg class="bm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPaths['budget'] !!}</svg>Budget</span>
                        <span class="bm-summary-value">&#8369;3,000 - &#8369;6,000</span>
                    </div>
                    <div class="bm-summary-row">
                        <span class="bm-summary-label"><svg class="bm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPaths['location'] !!}</svg>Location</span>
                        <span class="bm-summary-value">Quezon City</span>
                    </div>
                    <div class="bm-summary-row">
                        <span class="bm-summary-label"><svg class="bm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPaths['home'] !!}</svg>Room Type</span>
                        <span class="bm-summary-value">Private Room</span>
                    </div>
                    <div class="bm-summary-row">
                        <span class="bm-summary-label"><svg class="bm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPaths['spark'] !!}</svg>Amenities</span>
                        <span class="bm-summary-value">Wi-Fi, Kitchen, Laundry</span>
                    </div>
                    <div class="bm-summary-row">
                        <span class="bm-summary-label"><svg class="bm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPaths['sort'] !!}</svg>Sort By</span>
                        <span class="bm-summary-value">Recommended</span>
                    </div>
                </div>
                <button class="bm-edit-search" type="button">Edit Search</button>
            </section>

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
                <a class="bm-view-amenities" href="#">View all amenities</a>
            </section>
        </aside>
    </div>

    <section class="bm-safety">
        <div class="bm-safety-main">
            <span class="bm-shield">
                <svg class="bm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $iconPaths['shield'] !!}</svg>
            </span>
            <p>
                <strong>Your safety is our priority.</strong>
                <span>All boarding houses are verified and regularly inspected for your peace of mind.</span>
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
            toggleSave(name) {
                if (this.saved.includes(name)) {
                    this.saved = this.saved.filter((item) => item !== name);
                    return;
                }

                this.saved.push(name);
            },
            isSaved(name) {
                return this.saved.includes(name);
            },
            clearAll() {
                this.search = '';
            },
        };
    }
</script>
</x-user.shell>
</x-layouts.dashboard>
