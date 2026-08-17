<x-layouts.dashboard>
<x-admin.shell :show-header="false">
    @php
        $workspace = request()->routeIs('owner.*') ? 'owner' : 'admin';
        $route = fn (string $name, $params = []) => route($workspace.'.'.$name, $params);

        $statusLabel = fn ($status) => match (strtolower((string) $status)) {
            'checked-in', 'checked_in', 'checkedin' => 'Currently Staying',
            'checked-out', 'checked_out', 'checkedout' => 'Completed Stay',
            'approved' => 'Confirmed',
            'rejected' => 'Rejected',
            default => ucfirst((string) ($status ?: 'pending')),
        };

        $acceptReservationLabel = $workspace === 'owner' ? 'Accept' : 'Approve';
        $acceptReservationPastTense = $workspace === 'owner' ? 'accepted' : 'approved';

        $statusBadge = fn ($status) => match (strtolower((string) $status)) {
            'approved', 'confirmed' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'checked-in', 'checked_in', 'checkedin' => 'border-blue-200 bg-blue-50 text-blue-700',
            'pending' => 'border-amber-200 bg-amber-50 text-amber-700',
            'cancelled', 'rejected' => 'border-rose-200 bg-rose-50 text-rose-700',
            'checked-out', 'checked_out', 'checkedout' => 'border-slate-200 bg-slate-100 text-slate-600',
            default => 'border-slate-200 bg-slate-100 text-slate-600',
        };

        $paymentLabel = fn ($status) => match (strtolower((string) $status)) {
            'paid' => 'Paid',
            'partially paid', 'partial', 'partial_paid', 'partially_paid' => 'Partially Paid',
            'refunded' => 'Refunded',
            'unpaid', 'pending', '' => 'Unpaid',
            default => ucfirst((string) $status),
        };

        $paymentBadge = fn ($status) => match (strtolower((string) $status)) {
            'paid' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'partially paid', 'partial', 'partial_paid', 'partially_paid' => 'border-amber-200 bg-amber-50 text-amber-700',
            'refunded' => 'border-slate-200 bg-slate-100 text-slate-600',
            'unpaid', 'pending', '' => 'border-rose-200 bg-rose-50 text-rose-700',
            default => 'border-slate-200 bg-slate-100 text-slate-600',
        };

        $trendTone = fn (string $tone) => match ($tone) {
            'negative' => 'text-rose-600',
            'neutral' => 'text-slate-500',
            default => 'text-emerald-600',
        };

        $toneSurface = fn (string $tone) => match ($tone) {
            'amber' => 'bg-amber-50 text-amber-600 ring-amber-100',
            'rose' => 'bg-rose-50 text-rose-600 ring-rose-100',
            'emerald' => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
            'cyan' => 'bg-cyan-50 text-cyan-600 ring-cyan-100',
            'slate' => 'bg-slate-100 text-slate-600 ring-slate-200',
            default => 'bg-blue-50 text-blue-600 ring-blue-100',
        };

        $stats = $reservationStats ?? [];
        $workbench = $reservationWorkbench ?? [];
        $quickMetrics = collect($workbench['quick_metrics'] ?? []);
        $sidebarTasks = collect($workbench['tasks'] ?? []);
        $sidebarOverview = collect($workbench['overview'] ?? []);
        $upcomingMoveIns = collect($workbench['upcoming_move_ins'] ?? []);
        $activeTab = request('status') ?: 'all';

        $mainTabs = [
            'all' => ['label' => 'All', 'count' => $stats['total'] ?? $reservations->total()],
            'confirmed' => ['label' => 'Confirmed', 'count' => $stats['confirmed'] ?? 0],
            'pending' => ['label' => 'Pending', 'count' => $stats['pending'] ?? 0],
            'cancelled' => ['label' => 'Cancelled', 'count' => $stats['cancelled'] ?? 0],
        ];

        $summaryCards = [
            [
                'label' => 'Total Reservations',
                'value' => $stats['total'] ?? $reservations->total(),
                'trend' => $stats['totalTrend'] ?? '+0 this week',
                'tone' => 'blue',
                'trend_tone' => 'positive',
                'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z',
            ],
            [
                'label' => 'Confirmed Reservations',
                'value' => $stats['confirmed'] ?? 0,
                'trend' => $stats['confirmedTrend'] ?? '+0 this week',
                'tone' => 'emerald',
                'trend_tone' => 'positive',
                'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z',
            ],
            [
                'label' => 'Pending Reservations',
                'value' => $stats['pending'] ?? 0,
                'trend' => $stats['pendingTrend'] ?? '+0 this week',
                'tone' => 'amber',
                'trend_tone' => $stats['pendingTone'] ?? 'positive',
                'icon' => 'M12 6v6l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z',
            ],
            [
                'label' => 'Cancelled Reservations',
                'value' => $stats['cancelled'] ?? 0,
                'trend' => $stats['cancelledTrend'] ?? '+0 this week',
                'tone' => 'rose',
                'trend_tone' => 'positive',
                'icon' => 'M9.75 9.75 14.25 14.25m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z',
            ],
        ];

        $activeFilterSummary = collect([
            request('status') ? 'Status: '.($mainTabs[request('status')]['label'] ?? $statusLabel(request('status'))) : null,
            request('payment_status') ? 'Payment: '.($paymentLabel(request('payment_status')) === 'Action-needed' ? 'Action Needed' : $paymentLabel(request('payment_status'))) : null,
            request('date_from') ? 'From: '.\Carbon\Carbon::parse(request('date_from'))->format('M d, Y') : null,
            request('date_to') ? 'To: '.\Carbon\Carbon::parse(request('date_to'))->format('M d, Y') : null,
            request('q') ? 'Search: '.request('q') : null,
        ])->filter()->values();

        $walkInTenantOptions = collect($walkInTenants ?? [])->map(fn ($tenantRecord) => [
            'id' => (string) $tenantRecord->id,
            'house_id' => (string) $tenantRecord->boarding_house_id,
            'name' => $tenantRecord->user?->name ?? 'Tenant #'.$tenantRecord->id,
            'house' => $tenantRecord->boardingHouse?->name ?? 'Boarding house',
        ])->values();
        $walkInHouseOptions = collect($walkInHouses ?? [])->map(fn ($house) => [
            'id' => (string) $house->id,
            'name' => $house->name,
            'rooms' => $house->rooms
                ->filter(fn ($room) => strtolower((string) $room->status) === 'available' && (int) ($room->available_slots ?? 1) > 0)
                ->map(fn ($room) => [
                    'id' => (string) $room->id,
                    'label' => $room->room_no ?? $room->room_number ?? $room->name ?? 'Room #'.$room->id,
                    'price' => (float) ($room->price ?? 0),
                ])->values(),
            'services' => $house->services
                ->where('is_active', true)
                ->map(fn ($service) => [
                    'id' => (string) $service->id,
                    'name' => $service->name,
                    'price' => (float) $service->price,
                ])->values(),
        ])->values();
        $oldWalkInServiceIds = collect(old('service_ids', []))->map(fn ($id) => (string) $id)->values()->all();

        $roomTypeLabel = function ($reservation): string {
            return $reservation->room->room_type
                ?? $reservation->room->type
                ?? $reservation->room->room_name
                ?? $reservation->room->name
                ?? $reservation->room->effective_room_number
                ?? 'Room';
        };

        $reservationNoFor = fn ($reservation): string => 'RSV-'.now()->format('Y').'-'.str_pad((string) $reservation->id, 5, '0', STR_PAD_LEFT);

        $upcomingMoveInPayload = function ($reservation) use ($reservationNoFor, $statusLabel, $paymentLabel, $roomTypeLabel, $route) {
            $paymentStatus = $reservation->payment_status ?? 'unpaid';
            $amount = (float) ($reservation->total_amount ?? $reservation->amount ?? $reservation->room->price ?? 0);

            return [
                'reservation_no' => $reservationNoFor($reservation),
                'tenant' => $reservation->user->name ?? 'Tenant',
                'tenant_photo_url' => $reservation->user?->photo_url,
                'house' => $reservation->boardingHouse->name ?? 'Boarding house',
                'location' => $reservation->boardingHouse->address
                    ?? $reservation->boardingHouse->full_address
                    ?? $reservation->boardingHouse->city?->city_name
                    ?? 'Location not set',
                'room' => $roomTypeLabel($reservation),
                'move_in' => $reservation->check_in_date?->format('M d, Y') ?? 'Not set',
                'move_out' => $reservation->check_out_date?->format('M d, Y') ?? 'Not set',
                'status' => $statusLabel($reservation->status),
                'status_value' => strtolower((string) ($reservation->status ?? 'pending')),
                'payment' => $paymentLabel($paymentStatus),
                'amount' => $amount > 0 ? 'PHP '.number_format($amount, 2) : 'PHP 0.00',
                'notes' => $reservation->notes,
                'notes_value' => $reservation->notes ?? '',
                'update_url' => $route('reservations.update', $reservation),
            ];
        };
    @endphp

    <script>
        window.reservationsData = function (config) {
            return {
                editOpen: false,
                walkInOpen: Boolean(config.walkInOpen),
                walkInSaving: false,
                walkInTenants: config.walkInTenants || [],
                walkInHouses: config.walkInHouses || [],
                walkIn: config.walkIn || {
                    tenant_id: '',
                    boarding_house_id: '',
                    room_id: '',
                    total_amount: '',
                    service_ids: [],
                },
                filterOpen: false,
                menuOpen: null,
                selected: {},
                filtering: false,
                submitting: false,
                confirmOpen: false,
                confirmAction: { url: '', method: 'PATCH', status: '', title: '', message: '', label: '', tone: 'emerald' },
                csrfToken: config.csrfToken,

                /* edit-modal state */
                editForm: { room_id: '', check_in_date: '', due_date: '', total_amount: '', status: '', payment_status: '', house_rules: '', notes: '' },
                editRooms: [],
                editRoomsLoading: false,
                editSelectedRoom: null,
                editSaving: false,
                editSuccess: '',
                editError: '',
                editErrors: {},
                toast: null,
                _toastTimer: null,
                selectedTemplate: '',
                houseRuleTemplates: [
                    { label: 'Standard House Rules', text: '• Respect other tenants. Avoid noise and disturbance.\n• Keep the room and common areas clean.\n• No smoking, drinking, or illegal activities inside the property.\n• Visitors are allowed only from 8:00 AM to 10:00 PM.\n• Pay rent on or before the due date.\n• Report any damage or maintenance issue immediately.\n• Follow all safety and security guidelines.' },
                    { label: 'Strict Rules', text: '• Strictly no visitors allowed inside rooms.\n• Curfew at 10:00 PM for all tenants.\n• No cooking inside rooms — use the common kitchen only.\n• Quiet hours: 9:00 PM to 7:00 AM.\n• No pets allowed.\n• Rent must be paid on or before the 5th of the month.\n• Any violation may result in termination of the lease.' },
                    { label: 'Student-Friendly Rules', text: '• Respect quiet hours (11:00 PM – 6:00 AM).\n• Keep shared spaces clean after use.\n• Visitors allowed until 8:00 PM only.\n• No illegal substances on the premises.\n• Segregate and dispose of garbage properly.\n• Wi-Fi is shared — avoid heavy streaming during peak hours.\n• Report maintenance issues within 24 hours.' },
                ],

                get walkInSelectedHouse() {
                    return this.walkInHouses.find(house => String(house.id) === String(this.walkIn.boarding_house_id)) || null;
                },

                get walkInTenantOptions() {
                    if (!this.walkIn.boarding_house_id) return this.walkInTenants;
                    return this.walkInTenants.filter(tenant => String(tenant.house_id) === String(this.walkIn.boarding_house_id));
                },

                get walkInRoomOptions() {
                    return this.walkInSelectedHouse?.rooms || [];
                },

                get walkInServiceOptions() {
                    return this.walkInSelectedHouse?.services || [];
                },

                openWalkIn() {
                    this.walkInSaving = false;
                    this.walkInOpen = true;
                },

                onWalkInTenantChange() {
                    const tenant = this.walkInTenants.find(item => String(item.id) === String(this.walkIn.tenant_id));
                    if (!tenant) return;
                    this.walkIn.boarding_house_id = String(tenant.house_id);
                    this.onWalkInHouseChange();
                },

                onWalkInHouseChange() {
                    const tenant = this.walkInTenants.find(item => String(item.id) === String(this.walkIn.tenant_id));
                    if (tenant && String(tenant.house_id) !== String(this.walkIn.boarding_house_id)) {
                        this.walkIn.tenant_id = '';
                    }

                    if (!this.walkInRoomOptions.some(room => String(room.id) === String(this.walkIn.room_id))) {
                        this.walkIn.room_id = '';
                    }

                    const validServiceIds = new Set(this.walkInServiceOptions.map(service => String(service.id)));
                    this.walkIn.service_ids = (this.walkIn.service_ids || []).filter(id => validServiceIds.has(String(id)));
                },

                onWalkInRoomChange() {
                    const room = this.walkInRoomOptions.find(item => String(item.id) === String(this.walkIn.room_id));
                    if (room && Number(room.price) >= 0) this.walkIn.total_amount = Number(room.price).toFixed(2);
                },

                get availableRoomOptions() {
                    return this.editRooms.filter(r => r.available);
                },

                get houseRulesWordCount() {
                    const text = (this.editForm.house_rules || '').trim();
                    return text ? text.split(/\s+/).length : 0;
                },

                openEdit(payload) {
                    this.selected = payload;
                    this.editForm = {
                        room_id:        payload.room_id       || '',
                        check_in_date:  payload.check_in_date_raw  || '',
                        due_date:       payload.due_date_raw   || '',
                        total_amount:   payload.total_amount_raw != null ? payload.total_amount_raw : '',
                        status:         ['pending','approved','confirmed','cancelled','completed'].includes(payload.status_value) ? payload.status_value : 'pending',
                        payment_status: this.normalizePayment(payload.payment_status_value),
                        house_rules:    payload.house_rules_value   || '',
                        notes:          payload.notes_value    || '',
                    };
                    if (!this.editForm.due_date && this.editForm.check_in_date) {
                        this.editForm.due_date = this.addOneMonth(this.editForm.check_in_date);
                    }
                    this.editRooms = [];
                    this.editSelectedRoom = null;
                    this.selectedTemplate = '';
                    this.editSuccess = '';
                    this.editError = '';
                    this.editErrors = {};
                    this.editOpen = true;
                    this.fetchRooms(payload.boarding_house_id, payload.reservation_id);
                },

                normalizePayment(value) {
                    const v = (value || '').toLowerCase();
                    if (v === 'paid') return 'paid';
                    if (v.includes('partial')) return 'partial';
                    if (v === 'unpaid' || v === 'pending' || v === '') return 'unpaid';
                    return 'unpaid';
                },

                async fetchRooms(bhId, reservationId) {
                    if (!bhId) return;
                    this.editRoomsLoading = true;
                    try {
                        const urlTemplate = config.availableRoomsUrlTemplate;
                        const url = urlTemplate.replace('__HOUSE__', encodeURIComponent(bhId))
                            + '?reservation_id=' + encodeURIComponent(reservationId || '');
                        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' } });
                        const json = await res.json();
                        this.editRooms = json.rooms || [];
                        this.editSelectedRoom = this.editRooms.find(r => String(r.id) === String(this.editForm.room_id)) || null;
                    } catch { this.editRooms = []; }
                    finally { this.editRoomsLoading = false; }
                },

                onRoomChange() {
                    this.editSelectedRoom = this.editRooms.find(r => String(r.id) === String(this.editForm.room_id)) || null;
                    delete this.editErrors.room_id;
                    if (this.editSelectedRoom && this.editSelectedRoom.price > 0) {
                        this.editForm.total_amount = this.editSelectedRoom.price;
                    }
                },

                addOneMonth(dateStr) {
                    const d = new Date(dateStr + 'T00:00:00');
                    if (isNaN(d)) return '';
                    const day = d.getDate();
                    d.setMonth(d.getMonth() + 1);
                    // Clamp overflow (e.g. Jan 31 -> Feb 28)
                    if (d.getDate() !== day) d.setDate(0);
                    return d.toISOString().slice(0, 10);
                },

                onMoveInChange() {
                    delete this.editErrors.check_in_date;
                    // Auto-calculate next payment due date: one month after move-in
                    if (this.editForm.check_in_date) {
                        const next = this.addOneMonth(this.editForm.check_in_date);
                        if (next) { this.editForm.due_date = next; delete this.editErrors.due_date; }
                    }
                },

                applyTemplate() {
                    const tpl = this.houseRuleTemplates.find(t => t.label === this.selectedTemplate);
                    if (tpl) {
                        this.editForm.house_rules = tpl.text;
                        delete this.editErrors.house_rules;
                    }
                },

                validateEdit() {
                    const errors = {};
                    if (!this.editForm.room_id) errors.room_id = ['Please assign a room before saving.'];
                    if (!this.editForm.check_in_date) errors.check_in_date = ['Move-in date is required.'];
                    if (!this.editForm.due_date) errors.due_date = ['Due date is required.'];
                    if (this.editForm.total_amount === '' || this.editForm.total_amount === null) {
                        errors.total_amount = ['Monthly rate is required.'];
                    } else if (Number(this.editForm.total_amount) < 0) {
                        errors.total_amount = ['Monthly rate cannot be negative.'];
                    }
                    if (!this.editForm.status) errors.status = ['Reservation status is required.'];
                    if (!this.editForm.payment_status) errors.payment_status = ['Please choose a payment status.'];
                    if (!(this.editForm.house_rules || '').trim()) errors.house_rules = ['House rules are required. Type them or insert a template.'];
                    this.editErrors = errors;
                    return Object.keys(errors).length === 0;
                },

                async saveEdit() {
                    if (this.editSaving) return;
                    this.editSuccess = '';
                    this.editError = '';
                    if (!this.validateEdit()) {
                        this.editError = 'Please fix the highlighted fields before saving.';
                        return;
                    }
                    this.editSaving = true;
                    try {
                        const fd = new FormData();
                        fd.append('_token', this.csrfToken);
                        fd.append('_method', 'PATCH');
                        fd.append('_full_edit', '1');
                        Object.entries(this.editForm).forEach(([k, v]) => fd.append(k, v ?? ''));
                        const res = await fetch(this.selected.update_url, {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
                            body: fd,
                        });
                        const json = await res.json();
                        if (res.ok && json.success) {
                            this.updateRow(json.reservation);
                            this.showToast('success', json.message || 'Reservation updated successfully.');
                            this.editOpen = false;
                        } else if (res.status === 422 && json.errors) {
                            this.editErrors = json.errors;
                            this.editError = 'Please fix the highlighted fields.';
                        } else {
                            this.editError = json.message || 'Something went wrong. Please try again.';
                        }
                    } catch { this.editError = 'Network error. Please try again.'; }
                    finally { this.editSaving = false; }
                },

                statusBadgeClass(status) {
                    if (['approved', 'confirmed'].includes(status)) return 'border-emerald-200 bg-emerald-50 text-emerald-700';
                    if (status === 'pending') return 'border-amber-200 bg-amber-50 text-amber-700';
                    if (['cancelled', 'rejected'].includes(status)) return 'border-rose-200 bg-rose-50 text-rose-700';
                    return 'border-slate-200 bg-slate-100 text-slate-600';
                },

                paymentBadgeClass(payment) {
                    if (payment === 'paid') return 'border-emerald-200 bg-emerald-50 text-emerald-700';
                    if (payment === 'partial') return 'border-amber-200 bg-amber-50 text-amber-700';
                    return 'border-rose-200 bg-rose-50 text-rose-700';
                },

                updateRow(r) {
                    if (!r) return;
                    const row = document.querySelector('[data-reservation-row=\'' + r.id + '\']');
                    if (!row) return;
                    const badgeBase = 'inline-flex rounded-full border px-2.5 py-1 text-[11px] font-black shadow-sm ';
                    const set = (name, text) => {
                        const el = row.querySelector('[data-cell=\'' + name + '\']');
                        if (el) el.textContent = text;
                    };
                    set('room', r.room_type);
                    set('move_in', r.move_in_formatted);
                    set('amount', r.amount_formatted);
                    const statusEl = row.querySelector('[data-cell=\'status\']');
                    if (statusEl) { statusEl.textContent = r.status_label; statusEl.className = badgeBase + this.statusBadgeClass(r.status_value); }
                    const payEl = row.querySelector('[data-cell=\'payment\']');
                    if (payEl) { payEl.textContent = r.payment_label; payEl.className = badgeBase + this.paymentBadgeClass(r.payment_status_value); }
                    // Keep the cached payload in sync so reopening the modal shows saved values
                    this.selected.room_id = r.room_id;
                    this.selected.status_value = r.status_value;
                    this.selected.payment_status_value = r.payment_status_value;
                    this.selected.check_in_date_raw = r.check_in_date || '';
                    this.selected.due_date_raw = r.due_date || '';
                    this.selected.total_amount_raw = r.total_amount;
                    this.selected.house_rules_value = r.house_rules || '';
                    this.selected.notes_value = r.notes || '';
                },

                showToast(type, message) {
                    this.toast = { type, message };
                    clearTimeout(this._toastTimer);
                    this._toastTimer = setTimeout(() => { this.toast = null; }, 3500);
                },

                askConfirm(action) {
                    if (this.submitting) return;
                    this.confirmAction = action;
                    this.menuOpen = null;
                    this.editOpen = false;
                    this.confirmOpen = true;
                },
            };
        };
    </script>

    <div
        x-data="reservationsData({
            csrfToken: '{{ csrf_token() }}',
            availableRoomsUrlTemplate: @json($route('api.boarding-houses.available-rooms', ['boardingHouse' => '__HOUSE__'])),
            walkInOpen: @js($errors->walkIn->any()),
            walkInTenants: @js($walkInTenantOptions),
            walkInHouses: @js($walkInHouseOptions),
            walkIn: @js([
                'tenant_id' => (string) old('tenant_id', ''),
                'boarding_house_id' => (string) old('boarding_house_id', ''),
                'room_id' => (string) old('room_id', ''),
                'total_amount' => old('total_amount', ''),
                'service_ids' => $oldWalkInServiceIds,
            ])
        })"
        x-init="window.addEventListener('pageshow', () => { filtering = false; submitting = false; editSaving = false; })"
        @keydown.escape.window="if (!submitting && !editSaving) { editOpen = false; filterOpen = false; menuOpen = null; confirmOpen = false; }"
        class="space-y-5 text-slate-950"
    >
        @if ($workspace !== 'admin')
        <header data-reservations-toolbar class="overflow-hidden rounded-[1.35rem] border border-slate-200/80 bg-white shadow-[0_10px_24px_rgba(15,23,42,0.04)]">
            <div class="px-5 py-4 sm:px-6">
            <div class="space-y-3.5">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0">
                        <h1 class="text-[1.6rem] font-semibold tracking-[-0.04em] text-slate-950">Reservations</h1>
                        <p class="mt-1 text-[13px] leading-5 text-slate-500">Track reservation requests, room assignments, and payment progress.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 lg:justify-end">
                        <a href="{{ $route('reservations.export', request()->query()) }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 text-[13px] font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14"/>
                            </svg>
                            Export
                        </a>
                        <button type="button" @click="filterOpen = true" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 text-[13px] font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M7 12h10M10 18h4"/>
                            </svg>
                            Filters
                        </button>
                    </div>
                </div>

                    <div class="border-t border-slate-100 pt-3.5">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                <div class="flex flex-wrap items-center gap-3 lg:flex-1">
                                    @foreach ($mainTabs as $value => $tab)
                                        @php
                                            $href = $value === 'all'
                                                ? $route('reservations', request()->except('status', 'page'))
                                                : $route('reservations', array_merge(request()->except('page'), ['status' => $value]));
                                            $isActive = $activeTab === $value || ($value === 'all' && blank(request('status')));
                                        @endphp
                                        <a
                                            href="{{ $href }}"
                                            @click="filtering = true"
                                            class="inline-flex h-9 items-center gap-2 rounded-full border px-4 text-[12px] font-semibold transition {{ $isActive ? 'border-blue-600 bg-blue-600 text-white shadow-sm shadow-blue-600/20' : 'border-slate-200 bg-white text-slate-600 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700' }}"
                                            @if ($isActive) aria-current="page" @endif
                                        >
                                            <span>{{ $tab['label'] }}</span>
                                            <span class="inline-flex min-w-5 items-center justify-center rounded-full px-1.5 py-0.5 text-[10px] font-black {{ $isActive ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-500' }}">{{ number_format((int) $tab['count']) }}</span>
                                        </a>
                                    @endforeach
                                    @if ($activeFilterSummary->isNotEmpty())
                                        <a href="{{ $route('reservations') }}" class="inline-flex h-9 items-center justify-center rounded-full border border-slate-200 bg-white px-4 text-[12px] font-medium text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">Clear</a>
                                    @endif
                                </div>

                                <div class="flex min-w-0 lg:w-[50%] lg:max-w-[500px]">
                                    <form method="GET" action="{{ $route('reservations') }}" @submit="filtering = true" class="min-w-0 flex-1">
                                        @foreach (request()->except('q', 'page') as $key => $value)
                                            @if (is_scalar($value) && filled($value))
                                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                            @endif
                                        @endforeach
                                        <label class="relative block">
                                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <circle cx="10.5" cy="10.5" r="6.5" stroke-width="1.8"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16 16 4 4"/>
                                                </svg>
                                            </span>
                                            <input
                                                name="q"
                                                value="{{ request('q') }}"
                                                class="h-11 w-full rounded-xl border border-slate-200 bg-white px-10 pr-4 text-[13px] text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                                placeholder="Search tenant, boarding house, reservation no., or status..."
                                            >
                                        </label>
                                    </form>
                                </div>
                            </div>
                        </div>
                </div>

                @if ($activeFilterSummary->isNotEmpty())
                    <div class="flex flex-wrap gap-2 border-t border-slate-100 pt-3">
                        @foreach ($activeFilterSummary as $filterLabel)
                            <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-semibold text-slate-600">{{ $filterLabel }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </header>
        @endif

        <div
            data-walk-in-workspace
            x-data="walkInReservation({
                walkInOpen: @js($errors->walkIn->any()),
                walkInTenants: @js($walkInTenantOptions),
                walkInHouses: @js($walkInHouseOptions),
                walkIn: @js([
                    'tenant_id' => (string) old('tenant_id', ''),
                    'boarding_house_id' => (string) old('boarding_house_id', ''),
                    'room_id' => (string) old('room_id', ''),
                    'total_amount' => old('total_amount', ''),
                    'service_ids' => $oldWalkInServiceIds,
                ])
            })"
        >
        <section class="rounded-2xl border border-blue-100 bg-blue-50/50 p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div><h2 class="text-sm font-bold text-slate-950">Front desk walk-in booking</h2><p class="mt-1 text-xs text-slate-500">Paid walk-ins are automatically prioritized and get a printable receipt.</p></div>
                <button type="button" data-walk-in-trigger data-no-loading-overlay @click.prevent="openWalkIn()" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-[13px] font-bold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 5v14m7-7H5"/></svg>
                    Create Walk-in
                </button>
            </div>
        </section>

        <template x-teleport="body">
            <div data-modal-root data-walk-in-modal role="dialog" aria-modal="true" aria-labelledby="walk-in-modal-title" x-show="walkInOpen" x-cloak @click.self="closeWalkIn()" @keydown.escape.window="closeWalkIn()" class="bm-modal-overlay">
                <form method="POST" action="{{ $route('reservations.walk-in.store') }}" @submit="walkInSaving = true" class="bm-modal bm-modal--lg">
                    @csrf
                    <div class="bm-modal__header">
                        <div>
                            <p class="bm-modal__eyebrow">Front Desk</p>
                            <h2 id="walk-in-modal-title" class="bm-modal__title">Create Walk-in Reservation</h2>
                            <p class="bm-modal__subtitle">Record a same-day booking, payment status, and optional services.</p>
                        </div>
                        <button type="button" @click="closeWalkIn()" class="bm-modal__close" aria-label="Close walk-in reservation modal">&times;</button>
                    </div>
                    <div class="bm-modal__body">
                        @if ($errors->walkIn->any())
                            <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">
                                <p class="font-bold">The walk-in reservation was not saved.</p>
                                <ul class="mt-1 list-disc space-y-1 pl-5">
                                    @foreach ($errors->walkIn->all() as $message)
                                        <li>{{ $message }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="bm-modal__grid bm-modal__grid--two-col">
                            <label>Boarding House
                                <select x-ref="walkInHouse" name="boarding_house_id" x-model="walkIn.boarding_house_id" @change="onWalkInHouseChange()" required>
                                    <option value="">Select boarding house</option>
                                    <template x-for="house in walkInHouses" :key="house.id">
                                        <option :value="String(house.id)" x-text="house.name"></option>
                                    </template>
                                </select>
                                @error('boarding_house_id', 'walkIn')<span class="mt-1 block text-xs font-semibold text-rose-600">{{ $message }}</span>@enderror
                            </label>
                            <label>Tenant
                                <select name="tenant_id" x-model="walkIn.tenant_id" @change="onWalkInTenantChange()" required>
                                    <option value="">Select tenant</option>
                                    <template x-for="tenant in walkInTenantOptions" :key="tenant.id">
                                        <option :value="String(tenant.id)" x-text="tenant.name"></option>
                                    </template>
                                </select>
                                <span x-show="walkIn.boarding_house_id && walkInTenantOptions.length === 0" class="mt-1 block text-xs font-semibold text-amber-600">No active tenant is assigned to this property.</span>
                                @error('tenant_id', 'walkIn')<span class="mt-1 block text-xs font-semibold text-rose-600">{{ $message }}</span>@enderror
                            </label>
                            <label>Room <span class="font-normal text-slate-400">(optional)</span>
                                <select name="room_id" x-model="walkIn.room_id" @change="onWalkInRoomChange()" :disabled="!walkIn.boarding_house_id">
                                    <option value="">Any available room</option>
                                    <template x-for="room in walkInRoomOptions" :key="room.id">
                                        <option :value="String(room.id)" x-text="room.label"></option>
                                    </template>
                                </select>
                                @error('room_id', 'walkIn')<span class="mt-1 block text-xs font-semibold text-rose-600">{{ $message }}</span>@enderror
                            </label>
                            <label>Total Amount
                                <input name="total_amount" x-model="walkIn.total_amount" required type="number" min="0" step="0.01" placeholder="0.00">
                                @error('total_amount', 'walkIn')<span class="mt-1 block text-xs font-semibold text-rose-600">{{ $message }}</span>@enderror
                            </label>
                            <label>Check-in Date
                                <input name="check_in_date" type="date" value="{{ old('check_in_date', today()->toDateString()) }}">
                                @error('check_in_date', 'walkIn')<span class="mt-1 block text-xs font-semibold text-rose-600">{{ $message }}</span>@enderror
                            </label>
                            <label>Payment Status
                                <select name="payment_status" required><option value="paid" @selected(old('payment_status', 'paid') === 'paid')>Paid</option><option value="unpaid" @selected(old('payment_status') === 'unpaid')>Unpaid</option></select>
                            </label>
                            <label>Payment Method
                                <input type="hidden" name="payment_method" value="cash">
                                <input value="Cash" readonly>
                            </label>
                            <label>Payment Reference <span class="font-normal text-slate-400">(optional)</span>
                                <input name="payment_reference" value="{{ old('payment_reference') }}" placeholder="Reference number">
                            </label>
                            <label class="sm:col-span-2">Notes <span class="font-normal text-slate-400">(optional)</span>
                                <textarea name="notes" rows="3" placeholder="Add notes about this walk-in booking">{{ old('notes') }}</textarea>
                            </label>
                        </div>

                        <section class="bm-modal__section mt-5">
                            <div>
                                <h3 class="bm-modal__section-title">Optional Services</h3>
                                <p class="bm-modal__section-copy">Add laundry, parking, cleaning, or other services to this reservation.</p>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <template x-for="service in walkInServiceOptions" :key="service.id">
                                    <label class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600">
                                        <input type="checkbox" name="service_ids[]" :value="String(service.id)" x-model="walkIn.service_ids" class="rounded border-slate-300 text-blue-600">
                                        <span x-text="service.name + ' (₱' + Number(service.price).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ')' "></span>
                                    </label>
                                </template>
                                <p x-show="walkIn.boarding_house_id && walkInServiceOptions.length === 0" class="text-xs text-slate-500">No active services for this property.</p>
                            </div>
                            @error('service_ids', 'walkIn')<span class="mt-2 block text-xs font-semibold text-rose-600">{{ $message }}</span>@enderror
                        </section>
                    </div>
                    <div class="bm-modal__footer">
                        <button type="button" @click="closeWalkIn()" class="bm-modal__button bm-modal__button--secondary">Cancel</button>
                        <button :disabled="walkInSaving" class="bm-modal__button bm-modal__button--primary disabled:cursor-not-allowed disabled:opacity-60">
                            <span x-text="walkInSaving ? 'Saving Walk-in...' : 'Save Walk-in Reservation'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </template>
        </div>

        {{-- Stat Cards --}}
        @php
            $unpaidCount = $quickMetrics->firstWhere('label', 'Unpaid Deposits')['value'] ?? 0;
            $statCards = [
                ['label' => 'Total', 'value' => $stats['total'] ?? 0, 'tone' => 'blue', 'href' => $route('reservations'), 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z'],
                ['label' => 'Pending', 'value' => $stats['pending'] ?? 0, 'tone' => 'amber', 'href' => $route('reservations', ['status' => 'pending']), 'icon' => 'M12 6v6l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'],
                ['label' => 'Confirmed', 'value' => $stats['confirmed'] ?? 0, 'tone' => 'emerald', 'href' => $route('reservations', ['status' => 'confirmed']), 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'],
                ['label' => 'Cancelled', 'value' => $stats['cancelled'] ?? 0, 'tone' => 'rose', 'href' => $route('reservations', ['status' => 'cancelled']), 'icon' => 'M9.75 9.75 14.25 14.25m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'],
                ['label' => 'Unpaid', 'value' => $unpaidCount, 'tone' => 'orange', 'href' => $route('reservations', ['payment_status' => 'action-needed']), 'icon' => 'M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z'],
            ];
            $cardColor = fn($t) => match($t) {
                'amber'   => 'bg-amber-50 text-amber-600',
                'emerald' => 'bg-emerald-50 text-emerald-600',
                'rose'    => 'bg-rose-50 text-rose-600',
                'orange'  => 'bg-orange-50 text-orange-600',
                default   => 'bg-blue-50 text-blue-600',
            };
        @endphp
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($statCards as $sc)
                <a href="{{ $sc['href'] }}" @click="filtering = true" class="flex items-center gap-3 rounded-[1.1rem] border border-slate-200/80 bg-white px-4 py-3.5 shadow-[0_4px_12px_rgba(15,23,42,0.04)] transition hover:border-blue-200 hover:shadow-[0_4px_16px_rgba(37,99,235,0.08)]">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $cardColor($sc['tone']) }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $sc['icon'] }}"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $sc['label'] }}</p>
                        <p class="text-[1.3rem] font-black tracking-tight text-slate-950">{{ number_format((int) $sc['value']) }}</p>
                    </div>
                </a>
            @endforeach
        </div>

        <div>
            <main class="min-w-0">
                <section class="relative overflow-hidden rounded-[1.35rem] border border-slate-200/80 bg-white shadow-[0_14px_32px_rgba(15,23,42,0.05)]">
                    <template x-if="filtering">
                        <div class="absolute inset-0 z-20 flex items-center justify-center bg-white/70 backdrop-blur-[2px]" aria-live="polite">
                            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-3 shadow-lg shadow-slate-900/10">
                                <svg class="h-5 w-5 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"/>
                                </svg>
                                <span class="text-[13px] font-semibold text-slate-700">Loading reservations&hellip;</span>
                            </div>
                        </div>
                    </template>

                    <div class="overflow-x-auto transition-opacity duration-200" :class="filtering ? 'opacity-50' : ''">
                        <table class="min-w-[1180px] w-full text-left text-[13px]">
                            <thead class="sticky top-0 z-10 bg-slate-50/95 text-[11px] font-bold uppercase tracking-wide text-slate-500 backdrop-blur">
                                <tr>
                                    <th class="px-5 py-3.5">Reservation No.</th>
                                    <th class="px-5 py-3.5">Tenant</th>
                                    <th class="px-5 py-3.5">Boarding House</th>
                                    <th class="px-5 py-3.5">Room Type</th>
                                    <th class="px-5 py-3.5">Move-in Date</th>
                                    <th class="px-5 py-3.5">Reservation Status</th>
                                    <th class="px-5 py-3.5">Payment Status</th>
                                    <th class="px-5 py-3.5">Amount</th>
                                    <th class="px-5 py-3.5">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($reservations as $reservation)
                                    @php
                                        $tenantName = $reservation->user->name ?? 'Tenant';
                                        $tenantInitials = collect(explode(' ', trim($tenantName)))
                                            ->filter()
                                            ->take(2)
                                            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                                            ->join('') ?: 'T';
                                        $tenantPhotoUrl = $reservation->user?->photo_url;
                                        $reservationNo = $reservationNoFor($reservation);
                                        $houseName = $reservation->boardingHouse->name ?? 'Boarding House';
                                        $houseLocation = $reservation->boardingHouse->address
                                            ?? $reservation->boardingHouse->full_address
                                            ?? $reservation->boardingHouse->city?->city_name
                                            ?? 'Location not set';
                                        $roomType = $roomTypeLabel($reservation);
                                        $paymentStatus = $reservation->payment_status ?? 'unpaid';
                                        $amount = (float) ($reservation->total_amount ?? $reservation->amount ?? $reservation->room->price ?? 0);
                                        $payload = [
                                            'reservation_id'       => $reservation->id,
                                            'reservation_no'       => $reservationNo,
                                            'tenant'               => $tenantName,
                                            'tenant_initials'      => $tenantInitials,
                                            'tenant_photo_url'     => $tenantPhotoUrl,
                                            'tenant_email'         => $reservation->user->email ?? '',
                                            'house'                => $houseName,
                                            'boarding_house_id'    => $reservation->boarding_house_id,
                                            'location'             => $houseLocation,
                                            'room'                 => $roomType,
                                            'room_id'              => $reservation->room_id,
                                            'move_in'              => $reservation->check_in_date?->format('M d, Y') ?? 'Not set',
                                            'move_out'             => $reservation->check_out_date?->format('M d, Y') ?? 'Not set',
                                            'check_in_date_raw'    => $reservation->check_in_date?->format('Y-m-d') ?? '',
                                            'due_date_raw'         => $reservation->due_date?->format('Y-m-d') ?? '',
                                            'total_amount_raw'     => $amount > 0 ? $amount : '',
                                            'status'               => $statusLabel($reservation->status),
                                            'status_value'         => strtolower((string) ($reservation->status ?? 'pending')),
                                            'payment'              => $paymentLabel($paymentStatus),
                                            'payment_status_value' => $paymentStatus,
                                            'amount'               => $amount > 0 ? 'PHP '.number_format($amount, 2) : 'PHP 0.00',
                                            'notes'                => $reservation->notes,
                                            'notes_value'          => $reservation->notes ?? '',
                                            'house_rules_value'    => $reservation->house_rules ?? '',
                                            'update_url'           => $route('reservations.update', $reservation),
                                            'payment_url'          => $route('transactions.index', ['q' => $tenantName]),
                                        ];

                                        $rowStatus = strtolower((string) ($reservation->status ?? 'pending'));
                                        $isPendingRow = $rowStatus === 'pending';

                                        $confirmApprove = [
                                            'url' => $route('reservations.update', $reservation),
                                            'method' => 'PATCH',
                                            'status' => $isPendingRow ? 'approved' : 'confirmed',
                                            'title' => $isPendingRow ? $acceptReservationLabel.' this reservation?' : 'Confirm this reservation?',
                                            'message' => $reservationNo.' for '.$tenantName.' will be '.($isPendingRow ? $acceptReservationPastTense : 'marked as Confirmed').'. The tenant will be notified and the payment status will be set for follow-up.',
                                            'label' => $isPendingRow ? 'Yes, '.$acceptReservationLabel : 'Yes, Confirm Reservation',
                                            'tone' => 'emerald',
                                        ];
                                        $confirmReject = [
                                            'url' => $route('reservations.update', $reservation),
                                            'method' => 'PATCH',
                                            'status' => 'rejected',
                                            'title' => 'Reject this reservation?',
                                            'message' => $reservationNo.' for '.$tenantName.' will be rejected, the held room will be released, and the tenant will be notified.',
                                            'label' => 'Yes, Reject',
                                            'tone' => 'rose',
                                        ];
                                        $confirmCancel = [
                                            'url' => $route('reservations.update', $reservation),
                                            'method' => 'PATCH',
                                            'status' => 'cancelled',
                                            'title' => 'Cancel this reservation?',
                                            'message' => $reservationNo.' for '.$tenantName.' will be cancelled, the held room will be released, and the tenant will be notified.',
                                            'label' => 'Yes, Cancel Reservation',
                                            'tone' => 'rose',
                                        ];
                                        $confirmDelete = [
                                            'url' => $route('reservations.destroy', $reservation),
                                            'method' => 'DELETE',
                                            'status' => '',
                                            'title' => 'Delete this reservation?',
                                            'message' => $reservationNo.' for '.$tenantName.' will be permanently removed. This cannot be undone.',
                                            'label' => 'Yes, Delete',
                                            'tone' => 'rose',
                                        ];
                                        $payload['actions'] = [
                                            'approve' => $confirmApprove,
                                            'reject' => $confirmReject,
                                            'cancel' => $confirmCancel,
                                            'delete' => $confirmDelete,
                                        ];
                                    @endphp
                                    <tr
                                        class="cursor-pointer bg-white transition duration-200 hover:bg-slate-50/90 focus-within:bg-blue-50/40"
                                        data-reservation-row="{{ $reservation->id }}"
                                        role="button"
                                        tabindex="0"
                                        @click="openEdit({{ \Illuminate\Support\Js::from($payload) }})"
                                        @keydown.enter="openEdit({{ \Illuminate\Support\Js::from($payload) }})"
                                        @keydown.space.prevent="openEdit({{ \Illuminate\Support\Js::from($payload) }})"
                                    >
                                        <td class="whitespace-nowrap px-5 py-4 text-xs font-black text-slate-800">{{ $reservationNo }}</td>
                                        <td class="px-5 py-4">
                                            <div class="flex items-center gap-2.5">
                                                <div class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full bg-blue-100 text-[11px] font-black text-blue-700">@if ($tenantPhotoUrl)<img src="{{ $tenantPhotoUrl }}" alt="{{ $tenantName }}" class="h-full w-full object-cover" loading="lazy">@else{{ $tenantInitials }}@endif</div>
                                                <div class="min-w-0">
                                                    <p class="truncate text-[13px] font-semibold text-slate-900">{{ $tenantName }}</p>
                                                    <p class="truncate text-[11px] text-slate-500">{{ $reservation->user->email ?? 'No email' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4">
                                            <p class="font-semibold text-slate-900">{{ $houseName }}</p>
                                            <p class="mt-0.5 flex items-center gap-1.5 text-[11px] text-slate-500">
                                                <svg class="h-3 w-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 21s7-4.5 7-11a7 7 0 1 0-14 0c0 6.5 7 11 7 11z"/>
                                                    <circle cx="12" cy="10" r="2.5" stroke-width="1.8"/>
                                                </svg>
                                                {{ $houseLocation }}
                                            </p>
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-4 text-[13px] font-medium text-slate-800" data-cell="room">{{ $roomType }}</td>
                                        <td class="whitespace-nowrap px-5 py-4 text-[13px] font-medium text-slate-800" data-cell="move_in">{{ $reservation->check_in_date?->format('M d, Y') ?? 'Not set' }}</td>
                                        <td class="whitespace-nowrap px-5 py-4">
                                            <span data-cell="status" class="inline-flex rounded-full border px-2.5 py-1 text-[11px] font-black shadow-sm {{ $statusBadge($reservation->status) }}">
                                                {{ $statusLabel($reservation->status) }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-4">
                                            <span data-cell="payment" class="inline-flex rounded-full border px-2.5 py-1 text-[11px] font-black shadow-sm {{ $paymentBadge($paymentStatus) }}">
                                                {{ $paymentLabel($paymentStatus) }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-4 text-[13px] font-semibold text-slate-900" data-cell="amount">{{ $amount > 0 ? 'PHP '.number_format($amount, 2) : 'PHP 0.00' }}</td>
                                        <td class="whitespace-nowrap px-5 py-4" @click.stop>
                                            <div class="flex items-center gap-2">
                                                @if ($isPendingRow)
                                                    <button
                                                        type="button"
                                                        @click.stop="askConfirm({{ \Illuminate\Support\Js::from($confirmApprove) }})"
                                                        class="inline-flex h-9 items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3 text-[12px] font-bold text-emerald-700 transition hover:-translate-y-0.5 hover:bg-emerald-100"
                                                        title="{{ $acceptReservationLabel }} reservation"
                                                    >
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        {{ $acceptReservationLabel }}
                                                    </button>
                                                    <button
                                                        type="button"
                                                        @click.stop="askConfirm({{ \Illuminate\Support\Js::from($confirmReject) }})"
                                                        class="inline-flex h-9 items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-3 text-[12px] font-bold text-rose-700 transition hover:-translate-y-0.5 hover:bg-rose-100"
                                                        title="Reject reservation"
                                                    >
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        Reject
                                                    </button>
                                                @else
                                                    <button
                                                        type="button"
                                                        @click.stop="openEdit({{ \Illuminate\Support\Js::from($payload) }})"
                                                        class="inline-flex h-9 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-[12px] font-bold text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"
                                                    >View details</button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-5 py-16">
                                            <div class="mx-auto max-w-md text-center">
                                                <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-[2rem] bg-[radial-gradient(circle_at_top,_rgba(37,99,235,0.18),_transparent_62%),linear-gradient(180deg,#eff6ff_0%,#ffffff_100%)] text-blue-600 shadow-inner dark:bg-[radial-gradient(circle_at_top,_rgba(59,130,246,0.2),_transparent_62%),linear-gradient(180deg,#1e293b_0%,#0f172a_100%)]">
                                                    <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9 14h6"/>
                                                    </svg>
                                                </div>
                                                @if ($activeFilterSummary->isNotEmpty())
                                                    <h3 class="mt-5 text-[17px] font-semibold tracking-[-0.02em] text-slate-950">No reservations match your filters</h3>
                                                    <p class="mt-2 text-[14px] leading-6 text-slate-500">Try a different search term, status tab, or date range &mdash; or clear the filters to see every reservation.</p>
                                                    <a href="{{ $route('reservations') }}" @click="filtering = true" class="mt-5 inline-flex h-10 items-center justify-center rounded-xl bg-blue-600 px-5 text-[13px] font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700">Clear Filters</a>
                                                @else
                                                    <h3 class="mt-5 text-[17px] font-semibold tracking-[-0.02em] text-slate-950">No reservations found</h3>
                                                    <p class="mt-2 text-[14px] leading-6 text-slate-500">Reservation requests will appear here once tenants start booking.</p>
                                                    <a href="{{ $route('boarding-houses') }}" class="mt-5 inline-flex h-10 items-center justify-center rounded-xl bg-blue-600 px-5 text-[13px] font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700">View Boarding Houses</a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-col gap-2.5 border-t border-slate-100 px-5 py-4 text-[13px] text-slate-500 md:flex-row md:items-center md:justify-between">
                        <p>Showing {{ $reservations->firstItem() ?? 0 }} to {{ $reservations->lastItem() ?? 0 }} of {{ $reservations->total() }} results</p>
                        @if ($reservations->hasPages())
                            <nav class="flex items-center gap-2" aria-label="Pagination">
                                @if ($reservations->onFirstPage())
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-300">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7"/></svg>
                                    </span>
                                @else
                                    <a href="{{ $reservations->previousPageUrl() }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7"/></svg>
                                    </a>
                                @endif

                                @foreach ($reservations->getUrlRange(1, $reservations->lastPage()) as $page => $url)
                                    @if ($page === $reservations->currentPage())
                                        <span class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg bg-blue-600 px-2.5 font-bold text-white shadow-sm shadow-blue-600/20">{{ $page }}</span>
                                    @elseif ($page <= 3 || $page === $reservations->lastPage() || abs($page - $reservations->currentPage()) <= 1)
                                        <a href="{{ $url }}" class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg border border-slate-200 px-2.5 font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">{{ $page }}</a>
                                    @elseif ($page === 4 && $reservations->currentPage() > 5)
                                        <span class="px-1 font-bold text-slate-400">...</span>
                                    @endif
                                @endforeach

                                @if ($reservations->hasMorePages())
                                    <a href="{{ $reservations->nextPageUrl() }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
                                    </a>
                                @else
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-300">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
                                    </span>
                                @endif
                            </nav>
                        @endif
                    </div>
                </section>
            </main>

        </div>

        <div
            data-modal-root
            role="dialog"
            aria-modal="true"
            x-show="filterOpen"
            x-cloak
            x-transition
            class="fixed inset-0 z-[90] flex items-center justify-center bg-black/30 p-3 backdrop-blur-sm"
        >
            <form method="GET" action="{{ $route('reservations') }}" @submit="filtering = true; filterOpen = false" class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-5 shadow-xl shadow-slate-900/15">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.16em] text-blue-600">Filter Reservations</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-950">Refine reservation results</h2>
                    </div>
                    <button type="button" @click="filterOpen = false" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <label class="text-sm font-semibold text-slate-700">
                        Reservation Status
                        <select name="status" class="mt-1 h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                            <option value="">All</option>
                            @foreach (['confirmed' => 'Confirmed', 'pending' => 'Pending', 'cancelled' => 'Cancelled', 'currently-staying' => 'Currently Staying', 'completed-stay' => 'Completed Stay'] as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-sm font-semibold text-slate-700">
                        Payment Status
                        <select name="payment_status" class="mt-1 h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                            <option value="">All</option>
                            <option value="paid" @selected(request('payment_status') === 'paid')>Paid</option>
                            <option value="unpaid" @selected(request('payment_status') === 'unpaid')>Unpaid</option>
                            <option value="action-needed" @selected(request('payment_status') === 'action-needed')>Action Needed</option>
                            <option value="partially paid" @selected(request('payment_status') === 'partially paid')>Partially Paid</option>
                            <option value="refunded" @selected(request('payment_status') === 'refunded')>Refunded</option>
                        </select>
                    </label>
                    <label class="text-sm font-semibold text-slate-700">
                        From
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="mt-1 h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                    </label>
                    <label class="text-sm font-semibold text-slate-700">
                        To
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="mt-1 h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                    </label>
                </div>

                <label class="mt-4 block text-sm font-semibold text-slate-700">
                    Search
                    <input name="q" value="{{ request('q') }}" class="mt-1 h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100" placeholder="Search tenant, boarding house, reservation no., or status...">
                </label>

                <div class="mt-5 flex justify-end gap-2">
                    <a href="{{ $route('reservations') }}" class="inline-flex h-9 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Clear</a>
                    <button class="inline-flex h-9 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">Apply Filters</button>
                </div>
            </form>
        </div>

        {{-- Edit Reservation Modal --}}
        <div
            data-modal-root
            role="dialog"
            aria-modal="true"
            x-show="editOpen"
            x-cloak
            x-transition
            class="fixed inset-0 z-[90] flex items-center justify-center bg-black/30 p-3 backdrop-blur-sm"
        >
            <div class="flex w-full max-w-3xl max-h-[92vh] flex-col overflow-x-hidden overflow-y-auto rounded-2xl bg-white shadow-2xl shadow-slate-900/25">
                {{-- Header --}}
                <div class="flex shrink-0 items-start justify-between border-b border-slate-100 px-7 py-5">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full bg-blue-100 text-sm font-black text-blue-700"><template x-if="selected.tenant_photo_url"><img :src="selected.tenant_photo_url" :alt="selected.tenant" class="h-full w-full object-cover"></template><span x-show="!selected.tenant_photo_url" x-text="selected.tenant_initials || 'T'"></span></span>
                        <div class="min-w-0">
                            <h2 class="text-[19px] font-bold tracking-[-0.02em] text-slate-950">Edit Reservation</h2>
                            <p class="mt-0.5 truncate text-[13px] font-bold tracking-wide text-blue-600"><span x-text="selected.reservation_no"></span> · <span x-text="selected.tenant"></span></p>
                        </div>
                    </div>
                    <button type="button" @click="editOpen = false" :disabled="editSaving" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Modal body; the whole dialog scrolls so its actions cannot cover content. --}}
                <div class="shrink-0 px-7 py-5">
                    {{-- Error banner --}}
                    <div x-show="editError" x-cloak class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-[13px] font-semibold text-rose-800" x-text="editError"></div>

                    <div class="space-y-6">
                        {{-- Boarding House (Read-only) --}}
                        <section>
                            <p class="mb-2.5 text-[11px] font-black uppercase tracking-[0.14em] text-slate-400">Boarding House</p>
                            <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200/80 bg-slate-50/70 px-4 py-3.5">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-700">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M9 12h.01M9 15h.01M15 9h.01M15 12h.01M15 15h.01"/>
                                        </svg>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-[14px] font-bold text-slate-900" x-text="selected.house"></p>
                                        <p class="truncate text-[12px] text-slate-500" x-text="selected.location"></p>
                                    </div>
                                </div>
                                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1.5 text-[11px] font-bold text-blue-700">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <rect x="5" y="11" width="14" height="9" rx="2" stroke-width="1.8"/>
                                        <path stroke-linecap="round" stroke-width="1.8" d="M8 11V7a4 4 0 0 1 8 0v4"/>
                                    </svg>
                                    Read-only
                                </span>
                            </div>
                        </section>

                        {{-- Room Assignment --}}
                        <section>
                            <p class="mb-2.5 text-[11px] font-black uppercase tracking-[0.14em] text-slate-400">Room Assignment</p>
                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                <div>
                                    <label class="block text-[13px] font-semibold text-slate-700">
                                        Assign Room <span class="text-rose-600">*</span>
                                        <select x-model="editForm.room_id" @change="onRoomChange()" :disabled="editSaving || editRoomsLoading" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-[13px] text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100 disabled:bg-slate-50 disabled:opacity-60" :class="editErrors.room_id ? 'border-rose-300 bg-rose-50' : ''">
                                            <option value="">-- Select an available room --</option>
                                            <template x-for="room in availableRoomOptions" :key="room.id">
                                                <option :value="room.id" x-text="room.label + ' (' + room.price_formatted + ' / month)'"></option>
                                            </template>
                                        </select>
                                    </label>
                                    <p x-show="editRoomsLoading" x-cloak class="mt-1.5 text-[11px] font-medium text-blue-600">Loading available rooms&hellip;</p>
                                    <p x-show="!editRoomsLoading && editOpen && availableRoomOptions.length === 0" x-cloak class="mt-1.5 text-[11px] text-slate-500">No vacant rooms in this boarding house right now.</p>
                                    <p x-show="editErrors.room_id" x-cloak class="mt-1.5 text-[11px] font-semibold text-rose-600" x-text="editErrors.room_id?.[0]"></p>
                                </div>

                                <div>
                                    <p class="text-[13px] font-semibold text-slate-700">Room Details</p>
                                    <div x-show="editSelectedRoom" x-cloak class="mt-1.5 rounded-xl border border-blue-100 bg-blue-50/60 px-4 py-3">
                                        <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-[12px] sm:grid-cols-4">
                                            <div>
                                                <dt class="text-slate-500">Room Number</dt>
                                                <dd class="mt-0.5 text-[13px] font-bold text-slate-900" x-text="editSelectedRoom?.number"></dd>
                                            </div>
                                            <div>
                                                <dt class="text-slate-500">Room Type</dt>
                                                <dd class="mt-0.5 text-[13px] font-bold text-slate-900" x-text="editSelectedRoom?.type"></dd>
                                            </div>
                                            <div>
                                                <dt class="text-slate-500">Floor / Location</dt>
                                                <dd class="mt-0.5 text-[13px] font-bold text-slate-900" x-text="editSelectedRoom?.floor"></dd>
                                            </div>
                                            <div>
                                                <dt class="text-slate-500">Status</dt>
                                                <dd class="mt-0.5"><span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-bold text-emerald-700" x-text="editSelectedRoom?.status"></span></dd>
                                            </div>
                                        </dl>
                                    </div>
                                    <div x-show="!editSelectedRoom" x-cloak class="mt-1.5 rounded-xl border border-dashed border-slate-200 bg-slate-50/50 px-4 py-3 text-[12px] text-slate-400">
                                        Select a room to see its details.
                                    </div>
                                </div>
                            </div>

                            {{-- Rate + Dates row --}}
                            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div>
                                    <label class="block text-[13px] font-semibold text-slate-700">
                                        Monthly Rate (PHP) <span class="text-rose-600">*</span>
                                        <span class="relative mt-1.5 block">
                                            <input type="number" step="0.01" min="0" x-model="editForm.total_amount" @input="delete editErrors.total_amount" :disabled="editSaving" class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 pr-14 text-[13px] text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100 disabled:bg-slate-50 disabled:opacity-60" :class="editErrors.total_amount ? 'border-rose-300 bg-rose-50' : ''">
                                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[12px] font-bold text-slate-400">PHP</span>
                                        </span>
                                    </label>
                                    <p x-show="editErrors.total_amount" x-cloak class="mt-1.5 text-[11px] font-semibold text-rose-600" x-text="editErrors.total_amount?.[0]"></p>
                                </div>

                                <div>
                                    <label class="block text-[13px] font-semibold text-slate-700">
                                        Move-in Date <span class="text-rose-600">*</span>
                                        <input type="date" x-model="editForm.check_in_date" @change="onMoveInChange()" :disabled="editSaving" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-[13px] text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100 disabled:bg-slate-50 disabled:opacity-60" :class="editErrors.check_in_date ? 'border-rose-300 bg-rose-50' : ''">
                                    </label>
                                    <p x-show="editErrors.check_in_date" x-cloak class="mt-1.5 text-[11px] font-semibold text-rose-600" x-text="editErrors.check_in_date?.[0]"></p>
                                </div>

                                <div>
                                    <label class="block text-[13px] font-semibold text-slate-700">
                                        Due Date <span class="text-rose-600">*</span>
                                        <input type="date" x-model="editForm.due_date" @change="delete editErrors.due_date" :disabled="editSaving" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-[13px] text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100 disabled:bg-slate-50 disabled:opacity-60" :class="editErrors.due_date ? 'border-rose-300 bg-rose-50' : ''">
                                    </label>
                                    <p x-show="editErrors.due_date" x-cloak class="mt-1.5 text-[11px] font-semibold text-rose-600" x-text="editErrors.due_date?.[0]"></p>
                                </div>
                            </div>
                        </section>

                        {{-- Status Row: Reservation + Payment --}}
                        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <p class="mb-2.5 text-[11px] font-black uppercase tracking-[0.14em] text-slate-400">Reservation Status</p>
                                <label class="block text-[13px] font-semibold text-slate-700">
                                    Status <span class="text-rose-600">*</span>
                                    <select x-model="editForm.status" @change="delete editErrors.status" :disabled="editSaving" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-[13px] text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100 disabled:bg-slate-50 disabled:opacity-60" :class="editErrors.status ? 'border-rose-300 bg-rose-50' : ''">
                                        <option value="pending">Pending</option>
                                        <option value="approved">Approved</option>
                                        <option value="confirmed">Confirmed</option>
                                        <option value="cancelled">Cancelled</option>
                                        <option value="completed">Completed</option>
                                    </select>
                                </label>
                                <p x-show="editErrors.status" x-cloak class="mt-1.5 text-[11px] font-semibold text-rose-600" x-text="editErrors.status?.[0]"></p>
                            </div>

                            <div>
                                <p class="mb-2.5 text-[11px] font-black uppercase tracking-[0.14em] text-slate-400">Payment Status</p>
                                <label class="block text-[13px] font-semibold text-slate-700">
                                    Payment Status <span class="text-rose-600">*</span>
                                    <select x-model="editForm.payment_status" @change="delete editErrors.payment_status" :disabled="editSaving" class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-[13px] text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100 disabled:bg-slate-50 disabled:opacity-60" :class="editErrors.payment_status ? 'border-rose-300 bg-rose-50' : ''">
                                        <option value="paid">Paid</option>
                                        <option value="unpaid">Unpaid</option>
                                        <option value="partial">Partial</option>
                                    </select>
                                </label>
                                <p x-show="editErrors.payment_status" x-cloak class="mt-1.5 text-[11px] font-semibold text-rose-600" x-text="editErrors.payment_status?.[0]"></p>
                            </div>
                        </section>

                        {{-- House Rules / Policy --}}
                        <section>
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-[11px] font-black uppercase tracking-[0.14em] text-slate-400">House Rules / Policy <span class="text-rose-600">*</span></p>
                                    <p class="mt-1 text-[12px] text-slate-500">Define the rules and policies that the tenant must follow.</p>
                                </div>
                                <div class="flex shrink-0 items-center gap-2">
                                    <select x-model="selectedTemplate" :disabled="editSaving" class="h-9 rounded-xl border border-slate-200 bg-white px-3 text-[12px] font-semibold text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100 disabled:opacity-60">
                                        <option value="">Use Template</option>
                                        <template x-for="tpl in houseRuleTemplates" :key="tpl.label">
                                            <option :value="tpl.label" x-text="tpl.label"></option>
                                        </template>
                                    </select>
                                    <button type="button" @click="applyTemplate()" :disabled="editSaving || !selectedTemplate" class="inline-flex h-9 items-center gap-1.5 rounded-xl bg-blue-50 px-3.5 text-[12px] font-bold text-blue-700 transition hover:bg-blue-100 disabled:cursor-not-allowed disabled:opacity-50">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/></svg>
                                        Insert Template
                                    </button>
                                </div>
                            </div>
                            <div class="relative mt-3">
                                <textarea x-model="editForm.house_rules" @input="delete editErrors.house_rules" rows="8" :disabled="editSaving" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 pb-8 text-[13px] leading-relaxed text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100 disabled:bg-slate-50 disabled:opacity-60" placeholder="Type the house rules here, or pick a template above and edit it&hellip;" :class="editErrors.house_rules ? 'border-rose-300 bg-rose-50' : ''"></textarea>
                                <span class="pointer-events-none absolute bottom-3 right-4 text-[11px] font-medium text-slate-400" x-text="houseRulesWordCount + ' words'"></span>
                            </div>
                            <p x-show="editErrors.house_rules" x-cloak class="mt-1.5 text-[11px] font-semibold text-rose-600" x-text="editErrors.house_rules?.[0]"></p>
                        </section>

                        {{-- Notes --}}
                        <section>
                            <p class="mb-2.5 text-[11px] font-black uppercase tracking-[0.14em] text-slate-400">Notes <span class="font-bold normal-case tracking-normal text-slate-400">(optional)</span></p>
                            <div class="relative">
                                <textarea x-model="editForm.notes" maxlength="500" rows="3" :disabled="editSaving" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 pb-8 text-[13px] text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100 disabled:bg-slate-50 disabled:opacity-60" placeholder="Add any notes about this reservation&hellip;"></textarea>
                                <span class="pointer-events-none absolute bottom-3 right-4 text-[11px] font-medium text-slate-400" x-text="(editForm.notes || '').length + ' / 500'"></span>
                            </div>
                        </section>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex shrink-0 flex-wrap items-center justify-end gap-2.5 border-t border-slate-100 bg-white px-7 py-4">
                    <button
                        type="button"
                        x-show="selected.status_value === 'pending'"
                        @click="askConfirm(selected.actions.approve)"
                        class="inline-flex h-10 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 text-[13px] font-bold text-emerald-700 transition hover:bg-emerald-100"
                    >{{ $acceptReservationLabel }}</button>
                    <button
                        type="button"
                        x-show="selected.status_value === 'pending'"
                        @click="askConfirm(selected.actions.reject)"
                        class="inline-flex h-10 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 px-4 text-[13px] font-bold text-rose-700 transition hover:bg-rose-100"
                    >Reject</button>
                    <button
                        type="button"
                        x-show="selected.status_value !== 'pending' && !['confirmed', 'approved', 'cancelled', 'rejected', 'expired'].includes(selected.status_value)"
                        @click="askConfirm(selected.actions.approve)"
                        class="inline-flex h-10 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 text-[13px] font-bold text-emerald-700 transition hover:bg-emerald-100"
                    >Confirm</button>
                    <button
                        type="button"
                        x-show="!['cancelled', 'rejected', 'expired'].includes(selected.status_value)"
                        @click="askConfirm(selected.actions.cancel)"
                        class="inline-flex h-10 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 px-4 text-[13px] font-bold text-rose-700 transition hover:bg-rose-100"
                    >Cancel Reservation</button>
                    <a
                        :href="selected.payment_url"
                        class="inline-flex h-10 items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-4 text-[13px] font-bold text-blue-700 transition hover:bg-blue-100"
                    >Payments</a>
                    <button
                        type="button"
                        @click="askConfirm(selected.actions.delete)"
                        class="inline-flex h-10 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 px-4 text-[13px] font-bold text-rose-700 transition hover:bg-rose-100"
                    >Delete</button>
                    <button type="button" @click="editOpen = false" :disabled="editSaving" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-[13px] font-bold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60">
                        Cancel
                    </button>
                    <button type="button" @click="saveEdit()" :disabled="editSaving" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 text-[13px] font-bold text-white shadow-sm shadow-blue-600/25 transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-70">
                        <svg x-show="editSaving" x-cloak class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"/>
                        </svg>
                        <span x-text="editSaving ? 'Saving…' : 'Save Changes'">Save Changes</span>
                    </button>
                </div>
            </div>
        </div>

        <div
            data-modal-root
            role="dialog"
            aria-modal="true"
            x-show="confirmOpen"
            x-cloak
            x-transition
            class="fixed inset-0 z-[90] flex items-center justify-center bg-black/30 p-3 backdrop-blur-sm"
        >
            <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 shadow-xl shadow-slate-900/15">
                <div class="flex items-start gap-3.5">
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                        :class="confirmAction.tone === 'rose' ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600'"
                    >
                        <svg x-show="confirmAction.tone !== 'rose'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                        </svg>
                        <svg x-show="confirmAction.tone === 'rose'" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-[15px] font-bold tracking-[-0.01em] text-slate-950" x-text="confirmAction.title"></h2>
                        <p class="mt-1.5 text-[13px] leading-5 text-slate-500" x-text="confirmAction.message"></p>
                    </div>
                </div>

                <form method="POST" :action="confirmAction.url" @submit="submitting = true" class="mt-5 flex justify-end gap-2">
                    @csrf
                    <input type="hidden" name="_method" :value="confirmAction.method">
                    <template x-if="confirmAction.status">
                        <input type="hidden" name="status" :value="confirmAction.status">
                    </template>
                    <button type="button" @click="confirmOpen = false" :disabled="submitting" class="inline-flex h-9 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60">Go Back</button>
                    <button
                        :disabled="submitting"
                        class="inline-flex h-9 items-center justify-center gap-2 rounded-xl px-4 text-sm font-bold text-white shadow-sm transition disabled:cursor-not-allowed disabled:opacity-70"
                        :class="confirmAction.tone === 'rose' ? 'bg-rose-600 hover:bg-rose-700 shadow-rose-600/20' : 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/20'"
                    >
                        <svg x-show="submitting" x-cloak class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"/>
                        </svg>
                        <span x-text="submitting ? 'Working…' : (confirmAction.label || 'Confirm')"></span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Toast Notification --}}
        <div
            x-cloak
            x-show="toast"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-y-3 opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed bottom-6 right-6 z-[70] flex items-center gap-3 rounded-2xl border bg-white px-4 py-3 shadow-xl shadow-slate-900/15"
            :class="toast?.type === 'success' ? 'border-emerald-200' : 'border-rose-200'"
            role="status"
            aria-live="polite"
        >
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl" :class="toast?.type === 'success' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'">
                <svg x-show="toast?.type === 'success'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                </svg>
                <svg x-show="toast?.type !== 'success'" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                </svg>
            </span>
            <p class="text-[13px] font-semibold text-slate-800" x-text="toast?.message"></p>
            <button type="button" @click="toast = null" class="ml-1 inline-flex h-6 w-6 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
</x-admin.shell>
</x-layouts.dashboard>
