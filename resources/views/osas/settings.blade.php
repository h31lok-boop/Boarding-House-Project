<x-layouts.caretaker>
@php($r = fn($name,$params=[]) => \Illuminate\Support\Facades\Route::has($name) ? route($name,$params) : url()->current())
<x-osas.shell>
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold">Settings</h1>
        <div class="ui-card p-6">
            <p class="ui-muted">Settings panel is ready. Add notification and profile preferences here.</p>
        </div>
    </div>
</x-osas.shell>
</x-layouts.caretaker>
