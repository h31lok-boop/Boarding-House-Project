<x-layouts.caretaker>
<x-caretaker.shell>
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold text-slate-900">Notices & Announcements</h1>
        @if(session('status'))<div class="bg-emerald-50 text-emerald-700 px-4 py-2 rounded-lg">{{ session('status') }}</div>@endif

        <div class="bg-white rounded-2xl shadow p-5 space-y-3">
            <h3 class="font-semibold text-slate-900">Send Notice</h3>
            <form method="POST" action="{{ route('caretaker.notices.store') }}" class="space-y-3">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <input name="title" required placeholder="Title" class="border border-slate-200 rounded-lg px-3 py-2 w-full" />
                    <select name="audience" class="border border-slate-200 rounded-lg px-3 py-2 w-full">
                        <option value="all">All Tenants</option>
                        <option value="floor">Specific Floor</option>
                        <option value="room">Specific Room</option>
                    </select>
                </div>
                <textarea name="message" rows="3" class="border border-slate-200 rounded-lg px-3 py-2 w-full" placeholder="Message"></textarea>
                <button class="px-4 py-2 rounded-full bg-indigo-600 text-white text-sm font-semibold">Send Notice</button>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Notice</th>
                    <th class="px-4 py-3 text-left">Audience</th>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($notices as $n)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-semibold text-slate-900">{{ $n['title'] }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $n['audience'] }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $n['date'] }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-1 text-xs rounded-full font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">{{ $n['status'] }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    </div>
</x-caretaker.shell>
</x-layouts.caretaker>
