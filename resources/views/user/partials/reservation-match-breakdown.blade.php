@if ($reservationCompatibility && $reservationCompatibility['breakdown'])
    <section id="reservation-match-breakdown" aria-labelledby="reservation-match-breakdown-title" class="scroll-mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/50 dark:border-slate-800 dark:bg-slate-950">
        <h2 id="reservation-match-breakdown-title" class="text-sm font-bold text-slate-950 dark:text-white">Why This Matches You</h2>
        <p class="mt-3 text-xs leading-5 text-slate-500 dark:text-slate-400">Each factor score is multiplied by its weight, then added to produce your overall percentage. This reflects the property's current listing and availability, including any changes since you reserved.</p>
        <div class="mt-4 grid gap-4">
            @foreach ($reservationCompatibility['breakdown'] as $criterion => $factor)
                @php
                    $factorPercent = round($factor['score'] * 100, 1);
                    $weightPercent = round($factor['weight'] * 100, 1);
                    $contribution = round($factor['weighted_score'] * 100, 2);
                @endphp
                <div data-match-factor="{{ $criterion }}" class="rounded-xl bg-slate-50 p-3 dark:bg-slate-900">
                    <div class="flex items-start justify-between gap-3 text-xs">
                        <span id="reservation-factor-{{ $criterion }}" class="font-semibold text-slate-900 dark:text-white">{{ $factor['label'] }} <span class="font-normal text-slate-500 dark:text-slate-400">({{ $weightPercent }}% weight)</span></span>
                        <span class="font-bold tabular-nums text-blue-700 dark:text-blue-300">{{ $factorPercent }}%</span>
                    </div>
                    <div role="progressbar" aria-labelledby="reservation-factor-{{ $criterion }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $factorPercent }}" class="mt-2 h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                        <div class="h-full rounded-full bg-blue-600" style="width: {{ $factorPercent }}%"></div>
                    </div>
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Contributes {{ $contribution }} of {{ $weightPercent }} possible points.</p>
                </div>
            @endforeach
        </div>
        <ul class="mt-4 list-disc space-y-1 pl-5 text-sm text-slate-600 dark:text-slate-300">
            @foreach ($reservationCompatibility['reasons'] as $reason)
                <li>{{ $reason }}</li>
            @endforeach
        </ul>
        @if ($reservationCompatibility['warnings'])
            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
                <p class="font-semibold">Things to consider</p>
                <ul class="mt-1 list-disc space-y-1 pl-5">
                    @foreach ($reservationCompatibility['warnings'] as $warning)
                        <li>{{ $warning }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </section>
@endif
