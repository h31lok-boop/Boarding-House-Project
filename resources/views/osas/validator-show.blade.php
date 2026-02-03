<x-layouts.caretaker>
@php($r = fn($name,$params=[]) => \Illuminate\Support\Facades\Route::has($name) ? route($name,$params) : url()->current())
<x-osas.shell>
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold">Validator Profile</h1>
        <div class="ui-card p-5 space-y-4">
            <div class="flex items-center gap-4">
                <img src="https://i.pravatar.cc/80?u={{ $validator->id }}" class="h-16 w-16 rounded-full" alt="{{ $validator->name }}">
                <div>
                    <p class="text-lg font-semibold">{{ $validator->name }}</p>
                    <p class="text-sm ui-muted">{{ $validator->email }}</p>
                </div>
            </div>
        </div>
        <div class="ui-card overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Task</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($tasks as $t)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-semibold">{{ $t->boardingHouse->name ?? 'Boarding House' }}</td>
                            <td class="px-4 py-3 ui-muted">{{ ucfirst($t->status) }}</td>
                            <td class="px-4 py-3"><a href="{{ route('osas.validations.show',$t->id) }}" class="text-indigo-600 text-sm">Open</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-osas.shell>
</x-layouts.caretaker>
