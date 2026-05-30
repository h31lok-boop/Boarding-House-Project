<x-layouts.dashboard>
<x-user.shell>
@php
    $amenitiesList    = $amenities ?? collect();
    $citiesList       = $cities    ?? collect();
    $initialCount     = isset($houses) ? $houses->total() : 0;
    $initialHousesArr = isset($initialHouses) ? $initialHouses->toArray() : [];

    $priceRanges = [
        ['label' => 'Under ₱3,000',      'min' => '',     'max' => '3000'],
        ['label' => '₱3,000 – ₱5,000',   'min' => '3000', 'max' => '5000'],
        ['label' => '₱5,000 – ₱8,000',   'min' => '5000', 'max' => '8000'],
        ['label' => '₱8,000 – ₱12,000',  'min' => '8000', 'max' => '12000'],
        ['label' => 'Above ₱12,000',      'min' => '12000','max' => ''],
    ];

    $citiesForJs = $citiesList->map(fn($c) => ['id' => (string)$c->id, 'name' => $c->city_name])->values()->toArray();
    $amenitiesForJs = $amenitiesList->map(fn($a) => ['id' => (string)$a->id, 'name' => $a->name])->values()->toArray();

    $requestAmenities = array_map('strval', (array) request('amenities', []));
@endphp

<style>
.bm-pill {
    display:inline-flex; align-items:center; gap:6px;
    padding:8px 14px; border-radius:999px; font-size:13px; font-weight:600;
    border:1.5px solid var(--border); background:var(--surface); color:var(--text);
    cursor:pointer; white-space:nowrap; user-select:none;
    transition:border-color .15s, background .15s, color .15s;
}
.bm-pill:hover   { border-color:var(--brand-500); color:var(--brand-600); }
.bm-pill.active  { border-color:var(--brand-500); background:rgba(255,126,95,.08); color:var(--brand-600); }
.bm-pill svg     { width:14px; height:14px; flex-shrink:0; }
.bm-pill .caret  { width:11px; height:11px; flex-shrink:0; transition:transform .15s; }
.bm-pill.open .caret { transform:rotate(180deg); }

.bm-drop {
    position:absolute; top:calc(100% + 8px); left:0; z-index:200;
    background:var(--surface); border:1px solid var(--border);
    border-radius:16px; padding:6px; min-width:220px;
    box-shadow:0 16px 48px rgba(26,18,15,.13);
}
.bm-drop.right { left:auto; right:0; }

.bm-opt {
    display:flex; align-items:center; gap:9px;
    padding:9px 11px; border-radius:10px; font-size:13px;
    cursor:pointer; transition:background .1s;
}
.bm-opt:hover     { background:var(--surface-2); }
.bm-opt.selected  { background:rgba(255,126,95,.1); color:var(--brand-600); font-weight:600; }

