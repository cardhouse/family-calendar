<div class="rounded-3xl border border-dash-border bg-dash-card p-6">
    <div class="flex items-center gap-3">
        <flux:icon name="calendar" variant="outline" class="size-6 text-blue-400" />
        <div>
            <flux:heading size="lg" class="font-extrabold text-slate-100">Upcoming events</flux:heading>
            <flux:text class="text-slate-400">Next three calendar moments.</flux:text>
        </div>
    </div>

    <div class="mt-5 flex flex-col gap-3">
        @forelse ($events as $event)
            <div
                wire:key="event-{{ $event->id }}"
                class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-dash-border bg-dash-card px-4 py-3"
                style="border-left: 4px solid {{ $event->color ?? '#64748b' }};"
            >
                <div>
                    <div class="text-sm font-bold text-slate-100">{{ $event->name }}</div>
                    <div class="text-xs text-slate-400">
                        {{ $event->starts_at?->format('D M j, g:i A') }}
                    </div>
                </div>
                @if ($event->starts_at)
                    <div
                        x-data="DashboardTime.eventCountdown({{ $event->starts_at->timestamp }})"
                        class="rounded-full bg-slate-800 px-3 py-1 text-sm font-bold text-slate-200"
                        x-text="timeLeft"
                    ></div>
                @endif
            </div>
        @empty
            <div class="flex flex-col items-center gap-2 rounded-2xl border border-dashed border-dash-border px-4 py-8 text-center text-sm text-slate-400">
                <flux:icon name="calendar-days" variant="outline" class="size-6" />
                <span>No upcoming events yet.</span>
            </div>
        @endforelse
    </div>
</div>
