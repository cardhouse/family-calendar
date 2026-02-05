<div class="rounded-3xl border border-slate-800 bg-slate-900/70 p-6 shadow-sm">
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-2xl" style="background-color: {{ $child->avatar_color }};"></div>
            <div>
                <h3 class="text-lg font-semibold text-slate-100">{{ $child->name }}</h3>
                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Checklist</p>
            </div>
        </div>
        <div class="text-sm font-semibold text-slate-200">{{ $this->progressPercent }}%</div>
    </div>

    <div class="mt-4 h-2 w-full rounded-full bg-slate-800">
        <div
            class="h-2 rounded-full bg-emerald-400 transition-all"
            style="width: {{ $this->progressPercent }}%;"
        ></div>
    </div>

    <div class="mt-6 flex flex-col gap-3">
        @forelse ($this->visibleAssignments as $assignment)
            <button
                type="button"
                wire:key="assignment-{{ $assignment->id }}"
                wire:click="toggleCompletion({{ $assignment->id }})"
                class="flex items-center justify-between gap-3 rounded-2xl border border-slate-800 bg-slate-900/50 px-4 py-3 text-left"
            >
                <div>
                    <div class="text-sm font-semibold text-slate-100">{{ $assignment->routineItem->name }}</div>
                    <div class="text-xs text-slate-400">
                        {{ $assignment->todayCompletion ? 'Completed' : 'Pending' }}
                    </div>
                </div>
                <div
                    class="flex h-6 w-6 items-center justify-center rounded-full border"
                    @class([
                        'border-emerald-400 bg-emerald-400/20 text-emerald-200' => $assignment->todayCompletion,
                        'border-slate-700 text-slate-400' => ! $assignment->todayCompletion,
                    ])
                >
                    <span class="text-xs">{{ $assignment->todayCompletion ? '✓' : '' }}</span>
                </div>
            </button>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-800 px-4 py-6 text-center text-sm text-slate-400">
                No routine items yet.
            </div>
        @endforelse
    </div>

    @if ($confettiEnabled && $shouldCelebrate)
        <div class="mt-4 flex items-center gap-1 overflow-hidden rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-3 py-2">
            @for ($i = 0; $i < 12; $i++)
                <span
                    @class([
                        'h-2 w-2 animate-bounce rounded-full',
                        'bg-cyan-300' => $i % 4 === 0,
                        'bg-emerald-300' => $i % 4 === 1,
                        'bg-amber-300' => $i % 4 === 2,
                        'bg-rose-300' => $i % 4 === 3,
                    ])
                    style="animation-delay: {{ $i * 0.05 }}s;"
                ></span>
            @endfor
        </div>
    @endif

    @if ($this->isComplete)
        <div class="mt-4 rounded-2xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
            {{ $celebrationMessage ?? 'All set for the day. Great work!' }}
        </div>
    @endif
</div>
