<x-layouts.dashboard>
<x-user.shell>
@php
    $options               = $fieldOptions ?? [];
    $matchProfilesAvailable = $matchProfilesAvailable ?? true;
    $selectedHobbies       = old('hobbies', $profile->hobbies ?? ['reading', 'coding']);
    $selectedAmenityIds    = collect(old('preferred_amenity_ids', $profile->preferred_amenity_ids ?? []))->map(fn($id) => (int)$id)->all();

    $fallbackAmenities = collect(['Wi-Fi','Study Table','Air Conditioning','Kitchen','Laundry','Parking','CCTV','Water Included','Electricity Included','Security Guard','Pet Friendly']);
    $visibleAmenities  = ($amenities ?? collect())->isNotEmpty()
        ? $amenities->values()
        : $fallbackAmenities->map(fn($name,$i) => (object)['id'=>$i+1,'name'=>$name,'disabled'=>true]);

    // Profile completion percentage
    $fields = ['gender_preference','sleep_schedule','study_habits','cleanliness_level','noise_tolerance','smoking_preference','drinking_preference','pets_preference','internet_usage','budget_min','budget_max'];
    $filled = collect($fields)->filter(fn($f) => !empty($profile->{$f}))->count();
    $completionPct = (int) round(($filled / count($fields)) * 100);
    $completionPct = max(15, $completionPct); // show at least 15 for new profiles

    // SVG circle math
    $radius = 54; $circ = round(2 * M_PI * $radius, 2);
    $dash   = round($circ * $completionPct / 100, 2);
    $gap    = round($circ - $dash, 2);

    $locationTags = ['Loyola Heights','Katipunan','UP Village'];

    $priceRanges = [
        ['₱20,000 – ₱50,000', '₱50,000 – ₱100,000', 'Below ₱20,000', 'Above ₱100,000'],
        ['₱5,000 – ₱10,000',  '₱3,000 – ₱5,000',   'Below ₱3,000',  'Above ₱10,000'],
        ['₱2,000 – ₱4,000',   '₱4,000 – ₱6,000',   '₱6,000 – ₱10,000','Above ₱10,000'],
    ];
@endphp

