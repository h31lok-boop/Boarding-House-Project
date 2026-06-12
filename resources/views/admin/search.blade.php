<x-layouts.dashboard>
<x-admin.shell>
    <div class="space-y-6">
        <div class="ui-card rounded-2xl p-5 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-blue-700">Search</p>
                    <h1 class="mt-2 text-2xl font-bold text-slate-950">Admin Search</h1>
                    <p class="mt-2 text-sm text-slate-500">Search boarding houses, tenants, reservations, payments, and inquiries.</p>
                </div>
                <form method="GET" action="{{ route('admin.search') }}" class="flex w-full gap-2 lg:max-w-xl">
                    <input name="query" value="{{ $query }}" class="ui-input flex-1 text-sm" placeholder="Search admin records...">
                    <button class="btn-primary">Search</button>
                </form>
            </div>
        </div>

        @if ($query === '')
            <div class="ui-card rounded-2xl p-8 text-center text-sm text-slate-500 shadow-sm">Enter a search term to find admin records.</div>
        @else
            <div class="grid gap-5 lg:grid-cols-2">
                <x-admin.search-section title="Boarding Houses" :items="$boardingHouses" empty="No boarding houses matched.">
                    @foreach ($boardingHouses as $house)
                        <a href="{{ route('admin.boarding-houses', ['q' => $house->name]) }}" class="block rounded-xl border border-slate-100 bg-slate-50 p-3 transition hover:border-blue-200 hover:bg-blue-50">
                            <p class="font-bold text-slate-900">{{ $house->name }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $house->address ?: $house->full_address ?: 'No address listed' }}</p>
                        </a>
                    @endforeach
                </x-admin.search-section>

                <x-admin.search-section title="Tenants" :items="$tenants" empty="No tenants matched.">
                    @foreach ($tenants as $tenant)
                        <a href="{{ route('admin.tenants.index', ['q' => $tenant->name]) }}" class="block rounded-xl border border-slate-100 bg-slate-50 p-3 transition hover:border-blue-200 hover:bg-blue-50">
                            <p class="font-bold text-slate-900">{{ $tenant->name }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $tenant->email }}</p>
                        </a>
                    @endforeach
                </x-admin.search-section>

                <x-admin.search-section title="Reservations" :items="$reservations" empty="No reservations matched.">
                    @foreach ($reservations as $reservation)
                        <a href="{{ route('admin.reservations', ['q' => $reservation->user->name ?? null]) }}" class="block rounded-xl border border-slate-100 bg-slate-50 p-3 transition hover:border-blue-200 hover:bg-blue-50">
                            <p class="font-bold text-slate-900">{{ $reservation->user->name ?? 'Tenant' }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $reservation->boardingHouse->name ?? 'Boarding house' }} · {{ ucfirst($reservation->status ?? 'pending') }}</p>
                        </a>
                    @endforeach
                </x-admin.search-section>

                <x-admin.search-section title="Payments" :items="$payments" empty="No payments matched.">
                    @foreach ($payments as $payment)
                        <a href="{{ route('admin.transactions.index', ['status' => $payment->status]) }}" class="block rounded-xl border border-slate-100 bg-slate-50 p-3 transition hover:border-blue-200 hover:bg-blue-50">
                            <p class="font-bold text-slate-900">PHP {{ number_format((float) $payment->amount, 2) }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $payment->tenant->user->name ?? 'Tenant' }} · {{ ucfirst($payment->status ?? 'pending') }}</p>
                        </a>
                    @endforeach
                </x-admin.search-section>

                <x-admin.search-section title="Inquiries" :items="$inquiries" empty="No inquiries matched.">
                    @foreach ($inquiries as $inquiry)
                        <a href="{{ route('admin.inquiries', ['q' => $inquiry->user->name ?? null]) }}" class="block rounded-xl border border-slate-100 bg-slate-50 p-3 transition hover:border-blue-200 hover:bg-blue-50">
                            <p class="font-bold text-slate-900">{{ $inquiry->user->name ?? 'Tenant' }}</p>
                            <p class="mt-1 line-clamp-2 text-xs text-slate-500">{{ $inquiry->message }}</p>
                        </a>
                    @endforeach
                </x-admin.search-section>
            </div>
        @endif
    </div>
</x-admin.shell>
</x-layouts.dashboard>
