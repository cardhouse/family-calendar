<section
    wire:poll.900s="refreshWeather"
    @class([
        'rounded-2xl border border-slate-800 bg-slate-900/60 text-slate-100',
        'px-3 py-2 text-xs' => $this->isCompact,
        'px-4 py-3 text-sm' => ! $this->isCompact && ! $this->isLarge,
        'px-5 py-4 text-sm' => $this->isLarge,
    ])
>
    @if ($weather)
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="font-semibold text-slate-100">{{ $weather['condition'] }}</p>
                <p class="text-xs text-slate-300">{{ $weather['location_label'] }}</p>
                @if (! $this->isCompact)
                    <p class="mt-1 text-xs text-slate-400">
                        Updated {{ \Illuminate\Support\Carbon::parse($weather['fetched_at'])->diffForHumans() }}
                    </p>
                @endif
            </div>

            <div class="text-right">
                <p @class([
                    'font-semibold text-slate-100',
                    'text-lg' => $this->isCompact,
                    'text-2xl' => ! $this->isCompact && ! $this->isLarge,
                    'text-3xl' => $this->isLarge,
                ])>
                    {{ round($weather['temperature']) }}&deg;{{ $weather['unit_symbol'] }}
                </p>
                @if ($showFeelsLike && $weather['feels_like'] !== null)
                    <p class="text-xs text-slate-300">
                        Feels {{ round($weather['feels_like']) }}&deg;{{ $weather['unit_symbol'] }}
                    </p>
                @endif
            </div>
        </div>

        @if ($showPrecipitationAlerts && $weather['precipitation'] !== null && $weather['precipitation'] > 0)
            <div class="mt-3 rounded-xl border border-cyan-400/30 bg-cyan-500/10 px-3 py-2 text-xs text-cyan-100">
                Precipitation now: {{ number_format($weather['precipitation'], 1) }}
            </div>
        @endif
    @else
        <div class="space-y-1">
            <p class="font-semibold text-slate-200">Weather unavailable</p>
            <p class="text-xs text-slate-400">
                @if ($location)
                    Unable to load weather right now. Showing this fallback until the next refresh.
                @else
                    Choose a location in Admin Weather to enable this widget.
                @endif
            </p>
        </div>
    @endif
</section>
