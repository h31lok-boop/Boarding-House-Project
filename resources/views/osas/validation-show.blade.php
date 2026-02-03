<x-layouts.caretaker>
@php($r = fn($name,$params=[]) => \Illuminate\Support\Facades\Route::has($name) ? route($name,$params) : url()->current())
<x-osas.shell>
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold">Validation Record</h1>
        <div class="ui-card p-6 space-y-4">
            <h3 class="text-lg font-semibold">{{ $task->boardingHouse->name ?? 'Boarding House' }}</h3>
            <p class="ui-muted">Validator: {{ $task->validator->name ?? '—' }}</p>
            <p class="ui-muted">Status: {{ ucfirst($task->status) }}</p>
            <form method="POST" action="{{ route('osas.validations.start', $task->id) }}">@csrf<button class="btn-primary">Start Validation</button></form>
        </div>
        <div class="ui-card p-6 space-y-4">
            <h3 class="text-lg font-semibold">Findings</h3>
            <form method="POST" action="{{ route('osas.validations.finding', $task->id) }}" class="space-y-3">@csrf
                <input name="type" class="ui-input" placeholder="Finding type" required>
                <input name="severity" class="ui-input" placeholder="Severity" required>
                <textarea name="description" class="ui-input" rows="3" placeholder="Description" required></textarea>
                <button class="btn-primary">Add Finding</button>
            </form>
        </div>
        <div class="ui-card p-6 space-y-4">
            <h3 class="text-lg font-semibold">Evidence</h3>
            <form method="POST" action="{{ route('osas.validations.evidence', $task->id) }}" enctype="multipart/form-data">@csrf
                <input type="file" name="evidence" class="ui-input" required>
                <button class="btn-primary">Upload</button>
            </form>
        </div>
        <div class="ui-card p-6">
            <form method="POST" action="{{ route('osas.validations.submit', $task->id) }}">@csrf
                <button class="btn-primary">Submit Findings</button>
            </form>
        </div>
    </div>
</x-osas.shell>
</x-layouts.caretaker>
