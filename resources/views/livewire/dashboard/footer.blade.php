<div class="rounded-3xl border border-slate-800 bg-slate-900/70 p-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-slate-100">Upcoming events</h3>
            <p class="text-sm text-slate-400">Next three calendar moments.</p>
        </div>
    </div>

    <div class="mt-5 flex flex-col gap-3">
        @forelse ($events as $event)
            <div
                wire:key="event-{{ $event->id }}"
                class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-800 bg-slate-900/50 px-4 py-3"
            >
                <div>
                    <div class="text-sm font-semibold text-slate-100">{{ $event->name }}</div>
                    <div class="text-xs text-slate-400">
                        {{ $event->starts_at?->format('D M j, g:i A') }}
                    </div>
                </div>
                @if ($event->starts_at)
                    <div
                        x-data="DashboardTime.eventCountdown({{ $event->starts_at->timestamp }})"
                        class="text-sm font-semibold text-slate-200"
                        x-text="timeLeft"
                    ></div>
                @endif
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-800 px-4 py-6 text-center text-sm text-slate-400">
                No upcoming events yet.
            </div>
        @endforelse
    </div>
</div>
