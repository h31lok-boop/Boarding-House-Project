@if ($reservationCompatibility)
    <section data-reservation-compatibility aria-labelledby="reservation-compatibility-title" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/50 dark:border-slate-800 dark:bg-slate-950 sm:p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="min-w-0">
                <h2 id="reservation-compatibility-title" class="text-sm font-bold text-slate-950 dark:text-white">Your Compatibility with This Property</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $activeHouse->name }} · Based on your current saved preferences.</p>
            </div>
            @if ($reservationCompatibility['breakdown'])
                <div class="rounded-xl bg-blue-50 px-4 py-2 text-right text-blue-700 dark:bg-blue-400/10 dark:text-blue-200">
                    <p class="text-2xl font-bold tabular-nums" data-reservation-match-score>{{ $reservationCompatibility['recommendation_percent'] }}%</p>
                    <p class="text-xs font-semibold">{{ $reservationCompatibility['match_label'] }}</p>
                </div>
            @endif
        </div>

        @if ($reservationCompatibility['breakdown'])
            <a href="#reservation-match-breakdown" class="mt-4 inline-flex text-xs font-semibold text-blue-700 hover:underline dark:text-blue-300">View Score Breakdown &rarr;</a>
        @else
            <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">Save your preferences to see a personalized compatibility score and breakdown.</p>
        @endif
        <a href="{{ route('user.preferences.index') }}" class="mt-3 inline-flex text-xs font-semibold text-blue-700 hover:underline dark:text-blue-300">Update My Preferences</a>
    </section>
@endif
