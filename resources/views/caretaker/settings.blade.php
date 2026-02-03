<x-layouts.caretaker>
<x-caretaker.shell>
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold text-slate-900">Settings</h1>
        <div class="bg-white rounded-2xl shadow p-5 space-y-3">
            <form class="space-y-3">
                <div>
                    <label class="text-xs text-slate-500">Notification Email</label>
                    <input type="email" class="w-full border border-slate-200 rounded-lg px-3 py-2" value="caretaker@staysafe.com" />
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" checked class="h-4 w-4" />
                    <span class="text-sm text-slate-700">Enable alerts for maintenance and incidents</span>
                </div>
                <button class="px-4 py-2 rounded-full bg-indigo-600 text-white text-sm font-semibold" type="button">Save Preferences</button>
            </form>
        </div>
    </div>
</x-caretaker.shell>
</x-layouts.caretaker>
