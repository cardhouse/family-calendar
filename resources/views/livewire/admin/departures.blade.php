<?php

use App\Models\DepartureTime;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

return new class extends Component
{
    #[Layout('layouts::admin')]
    /**
     * @var Collection<int, DepartureTime>
     */
    public Collection $departures;

    /**
     * @var array<int, string>
     */
    public array $dayOptions = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $departureTime = '';

    /**
     * @var array<int, string>
     */
    public array $applicableDays = [];

    public bool $isActive = true;

    public function mount(): void
    {
        $this->departures = $this->loadDepartures();
    }

    public function openCreate(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->departureTime = '';
        $this->applicableDays = [];
        $this->isActive = true;
        $this->showModal = true;
    }

    public function openEdit(int $departureId): void
    {
        $departure = DepartureTime::query()->findOrFail($departureId);

        $this->editingId = $departure->id;
        $this->name = $departure->name;
        $this->departureTime = Carbon::parse($departure->departure_time)->format('H:i');
        $this->applicableDays = $departure->applicable_days ?? [];
        $this->isActive = $departure->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'departureTime' => ['required', 'date_format:H:i'],
            'applicableDays' => ['nullable', 'array'],
            'applicableDays.*' => ['string', Rule::in($this->dayOptions)],
            'isActive' => ['boolean'],
        ]);

        $payload = [
            'name' => $validated['name'],
            'departure_time' => $this->normalizeTime($validated['departureTime']),
            'applicable_days' => $validated['applicableDays'] ?? [],
            'is_active' => $validated['isActive'],
        ];

        if ($this->editingId !== null) {
            DepartureTime::query()->whereKey($this->editingId)->update($payload);
        } else {
            $order = (int) DepartureTime::query()->max('display_order');

            DepartureTime::query()->create(array_merge($payload, [
                'display_order' => $order + 1,
            ]));
        }

        $this->showModal = false;
        $this->departures = $this->loadDepartures();
    }

    public function delete(int $departureId): void
    {
        DepartureTime::query()->whereKey($departureId)->delete();

        $this->resequenceDepartures();
        $this->departures = $this->loadDepartures();
    }

    public function reorder(int $departureId, int $position): void
    {
        $departures = DepartureTime::query()->ordered()->get()->values();
        $moving = $departures->firstWhere('id', $departureId);

        if ($moving === null) {
            return;
        }

        $departures = $departures->reject(function (DepartureTime $departure) use ($departureId): bool {
            return $departure->id === $departureId;
        })->values();

        $departures->splice($position, 0, [$moving]);

        $departures->values()->each(function (DepartureTime $departure, int $index): void {
            $departure->update(['display_order' => $index + 1]);
        });

        $this->departures = $this->loadDepartures();
    }

    /**
     * @return Collection<int, DepartureTime>
     */
    private function loadDepartures(): Collection
    {
        return DepartureTime::query()->ordered()->get();
    }

    private function normalizeTime(string $time): string
    {
        if (strlen($time) === 5) {
            return $time.':00';
        }

        if (strlen($time) === 8) {
            return $time;
        }

        return Carbon::parse($time)->format('H:i:s');
    }

    private function resequenceDepartures(): void
    {
        $departures = DepartureTime::query()->ordered()->get();

        $departures->values()->each(function (DepartureTime $departure, int $index): void {
            $departure->update(['display_order' => $index + 1]);
        });
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <flux:icon name="clock" variant="outline" class="size-7 text-amber-500" />
            <div>
                <flux:heading size="xl" level="1">Departures</flux:heading>
                <flux:text>Define departure windows and applicable days.</flux:text>
            </div>
        </div>
        <flux:button variant="primary" wire:click="openCreate">Add departure</flux:button>
    </div>

    <div class="space-y-3" wire:sort="reorder">
        @forelse ($departures as $departure)
            <div
                wire:key="departure-{{ $departure->id }}"
                wire:sort:item="{{ $departure->id }}"
                class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-sm transition-all hover:border-amber-300/60 hover:shadow-md"
            >
                <div>
                    <div class="flex items-center gap-2">
                        <div class="text-sm font-bold text-slate-900">{{ $departure->name }}</div>
                        @if ($departure->is_active)
                            <flux:badge size="sm" color="lime">Active</flux:badge>
                        @else
                            <flux:badge size="sm" color="zinc">Paused</flux:badge>
                        @endif
                    </div>
                    <div class="mt-1 flex items-center gap-2 text-xs text-slate-500">
                        <flux:icon name="clock" variant="outline" class="size-3.5" />
                        {{ \Illuminate\Support\Carbon::parse($departure->departure_time)->format('g:i A') }}
                    </div>
                    <div class="mt-1.5 flex flex-wrap gap-1">
                        @if ($departure->applicable_days)
                            @foreach ($departure->applicable_days as $day)
                                <flux:badge size="sm" color="zinc">{{ strtoupper($day) }}</flux:badge>
                            @endforeach
                        @else
                            <flux:badge size="sm" color="sky">Daily</flux:badge>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <flux:button size="xs" variant="subtle" wire:click="openEdit({{ $departure->id }})">Edit</flux:button>
                    <flux:button size="xs" variant="danger" wire:click="delete({{ $departure->id }})">Delete</flux:button>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-10 text-center text-sm text-slate-500">
                <flux:icon name="clock" variant="outline" class="size-8 text-slate-300" />
                <span>No departure times configured.</span>
            </div>
        @endforelse
    </div>

    <flux:modal wire:model.self="showModal" class="md:w-xl">
        <form wire:submit.prevent="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingId ? 'Edit departure' : 'Add departure' }}</flux:heading>
                <flux:text class="mt-2">{{ $editingId ? 'Update departure details.' : 'Add a new departure time.' }}</flux:text>
            </div>

            <flux:input wire:model="name" label="Name" />

            <flux:input type="time" wire:model="departureTime" label="Departure time" />

            <flux:checkbox.group wire:model="applicableDays" label="Applicable days">
                @foreach ($dayOptions as $day)
                    <flux:checkbox label="{{ strtoupper($day) }}" value="{{ $day }}" />
                @endforeach
            </flux:checkbox.group>
            <flux:error name="applicableDays" />
            <flux:error name="applicableDays.*" />

            <flux:field variant="inline">
                <flux:label>Active departure</flux:label>
                <flux:switch wire:model="isActive" />
            </flux:field>

            <div class="flex">
                <flux:spacer />
                <div class="flex gap-3">
                    <flux:button wire:click="$set('showModal', false)">Cancel</flux:button>
                    <flux:button type="submit" variant="primary">Save departure</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>
</div>
