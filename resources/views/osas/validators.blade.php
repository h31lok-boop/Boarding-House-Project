<x-layouts.caretaker>
@php($r = fn($name,$params=[]) => \Illuminate\Support\Facades\Route::has($name) ? route($name,$params) : url()->current())
<div class="max-w-6xl mx-auto px-6 py-6 space-y-4">
    <h1 class="text-2xl font-semibold text-slate-900">Validator Accounts</h1>
    @if(session('status'))<div class="bg-emerald-50 text-emerald-700 px-4 py-2 rounded-lg">{{ session('status') }}</div>@endif
    <div class="card overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Validator</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Tasks</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($validators as $v)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-semibold text-slate-900"><a href="{{ $r('osas.validators.show',$v->id) }}" class="text-indigo-600 hover:underline">{{ $v->name }}</a></td>
                        <td class="px-4 py-3 text-slate-700">{{ $v->email }}</td>
                        <td class="px-4 py-3"><span class="badge {{ $v->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-100':'bg-slate-100 text-slate-700 border border-slate-200' }}">{{ $v->is_active ? 'Active':'Disabled' }}</span></td>
                        <td class="px-4 py-3 text-slate-700">{{ $v->validationTasks()->count() }}</td>
                        <td class="px-4 py-3 space-x-2">
                            <a href="{{ $r('osas.validators.show',$v->id) }}" class="pill bg-slate-100 text-slate-700 text-xs">View</a>
                            <form method="POST" action="{{ $r('osas.validators.toggle',$v->id) }}" class="inline">@csrf<button class="pill bg-amber-50 text-amber-700 text-xs">{{ $v->is_active ? 'Disable':'Enable' }}</button></form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</x-layouts.caretaker>
