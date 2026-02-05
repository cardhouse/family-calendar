<div class="min-h-screen px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto flex w-full max-w-6xl flex-col gap-8">
        <div class="fixed right-4 top-4 z-50 opacity-50 transition-opacity hover:opacity-100">
            <flux:button variant="ghost" icon="cog-6-tooth" href="{{ route('admin.routines') }}" aria-label="Open admin settings" />
        </div>

        <livewire:dashboard.header :next-departure="$nextDeparture" />

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <flux:heading size="xl" class="font-extrabold text-slate-100">Morning checklists</flux:heading>
                <flux:text class="text-slate-400">Keep everyone moving with the next routine steps.</flux:text>
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
                class="flex items-center gap-3 rounded-full border border-dash-border bg-dash-card px-5 py-2.5 text-sm"
            >
                <span class="font-semibold text-slate-300">Completed</span>
                <button
                    type="button"
                    class="rounded-full bg-slate-700 px-4 py-1.5 text-xs font-bold uppercase tracking-wide text-slate-100 transition-colors hover:bg-slate-600"
                    x-text="showCompleted ? 'Hide' : 'Show'"
                    @click="showCompleted = !showCompleted; localStorage.setItem('dashboard.showCompleted', showCompleted); dispatch();"
                ></button>
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-2 md:gap-6">
            @forelse ($children as $child)
                <livewire:dashboard.child-card :child="$child" wire:key="child-{{ $child->id }}" />
            @empty
                <div class="col-span-full flex flex-col items-center gap-3 rounded-3xl border border-dashed border-dash-border bg-dash-card p-10 text-center text-slate-400">
                    <flux:icon name="user-plus" variant="outline" class="size-8" />
                    <span>Add children to start planning morning routines.</span>
                </div>
            @endforelse
        </div>

        <livewire:dashboard.footer :events="$upcomingEvents" />
    </div>
</div>
