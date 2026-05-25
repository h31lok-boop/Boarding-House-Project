@php
    $isAdminWorkspace = request()->routeIs('admin.*');
    $routeName = fn (string $admin, string $owner, $params = []) => route($isAdminWorkspace ? $admin : $owner, $params);
    $filters = $filters ?? ['q' => request('q'), 'status' => request('status')];
    $statusClass = function ($house): string {
        $status = strtolower((string) ($house->approval_status ?: $house->status));
        return match ($status) {
            'approved', 'active', 'published', 'accredited' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
            'rejected', 'denied', 'suspended' => 'bg-rose-100 text-rose-700 ring-rose-200',
            'draft' => 'bg-slate-100 text-slate-700 ring-slate-200',
            default => 'bg-amber-100 text-amber-700 ring-amber-200',
        };
    };
    $statusLabel = fn ($house) => ucfirst(str_replace('_', ' ', (string) ($house->approval_status ?: $house->status ?: 'pending')));
@endphp

<x-layouts.caretaker>
    <x-owner.shell :show-header="false">
        <div class="space-y-6">
            <section class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Listings</h1>
                    <p class="mt-1 text-sm text-slate-600 sm:text-base">Create, update, submit, and monitor your boarding house listings.</p>
                </div>
                <a href="{{ $routeName('admin.listings.create', 'owner.boarding-houses.create') }}" class="inline-flex h-11 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                    Add New Listing
                </a>
            </section>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    ['label' => 'Total Listings', 'value' => $houses->total(), 'tone' => 'bg-blue-100 text-blue-700'],
                    ['label' => 'Approved', 'value' => $houses->getCollection()->filter(fn ($h) => strtolower((string) $h->approval_status) === 'approved')->count(), 'tone' => 'bg-emerald-100 text-emerald-700'],
                    ['label' => 'Pending', 'value' => $houses->getCollection()->filter(fn ($h) => in_array(strtolower((string) $h->approval_status), ['pending', ''], true))->count(), 'tone' => 'bg-amber-100 text-amber-700'],
                    ['label' => 'Rooms', 'value' => $houses->getCollection()->sum('rooms_count'), 'tone' => 'bg-violet-100 text-violet-700'],
                ] as $stat)
                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-600">{{ $stat['label'] }}</p>
                        <p class="mt-2 text-3xl font-bold text-slate-950">{{ number_format($stat['value']) }}</p>
                        <span class="mt-3 inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $stat['tone'] }}">Current page</span>
                    </article>
                @endforeach
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <form method="GET" action="{{ $routeName('admin.listings', 'owner.boarding-houses') }}" class="grid gap-3 border-b border-slate-200 p-4 lg:grid-cols-[minmax(260px,1fr)_200px_auto]">
                    <input name="q" value="{{ $filters['q'] }}" type="search" placeholder="Search by name, address, or contact" class="h-11 rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <select name="status" class="h-11 rounded-xl border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All status</option>
                        @foreach (['draft', 'pending', 'approved', 'rejected'] as $status)
                            <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    <div class="flex gap-2">
                        <button class="h-11 rounded-xl bg-blue-700 px-4 text-sm font-bold text-white hover:bg-blue-800">Filter</button>
                        <a href="{{ $routeName('admin.listings', 'owner.boarding-houses') }}" class="inline-flex h-11 items-center rounded-xl border border-slate-200 px-4 text-sm font-bold text-slate-700 hover:bg-slate-50">Reset</a>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-[1080px] w-full text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-4">Listing</th>
                                <th class="px-5 py-4">Contact</th>
                                <th class="px-5 py-4">Rooms</th>
                                <th class="px-5 py-4">Activity</th>
                                <th class="px-5 py-4">Compliance</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($houses as $house)
                                <tr class="align-top hover:bg-slate-50/80">
                                    <td class="px-5 py-4">
                                        <div class="flex gap-3">
                                            <span class="h-16 w-20 shrink-0 overflow-hidden rounded-xl bg-slate-100">
                                                @if ($house->featured_image)
                                                    <img src="{{ asset('storage/'.$house->featured_image) }}" alt="{{ $house->name }}" class="h-full w-full object-cover">
                                                @endif
                                            </span>
                                            <span>
                                                <span class="block font-bold text-slate-950">{{ $house->name }}</span>
                                                <span class="mt-1 block max-w-sm text-xs leading-5 text-slate-500">{{ $house->address ?: $house->full_address }}</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-slate-700">
                                        <p>{{ $house->contact_name ?: $house->owner?->name }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $house->contact_phone ?: $house->contact_number }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-slate-700">
                                        <p>{{ number_format($house->rooms_count) }} total</p>
                                        <p class="mt-1 text-xs text-emerald-700">{{ number_format($house->available_rooms_count) }} available</p>
                                    </td>
                                    <td class="px-5 py-4 text-slate-700">
                                        <p>{{ number_format($house->inquiries_count) }} inquiries</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ number_format($house->reservations_count) }} reservations</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-lg px-3 py-1 text-xs font-bold {{ $house->owner_compliance['badge'] }}">{{ $house->owner_compliance['label'] }}</span>
                                        <p class="mt-2 max-w-xs text-xs text-slate-500">{{ $house->owner_compliance['remarks'] }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-lg px-3 py-1 text-xs font-bold ring-1 {{ $statusClass($house) }}">{{ $statusLabel($house) }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex justify-end gap-2">
                                            @if (Route::has('user.boarding-houses.show'))
                                                <a href="{{ route('user.boarding-houses.show', $house) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">Preview</a>
                                            @endif
                                            <a href="{{ $routeName('admin.listings.edit', 'owner.boarding-houses.edit', $house) }}" class="rounded-lg border border-blue-200 px-3 py-2 text-xs font-bold text-blue-700 hover:bg-blue-50">Edit</a>
                                            @if (! in_array(strtolower((string) $house->approval_status), ['approved', 'pending'], true))
                                                <form method="POST" action="{{ $routeName('admin.listings.submit', 'owner.boarding-houses.submit', $house) }}">
                                                    @csrf
                                                    <button class="rounded-lg border border-emerald-200 px-3 py-2 text-xs font-bold text-emerald-700 hover:bg-emerald-50">Submit</button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ $routeName('admin.listings.destroy', 'owner.boarding-houses.destroy', $house) }}" onsubmit="return confirm('Delete this listing?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-bold text-rose-700 hover:bg-rose-50">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-500">No listings match the current filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-200 p-4">
                    {{ $houses->links() }}
                </div>
            </section>
        </div>
    </x-owner.shell>
</x-layouts.caretaker>
