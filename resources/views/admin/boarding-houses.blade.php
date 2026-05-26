<x-layouts.dashboard>
<x-admin.shell>
    @php
        $statusBadge = function ($status) {
            return match (strtolower((string) $status)) {
                'approved', 'active' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                'rejected', 'inactive' => 'bg-rose-100 text-rose-700 border-rose-200',
                default => 'bg-slate-100 text-slate-700 border-slate-200',
            };
        };
    @endphp

    <div x-data="{ addOpen: false, viewOpen: false, editOpen: false, selected: {} }" class="space-y-6">
        <div class="ui-card p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[color:var(--brand-600)]">Management</p>
                    <h1 class="mt-2 text-2xl font-bold">Boarding Houses</h1>
                    <p class="mt-2 text-sm ui-muted">Create, verify, and manage owner listings and availability details.</p>
                </div>
                <button type="button" @click="addOpen = true" class="btn-primary">Add Boarding House</button>
            </div>
        </div>

        <form method="GET" class="ui-card p-4 grid gap-3 md:grid-cols-[1fr_160px_180px_auto]">
            <input name="q" value="{{ request('q') }}" class="ui-input text-sm" placeholder="Search name or address">
            <select name="status" class="ui-input text-sm">
                <option value="">All statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
            <select name="approval" class="ui-input text-sm">
                <option value="">All approvals</option>
                <option value="approved" @selected(request('approval') === 'approved')>Approved</option>
                <option value="pending" @selected(request('approval') === 'pending')>Pending</option>
                <option value="rejected" @selected(request('approval') === 'rejected')>Rejected</option>
            </select>
            <button class="btn-secondary">Filter</button>
        </form>

        <div class="ui-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-[color:var(--surface-2)] text-xs uppercase ui-muted">
                        <tr>
                            <th class="px-5 py-3 text-left">Boarding House</th>
                            <th class="px-5 py-3 text-left">Owner</th>
                            <th class="px-5 py-3 text-left">Rooms</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y ui-border">
                        @forelse ($houses as $house)
                            @php
                                $activeLabel = $house->is_active ? 'Active' : 'Inactive';
                                $approval = $house->approval_status ?: ($house->status ?: 'pending');
                                $payload = [
                                    'name' => $house->name,
                                    'address' => $house->address ?: $house->full_address,
                                    'description' => $house->description,
                                    'landlord_info' => $house->landlord_info ?: $house->contact_person,
                                    'contact_name' => $house->contact_name ?: $house->contact_person,
                                    'contact_phone' => $house->contact_phone ?: $house->contact_number,
                                    'monthly_payment' => $house->monthly_payment ?: $house->price,
                                    'capacity' => $house->capacity ?: $house->max_capacity,
                                    'approval_status' => $approval,
                                    'is_active' => (bool) $house->is_active,
                                    'update_url' => route('admin.listings.update', $house),
                                ];
                            @endphp
                            <tr>
                                <td class="px-5 py-4">
                                    <p class="font-semibold">{{ $house->name }}</p>
                                    <p class="text-xs ui-muted">{{ $house->address ?: $house->full_address ?: 'No address set' }}</p>
                                </td>
                                <td class="px-5 py-4 ui-muted">{{ $house->owner->name ?? $house->landlord_info ?? 'Owner' }}</td>
                                <td class="px-5 py-4">
                                    <p>{{ $house->rooms_count }} rooms</p>
                                    <p class="text-xs ui-muted">{{ $house->reservations_count }} reservations · {{ $house->inquiries_count }} inquiries</p>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <span class="badge border {{ $statusBadge($activeLabel) }}">{{ $activeLabel }}</span>
                                        <span class="badge border {{ $statusBadge($approval) }}">{{ ucfirst($approval) }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" class="btn-secondary px-3 py-1.5 text-xs" @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; viewOpen = true">View</button>
                                        <button type="button" class="btn-secondary px-3 py-1.5 text-xs" @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; editOpen = true">Edit</button>
                                        <form method="POST" action="{{ route('admin.listings.destroy', $house) }}" onsubmit="return confirm('Delete this boarding house?')">
                                            @csrf @method('DELETE')
                                            <button class="btn-danger px-3 py-1.5 text-xs">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center ui-muted">No boarding houses found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t ui-border px-5 py-4">{{ $houses->links() }}</div>
        </div>

        <div x-show="addOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <form method="POST" action="{{ route('admin.listings.store') }}" class="ui-card max-h-[90vh] w-full max-w-3xl overflow-y-auto p-6">
                @csrf
                <div class="flex items-center justify-between"><h2 class="text-lg font-semibold">Add Boarding House</h2><button type="button" @click="addOpen = false" class="text-xl ui-muted">x</button></div>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="text-sm">Name<input name="name" required class="ui-input mt-1"></label>
                    <label class="text-sm">Monthly Fee<input name="monthly_payment" type="number" min="0" step="0.01" class="ui-input mt-1"></label>
                    <label class="text-sm md:col-span-2">Address<input name="address" required class="ui-input mt-1"></label>
                    <label class="text-sm">Owner / Landlord<input name="landlord_info" class="ui-input mt-1"></label>
                    <label class="text-sm">Contact Number<input name="contact_phone" class="ui-input mt-1"></label>
                    <label class="text-sm">Capacity<input name="capacity" type="number" min="1" class="ui-input mt-1"></label>
                    <label class="text-sm">Approval<select name="approval_status" class="ui-input mt-1"><option value="approved">Approved</option><option value="pending">Pending</option><option value="rejected">Rejected</option></select></label>
                    <label class="text-sm md:col-span-2">Description<textarea name="description" rows="3" class="ui-input mt-1"></textarea></label>
                    <label class="md:col-span-2 flex items-center gap-2 text-sm"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" checked> Active listing</label>
                </div>
                <div class="mt-6 flex justify-end gap-2"><button type="button" @click="addOpen = false" class="btn-secondary">Cancel</button><button class="btn-primary">Save Listing</button></div>
            </form>
        </div>

        <div x-show="viewOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="ui-card w-full max-w-2xl p-6">
                <div class="flex items-center justify-between"><h2 class="text-lg font-semibold">Boarding House Details</h2><button type="button" @click="viewOpen = false" class="text-xl ui-muted">x</button></div>
                <dl class="mt-5 grid gap-4 text-sm md:grid-cols-2">
                    <div><dt class="ui-muted">Name</dt><dd class="font-semibold" x-text="selected.name"></dd></div>
                    <div><dt class="ui-muted">Monthly Fee</dt><dd x-text="selected.monthly_payment || 'Not set'"></dd></div>
                    <div class="md:col-span-2"><dt class="ui-muted">Address</dt><dd x-text="selected.address || 'Not set'"></dd></div>
                    <div><dt class="ui-muted">Owner</dt><dd x-text="selected.landlord_info || 'Not set'"></dd></div>
                    <div><dt class="ui-muted">Contact</dt><dd x-text="selected.contact_phone || 'Not set'"></dd></div>
                    <div class="md:col-span-2"><dt class="ui-muted">Description</dt><dd x-text="selected.description || 'No description'"></dd></div>
                </dl>
                <div class="mt-6 flex justify-end"><button type="button" @click="viewOpen = false" class="btn-secondary">Close</button></div>
            </div>
        </div>

        <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <form method="POST" :action="selected.update_url" class="ui-card max-h-[90vh] w-full max-w-3xl overflow-y-auto p-6">
                @csrf @method('PUT')
                <div class="flex items-center justify-between"><h2 class="text-lg font-semibold">Edit Boarding House</h2><button type="button" @click="editOpen = false" class="text-xl ui-muted">x</button></div>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="text-sm">Name<input name="name" required class="ui-input mt-1" :value="selected.name"></label>
                    <label class="text-sm">Monthly Fee<input name="monthly_payment" type="number" min="0" step="0.01" class="ui-input mt-1" :value="selected.monthly_payment"></label>
                    <label class="text-sm md:col-span-2">Address<input name="address" required class="ui-input mt-1" :value="selected.address"></label>
                    <label class="text-sm">Owner / Landlord<input name="landlord_info" class="ui-input mt-1" :value="selected.landlord_info"></label>
                    <label class="text-sm">Contact Number<input name="contact_phone" class="ui-input mt-1" :value="selected.contact_phone"></label>
                    <label class="text-sm">Capacity<input name="capacity" type="number" min="1" class="ui-input mt-1" :value="selected.capacity"></label>
                    <label class="text-sm">Approval<select name="approval_status" class="ui-input mt-1" :value="selected.approval_status"><option value="approved">Approved</option><option value="pending">Pending</option><option value="rejected">Rejected</option></select></label>
                    <label class="text-sm md:col-span-2">Description<textarea name="description" rows="3" class="ui-input mt-1" x-text="selected.description"></textarea></label>
                    <label class="md:col-span-2 flex items-center gap-2 text-sm"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" :checked="selected.is_active"> Active listing</label>
                </div>
                <div class="mt-6 flex justify-end gap-2"><button type="button" @click="editOpen = false" class="btn-secondary">Cancel</button><button class="btn-primary">Save Changes</button></div>
            </form>
        </div>
    </div>
</x-admin.shell>
</x-layouts.dashboard>
