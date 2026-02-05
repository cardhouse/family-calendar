<?php

use Livewire\Component;

return new class extends Component
{
    /**
     * @var array<string, mixed>|null
     */
    public ?array $nextDeparture = null;
};
?>

<div class="grid gap-4 md:grid-cols-3">
    {{-- Clock Panel --}}
    <div class="flex items-center gap-4 rounded-2xl border border-dash-border bg-dash-card p-5">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/10 text-blue-400">
            <flux:icon name="clock" variant="outline" class="size-6" />
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Right now</p>
            <p class="text-3xl font-extrabold text-slate-100 md:text-4xl" x-data="DashboardTime.clock()" x-text="time"></p>
        </div>
    </div>

    {{-- Departure Countdown Panel --}}
    <div class="flex flex-col justify-center rounded-2xl border border-dash-border bg-dash-card p-5">
        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Next departure</p>
        @if ($nextDeparture)
            <div class="mt-2 flex items-center gap-2">
                <flux:icon name="truck" variant="outline" class="size-5 text-amber-400" />
                <span class="text-lg font-extrabold text-slate-100">{{ $nextDeparture['label'] }}</span>
            </div>
            @if (count($nextDeparture['labels']) > 1)
                <div class="mt-1 text-sm text-slate-400">
                    {{ implode(' · ', $nextDeparture['labels']) }}
                </div>
            @endif
            <div
                x-data="DashboardTime.countdown({{ $nextDeparture['timestamp']->timestamp }})"
                :class="urgencyClass"
                class="mt-2 text-4xl font-black md:text-5xl"
                x-text="timeLeft"
            ></div>
        @else
            <div class="mt-2 text-lg text-slate-400">No departures scheduled.</div>
        @endif
    </div>

    {{-- Weather Panel --}}
    <div class="flex items-center gap-4 rounded-2xl border border-dash-border bg-dash-card p-5">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-sky-500/10 text-sky-400">
            <flux:icon name="cloud" variant="outline" class="size-6" />
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Weather</p>
            <p class="mt-1 text-sm font-semibold text-slate-300">Coming soon</p>
        </div>
    </div>
</div>
