<x-layouts.caretaker>
<x-caretaker.shell>
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold text-slate-900">Tenant Profile</h1>
        @if(session('status'))<div class="bg-emerald-50 text-emerald-700 px-4 py-2 rounded-lg">{{ session('status') }}</div>@endif
        <div class="bg-white rounded-2xl shadow p-6 space-y-4">
            <div class="flex gap-4 items-center">
                <img src="https://i.pravatar.cc/96?u={{ $tenant['id'] }}" class="h-16 w-16 rounded-full" alt="{{ $tenant['name'] }}" />
                <div>
                    <p class="text-lg font-semibold text-slate-900">{{ $tenant['name'] }}</p>
                    <p class="text-sm text-slate-600">{{ $tenant['email'] }}</p>
                    <p class="text-sm text-slate-600">{{ $tenant['phone'] }}</p>
                </div>
            </div>
            <p class="text-slate-700">Room / Unit: {{ $tenant['room'] }}</p>
            <div class="flex flex-wrap gap-2">
                <form method="POST" action="{{ route('caretaker.tenants.checkin', $tenant['id']) }}">@csrf<button class="px-4 py-2 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 text-sm">Check-in</button></form>
                <form method="POST" action="{{ route('caretaker.tenants.checkout', $tenant['id']) }}">@csrf<button class="px-4 py-2 rounded-full bg-rose-50 text-rose-700 border border-rose-100 text-sm">Check-out</button></form>
                <form method="POST" action="{{ route('caretaker.tenants.update', $tenant['id']) }}">@csrf<button class="px-4 py-2 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100 text-sm">Update Info</button></form>
            </div>
        </div>
    </div>
</x-caretaker.shell>
</x-layouts.caretaker>
