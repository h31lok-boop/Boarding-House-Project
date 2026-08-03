<x-layouts.dashboard>
<x-admin.shell>
<div class="space-y-5">
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">Payment configuration</p>
        <h1 class="mt-1 text-2xl font-black text-slate-950">Owner GCash settings</h1>
        <p class="mt-1 text-sm text-slate-500">Each owner can keep their own receiving account and API key. Admins can review all owner configurations.</p>
    </div>
    @if (session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('success') }}</div>@endif
    <div class="grid gap-4 lg:grid-cols-2">
        @forelse ($owners as $owner)
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4"><h2 class="font-bold text-slate-950">{{ $owner->name }}</h2><p class="text-xs text-slate-500">{{ $owner->email }} · {{ ucfirst($owner->role) }}</p></div>
                <form method="POST" action="{{ route(request()->routeIs('owner.*') ? 'owner.payment-settings.update' : 'admin.payment-settings.update') }}" class="space-y-3">
                    @csrf @method('PUT')
                    <input type="hidden" name="owner_id" value="{{ $owner->id }}">
                    <label class="block text-xs font-semibold text-slate-600">GCash account name<input name="gcash_account_name" value="{{ old('gcash_account_name', $owner->ownerProfile?->gcash_account_name) }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm"></label>
                    <label class="block text-xs font-semibold text-slate-600">GCash number<input name="gcash_number" value="{{ old('gcash_number', $owner->ownerProfile?->gcash_number) }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm"></label>
                    <label class="block text-xs font-semibold text-slate-600">GCash API key<input name="gcash_api_key" value="{{ old('gcash_api_key', $owner->ownerProfile?->gcash_api_key) }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm"></label>
                    <button class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700">Save settings</button>
                </form>
            </section>
        @empty
            <p class="text-sm text-slate-500">No owner profiles found.</p>
        @endforelse
    </div>
</div>
</x-admin.shell>
</x-layouts.dashboard>
