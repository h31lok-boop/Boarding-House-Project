<x-layouts.caretaker>
<x-tenant.shell>
  <div class="ui-card p-4 mb-6">
    <div class="flex flex-col gap-1">
      <h2 class="text-xl font-semibold ">Boarding House Policies</h2>
      <p class="text-sm ui-muted">Review the guidelines that keep the boarding house safe, clean, and fair for everyone.</p>
    </div>
  </div>

  

  <div class="space-y-6">
    <div class="ui-surface border ui-border shadow-sm sm:rounded-lg p-6">
      <h3 class="text-lg font-semibold ">Living well together</h3>
      <p class="mt-2 text-sm ui-muted">
        These policies cover noise expectations, safety procedures, payment reminders, and how to request support.
      </p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      @foreach ($policyCategories as $category)
        <article class="ui-surface border ui-border shadow-sm rounded-lg p-5 space-y-3">
          <header class="text-xs uppercase tracking-wide ui-muted">
            {{ $category['title'] }}
          </header>
          <p class="text-sm ui-muted">{{ $category['description'] }}</p>
          <ul class="mt-3 space-y-2 text-sm ui-muted list-disc list-inside">
            @foreach ($category['items'] as $item)
              <li>{{ $item }}</li>
            @endforeach
          </ul>
        </article>
      @endforeach
    </div>
  </div>
</x-tenant.shell>
</x-layouts.caretaker>
