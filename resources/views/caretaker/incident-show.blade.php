<x-layouts.caretaker>
<x-caretaker.shell>
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold text-slate-900">Incident {{ $incident['id'] }}</h1>
        @if(session('status'))<div class="bg-emerald-50 text-emerald-700 px-4 py-2 rounded-lg">{{ session('status') }}</div>@endif
        <div class="bg-white rounded-2xl shadow p-6 space-y-3">
            <p class="text-sm text-slate-600">Room: {{ $incident['room'] }}</p>
            <p class="text-sm text-slate-600">Tenant: {{ $incident['tenant'] ?? 'N/A' }}</p>
            <p class="text-sm text-slate-600">Floor: {{ $incident['floor'] }}</p>
            <p class="text-sm text-slate-600">Reported: {{ $incident['date'] }}</p>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('caretaker.incidents.update', $incident['id']) }}">@csrf<button class="px-4 py-2 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-sm">Update</button></form>
                <form method="POST" action="{{ route('caretaker.incidents.resolve', $incident['id']) }}">@csrf<button class="px-4 py-2 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 text-sm">Resolve</button></form>
            </div>
        </div>
    </div>
</x-caretaker.shell>
</x-layouts.caretaker>
