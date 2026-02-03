<x-layouts.caretaker>
<x-caretaker.shell>
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold text-slate-900">Reports & Analytics</h1>
        @if(session('status'))<div class="bg-emerald-50 text-emerald-700 px-4 py-2 rounded-lg">{{ session('status') }}</div>@endif
        <div class="bg-white rounded-2xl shadow p-5 space-y-4">
            <form method="POST" action="{{ route('caretaker.reports.generate') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                @csrf
                <div>
                    <label class="text-xs text-slate-500">Date Range</label>
                    <input type="date" name="from" class="w-full border border-slate-200 rounded-lg px-3 py-2" />
                </div>
                <div>
                    <label class="text-xs text-slate-500">To</label>
                    <input type="date" name="to" class="w-full border border-slate-200 rounded-lg px-3 py-2" />
                </div>
                <div class="flex items-end">
                    <button class="px-4 py-2 rounded-full bg-indigo-600 text-white text-sm font-semibold">Generate Report</button>
                </div>
            </form>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-slate-700">
                <div class="bg-white rounded-2xl shadow border border-slate-100 p-4">
                    <p class="text-xs uppercase text-slate-500">Occupancy Rate</p>
                    <p class="text-2xl font-bold">78%</p>
                </div>
                <div class="bg-white rounded-2xl shadow border border-slate-100 p-4">
                    <p class="text-xs uppercase text-slate-500">Maintenance Requests</p>
                    <p class="text-2xl font-bold">24</p>
                </div>
                <div class="bg-white rounded-2xl shadow border border-slate-100 p-4">
                    <p class="text-xs uppercase text-slate-500">Incidents This Month</p>
                    <p class="text-2xl font-bold">5</p>
                </div>
            </div>
        </div>
    </div>
</x-caretaker.shell>
</x-layouts.caretaker>
