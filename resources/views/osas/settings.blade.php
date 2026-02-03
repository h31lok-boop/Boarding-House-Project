<x-layouts.caretaker>
<div class="max-w-4xl mx-auto px-6 py-6 space-y-4">
    <h1 class="text-2xl font-semibold text-slate-900">Settings</h1>
    <div class="card p-6 space-y-3">
        <form class="space-y-3">
            <div>
                <label class="text-xs text-slate-500">Notification Email</label>
                <input type="email" class="w-full border border-slate-200 rounded-lg px-3 py-2" value="osas@staysafe.com" />
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" checked class="h-4 w-4" />
                <span class="text-sm text-slate-700">Enable alerts for validations and accreditation changes</span>
            </div>
            <button class="pill bg-indigo-600 text-white" type="button">Save Preferences</button>
        </form>
    </div>
</div>
</x-layouts.caretaker>
