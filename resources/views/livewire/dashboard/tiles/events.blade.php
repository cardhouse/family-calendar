<?php

declare(strict_types=1);

use App\Models\CalendarEvent;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

return new class extends Component
{
    /**
     * @return Collection<int, CalendarEvent>
     */
    #[Computed]
    public function upcomingEvents(): Collection
    {
        return CalendarEvent::query()
            ->upcoming()
            ->limit(3)
            ->get();
    }
};
?>

@placeholder
    <div>
        <flux:skeleton.group animate="shimmer" class="rounded-3xl border border-dash-border bg-dash-card p-6">
            <div class="flex items-center gap-3">
                <flux:skeleton class="h-10 w-10 rounded-xl" />
                <div class="w-48 space-y-2">
                    <flux:skeleton.line class="w-2/3" />
                    <flux:skeleton.line class="w-full" />
                </div>
            </div>

            <div class="mt-5 space-y-3">
                <flux:skeleton class="h-16 w-full rounded-2xl" />
                <flux:skeleton class="h-16 w-full rounded-2xl" />
                <flux:skeleton class="h-16 w-5/6 rounded-2xl" />
            </div>
        </flux:skeleton.group>
    </div>
@endplaceholder

<div>
    <livewire:dashboard.footer :events="$this->upcomingEvents" />
</div>