.chk-box {
    width:16px; height:16px; border-radius:5px;
    border:2px solid #d1d5db; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    transition:background .15s, border-color .15s;
}
.chk-box.checked { background:var(--brand-500); border-color:var(--brand-500); }
.chk-box svg { width:10px; height:10px; color:#fff; }

.match-badge {
    display:inline-flex; align-items:center; gap:5px;
    border-radius:999px; padding:4px 10px;
    font-size:11.5px; font-weight:700;
}
.mb-best  { background:#d1fae5; color:#065f46; }
.mb-great { background:#dcfce7; color:#166534; }
.mb-good  { background:#fff7ed; color:#c2410c; }
.mb-fair  { background:#f3f4f6; color:#4b5563; }

.hcard { transition:box-shadow .2s, transform .2s; }
.hcard:hover { box-shadow:0 12px 32px rgba(26,18,15,.1); transform:translateY(-1px); }

@keyframes sk { 0%,100%{opacity:1} 50%{opacity:.45} }
.sk { background:var(--surface-2); border-radius:8px; animation:sk 1.5s ease-in-out infinite; }
</style>

<div
    x-data="browseApp()"
    x-init="init()"
    @keydown.escape.window="cityOpen=false;priceOpen=false;rtOpen=false;amenityOpen=false;sortOpen=false"
    class="space-y-6">

    {{-- ── Breadcrumb ── --}}
    <nav class="flex items-center gap-1.5 text-xs text-gray-400">
        <a href="{{ route('user.dashboard') }}" class="hover:text-gray-600 transition-colors">Home</a>
        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="font-medium text-gray-600">Find Boarding Houses</span>
    </nav>

    {{-- ── Header ── --}}
    <div class="ui-card p-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest" style="color:var(--brand-600)">Explore</p>
                <h1 class="mt-1 text-2xl font-bold">Find Boarding Houses</h1>
                <p class="mt-0.5 text-sm ui-muted">
                    <span x-text="total" class="font-semibold text-gray-800"></span>
                    <span x-text="total===1?' result':' results'"></span>
                    <span x-show="hasFilters" style="color:var(--brand-600)" class="font-medium"> matching your filters</span>
                </p>
            </div>
            <button x-show="hasFilters" @click="reset()"
                    class="flex items-center gap-1.5 text-sm font-semibold text-rose-500 border border-rose-200 rounded-lg px-4 py-2 hover:bg-rose-50 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                Clear Filters
            </button>
        </div>
    </div>

    {{-- ── Filter Bar ── --}}
    <div class="ui-card p-4 space-y-3">

        {{-- Search --}}
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input x-model.debounce.450ms="q" type="text"
                   placeholder="Search by name, location or keyword…"
                   class="ui-input pl-10 pr-9 py-2.5 text-sm w-full">
            <button x-show="q" @click="q=''" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Filter pills --}}
        <div class="flex flex-wrap gap-2 items-center">

            {{-- Location --}}
            <div class="relative">
                <button @click.stop="cityOpen=!cityOpen; priceOpen=false; rtOpen=false; amenityOpen=false; sortOpen=false"
                        :class="{ active: city_id, open: cityOpen }" class="bm-pill">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span x-text="cityLabel()"></span>
                    <svg class="caret" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="cityOpen" @click.outside="cityOpen=false" x-cloak class="bm-drop" style="min-width:200px">
                    <div @click="city_id=''; cityOpen=false" :class="{'selected':!city_id}" class="bm-opt">All Locations</div>
                    @foreach($citiesList as $city)
                    <div @click="city_id='{{ $city->id }}'; cityOpen=false"
                         :class="{'selected':city_id==='{{ $city->id }}'}" class="bm-opt">
                        {{ $city->city_name }}
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Price --}}
            <div class="relative">
                <button @click.stop="priceOpen=!priceOpen; cityOpen=false; rtOpen=false; amenityOpen=false; sortOpen=false"
                        :class="{ active: min_price||max_price, open: priceOpen }" class="bm-pill">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-text="(min_price||max_price) ? (priceLabel||'Custom') : 'Price Range'"></span>
                    {{-- Use <span> not <button> — nested buttons are invalid HTML and break layout --}}
                    <span x-show="min_price||max_price"
                          @click.stop="clearPrice()"
                          class="flex items-center text-gray-400 hover:text-rose-400 cursor-pointer leading-none">
                        <svg style="width:11px;height:11px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </span>
                    <svg x-show="!min_price && !max_price" class="caret" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="priceOpen" @click.outside="priceOpen=false" x-cloak class="bm-drop" style="min-width:240px">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 px-3 py-1.5">Quick Select</p>
                    @foreach($priceRanges as $r)
                    <div @click="setPriceRange('{{ $r['min'] }}','{{ $r['max'] }}','{{ $r['label'] }}')"
                         :class="{'selected':min_price==='{{ $r['min'] }}'&&max_price==='{{ $r['max'] }}'}" class="bm-opt">
                        {{ $r['label'] }}
                    </div>
                    @endforeach
                    <div class="border-t border-gray-100 mt-1 pt-2 px-3 pb-2 space-y-1.5">
                        <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wide">Custom (₱)</p>
                        <div class="flex gap-2 items-center">
                            <input x-model.number.lazy="min_price" type="number" placeholder="Min"
                                   class="ui-input text-sm py-1.5 px-2 w-24">
                            <span class="text-gray-400 text-sm">–</span>
                            <input x-model.number.lazy="max_price" type="number" placeholder="Max"
                                   class="ui-input text-sm py-1.5 px-2 w-24">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Room Type --}}
            <div class="relative">
                <button @click.stop="rtOpen=!rtOpen; cityOpen=false; priceOpen=false; amenityOpen=false; sortOpen=false"
                        :class="{ active: room_type, open: rtOpen }" class="bm-pill">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span x-text="roomTypeLabel()"></span>
                    <svg class="caret" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="rtOpen" @click.outside="rtOpen=false" x-cloak class="bm-drop">
                    @foreach(['' => 'Any Type','single'=>'Single Room','shared'=>'Shared Room','studio'=>'Studio','dormitory'=>'Dormitory'] as $v=>$l)
                    <div @click="room_type='{{ $v }}'; rtOpen=false" :class="{'selected':room_type==='{{ $v }}'}" class="bm-opt">{{ $l }}</div>
                    @endforeach
                </div>
            </div>

            {{-- Amenities ── custom checkbox dropdown --}}
            <div class="relative">
                <button @click.stop="amenityOpen=!amenityOpen; cityOpen=false; priceOpen=false; rtOpen=false; sortOpen=false"
                        :class="{ active: amenities.length>0, open: amenityOpen }" class="bm-pill">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    <span x-text="amenities.length ? 'Amenities ('+amenities.length+')' : 'Amenities'"></span>
                    <svg class="caret" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div x-show="amenityOpen" @click.outside="amenityOpen=false" x-cloak
                     class="bm-drop" style="max-height:280px;overflow-y:auto;min-width:220px">
                    @if($amenitiesList->isEmpty())
                        <p class="text-xs text-gray-400 px-3 py-3">No amenities available.</p>
                    @else
                        @foreach($amenitiesList->unique('name')->sortBy('name') as $amenity)
                        <label class="bm-opt cursor-pointer"
                               :class="{'selected': amenities.includes('{{ $amenity->id }}')}"
                               @click.prevent="toggleAmenity('{{ $amenity->id }}')">
                            <span :class="{'checked': amenities.includes('{{ $amenity->id }}')}" class="chk-box">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                            <span>{{ $amenity->name }}</span>
                        </label>
                        @endforeach
                    @endif
                    <template x-if="amenities.length > 0">
                        <div class="border-t border-gray-100 px-3 pt-1.5 pb-1">
                            <button @click="amenities=[]" class="text-xs text-rose-500 font-medium hover:underline">Clear all</button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- More Filters --}}
            <button @click="showMore=!showMore" :class="{ active: showMore }" class="bm-pill">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                More Filters
            </button>
        </div>

        {{-- More Filters panel --}}
        <div x-show="showMore" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="border-t border-dashed border-gray-200 pt-4">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">

                {{-- Available Only --}}
                <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-colors"
                       :class="available_only ? 'border-orange-300 bg-orange-50' : 'border-gray-100 hover:border-orange-200'">
                    <div class="relative flex-shrink-0">
                        <input type="checkbox" x-model="available_only" class="sr-only">
                        <div class="h-5 w-9 rounded-full transition-colors duration-200"
                             :class="available_only ? 'bg-orange-500' : 'bg-gray-200'"></div>
                        <div class="absolute top-0.5 left-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform duration-200"
                             :class="available_only ? 'translate-x-4' : ''"></div>
                    </div>
                    <div>
                        <p class="text-sm font-semibold" :class="available_only ? 'text-orange-700' : 'text-gray-700'">Available Only</p>
                        <p class="text-xs text-gray-400">Show houses with open slots</p>
                    </div>
                </label>

                {{-- Near Me --}}
                <div class="p-3 rounded-xl border transition-colors"
                     :class="near_me ? 'border-orange-300 bg-orange-50' : 'border-gray-100'">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold" :class="near_me ? 'text-orange-700' : 'text-gray-700'">Near My Location</p>
                            <p class="text-xs text-gray-400" x-text="near_me ? 'Sorting by distance' : 'Use GPS sorting'"></p>
                        </div>
                        <button @click="near_me ? (near_me=false;lat='';lng='') : getNearMe()"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors"
                                :class="near_me ? 'bg-rose-100 text-rose-600 hover:bg-rose-200' : 'bg-orange-100 text-orange-700 hover:bg-orange-200'">
                            <svg x-show="geoLoading" class="h-3.5 w-3.5 animate-spin inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            <span x-text="near_me ? 'Turn Off' : (geoLoading ? 'Finding…' : 'Enable')"></span>
                        </button>
                    </div>
                </div>

                {{-- Min Rating --}}
                <div class="p-3 rounded-xl border border-gray-100">
                    <p class="text-sm font-semibold text-gray-700 mb-2">Minimum Rating</p>
                    <div class="flex gap-1">
                        @foreach([''=>'Any','3'=>'3+','3.5'=>'3.5+','4'=>'4+','4.5'=>'4.5+'] as $v=>$l)
                        <button @click="min_rating = min_rating==='{{ $v }}' ? '' : '{{ $v }}'"
                                class="flex-1 py-1 rounded-lg text-xs font-semibold border transition-colors"
                                :class="min_rating==='{{ $v }}' ? 'border-orange-400 bg-orange-100 text-orange-700' : 'border-gray-200 text-gray-600 hover:border-orange-200'">
                            {{ $l }}
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Results bar + Sort ── --}}
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2 text-sm">
            <svg x-show="loading" class="h-4 w-4 animate-spin text-orange-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            <span x-show="loading" class="text-gray-400">Updating results…</span>
            <span x-show="!loading" class="ui-muted">
                <strong class="text-gray-800" x-text="total"></strong>
                <span x-text="total===1?' result':' results'"></span>
            </span>
        </div>

        <div class="relative">
            <button @click.stop="sortOpen=!sortOpen; cityOpen=false; priceOpen=false; rtOpen=false; amenityOpen=false"
                    :class="{ open: sortOpen }" class="bm-pill">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4"/></svg>
                <span x-text="sortLabel()"></span>
                <svg class="caret" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="sortOpen" @click.outside="sortOpen=false" x-cloak class="bm-drop right">
                @foreach(['newest'=>'Newest First','best_match'=>'Best Match','price_asc'=>'Lowest Price','price_desc'=>'Highest Price','rating'=>'Highest Rated','available'=>'Most Available'] as $v=>$l)
                <div @click="sort='{{ $v }}'; sortOpen=false" :class="{'selected':sort==='{{ $v }}'}" class="bm-opt">{{ $l }}</div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Results ── --}}
    <section class="space-y-4">

        {{-- Skeletons while loading empty state --}}
        <template x-if="loading && houses.length===0">
            <div class="space-y-4">
                <template x-for="i in 3" :key="i">
                    <div class="ui-card p-4">
                        <div class="grid gap-4 lg:grid-cols-[260px_1fr_160px]">
                            <div class="sk h-40 w-full rounded-xl"></div>
                            <div class="space-y-3 py-2">
                                <div class="sk h-5 w-3/4 rounded"></div>
                                <div class="sk h-4 w-1/2 rounded"></div>
                                <div class="flex gap-2 mt-3"><div class="sk h-6 w-16 rounded-full"></div><div class="sk h-6 w-16 rounded-full"></div></div>
                            </div>
                            <div class="space-y-3 py-2"><div class="sk h-7 w-24 rounded-full"></div><div class="sk h-8 w-28 rounded"></div><div class="sk h-10 w-full rounded-xl"></div></div>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        {{-- Empty state --}}
        <template x-if="!loading && houses.length===0">
            <div class="ui-card p-12 text-center">
                <div class="h-16 w-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">No boarding houses found</h3>
                <p class="text-sm ui-muted mb-5">Try adjusting your filters or clearing them to see more results.</p>
                <button @click="reset()" class="btn-primary px-5 py-2 text-sm">Clear All Filters</button>
            </div>
        </template>

        {{-- House cards --}}
        <template x-for="h in houses" :key="h.id">
            <article class="ui-card p-4 hcard">
                <div class="grid gap-4 lg:grid-cols-[260px_1fr_auto] lg:items-start">

                    {{-- Image --}}
                    <div class="relative shrink-0">
                        <div class="h-44 lg:h-40 w-full rounded-xl overflow-hidden bg-gradient-to-br from-orange-50 to-amber-50 flex items-center justify-center">
                            <template x-if="h.image_url">
                                <img :src="h.image_url" :alt="h.name" class="h-full w-full object-cover"
                                     x-on:error="$el.style.display='none'">
                            </template>
                            <template x-if="!h.image_url">
                                <svg class="h-12 w-12 text-orange-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            </template>
                        </div>
                        {{-- Availability badge --}}
                        <template x-if="h.available_rooms > 0">
                            <span class="absolute bottom-2 left-2 bg-white/95 text-gray-700 text-xs font-semibold px-2.5 py-1 rounded-full shadow-sm">
                                <span x-text="h.available_rooms"></span> room<span x-text="h.available_rooms>1?'s':''"></span> open
                            </span>
                        </template>
                        <template x-if="h.available_rooms === 0">
                            <span class="absolute bottom-2 left-2 bg-rose-50 text-rose-600 text-xs font-semibold px-2.5 py-1 rounded-full border border-rose-200">Full</span>
                        </template>
                    </div>

                    {{-- Details --}}
                    <div class="min-w-0 py-1">
                        <h2 class="text-base font-bold text-gray-900 leading-snug" x-text="h.name"></h2>

                        <div class="flex items-center gap-1.5 mt-1">
                            <svg class="h-3.5 w-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <p class="text-sm ui-muted truncate"
                               x-text="h.city_name ? (h.city_name + (h.barangay_name ? ', '+h.barangay_name : '')) : (h.address||'Location not set')"></p>
                        </div>

                        <div class="flex flex-wrap items-center gap-3 mt-2">
                            <template x-if="h.rating > 0">
                                <span class="flex items-center gap-1 text-xs">
                                    <svg class="h-3.5 w-3.5 text-amber-400" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    <strong class="text-gray-700" x-text="Number(h.rating).toFixed(1)"></strong>
                                    <span class="text-gray-400" x-text="'('+h.reviews_count+')'"></span>
                                </span>
                            </template>
                            <template x-if="h.distance_km !== null && h.distance_km !== undefined">
                                <span class="text-xs ui-muted flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                    <span x-text="h.distance_km < 1 ? (h.distance_km*1000).toFixed(0)+'m away' : Number(h.distance_km).toFixed(1)+'km away'"></span>
                                </span>
                            </template>
                        </div>

                        {{-- Amenity chips --}}
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            <template x-for="a in h.amenities.slice(0,6)" :key="a.id">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border transition-colors"
                                      :class="amenities.includes(String(a.id))
                                          ? 'border-orange-300 bg-orange-50 text-orange-700'
                                          : 'ui-border ui-muted'"
                                      x-text="a.name"></span>
                            </template>
                            <span x-show="h.amenities.length > 6" class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500"
                                  x-text="'+'+(h.amenities.length-6)+' more'"></span>
                            <span x-show="h.amenities.length === 0" class="text-xs ui-muted">No amenities listed</span>
                        </div>
                    </div>

                    {{-- Price + Match + CTA --}}
                    <div class="flex flex-row lg:flex-col items-center lg:items-end justify-between lg:justify-start gap-3 lg:gap-4 lg:min-w-[152px]">

                        <span class="match-badge" :class="badgeCls(h.match_score)">
                            <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <span x-text="h.match_score+'% '+h.match_label"></span>
                        </span>

                        <div class="text-right lg:text-right">
                            <p class="text-xl font-bold text-gray-900" x-text="h.price_label"></p>
                            <p class="text-xs ui-muted">/ month</p>
                        </div>

                        <a :href="h.url"
                           class="w-full rounded-xl border-2 text-center text-sm font-semibold px-4 py-2.5 transition-colors"
                           style="border-color:var(--brand-500); color:var(--brand-600)"
                           @mouseenter="$el.style.background='var(--brand-500)'; $el.style.color='#fff'"
                           @mouseleave="$el.style.background=''; $el.style.color='var(--brand-600)'">
                            View Details
                        </a>
                    </div>

                </div>
            </article>
        </template>
    </section>

    {{-- Server-side pagination (initial load only) --}}
    @if(isset($houses) && $houses->hasPages())
    <div class="ui-card px-5 py-4">{{ $houses->links() }}</div>
    @endif

    {{-- ── Bottom Banner ── --}}
    <div class="ui-card p-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 shrink-0 rounded-xl flex items-center justify-center" style="background:rgba(255,126,95,.1)">
                <svg class="h-5 w-5" style="color:var(--brand-600)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 22s-8-4-8-10V5l8-3 8 3v7c0 6-8 10-8 10Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-900">All listed boarding houses are verified by BoardMatch.</p>
                <p class="text-xs text-gray-400">Only approved and active listings appear in your search results.</p>
            </div>
        </div>
        <a href="{{ route('user.recommendations') }}" class="text-sm font-semibold hover:underline shrink-0" style="color:var(--brand-600)">View AI Matches →</a>
    </div>

