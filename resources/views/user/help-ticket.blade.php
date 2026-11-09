<x-layouts.dashboard>
<x-user.shell>
<div class="w-full min-w-0 space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-sm font-bold text-blue-600">Support ticket #SR-{{ str_pad((string) $supportRequest->id, 5, '0', STR_PAD_LEFT) }}</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $supportRequest->subject }}</h1>
            <p class="mt-1 text-base text-slate-500 dark:text-slate-400">{{ optional($supportRequest->created_at)->format('M d, Y h:i A') ?: 'Date unavailable' }}</p>
        </div>
        <a href="{{ route('user.help-center.index') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">Back to Help Center</a>
    </div>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4 dark:border-slate-800">
            <div><p class="text-sm font-semibold text-slate-500 dark:text-slate-400">{{ $supportRequest->concern_type }}</p><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Submitted by {{ $supportRequest->full_name }}</p></div>
            <span class="rounded-full bg-blue-50 px-3 py-1 text-sm font-bold text-blue-700 dark:bg-blue-400/10 dark:text-blue-300">{{ $supportRequest->status ?: 'Pending' }}</span>
        </div>
        <p class="whitespace-pre-line pt-5 text-base leading-7 text-slate-700 dark:text-slate-200">{{ $supportRequest->message }}</p>
        @if ($supportRequest->screenshot)
            <a class="mt-5 inline-flex text-sm font-bold text-blue-600 hover:text-blue-700" href="{{ asset('storage/'.$supportRequest->screenshot) }}" target="_blank" rel="noopener">View attachment</a>
        @endif
        <p class="mt-6 border-t border-slate-100 pt-4 text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400">Support replies and status updates will appear here when they are recorded by the authorized support team.</p>
    </section>
</div>
</x-user.shell>
</x-layouts.dashboard>
