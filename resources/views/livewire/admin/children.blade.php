<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Children</h1>
            <p class="text-sm text-slate-500">Manage the kids shown on the morning dashboard.</p>
        </div>
        <button
            type="button"
            wire:click="openCreate"
            class="rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white"
        >
            Add child
        </button>
    </div>

    <div class="space-y-3" wire:sort="reorder">
        @forelse ($children as $child)
            <div
                wire:key="child-{{ $child->id }}"
                wire:sort:item="{{ $child->id }}"
                class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white px-4 py-3"
            >
                <div class="flex items-center gap-3">
                    <span class="h-10 w-10 rounded-2xl" style="background-color: {{ $child->avatar_color }};"></span>
                    <div>
                        <div class="text-sm font-semibold text-slate-900">{{ $child->name }}</div>
                        <div class="text-xs text-slate-500">Display order {{ $child->display_order }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        wire:click="openEdit({{ $child->id }})"
                        class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600"
                    >
                        Edit
                    </button>
                    <button
                        type="button"
                        wire:click="delete({{ $child->id }})"
                        class="rounded-full border border-rose-200 px-3 py-1 text-xs font-semibold text-rose-600"
                    >
                        Delete
                    </button>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-8 text-center text-sm text-slate-500">
                No children yet. Add the first profile to start organizing routines.
            </div>
        @endforelse
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-40 bg-slate-900/40"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <form
                wire:submit.prevent="save"
                class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-xl"
            >
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">
                        {{ $editingId ? 'Edit child' : 'Add child' }}
                    </h2>
                    <button type="button" class="text-sm text-slate-500" wire:click="$set('showModal', false)">
                        Close
                    </button>
                </div>

                <div class="mt-6 space-y-4">
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Name</label>
                        <input
                            type="text"
                            wire:model="name"
                            class="mt-2 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm"
                        />
                        @error('name')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Avatar color</label>
                        <div class="mt-2 flex items-center gap-3">
                            <input type="color" wire:model="avatarColor" class="h-10 w-14 rounded-lg border" />
                            <span class="text-xs text-slate-500">{{ $avatarColor }}</span>
                        </div>
                        @error('avatarColor')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600"
                        wire:click="$set('showModal', false)"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white"
                    >
                        Save child
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
