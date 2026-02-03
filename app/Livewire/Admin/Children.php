<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Child;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Children extends Component
{
    /**
     * @var Collection<int, Child>
     */
    public Collection $children;

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $avatarColor = '#94a3b8';

    public function mount(): void
    {
        $this->children = $this->loadChildren();
    }

    public function openCreate(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->avatarColor = '#94a3b8';
        $this->showModal = true;
    }

    public function openEdit(int $childId): void
    {
        $child = Child::query()->findOrFail($childId);

        $this->editingId = $child->id;
        $this->name = $child->name;
        $this->avatarColor = $child->avatar_color;
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'avatarColor' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        if ($this->editingId !== null) {
            Child::query()->whereKey($this->editingId)->update([
                'name' => $validated['name'],
                'avatar_color' => $validated['avatarColor'],
            ]);
        } else {
            $order = (int) Child::query()->max('display_order');

            Child::query()->create([
                'name' => $validated['name'],
                'avatar_color' => $validated['avatarColor'],
                'display_order' => $order + 1,
            ]);
        }

        $this->showModal = false;
        $this->children = $this->loadChildren();
    }

    public function delete(int $childId): void
    {
        Child::query()->whereKey($childId)->delete();

        $this->resequenceChildren();
        $this->children = $this->loadChildren();
    }

    public function reorder(int $childId, int $position): void
    {
        $children = Child::query()->ordered()->get()->values();
        $moving = $children->firstWhere('id', $childId);

        if ($moving === null) {
            return;
        }

        $children = $children->reject(function (Child $child) use ($childId): bool {
            return $child->id === $childId;
        })->values();

        $children->splice($position, 0, [$moving]);

        $children->values()->each(function (Child $child, int $index): void {
            $child->update(['display_order' => $index + 1]);
        });

        $this->children = $this->loadChildren();
    }

    public function render(): mixed
    {
        return view('livewire.admin.children');
    }

    /**
     * @return Collection<int, Child>
     */
    private function loadChildren(): Collection
    {
        return Child::query()->ordered()->get();
    }

    private function resequenceChildren(): void
    {
        $children = Child::query()->ordered()->get();

        $children->values()->each(function (Child $child, int $index): void {
            $child->update(['display_order' => $index + 1]);
        });
    }
}
