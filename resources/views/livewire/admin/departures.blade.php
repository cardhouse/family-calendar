<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Departures</h1>
            <p class="text-sm text-slate-500">Define departure windows and applicable days.</p>
        </div>
        <button
            type="button"
            wire:click="openCreate"
            class="rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white"
        >
            Add departure
        </button>
    </div>

    <div class="space-y-3" wire:sort="reorder">
        @forelse ($departures as $departure)
            <div
                wire:key="departure-{{ $departure->id }}"
                wire:sort:item="{{ $departure->id }}"
                class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white px-4 py-3"
            >
                <div>
                    <div class="text-sm font-semibold text-slate-900">{{ $departure->name }}</div>
                    <div class="text-xs text-slate-500">
                        {{ \Illuminate\Support\Carbon::parse($departure->departure_time)->format('g:i A') }}
                        · {{ $departure->is_active ? 'Active' : 'Paused' }}
                    </div>
                    <div class="text-xs text-slate-400">
                        Days: {{ $departure->applicable_days ? strtoupper(implode(', ', $departure->applicable_days)) : 'Daily' }}
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        wire:click="openEdit({{ $departure->id }})"
                        class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600"
                    >
                        Edit
                    </button>
                    <button
                        type="button"
                        wire:click="delete({{ $departure->id }})"
                        class="rounded-full border border-rose-200 px-3 py-1 text-xs font-semibold text-rose-600"
                    >
                        Delete
                    </button>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-8 text-center text-sm text-slate-500">
                No departure times configured.
            </div>
        @endforelse
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-40 bg-slate-900/40"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <form
                wire:submit.prevent="save"
                class="w-full max-w-xl rounded-3xl bg-white p-6 shadow-xl"
            >
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">
                        {{ $editingId ? 'Edit departure' : 'Add departure' }}
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
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Departure time</label>
                        <input
                            type="time"
                            wire:model="departureTime"
                            class="mt-2 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm"
                        />
                        @error('departureTime')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Applicable days</label>
                        <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
                            @foreach ($dayOptions as $day)
                                <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2">
                                    <input type="checkbox" value="{{ $day }}" wire:model="applicableDays" class="rounded" />
                                    <span class="text-slate-600">{{ strtoupper($day) }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('applicableDays')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                        @error('applicableDays.*')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <label class="flex items-center gap-3 rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                        <input type="checkbox" wire:model="isActive" class="rounded" />
                        <span class="text-slate-600">Active departure</span>
                    </label>
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
                        Save departure
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
