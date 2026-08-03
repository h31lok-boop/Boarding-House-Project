@php
    $namespace = $namespace ?? 'admin';
    $boardingHouses = collect($boardingHouses ?? []);
    $services = collect($services ?? []);
    $money = fn ($value) => '₱'.number_format((float) $value, 2);
@endphp

<div x-data="{ detailOpen: false, selected: {} }" class="space-y-5">
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">Property services</p>
        <h1 class="mt-1 text-2xl font-black text-slate-950">Add-ons tenants can reserve</h1>
        <p class="mt-1 text-sm text-slate-500">Offer laundry, parking, cleaning, shuttle, or other services and include them in a reservation.</p>
    </div>

    @foreach (['success' => 'emerald', 'error' => 'rose'] as $key => $tone)
        @if (session($key))
            <div class="rounded-xl border border-{{ $tone }}-200 bg-{{ $tone }}-50 px-4 py-3 text-sm font-semibold text-{{ $tone }}-700">{{ session($key) }}</div>
        @endif
    @endforeach

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-sm font-bold text-slate-950">Create a service</h2>
        <form method="POST" action="{{ route($namespace.'.services.store') }}" class="mt-4 grid gap-3 md:grid-cols-2 lg:grid-cols-5">
            @csrf
            <select name="boarding_house_id" required class="rounded-xl border-slate-200 text-sm">
                <option value="">Boarding house</option>
                @foreach ($boardingHouses as $house)
                    <option value="{{ $house->id }}">{{ $house->name }}</option>
                @endforeach
            </select>
            <input name="name" required placeholder="e.g. Laundry service" class="rounded-xl border-slate-200 text-sm">
            <input name="price" required type="number" min="0" step="0.01" placeholder="Price" class="rounded-xl border-slate-200 text-sm">
            <select name="billing_type" required class="rounded-xl border-slate-200 text-sm">
                <option value="per_use">Per use</option>
                <option value="monthly">Monthly</option>
                <option value="one_time">One time</option>
            </select>
            <button class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700">Add service</button>
            <textarea name="description" placeholder="Short description" class="md:col-span-2 lg:col-span-5 rounded-xl border-slate-200 text-sm"></textarea>
        </form>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3">Service</th><th class="px-5 py-3">Property</th><th class="px-5 py-3">Price</th><th class="px-5 py-3">Billing</th><th class="px-5 py-3">Status</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($services as $service)
                    @php
                        $servicePayload = [
                            'name' => $service->name,
                            'description' => $service->description,
                            'price' => $service->price,
                            'billing_type' => $service->billing_type,
                            'is_active' => (bool) $service->is_active,
                            'house' => $service->boardingHouse?->name,
                            'update_url' => route($namespace.'.services.update', $service),
                            'delete_url' => route($namespace.'.services.destroy', $service),
                        ];
                    @endphp
                    <tr
                        class="cursor-pointer transition hover:bg-blue-50/40 focus-within:bg-blue-50/40"
                        role="button"
                        tabindex="0"
                        @click="selected = {{ \Illuminate\Support\Js::from($servicePayload) }}; detailOpen = true"
                        @keydown.enter="selected = {{ \Illuminate\Support\Js::from($servicePayload) }}; detailOpen = true"
                        @keydown.space.prevent="selected = {{ \Illuminate\Support\Js::from($servicePayload) }}; detailOpen = true"
                    >
                        <td class="px-5 py-3"><p class="font-semibold text-slate-900">{{ $service->name }}</p><p class="text-xs text-slate-500">{{ $service->description ?: 'No description' }}</p></td>
                        <td class="px-5 py-3 text-slate-600">{{ $service->boardingHouse?->name }}</td>
                        <td class="px-5 py-3 font-semibold">{{ $money($service->price) }}</td>
                        <td class="px-5 py-3 capitalize text-slate-600">{{ str_replace('_', ' ', $service->billing_type) }}</td>
                        <td class="px-5 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $service->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $service->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="hidden">
                            <div class="hidden">
                                <details class="text-left">
                                    <summary class="cursor-pointer text-xs font-bold text-blue-600 hover:text-blue-700">Edit</summary>
                                    <form method="POST" action="{{ route($namespace.'.services.update', $service) }}" class="mt-2 w-64 space-y-2 rounded-xl border border-slate-200 bg-white p-3 shadow-lg">
                                        @csrf @method('PUT')
                                        <input name="name" value="{{ $service->name }}" required class="w-full rounded-lg border-slate-200 text-xs">
                                        <input name="price" value="{{ $service->price }}" required type="number" min="0" step="0.01" class="w-full rounded-lg border-slate-200 text-xs">
                                        <select name="billing_type" class="w-full rounded-lg border-slate-200 text-xs"><option value="per_use" @selected($service->billing_type === 'per_use')>Per use</option><option value="monthly" @selected($service->billing_type === 'monthly')>Monthly</option><option value="one_time" @selected($service->billing_type === 'one_time')>One time</option></select>
                                        <textarea name="description" class="w-full rounded-lg border-slate-200 text-xs" placeholder="Description">{{ $service->description }}</textarea>
                                        <label class="flex items-center gap-2 text-xs font-semibold text-slate-600"><input type="checkbox" name="is_active" value="1" @checked($service->is_active)> Active</label>
                                        <button class="w-full rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-bold text-white">Save changes</button>
                                    </form>
                                </details>
                                <form method="POST" action="{{ route($namespace.'.services.destroy', $service) }}" onsubmit="return confirm('Remove this service?')">@csrf @method('DELETE')<button class="text-xs font-bold text-rose-600 hover:text-rose-700">Remove</button></form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">No additional services yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <template x-teleport="body">
        <div data-modal-root role="dialog" aria-modal="true" x-show="detailOpen" x-cloak @keydown.escape.window="detailOpen = false" class="bm-modal-overlay">
            <form method="POST" :action="selected.update_url" class="bm-modal bm-modal--lg">
                @csrf @method('PUT')
                <div class="bm-modal__header">
                    <div>
                        <p class="bm-modal__eyebrow">View / Edit</p>
                        <h2 class="bm-modal__title" x-text="selected.name"></h2>
                        <p class="bm-modal__subtitle" x-text="selected.house"></p>
                    </div>
                    <button type="button" @click="detailOpen = false" class="bm-modal__close" aria-label="Close service details">&times;</button>
                </div>
                <div class="bm-modal__body">
                    <div class="bm-modal__grid bm-modal__grid--two-col">
                        <label>Service Name<input name="name" required x-model="selected.name"></label>
                        <label>Price<input name="price" required type="number" min="0" step="0.01" x-model="selected.price"></label>
                        <label>Billing Type
                            <select name="billing_type" x-model="selected.billing_type">
                                <option value="per_use">Per use</option>
                                <option value="monthly">Monthly</option>
                                <option value="one_time">One time</option>
                            </select>
                        </label>
                        <label class="bm-modal__checkbox mt-6"><input type="checkbox" name="is_active" value="1" x-model="selected.is_active" class="rounded"><span>Active service</span></label>
                        <label class="sm:col-span-2">Description<textarea name="description" rows="4" x-model="selected.description"></textarea></label>
                    </div>
                </div>
                <div class="bm-modal__footer">
                    <button type="submit" form="service-delete-form" class="bm-modal__button border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100">Delete</button>
                    <button type="submit" class="bm-modal__button bm-modal__button--primary">Save Changes</button>
                    <button type="button" @click="detailOpen = false" class="bm-modal__button bm-modal__button--secondary">Close</button>
                </div>
            </form>
            <form id="service-delete-form" method="POST" :action="selected.delete_url" onsubmit="return confirm('Remove this service?')" class="hidden">
                @csrf @method('DELETE')
            </form>
        </div>
    </template>
</div>
