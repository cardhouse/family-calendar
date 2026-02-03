<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\DepartureTime;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Departures extends Component
{
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

    public function render(): mixed
    {
        return view('livewire.admin.departures');
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
}
