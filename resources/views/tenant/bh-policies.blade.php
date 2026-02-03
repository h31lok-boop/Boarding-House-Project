<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="text-xl font-semibold text-gray-800">Boarding House Policies</h2>
            <p class="text-sm text-gray-500">Review the guidelines that keep the boarding house safe, clean, and fair for everyone.</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="bg-white border border-gray-100 shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900">Living well together</h3>
            <p class="mt-2 text-sm text-gray-600">
                These policies cover noise expectations, safety procedures, payment reminders, and how to request support.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($policyCategories as $category)
                <article class="bg-white border border-gray-100 shadow-sm rounded-lg p-5 space-y-3">
                    <header class="text-xs uppercase tracking-wide text-gray-500">
                        {{ $category['title'] }}
                    </header>
                    <p class="text-sm text-gray-600">{{ $category['description'] }}</p>
                    <ul class="mt-3 space-y-2 text-sm text-gray-600 list-disc list-inside">
                        @foreach ($category['items'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </article>
            @endforeach
        </div>
    </div>
</x-app-layout>
