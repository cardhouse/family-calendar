<div class="rounded-3xl border border-slate-800 bg-slate-900/70 p-6">
    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-800 text-slate-200">
                <span class="text-lg font-semibold">AM</span>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Current time</p>
                <p class="text-3xl font-semibold text-slate-100" x-data="DashboardTime.clock()" x-text="time"></p>
            </div>
        </div>

        <div class="flex flex-col gap-2">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Next departure</p>
            @if ($nextDeparture)
                <div class="text-2xl font-semibold text-slate-100">{{ $nextDeparture['label'] }}</div>
                @if (count($nextDeparture['labels']) > 1)
                    <div class="text-sm text-slate-400">
                        {{ implode(' · ', $nextDeparture['labels']) }}
                    </div>
                @endif
                <div
                    x-data="DashboardTime.countdown({{ $nextDeparture['timestamp']->timestamp }})"
                    :class="urgencyClass"
                    class="text-2xl font-semibold"
                    x-text="timeLeft"
                ></div>
            @else
                <div class="text-lg text-slate-400">No departures scheduled.</div>
            @endif
        </div>

        <div class="flex flex-col gap-2">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Weather</p>
            <div class="rounded-full border border-slate-800 bg-slate-900/60 px-4 py-2 text-sm text-slate-300">
                Weather coming soon
            </div>
        </div>
    </div>
</div>
