@props(['metric'])

@php
    $tones = [
        'emerald' => ['icon' => 'bg-emerald-50 text-emerald-700', 'line' => '#059669', 'wash' => 'from-emerald-50/70'],
        'blue' => ['icon' => 'bg-blue-50 text-blue-700', 'line' => '#2563eb', 'wash' => 'from-blue-50/70'],
        'cyan' => ['icon' => 'bg-cyan-50 text-cyan-700', 'line' => '#0891b2', 'wash' => 'from-cyan-50/70'],
        'indigo' => ['icon' => 'bg-indigo-50 text-indigo-700', 'line' => '#4f46e5', 'wash' => 'from-indigo-50/70'],
        'amber' => ['icon' => 'bg-amber-50 text-amber-700', 'line' => '#d97706', 'wash' => 'from-amber-50/70'],
        'rose' => ['icon' => 'bg-rose-50 text-rose-700', 'line' => '#e11d48', 'wash' => 'from-rose-50/70'],
        'slate' => ['icon' => 'bg-slate-100 text-slate-600', 'line' => '#64748b', 'wash' => 'from-slate-50'],
    ];
    $tone = $tones[$metric['tone'] ?? 'slate'];
    $values = collect($metric['sparkline'] ?? [])->map(fn ($value) => (float) $value)->values();
    $max = max((float) ($values->max() ?: 0), 1);
    $count = max($values->count() - 1, 1);
    $points = $values->map(fn ($value, $index) => round(($index / $count) * 100, 2).','.round(28 - (($value / $max) * 24), 2))->implode(' ');
    $trend = $metric['trend'] ?? null;
@endphp

<a
    href="{{ $metric['href'] }}"
    title="{{ $metric['tooltip'] }}"
    aria-label="{{ $metric['label'] }}: {{ $metric['value'] }}. {{ $metric['meta'] }}"
    class="group relative min-h-[8.25rem] overflow-hidden rounded-[1.15rem] border border-slate-200/80 bg-gradient-to-br {{ $tone['wash'] }} via-white to-white p-4 shadow-[0_10px_24px_rgba(15,23,42,0.045)] transition duration-200 hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-[0_15px_32px_rgba(15,23,42,0.08)] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
>
    <div class="flex items-start justify-between gap-3">
        <span class="flex h-8 w-8 items-center justify-center rounded-xl {{ $tone['icon'] }}">
            <x-owner.dashboard.icon :name="$metric['icon']" class="h-4 w-4" />
        </span>
        @if (! is_null($trend))
            <span class="inline-flex items-center rounded-full px-1.5 py-0.5 text-[9px] font-extrabold {{ $trend >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                {{ $trend >= 0 ? '+' : '' }}{{ $trend }}{{ $metric['icon'] === 'tenants' ? '' : '%' }}
            </span>
        @endif
    </div>
    <p class="mt-3 text-[10px] font-bold uppercase tracking-[0.1em] text-slate-500">{{ $metric['label'] }}</p>
    <p class="mt-1 text-[1.4rem] font-black leading-none tracking-tight text-slate-950">{{ $metric['value'] }}</p>
    <div class="mt-2 flex min-h-6 items-end gap-2">
        <p class="min-w-0 flex-1 text-[10px] leading-3.5 text-slate-500">{{ $metric['meta'] }}</p>
        @if ($values->isNotEmpty())
            <svg class="h-6 w-14 shrink-0 overflow-visible" viewBox="0 0 100 30" preserveAspectRatio="none" aria-hidden="true">
                <polyline points="{{ $points }}" fill="none" stroke="{{ $tone['line'] }}" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke"/>
            </svg>
        @endif
    </div>
</a>
