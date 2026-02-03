<div class="min-h-screen px-6 py-8">
    <div class="mx-auto flex w-full max-w-6xl flex-col gap-8">
        <livewire:dashboard.header :next-departure="$nextDeparture" />

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-slate-100">Morning checklists</h2>
                <p class="text-sm text-slate-400">Keep everyone moving with the next routine steps.</p>
            </div>
            <div
                x-data="{
                    showCompleted: true,
                    init() {
                        const stored = localStorage.getItem('dashboard.showCompleted');
                        if (stored !== null) {
                            this.showCompleted = stored === 'true';
                        }
                        this.dispatch();
                    },
                    dispatch() {
                        if (window.Livewire) {
                            Livewire.dispatch('dashboard:toggle-completed', { show: this.showCompleted });
                        }
                    },
                }"
                class="flex items-center gap-3 rounded-full border border-slate-800 bg-slate-900/60 px-4 py-2 text-sm"
            >
                <span class="text-slate-300">Completed</span>
                <button
                    type="button"
                    class="rounded-full bg-slate-800 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-100"
                    x-text="showCompleted ? 'Hide' : 'Show'"
                    @click="showCompleted = !showCompleted; localStorage.setItem('dashboard.showCompleted', showCompleted); dispatch();"
                ></button>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($children as $child)
                <livewire:dashboard.child-card :child="$child" wire:key="child-{{ $child->id }}" />
            @empty
                <div class="rounded-3xl border border-dashed border-slate-800 bg-slate-900/60 p-8 text-center text-slate-400">
                    Add children to start planning morning routines.
                </div>
            @endforelse
        </div>

        <livewire:dashboard.footer :events="$upcomingEvents" />
    </div>
</div>
