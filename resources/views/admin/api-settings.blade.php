<x-layouts.dashboard>
<x-admin.shell>
@php
    $fieldBase = 'w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-900 outline-none transition placeholder:font-normal placeholder:text-slate-400 focus:border-blue-400 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-blue-500 dark:focus:ring-blue-500/10';
@endphp

<div
    x-data="{ activeGroup: 'ai', saving: false, visibleSecrets: {} }"
    class="mx-auto w-full max-w-7xl space-y-4"
>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-950">
        <div class="flex flex-col gap-4 border-b border-slate-200 p-5 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-blue-600 dark:text-blue-400">Administrator only</p>
                <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950 dark:text-white">API & integration settings</h1>
                <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-500 dark:text-slate-400">Edit the services used across BoardMatch. Saved values are encrypted in the database and override <code>.env</code>; removing an override restores the server value.</p>
            </div>
            <div class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                Encrypted storage
            </div>
        </div>

        <div class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4" role="tablist" aria-label="Integration groups">
            @foreach ($integrationGroups as $group)
                <button
                    type="button"
                    role="tab"
                    @click="activeGroup = '{{ $group['key'] }}'"
                    :aria-selected="activeGroup === '{{ $group['key'] }}'"
                    class="rounded-xl border px-4 py-3 text-left transition"
                    :class="activeGroup === '{{ $group['key'] }}' ? 'border-blue-600 bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-blue-200 hover:bg-blue-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-blue-500/40 dark:hover:bg-blue-500/10'"
                >
                    <span class="flex items-center justify-between gap-2">
                        <span class="block text-sm font-black">{{ $group['title'] }}</span>
                        <span class="inline-flex h-2.5 w-2.5 shrink-0 rounded-full {{ $group['runtime_status']['active'] ? 'bg-emerald-400' : 'bg-slate-400' }}" aria-hidden="true"></span>
                    </span>
                    <span class="mt-1 block text-[10px] font-bold uppercase tracking-wide opacity-80">{{ $group['runtime_status']['label'] }}</span>
                </button>
            @endforeach
        </div>
    </section>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-400/20 dark:bg-rose-400/10 dark:text-rose-200">
            <p class="font-black">The integration settings could not be saved.</p>
            <ul class="mt-1 list-disc space-y-0.5 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.api-settings.update') }}" @submit="saving = true" class="space-y-4">
        @csrf
        @method('PUT')

        @foreach ($integrationGroups as $group)
            <section
                x-show="activeGroup === '{{ $group['key'] }}'"
                x-cloak
                role="tabpanel"
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-950"
            >
                <header class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-black text-slate-950 dark:text-white">{{ $group['title'] }}</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $group['description'] }}</p>
                    </div>
                    <button
                        type="button"
                        aria-disabled="true"
                        title="{{ $group['runtime_status']['detail'] }}"
                        class="inline-flex h-10 shrink-0 cursor-default items-center justify-center gap-2 self-start rounded-xl border px-3.5 text-xs font-black sm:self-center {{ $group['runtime_status']['active'] ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300' : 'border-slate-200 bg-slate-100 text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400' }}"
                        data-integration-runtime-status
                    >
                        <span class="h-2 w-2 rounded-full {{ $group['runtime_status']['active'] ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                        {{ $group['runtime_status']['label'] }}
                    </button>
                </header>

                <div class="grid gap-4 p-5 md:grid-cols-2">
                    @foreach ($group['fields'] as $field)
                        @php
                            $fieldName = 'settings['.$field['key'].']';
                            $oldValue = old('settings.'.$field['key'], $field['value']);
                            $isBoolean = $field['type'] === 'boolean';
                            $isSecret = (bool) ($field['secret'] ?? false);
                        @endphp

                        <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-900/60 {{ $isBoolean ? 'md:col-span-1' : '' }}">
                            <div class="mb-2 flex min-w-0 items-start justify-between gap-3">
                                <label for="integration_{{ $field['key'] }}" class="text-xs font-black text-slate-700 dark:text-slate-200">{{ $field['label'] }}</label>
                                <span class="shrink-0 rounded-full px-2 py-1 text-[9px] font-black uppercase tracking-wide {{ $field['has_override'] ? 'bg-blue-100 text-blue-700 dark:bg-blue-400/15 dark:text-blue-300' : 'bg-slate-200 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">{{ $field['source'] }}</span>
                            </div>

                            @if ($isBoolean)
                                <input type="hidden" name="{{ $fieldName }}" value="0">
                                <label class="flex min-h-11 cursor-pointer items-center justify-between rounded-xl border border-slate-200 bg-white px-3.5 dark:border-slate-700 dark:bg-slate-900">
                                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">{{ $field['configured'] ? 'Currently enabled' : 'Currently disabled' }}</span>
                                    <input id="integration_{{ $field['key'] }}" type="checkbox" name="{{ $fieldName }}" value="1" @checked((bool) $oldValue) class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800">
                                </label>
                            @elseif ($field['type'] === 'select')
                                <select id="integration_{{ $field['key'] }}" name="{{ $fieldName }}" class="{{ $fieldBase }}">
                                    @foreach ($field['options'] as $value => $label)
                                        <option value="{{ $value }}" @selected((string) $oldValue === (string) $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            @elseif ($isSecret)
                                <div class="relative">
                                    <input
                                        id="integration_{{ $field['key'] }}"
                                        name="{{ $fieldName }}"
                                        :type="visibleSecrets['{{ $field['key'] }}'] ? 'text' : 'password'"
                                        value=""
                                        autocomplete="new-password"
                                        class="{{ $fieldBase }} pr-20"
                                        placeholder="{{ $field['configured'] ? 'Configured — enter a replacement only' : 'Enter credential' }}"
                                    >
                                    <button type="button" @click="visibleSecrets['{{ $field['key'] }}'] = !visibleSecrets['{{ $field['key'] }}']" class="absolute inset-y-1 right-1 rounded-lg px-3 text-[11px] font-bold text-slate-500 transition hover:bg-slate-100 hover:text-slate-800 dark:hover:bg-slate-800 dark:hover:text-white" x-text="visibleSecrets['{{ $field['key'] }}'] ? 'Hide' : 'Show'"></button>
                                </div>
                            @else
                                <input
                                    id="integration_{{ $field['key'] }}"
                                    name="{{ $fieldName }}"
                                    type="{{ in_array($field['type'], ['url', 'email', 'number'], true) ? $field['type'] : 'text' }}"
                                    value="{{ $oldValue }}"
                                    @if(isset($field['step'])) step="{{ $field['step'] }}" @endif
                                    class="{{ $fieldBase }}"
                                >
                            @endif

                            @if (isset($field['help']))
                                <p class="mt-2 text-[11px] leading-5 text-slate-500 dark:text-slate-400">{{ $field['help'] }}</p>
                            @elseif ($isSecret)
                                <p class="mt-2 text-[11px] leading-5 text-slate-500 dark:text-slate-400">Existing credentials are never displayed. Leave blank to keep the current value.</p>
                            @endif

                            @if ($field['has_override'])
                                <label class="mt-3 flex cursor-pointer items-center gap-2 text-[11px] font-bold text-rose-600 dark:text-rose-300">
                                    <input type="hidden" name="reset[{{ $field['key'] }}]" value="0">
                                    <input type="checkbox" name="reset[{{ $field['key'] }}]" value="1" class="h-4 w-4 rounded border-rose-300 text-rose-600 focus:ring-rose-500 dark:border-rose-700 dark:bg-slate-900">
                                    Remove saved override and use .env
                                </label>
                            @endif

                            @error('settings.'.$field['key'])
                                <p class="mt-2 text-xs font-bold text-rose-600 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        <div class="sticky bottom-3 z-20 flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white/95 p-3 shadow-xl shadow-slate-900/10 backdrop-blur dark:border-slate-700 dark:bg-slate-950/95">
            <p class="hidden text-xs text-slate-500 dark:text-slate-400 sm:block">Changes apply to all roles and all future integration requests.</p>
            <button type="submit" :disabled="saving" class="ml-auto inline-flex h-11 items-center justify-center rounded-xl bg-blue-600 px-5 text-sm font-black text-white shadow-md shadow-blue-600/25 transition hover:bg-blue-700 disabled:cursor-wait disabled:opacity-60">
                <span x-show="!saving">Save API settings</span>
                <span x-show="saving" x-cloak>Saving securely...</span>
            </button>
        </div>
    </form>
</div>
</x-admin.shell>
</x-layouts.dashboard>
