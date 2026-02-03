<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="text-xl font-semibold text-gray-800">Boarding House Policies</h2>
            <p class="text-sm text-gray-500">Edit the policy categories in any locale without touching the Blade templates.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 space-y-1">
                    <div class="font-semibold">Please fix the following:</div>
                    <ul class="pl-4 list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white border border-gray-100 shadow-sm sm:rounded-lg p-6 space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Policy editor</h3>
                        <p class="text-sm text-gray-500">Pick a locale, paste in the category structure, and save. The tenant portal automatically displays the current locale’s copy.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.boarding-house-policies.update') }}" class="space-y-5">
                    @csrf

                    <div class="space-y-1">
                        <label class="text-sm font-medium text-gray-700" for="locale">Locale</label>
                        <input
                            id="locale"
                            name="locale"
                            type="text"
                            value="{{ old('locale', $locale) }}"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                            placeholder="e.g. en, es, fr"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium text-gray-700" for="categories_json">Categories (JSON)</label>
                        <textarea
                            id="categories_json"
                            name="categories_json"
                            rows="16"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 font-mono text-xs focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                        >{{ old('categories_json', json_encode($categories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
                        <p class="text-xs text-gray-500">
                            The structure is an array of entries with <code>title</code>, <code>description</code>, and <code>items</code> (array of strings).
                        </p>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                            Save policies
                        </button>
                        <a href="{{ route('admin.boarding-house-policies.index', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center justify-center rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Reset
                        </a>
                    </div>
                </form>

                <div>
                    <h4 class="text-sm font-semibold text-gray-800">Preview ({{ $locale }})</h4>
                    <div class="mt-2 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($categories as $category)
                            <article class="rounded-lg border border-gray-100 bg-gray-50 p-4 text-sm text-gray-700">
                                <div class="font-semibold text-gray-900">{{ $category['title'] ?? 'Untitled' }}</div>
                                <p class="text-xs text-gray-500">{{ $category['description'] ?? 'No description' }}</p>
                                <ul class="mt-2 space-y-1 list-disc pl-4">
                                    @foreach ($category['items'] ?? [] as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