<style>
/* ── Sel buttons ── */
.sel-btn{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:999px;border:1.5px solid #e5e7eb;font-size:13px;font-weight:600;color:#6b7280;background:#fff;cursor:pointer;transition:all .15s;white-space:nowrap;}
.sel-btn:hover{border-color:#6366f1;color:#6366f1;}
.sel-btn.active{background:#6366f1;border-color:#6366f1;color:#fff;}
/* ── Selects ── */
.pref-select{width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:9px 12px;font-size:13px;color:#374151;background:#fff;outline:none;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 10px center;background-size:16px;padding-right:32px;transition:border-color .15s;}
.pref-select:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12);}
/* ── Labels ── */
.pref-label{display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:10px;}
/* ── Location tags ── */
.loc-tag{display:inline-flex;align-items:center;gap:5px;background:#f3f4f6;border:1px solid #e5e7eb;border-radius:999px;padding:4px 10px;font-size:12px;font-weight:600;color:#374151;}
.loc-tag button{color:#9ca3af;transition:color .1s;}
.loc-tag button:hover{color:#ef4444;}
/* ── Amenity chips ── */
.am-chip{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:999px;border:1.5px solid #e5e7eb;font-size:12px;font-weight:600;color:#6b7280;background:#fff;cursor:pointer;transition:all .15s;}
.am-chip:hover{border-color:#6366f1;color:#6366f1;}
.am-chip.active{background:#eef2ff;border-color:#6366f1;color:#6366f1;}
/* ── Range slider ── */
input[type=range]{-webkit-appearance:none;appearance:none;height:5px;border-radius:9px;background:linear-gradient(to right,#6366f1 var(--val,30%),#e5e7eb var(--val,30%));outline:none;cursor:pointer;width:100%;}
input[type=range]::-webkit-slider-thumb{-webkit-appearance:none;appearance:none;width:18px;height:18px;border-radius:50%;background:#6366f1;border:3px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.2);cursor:pointer;}
/* ── Section divider ── */
.form-section{padding:24px 28px;border-bottom:1px solid #f3f4f6;}
.form-section:last-child{border-bottom:0;}
/* ── Sidebar card ── */
.side-card{background:#fff;border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;}
</style>

<div
    x-data="{
        roomType:      '{{ old('room_type',      $profile->room_type      ?? 'shared') }}',
        studyHabits:   '{{ old('study_habits',   $profile->study_habits   ?? 'quiet_focus') }}',
        cleanliness:   {{ (int) old('cleanliness_level', $profile->cleanliness_level ?? 3) }},
        safetyPref:    'high',
        distanceKm:    1.0,
        noiseTolerance:{{ (int) old('noise_tolerance', $profile->noise_tolerance ?? 30) }},
        locationTags:  {{ \Illuminate\Support\Js::from($locationTags) }},
        locationSearch:'',
        amenityOpen:   false,
        selectedAmenities: ['Wi-Fi','Study Table','Aircon','Laundry','Parking','Kitchen'],
        addLocation(loc){ loc=loc.trim(); if(loc&&!this.locationTags.includes(loc)) this.locationTags.push(loc); this.locationSearch=''; },
        removeLocation(loc){ this.locationTags=this.locationTags.filter(l=>l!==loc); },
        toggleAmenity(name){ const i=this.selectedAmenities.indexOf(name); if(i===-1) this.selectedAmenities.push(name); else this.selectedAmenities.splice(i,1); },
        updateSlider(el){ el.style.setProperty('--val',((el.value-el.min)/(el.max-el.min)*100)+'%'); },
    }"
    class="space-y-5">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-1.5 text-xs text-gray-400">
        <a href="{{ route('user.dashboard') }}" class="hover:text-gray-600 transition-colors">Dashboard</a>
        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-500 font-medium">My Preferences</span>
    </nav>

    {{-- Title --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">My Preferences</h1>
        <p class="mt-1 text-sm text-gray-500">Tell us about your lifestyle and needs so we can recommend the best boarding houses for you.</p>
    </div>

    @if(!$matchProfilesAvailable)
    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
        <svg class="inline h-4 w-4 mr-1 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        Tenant match profiles are not available yet.
    </div>
    @endif


    {{-- Two-column layout --}}
    <form method="POST" action="{{ route('user.profile.update') }}" class="grid gap-5 xl:grid-cols-[1fr_280px]">
        @csrf @method('PUT')

        {{-- ════ LEFT: Main Form Card ════ --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">

            {{-- Card header --}}
            <div class="px-7 py-5 border-b border-gray-100">
                <p class="text-base font-bold text-gray-900">Lifestyle Information for AI Recommendation</p>
                <p class="mt-0.5 text-xs text-indigo-500 font-medium">The more accurate your preferences, the better our recommendations.</p>
            </div>

            <div class="px-7 py-6 space-y-8">

                {{-- Row 1: Income / Allowance / Budget --}}
                <div class="grid gap-5 sm:grid-cols-3">
                    <div>
                        <label class="pref-label">Family Monthly Income</label>
                        <select name="family_income" class="pref-select">
                            @foreach($priceRanges[0] as $opt)
                            <option @selected(old('family_income','₱50,000 – ₱100,000')===$opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="pref-label">Monthly Allowance</label>
                        <select name="monthly_allowance" class="pref-select">
                            @foreach($priceRanges[1] as $opt)
                            <option @selected(old('monthly_allowance','₱5,000 – ₱10,000')===$opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="pref-label">Preferred Rental Budget</label>
                        <select name="rental_budget" class="pref-select">
                            @foreach($priceRanges[2] as $opt)
                            <option @selected(old('rental_budget','₱2,000 – ₱4,000')===$opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Row 2: Location / Distance / Room Type --}}
                <div class="grid gap-5 sm:grid-cols-3">
                    {{-- Preferred Location --}}
                    <div>
                        <label class="pref-label">Preferred Location</label>
                        <div class="relative mb-2">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" x-model="locationSearch" placeholder="Search locations or areas"
                                   @keydown.enter.prevent="addLocation(locationSearch)"
                                   class="w-full rounded-xl border border-gray-200 bg-white pl-9 pr-3 py-2.5 text-sm placeholder-gray-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            <template x-for="tag in locationTags" :key="tag">
                                <span class="loc-tag">
                                    <span x-text="tag"></span>
                                    <button type="button" @click="removeLocation(tag)">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </span>
                            </template>
                        </div>
                        <input type="hidden" name="preferred_location" :value="locationTags.join(',')">
                    </div>

                    {{-- Distance from School --}}
                    <div>
                        <div class="flex items-center justify-between mb-2.5">
                            <label class="pref-label mb-0">Distance from School</label>
                            <span class="text-sm font-bold text-orange-500" x-text="distanceKm.toFixed(1)+' km'"></span>
                        </div>
                        <input type="range" name="preferred_distance" min="0" max="10" step="0.5"
                               x-model="distanceKm"
                               x-init="$el.style.setProperty('--val',(distanceKm/10*100)+'%')"
                               @input="distanceKm=parseFloat($event.target.value);updateSlider($event.target)"
                               style="--val:10%">
                        <div class="mt-2 flex justify-between text-[10px] text-gray-400 font-semibold">
                            <span>Any</span><span>1 km</span><span>2 km</span><span>5 km</span><span>10+ km</span>
                        </div>
                    </div>

                    {{-- Room Type --}}
                    <div>
                        <label class="pref-label">Room Type</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['any'=>'Any','private'=>'Private Room','shared'=>'Shared Room','bedspace'=>'Bed Space'] as $val=>$lbl)
                            <button type="button" @click="roomType='{{ $val }}'" class="sel-btn" :class="roomType==='{{ $val }}'?'active':''">{{ $lbl }}</button>
                            @endforeach
                        </div>
                        <input type="hidden" name="room_type" :value="roomType">
                    </div>
                </div>

                {{-- Row 3: Study Habits / Sleeping Schedule / Cleanliness --}}
                <div class="grid gap-5 sm:grid-cols-3">
                    {{-- Study Habits --}}
                    <div>
                        <label class="pref-label">Study Habits</label>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" @click="studyHabits='quiet_focus'" class="sel-btn" :class="studyHabits==='quiet_focus'?'active':''">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 9.75L19.5 12m0 0l2.25 2.25M19.5 12l2.25-2.25M19.5 12l-2.25 2.25m-10.5-6l4.72-4.72a.75.75 0 011.28.53v15.88a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.01 9.01 0 012.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75z"/></svg>
                                Quiet
                            </button>
                            <button type="button" @click="studyHabits='group_study'" class="sel-btn" :class="studyHabits==='group_study'?'active':''">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                                Moderate
                            </button>
                            <button type="button" @click="studyHabits='flexible'" class="sel-btn" :class="studyHabits==='flexible'?'active':''">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                                Flexible
                            </button>
                        </div>
                        <input type="hidden" name="study_habits" :value="studyHabits">
                    </div>

                    {{-- Sleeping Schedule --}}
                    <div>
                        <label class="pref-label">Sleeping Schedule</label>
                        <select name="sleep_schedule" class="pref-select">
                            @foreach(['11:00 PM - 7:00 AM'=>'early_bird','9:00 PM - 5:00 AM'=>'early_bird','10:00 PM - 6:00 AM'=>'balanced','12:00 AM - 8:00 AM'=>'night_owl','Flexible'=>'balanced'] as $label=>$val)
                            <option value="{{ $val }}" @selected(old('sleep_schedule',$profile->sleep_schedule??'balanced')===$val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Cleanliness Level --}}
                    <div>
                        <label class="pref-label">Cleanliness Level</label>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" @click="cleanliness=1" class="sel-btn" :class="cleanliness===1?'active':''">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                Basic
                            </button>
                            <button type="button" @click="cleanliness=3" class="sel-btn" :class="cleanliness===3?'active':''">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                                Moderate
                            </button>
                            <button type="button" @click="cleanliness=5" class="sel-btn" :class="cleanliness===5?'active':''">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                                Very Clean
                            </button>
                        </div>
                        <input type="hidden" name="cleanliness_level" :value="cleanliness">
                    </div>
                </div>

                {{-- Row 4: Noise / Safety / Amenities --}}
                <div class="grid gap-5 sm:grid-cols-3">
                    {{-- Noise Tolerance --}}
                    <div>
                        <label class="pref-label">Noise Tolerance</label>
                        <input type="range" name="noise_tolerance" min="0" max="100" step="10"
                               x-model="noiseTolerance"
                               x-init="$el.style.setProperty('--val',noiseTolerance+'%')"
                               @input="noiseTolerance=parseInt($event.target.value);updateSlider($event.target)">
                        <div class="mt-2 flex justify-between text-[10px] text-gray-400 font-semibold">
                            <span>Very Low</span><span>Low</span><span>Moderate</span><span>High</span><span>Very High</span>
                        </div>
                    </div>

                    {{-- Safety Preference --}}
                    <div>
                        <label class="pref-label">Safety Preference</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['standard'=>'Standard','high'=>'High','very_high'=>'Very High'] as $val=>$lbl)
                            <button type="button" @click="safetyPref='{{ $val }}'" class="sel-btn" :class="safetyPref==='{{ $val }}'?'active':''">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                                {{ $lbl }}
                            </button>
                            @endforeach
                        </div>
                        <input type="hidden" name="safety_preference" :value="safetyPref">
                    </div>

                    {{-- Amenities --}}
                    <div>
                        <label class="pref-label">Amenities</label>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($visibleAmenities->take(6) as $am)
                            <button type="button" @click="toggleAmenity('{{ $am->name }}')" class="am-chip"
                                    :class="selectedAmenities.includes('{{ $am->name }}')?'active':''">
                                {{ $am->name }}
                            </button>
                            @endforeach
                            @if($visibleAmenities->count() > 6)
                            <button type="button" @click="amenityOpen=!amenityOpen" class="am-chip">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            @endif
                        </div>
                        {{-- expanded amenities --}}
                        <div x-show="amenityOpen" x-cloak class="mt-2 flex flex-wrap gap-1.5">
                            @foreach($visibleAmenities->slice(6) as $am)
                            <button type="button" @click="toggleAmenity('{{ $am->name }}')" class="am-chip"
                                    :class="selectedAmenities.includes('{{ $am->name }}')?'active':''">
                                {{ $am->name }}
                            </button>
                            @endforeach
                        </div>
                        <template x-for="am in selectedAmenities" :key="am">
                            <input type="hidden" name="preferred_amenities[]" :value="am">
                        </template>
                    </div>
                </div>

                {{-- Row 5: Smoking / Curfew / Internet --}}
                <div class="grid gap-5 sm:grid-cols-3">
                    <div>
                        <label class="pref-label">Smoking Preference</label>
                        <select name="smoking_preference" class="pref-select">
                            <option value="non_smoker_only" @selected(old('smoking_preference',$profile->smoking_preference??'non_smoker_only')==='non_smoker_only')>No Smoking</option>
                            <option value="smoker_ok"       @selected(old('smoking_preference',$profile->smoking_preference??'')==='smoker_ok')>Smoking Allowed</option>
                            <option value="outdoor_only"    @selected(old('smoking_preference',$profile->smoking_preference??'')==='outdoor_only')>Designated Area Only</option>
                        </select>
                    </div>
                    <div>
                        <label class="pref-label">Curfew Preference</label>
                        <select name="curfew_preference" class="pref-select">
                            @foreach(['10:00 PM','9:00 PM','11:00 PM','12:00 AM','No Curfew'] as $c)
                            <option @selected(old('curfew_preference','10:00 PM')===$c)>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="pref-label">Internet Needs</label>
                        <select name="internet_usage" class="pref-select">
                            <option value="heavy"       @selected(old('internet_usage',$profile->internet_usage??'heavy')==='heavy')>High Speed (For Classes &amp; Streaming)</option>
                            <option value="light"       @selected(old('internet_usage',$profile->internet_usage??'')==='light')>Basic (Browsing Only)</option>
                            <option value="moderate"    @selected(old('internet_usage',$profile->internet_usage??'')==='moderate')>Moderate Use</option>
                            <option value="remote_work" @selected(old('internet_usage',$profile->internet_usage??'')==='remote_work')>Remote Work / Streaming</option>
                        </select>
                    </div>
                </div>

                {{-- Hidden fields --}}
                <input type="hidden" name="gender_preference"   value="{{ old('gender_preference',  $profile->gender_preference   ?? 'no_preference') }}">
                <input type="hidden" name="drinking_preference" value="{{ old('drinking_preference', $profile->drinking_preference ?? 'occasional_ok') }}">
                <input type="hidden" name="pets_preference"     value="{{ old('pets_preference',     $profile->pets_preference     ?? 'no_pets') }}">
                @foreach((array)$selectedHobbies as $hobby)
                <input type="hidden" name="hobbies[]" value="{{ $hobby }}">
                @endforeach
                @foreach($selectedAmenityIds as $aid)
                <input type="hidden" name="preferred_amenity_ids[]" value="{{ $aid }}">
                @endforeach

            </div>

            {{-- Bottom actions --}}
            <div class="border-t border-gray-100 px-7 py-4 grid grid-cols-2 gap-3">
                <button type="button"
                        class="flex items-center justify-center gap-2 rounded-xl border border-gray-200 py-3 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                    Reset to Default
                </button>
                <button type="submit"
                        class="flex items-center justify-center gap-2 rounded-xl py-3 text-sm font-bold text-white transition-all hover:opacity-90"
                        style="background:linear-gradient(135deg,#6366f1,#7c3aed)">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"/></svg>
                    Save Preferences
                </button>
            </div>
        </div>

        {{-- ════ RIGHT Sidebar ════ --}}
        <div class="space-y-4">

            {{-- Profile Completion --}}
            <div class="side-card p-5">
                <p class="text-sm font-bold text-gray-900 mb-4">Profile Completion</p>

                {{-- SVG ring --}}
                <div class="flex justify-center mb-4">
                    <svg width="140" height="140" viewBox="0 0 140 140">
                        <circle cx="70" cy="70" r="{{ $radius }}" fill="none" stroke="#f3f4f6" stroke-width="10"/>
                        <circle cx="70" cy="70" r="{{ $radius }}" fill="none" stroke="#6366f1" stroke-width="10"
                                stroke-linecap="round"
                                stroke-dasharray="{{ $dash }} {{ $gap }}"
                                stroke-dashoffset="{{ round($circ * 0.25, 2) }}"
                                transform="rotate(-90 70 70)"/>
                        <text x="70" y="70" text-anchor="middle" dominant-baseline="central"
                              font-size="22" font-weight="800" fill="#111827">{{ $completionPct }}%</text>
                    </svg>
                </div>

                <p class="text-center text-sm font-semibold text-gray-700 mb-1">
                    @if($completionPct >= 85) Great job! You're almost done.
                    @elseif($completionPct >= 50) Keep going! You're halfway there.
                    @else Complete your profile to get matches.
                    @endif
                </p>

                {{-- Progress bar --}}
                <div class="h-1.5 w-full rounded-full bg-gray-100 mb-1">
                    <div class="h-1.5 rounded-full bg-indigo-600 transition-all" style="width:{{ $completionPct }}%"></div>
                </div>
                <p class="text-center text-xs text-gray-400 mb-4">{{ 100 - $completionPct }}% left to get optimal AI matches.</p>

                {{-- Checklist --}}
                <div class="space-y-2.5">
                    @php
                        $steps = [
                            'Basic Information'     => true,
                            'Contact Details'       => true,
                            'Lifestyle Preferences' => (bool)($profile->completed_at ?? false),
                            'Privacy Settings'      => true,
                        ];
                    @endphp
                    @foreach($steps as $label => $done)
                    <div class="flex items-center gap-2.5">
                        @if($done)
                        <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100">
                            <svg class="h-3 w-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-xs font-medium text-gray-600">{{ $label }}</span>
                        @else
                        <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 border-indigo-500 bg-white">
                            <div class="h-2 w-2 rounded-full bg-indigo-500"></div>
                        </div>
                        <span class="text-xs font-semibold text-indigo-600">{{ $label }}</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- AI Readiness --}}
            <div class="side-card p-5">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-sm font-bold text-gray-900">AI Readiness</p>
                    <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-600">Ready</span>
                </div>

                {{-- Robot icon --}}
                <div class="flex justify-center mb-3">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full" style="background:linear-gradient(135deg,#ede9fe,#ddd6fe)">
                        <svg class="h-9 w-9 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
                        </svg>
                    </div>
                </div>

                <p class="text-center text-sm font-bold text-gray-800 mb-1">Your preferences are ready!</p>
                <p class="text-center text-xs text-gray-400 mb-4">We have enough information to provide accurate recommendations.</p>

                <a href="{{ route('user.recommendations') }}"
                   class="flex items-center justify-center gap-2 w-full rounded-xl border border-gray-200 py-2.5 text-xs font-bold text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="h-3.5 w-3.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/></svg>
                    Learn how it works
                </a>
            </div>

            {{-- Why this helps AI --}}
            <div class="side-card p-5">
                <p class="text-sm font-bold text-gray-900 mb-4">Why this helps AI</p>
                <ul class="space-y-3">
                    @foreach(['Better matches that fit your lifestyle','Saves time by showing relevant options','Improves recommendation accuracy','Adapts as your preferences change'] as $tip)
                    <li class="flex items-start gap-2.5 text-xs text-gray-500">
                        <svg class="h-4 w-4 shrink-0 text-orange-400 mt-0.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        {{ $tip }}
                    </li>
                    @endforeach
                </ul>
            </div>

        </div>
    </form>

</div>
</x-user.shell>
</x-layouts.dashboard>