</div>

<script>
function browseApp() {
    return {
        // ── Filter state ──
        q:              '{{ addslashes(request("q","")) }}',
        city_id:        '{{ request("city_id","") }}',
        min_price:      '{{ request("min_price","") }}',
        max_price:      '{{ request("max_price","") }}',
        amenities:      {!! json_encode(array_map('strval', (array) request('amenities', []))) !!},
        room_type:      '{{ request("room_type","") }}',
        available_only: {{ request()->boolean('available_only') ? 'true' : 'false' }},
        near_me:        false,
        sort:           '{{ request("sort","newest") }}',
        min_rating:     '{{ request("min_rating","") }}',
        lat:            '',
        lng:            '',
        priceLabel:     '{{ request("min_price")||request("max_price") ? "Price set" : "" }}',

        // ── UI state ──
        houses:         {!! json_encode($initialHousesArr) !!},
        total:          {{ $initialCount }},
        loading:        false,
        showMore:       false,
        cityOpen:       false,
        priceOpen:      false,
        rtOpen:         false,
        amenityOpen:    false,
        sortOpen:       false,
        geoLoading:     false,
        _timer:         null,

        init() {
            this.$watch(
                () => JSON.stringify([
                    this.q, this.city_id, this.min_price, this.max_price,
                    this.amenities, this.room_type, this.available_only,
                    this.sort, this.min_rating, this.lat, this.lng
                ]),
                () => {
                    clearTimeout(this._timer);
                    this._timer = setTimeout(() => this.doFetch(), 550);
                }
            );
        },

        async doFetch() {
            this.loading = true;
            try {
                const p = new URLSearchParams();
                if (this.q)              p.set('q', this.q);
                if (this.city_id)        p.set('city_id', this.city_id);
                if (this.min_price)      p.set('min_price', this.min_price);
                if (this.max_price)      p.set('max_price', this.max_price);
                this.amenities.forEach(a => p.append('amenities[]', a));
                if (this.room_type)      p.set('room_type', this.room_type);
                if (this.available_only) p.set('available_only', '1');
                if (this.near_me && this.lat) { p.set('near_me','1'); p.set('lat',this.lat); p.set('lng',this.lng); }
                if (this.sort)           p.set('sort', this.sort);
                if (this.min_rating)     p.set('min_rating', this.min_rating);

                const r = await fetch('{{ route("user.browse") }}?' + p.toString(), {
                    headers: { 'X-Requested-With':'XMLHttpRequest', 'Accept':'application/json' }
                });
                const d = await r.json();
                this.houses = d.houses || [];
                this.total  = d.total  || 0;
            } catch(e) { console.error('Browse fetch error:', e); }
            finally    { this.loading = false; }
        },

        toggleAmenity(id) {
            const s = String(id);
            const i = this.amenities.indexOf(s);
            if (i === -1) this.amenities.push(s);
            else          this.amenities.splice(i, 1);
        },

        setPriceRange(min, max, label) {
            this.min_price  = min;
            this.max_price  = max;
            this.priceLabel = label;
            this.priceOpen  = false;
        },

        clearPrice() {
            this.min_price = '';
            this.max_price = '';
            this.priceLabel = '';
        },

        reset() {
            this.q = ''; this.city_id = ''; this.min_price = ''; this.max_price = '';
            this.amenities = []; this.room_type = ''; this.available_only = false;
            this.near_me = false; this.lat = ''; this.lng = '';
            this.sort = 'newest'; this.min_rating = '';
            this.priceLabel = ''; this.showMore = false;
        },

        getNearMe() {
            if (!navigator.geolocation) return;
            this.geoLoading = true;
            navigator.geolocation.getCurrentPosition(
                pos => { this.lat = pos.coords.latitude; this.lng = pos.coords.longitude; this.near_me = true; this.geoLoading = false; },
                ()  => { this.geoLoading = false; }
            );
        },

        get hasFilters() {
            return !!(this.q || this.city_id || this.min_price || this.max_price ||
                      this.amenities.length || this.room_type || this.available_only || this.min_rating);
        },

        cityLabel() {
            if (!this.city_id) return 'Location';
            const cities = {!! json_encode($citiesForJs) !!};
            const c = cities.find(x => x.id === String(this.city_id));
            return c ? c.name : 'Location';
        },

        roomTypeLabel() {
            const m = {'':'Room Type','single':'Single Room','shared':'Shared Room','studio':'Studio','dormitory':'Dormitory'};
            return m[this.room_type] || 'Room Type';
        },

        sortLabel() {
            const m = {'newest':'Newest','best_match':'Best Match','price_asc':'Lowest Price','price_desc':'Highest Price','rating':'Top Rated','available':'Most Available','nearest':'Nearest'};
            return m[this.sort] || 'Sort';
        },

        badgeCls(score) {
            if (score >= 90) return 'mb-best';
            if (score >= 80) return 'mb-great';
            if (score >= 70) return 'mb-good';
            return 'mb-fair';
        },
    };
}
</script>
</x-user.shell>
</x-layouts.dashboard>
