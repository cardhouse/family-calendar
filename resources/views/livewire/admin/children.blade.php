<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <flux:icon name="face-smile" variant="outline" class="size-7 text-amber-500" />
            <div>
                <flux:heading size="xl" level="1">Children</flux:heading>
                <flux:text>Manage the kids shown on the morning dashboard.</flux:text>
            </div>
        </div>
        <flux:button variant="primary" wire:click="openCreate">Add child</flux:button>
    </div>

    <div class="space-y-3" wire:sort="reorder">
        @forelse ($children as $child)
            <div
                wire:key="child-{{ $child->id }}"
                wire:sort:item="{{ $child->id }}"
                class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-sm transition-all hover:border-amber-300/60 hover:shadow-md"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-full text-lg font-black text-white shadow"
                        style="background-color: {{ $child->avatar_color }};"
                    >
                        {{ strtoupper(mb_substr($child->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-900">{{ $child->name }}</div>
                        <div class="text-xs text-slate-400">Position {{ $child->display_order }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <flux:button size="xs" variant="subtle" wire:click="openEdit({{ $child->id }})">Edit</flux:button>
                    <flux:button size="xs" variant="danger" wire:click="delete({{ $child->id }})">Delete</flux:button>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-10 text-center text-sm text-slate-500">
                <flux:icon name="face-smile" variant="outline" class="size-8 text-slate-300" />
                <span>No children yet. Add the first profile to start organizing routines.</span>
            </div>
        @endforelse
    </div>

    <flux:modal wire:model.self="showModal" class="md:w-96">
        <form wire:submit.prevent="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingId ? 'Edit child' : 'Add child' }}</flux:heading>
                <flux:text class="mt-2">{{ $editingId ? 'Update child details.' : 'Add a new child profile.' }}</flux:text>
            </div>

            <flux:input wire:model="name" label="Name" />

            <div>
                <flux:label>Avatar color</flux:label>
                <div class="mt-2 flex items-center gap-3">
                    <input type="color" wire:model="avatarColor" class="h-10 w-14 rounded-lg border" />
                    <flux:text>{{ $avatarColor }}</flux:text>
                </div>
                <flux:error name="avatarColor" />
            </div>

            <div class="flex">
                <flux:spacer />
                <div class="flex gap-3">
                    <flux:button wire:click="$set('showModal', false)">Cancel</flux:button>
                    <flux:button type="submit" variant="primary">Save child</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>
</div>
