<div class="min-h-screen px-4 py-6 sm:px-6 sm:py-8">
    <div class="mx-auto flex w-full max-w-6xl flex-col gap-8">
        <livewire:dashboard.header :next-departure="$nextDeparture" />

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-slate-100">Morning checklists</h2>
                <p class="text-sm text-slate-400">Keep everyone moving with the next routine steps.</p>
            </div>
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('admin.routines') }}"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-700 bg-slate-900/60 text-slate-200 hover:bg-slate-800"
                    title="Open admin panel"
                >
                    <span class="sr-only">Open admin panel</span>
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 15.5A3.5 3.5 0 1 0 12 8.5a3.5 3.5 0 0 0 0 7Z"/>
                        <path d="m19.4 15 .2-3-2.1-.7a5.8 5.8 0 0 0-.4-.9l1-2-2.1-2.1-2 1a5.8 5.8 0 0 0-.9-.4L12 4.8l-3 .2-.7 2.1a5.8 5.8 0 0 0-.9.4l-2-1L3.3 8.6l1 2a5.8 5.8 0 0 0-.4.9L1.8 12l.2 3 2.1.7a5.8 5.8 0 0 0 .4.9l-1 2 2.1 2.1 2-1c.3.2.6.3.9.4l.7 2.1 3-.2.7-2.1c.3-.1.6-.2.9-.4l2 1 2.1-2.1-1-2c.2-.3.3-.6.4-.9l2.1-.7Z"/>
                    </svg>
                </a>

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
        </div>

        @if ($children->isNotEmpty() && ! $hasAnyAssignments)
            <div class="rounded-3xl border border-dashed border-slate-700 bg-slate-900/60 px-6 py-8 text-center">
                <h3 class="text-lg font-semibold text-slate-100">No routines assigned yet</h3>
                <p class="mt-2 text-sm text-slate-400">Assign routines from the admin panel to populate each checklist.</p>
                <a
                    href="{{ route('admin.routines') }}"
                    class="mt-4 inline-flex rounded-full border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-slate-800"
                >
                    Open routine manager
                </a>
            </div>
        @endif

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
