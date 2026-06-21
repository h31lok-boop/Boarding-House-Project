@props([
    'name',
    'icon' => 'sparkles',
    'selected' => false,
])

<label
    class="group relative flex min-h-[112px] cursor-pointer flex-col justify-between rounded-lg border p-4 transition hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-sm focus-within:ring-4 focus-within:ring-blue-100 dark:hover:border-blue-500/50 dark:focus-within:ring-blue-500/20 {{ $selected ? 'border-blue-500 bg-blue-50 text-blue-950 dark:border-blue-400 dark:bg-blue-500/10 dark:text-white' : 'border-slate-200 bg-white text-slate-800 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100' }}"
    :class="selectedAmenities.includes(@js($name)) ? 'border-blue-500 bg-blue-50 text-blue-950 shadow-sm shadow-blue-500/10 dark:border-blue-400 dark:bg-blue-500/10 dark:text-white' : 'border-slate-200 bg-white text-slate-800 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100'"
>
    <input type="checkbox" name="amenities[]" value="{{ $name }}" x-model="selectedAmenities" class="sr-only" @checked($selected)>

    <span class="flex items-start justify-between gap-3">
        <span
            class="flex h-11 w-11 items-center justify-center rounded-lg transition"
            :class="selectedAmenities.includes(@js($name)) ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/20' : 'bg-slate-100 text-slate-500 group-hover:bg-blue-50 group-hover:text-blue-600 dark:bg-slate-900 dark:text-slate-400 dark:group-hover:bg-blue-500/10 dark:group-hover:text-blue-300'"
        >
            <x-user.preference-icon :name="$icon" class="h-5 w-5" />
        </span>

        <span
            class="flex h-6 w-6 items-center justify-center rounded-full border transition"
            :class="selectedAmenities.includes(@js($name)) ? 'border-blue-600 bg-blue-600 text-white dark:border-blue-400 dark:bg-blue-400 dark:text-slate-950' : 'border-slate-200 bg-white text-transparent dark:border-slate-700 dark:bg-slate-950'"
        >
            <x-user.preference-icon name="check-circle" class="h-4 w-4" />
        </span>
    </span>

    <span class="mt-4 text-sm font-black leading-5">{{ $name }}</span>
</label>
