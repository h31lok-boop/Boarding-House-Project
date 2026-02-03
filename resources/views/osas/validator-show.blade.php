<x-layouts.caretaker>
<div class="max-w-5xl mx-auto px-6 py-6 space-y-4">
    <h1 class="text-2xl font-semibold text-slate-900">Validator Profile</h1>
    @if(session('status'))<div class="bg-emerald-50 text-emerald-700 px-4 py-2 rounded-lg">{{ session('status') }}</div>@endif
    <div class="card p-6 space-y-3">
        <p class="text-lg font-semibold">{{ $validator->name }}</p>
        <p class="text-sm text-slate-600">{{ $validator->email }}</p>
        <p class="text-sm text-slate-600">Status: {{ $validator->is_active ? 'Active':'Disabled' }}</p>
        <form method="POST" action="{{ route('osas.validators.toggle',$validator->id) }}">@csrf<button class="pill bg-amber-50 text-amber-700">Toggle Active</button></form>
    </div>
    <div class="card p-6 space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold">Assigned Tasks</h3>
            <button class="pill bg-indigo-600 text-white" onclick="document.getElementById('assignForm').scrollIntoView()">Assign</button>
        </div>
        <div class="divide-y divide-slate-100">
            @foreach($tasks as $t)
                <div class="py-3 flex justify-between items-center">
                    <div>
                        <p class="font-semibold text-slate-900">{{ $t->boardingHouse->name ?? 'Boarding House' }}</p>
                        <p class="text-xs text-slate-500">Status: {{ ucfirst($t->status) }} | Priority: {{ $t->priority }}</p>
                    </div>
                    <a href="{{ route('osas.validationShow',$t->id) }}" class="text-indigo-600 text-sm">Open</a>
                </div>
            @endforeach
        </div>
    </div>
    <div id="assignForm" class="card p-6 space-y-3">
        <h3 class="font-semibold">Assign Task</h3>
        <form method="POST" action="{{ route('osas.validators.assign',$validator->id) }}" class="space-y-3">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <select name="boarding_house_id" class="border border-slate-200 rounded-lg px-3 py-2" required>
                    @foreach(\App\Models\BoardingHouse::all() as $house)
                        <option value="{{ $house->id }}">{{ $house->name }}</option>
                    @endforeach
                </select>
                <input type="date" name="scheduled_at" class="border border-slate-200 rounded-lg px-3 py-2">
                <select name="priority" class="border border-slate-200 rounded-lg px-3 py-2">
                    <option>High</option>
                    <option selected>Normal</option>
                    <option>Low</option>
                </select>
            </div>
            <button class="pill bg-indigo-600 text-white">Assign Task</button>
        </form>
    </div>
</div>
</x-layouts.caretaker>
