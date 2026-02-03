<x-layouts.caretaker>
<div class="max-w-5xl mx-auto px-6 py-6 space-y-4">
    <h1 class="text-2xl font-semibold text-slate-900">Validation Detail</h1>
    @if(session('status'))<div class="bg-emerald-50 text-emerald-700 px-4 py-2 rounded-lg">{{ session('status') }}</div>@endif
    <div class="card p-6 space-y-4">
        <div class="flex justify-between">
            <div>
                <p class="text-sm text-slate-500">Boarding House</p>
                <p class="text-lg font-semibold text-slate-900">{{ $task->boardingHouse->name ?? 'Boarding House' }}</p>
                <p class="text-sm text-slate-600">Validator: {{ $task->validator->name ?? '—' }}</p>
            </div>
            <span class="badge bg-amber-50 text-amber-700 border border-amber-100">{{ ucfirst($task->status) }}</span>
        </div>
        <div class="flex gap-2 flex-wrap">
            <form method="POST" action="{{ route('osas.validations.start',$task->id) }}">@csrf<button class="pill bg-emerald-50 text-emerald-700 border border-emerald-100">Start</button></form>
            <form method="POST" action="{{ route('osas.validations.save',$task->id) }}">@csrf<textarea name="notes" class="hidden">{{ optional($task->record)->notes }}</textarea><button class="pill bg-slate-100 text-slate-700 border border-slate-200">Save Draft</button></form>
            <form method="POST" action="{{ route('osas.validations.submit',$task->id) }}">@csrf<textarea name="notes" class="hidden">{{ optional($task->record)->notes }}</textarea><button class="pill bg-indigo-600 text-white">Submit Findings</button></form>
        </div>
    </div>

    <div class="card p-6 space-y-3">
        <h3 class="font-semibold">Findings</h3>
        <form method="POST" action="{{ route('osas.validations.finding',$task->id) }}" class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
            @csrf
            <input name="type" placeholder="Type (Safety)" class="border border-slate-200 rounded-lg px-3 py-2" required>
            <select name="severity" class="border border-slate-200 rounded-lg px-3 py-2" required>
                <option>Low</option><option>Moderate</option><option>Critical</option>
            </select>
            <input name="description" placeholder="Description" class="border border-slate-200 rounded-lg px-3 py-2 md:col-span-1" required>
            <button class="pill bg-indigo-600 text-white">Add Finding</button>
        </form>
        <div class="divide-y divide-slate-100">
            @foreach(optional($task->record)->findings ?? [] as $f)
                <div class="py-2">
                    <p class="font-semibold text-slate-900">{{ $f->type }} ({{ $f->severity }})</p>
                    <p class="text-sm text-slate-600">{{ $f->description }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <div class="card p-6 space-y-3">
        <h3 class="font-semibold">Evidence Upload</h3>
        <form method="POST" action="{{ route('osas.validations.evidence',$task->id) }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <input type="file" name="evidence" class="border border-slate-200 rounded-lg px-3 py-2" required>
            <button class="pill bg-indigo-600 text-white">Upload</button>
        </form>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            @foreach(optional($task->record)->evidence ?? [] as $e)
                <div class="p-3 rounded-lg border border-slate-200">
                    <p class="text-sm text-slate-700">{{ $e->type }}</p>
                    <a class="text-indigo-600 text-sm" href="{{ Storage::disk('public')->url($e->path) }}" target="_blank">View</a>
                </div>
            @endforeach
        </div>
    </div>
</div>
</x-layouts.caretaker>
