@php
    $headerIconPaths = [
        'bell' => '<path d="M15 17H9m9-5a6 6 0 1 0-12 0c0 3-1.5 4-2 5h16c-.5-1-2-2-2-5Z"/><path d="M10 20a2 2 0 0 0 4 0"/>',
        'question' => '<path d="M9.5 9a2.5 2.5 0 1 1 4 2c-1 .7-1.5 1.2-1.5 2.5"/><path d="M12 17h.01"/><circle cx="12" cy="12" r="9"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
    ];

    $headerIcon = fn ($name, $class = 'h-5 w-5') => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8">'.$headerIconPaths[$name].'</svg>';
@endphp

<x-admin.workspace-shell
    workspace="superduperadmin"
    title="Reports"
    subtitle="View performance and activity insights."
    profile-role-label="Owner"
    active="reports">

    <x-slot name="actions">
        <button type="button" class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50" aria-label="Notifications">
            {!! $headerIcon('bell', 'h-5 w-5') !!}
            <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[11px] font-bold text-white">3</span>
        </button>
        <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50" aria-label="Help">
            {!! $headerIcon('question', 'h-5 w-5') !!}
        </button>
        <button type="button" class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-left shadow-sm">
            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-700 text-xs font-bold text-white">JD</span>
            <span class="leading-tight">
                <span class="block text-sm font-semibold text-slate-950">Juan Dela Cruz</span>
                <span class="block text-xs text-slate-500">Owner</span>
            </span>
            <span class="text-slate-500">{!! $headerIcon('chevron-down', 'h-4 w-4') !!}</span>
        </button>
    </x-slot>

    @include('owner.reports._management', [
        'showPageHeader' => false,
    ])
</x-admin.workspace-shell>
