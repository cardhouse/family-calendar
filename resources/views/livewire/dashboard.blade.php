<?php

declare(strict_types=1);

use App\Enums\DashboardTheme;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Session;
use Livewire\Component;

return new class extends Component
{
    #[Layout('layouts::app')]
    #[Session(key: 'dashboard.tile-order')]
    public array $tileOrder = [];

    #[Session(key: 'dashboard.hidden-tiles')]
    public array $hiddenTileIds = [];

    #[Session(key: 'dashboard.show-completed')]
    public bool $showCompletedTasks = true;

    #[Session(key: 'dashboard.tile-sizes')]
    public array $tileSizes = [];

    #[Session(key: 'dashboard.theme')]
    public string $dashboardTheme = 'sunset-ember';

    public function mount(): void
    {
        if (DashboardTheme::tryFrom($this->dashboardTheme) === null) {
            $this->dashboardTheme = DashboardTheme::SunsetEmber->value;
        }

        $this->tileOrder = $this->normalizeTileOrder($this->tileOrder);
        $this->hiddenTileIds = $this->normalizeHiddenTileIds($this->hiddenTileIds);
        $this->tileSizes = $this->normalizeTileSizes($this->tileSizes);

        if (! $this->layoutIsValid($this->tileOrder, $this->hiddenTileIds, $this->tileSizes)) {
            $this->tileOrder = $this->defaultTileOrder();
            $this->hiddenTileIds = [];
            $this->tileSizes = $this->defaultTileSizes();
        }
    }

    public function setTheme(string $theme): void
    {
        if (DashboardTheme::tryFrom($theme) === null) {
            return;
        }

        $this->dashboardTheme = $theme;
    }

    public function sortTile(string $tileId, int $position): void
    {
        $tileOrder = $this->normalizeTileOrder($this->tileOrder);

        if (! in_array($tileId, $tileOrder, true) || in_array($tileId, $this->immovableTileIds(), true)) {
            return;
        }

        $currentPosition = array_search($tileId, $tileOrder, true);

        if (! is_int($currentPosition)) {
            return;
        }

        $maxPosition = max(count($tileOrder) - 1, 0);
        $targetPosition = max(0, min($position, $maxPosition));

        if ($currentPosition === $targetPosition) {
            return;
        }

        array_splice($tileOrder, $currentPosition, 1);
        array_splice($tileOrder, $targetPosition, 0, [$tileId]);

        if (! $this->layoutIsValid($tileOrder, $this->hiddenTileIds, $this->tileSizes)) {
            return;
        }

        $this->tileOrder = $tileOrder;
    }

    public function moveTileLeft(string $tileId): void
    {
        $this->moveTile($tileId, -1);
    }

    public function moveTileRight(string $tileId): void
    {
        $this->moveTile($tileId, 1);
    }

    public function resetTileOrder(): void
    {
        $this->tileOrder = $this->defaultTileOrder();
        $this->tileSizes = $this->defaultTileSizes();
    }

    #[On('dashboard:toggle-tile-visibility')]
    public function toggleTileVisibility(string $tileId): void
    {
        if (! in_array($tileId, $this->hideableTileIds(), true)) {
            return;
        }

        $hiddenTileIds = $this->normalizeHiddenTileIds($this->hiddenTileIds);

        if (in_array($tileId, $hiddenTileIds, true)) {
            $nextHiddenTileIds = array_values(array_filter(
                $hiddenTileIds,
                fn (string $hiddenTileId): bool => $hiddenTileId !== $tileId
            ));

            if (! $this->layoutIsValid($this->tileOrder, $nextHiddenTileIds, $this->tileSizes)) {
                return;
            }

            $this->hiddenTileIds = $nextHiddenTileIds;

            return;
        }

        $hiddenTileIds[] = $tileId;
        $nextHiddenTileIds = $this->normalizeHiddenTileIds($hiddenTileIds);

        if (! $this->layoutIsValid($this->tileOrder, $nextHiddenTileIds, $this->tileSizes)) {
            return;
        }

        $this->hiddenTileIds = $nextHiddenTileIds;
    }

    #[On('dashboard:toggle-completed')]
    public function handleToggleCompleted(bool $show): void
    {
        $this->showCompletedTasks = $show;
    }

    #[On('dashboard:update-tile-size')]
    public function updateTileSize(string $tileId, string $size): void
    {
        if (! array_key_exists($tileId, $this->tileDefinitions()) || ! array_key_exists($size, $this->tileSizeWidths())) {
            return;
        }

        $tileSizes = $this->normalizeTileSizes($this->tileSizes);
        $tileSizes[$tileId] = $size;

        if (! $this->layoutIsValid($this->tileOrder, $this->hiddenTileIds, $tileSizes)) {
            return;
        }

        $this->tileSizes = $tileSizes;
    }

    /**
     * @return list<array{id: string, title: string, description: string, span: string, size: string}>
     */
    #[Computed]
    public function orderedTiles(): array
    {
        $definitions = $this->tileDefinitions();

        return array_values(array_map(function (string $tileId) use ($definitions): array {
            return [
                'id' => $tileId,
                'title' => $definitions[$tileId]['title'],
                'description' => $definitions[$tileId]['description'],
                'span' => $this->spanClassForSize($this->tileSizes[$tileId] ?? $definitions[$tileId]['size']),
                'size' => $this->tileSizes[$tileId] ?? $definitions[$tileId]['size'],
            ];
        }, $this->visibleTileOrder()));
    }

    /**
     * @return array<string, array{can_move_left: bool, can_move_right: bool}>
     */
    #[Computed]
    public function tileMoveAvailability(): array
    {
        $visibleTileOrder = $this->visibleTileOrder();
        $totalVisibleTiles = count($visibleTileOrder);
        $availability = [];

        foreach ($visibleTileOrder as $index => $tileId) {
            $movable = ! in_array($tileId, $this->immovableTileIds(), true);

            $availability[$tileId] = [
                'can_move_left' => $movable && $index > 0,
                'can_move_right' => $movable && $index < $totalVisibleTiles - 1,
            ];
        }

        return $availability;
    }

    /**
     * @return list<array{id: string, title: string, hidden: bool}>
     */
    #[Computed]
    public function hideableTiles(): array
    {
        $definitions = $this->tileDefinitions();

        return array_map(function (string $tileId) use ($definitions): array {
            return [
                'id' => $tileId,
                'title' => $definitions[$tileId]['title'],
                'hidden' => in_array($tileId, $this->hiddenTileIds, true),
            ];
        }, $this->hideableTileIds());
    }

    /**
     * @return list<array{id: string, title: string, hidden: bool, size: string}>
     */
    #[Computed]
    public function configurableTiles(): array
    {
        $definitions = $this->tileDefinitions();
        $orderedTileIds = $this->normalizeTileOrder($this->tileOrder);

        return array_map(function (string $tileId) use ($definitions): array {
            return [
                'id' => $tileId,
                'title' => $definitions[$tileId]['title'],
                'hidden' => in_array($tileId, $this->hiddenTileIds, true),
                'size' => $this->tileSizes[$tileId] ?? $definitions[$tileId]['size'],
            ];
        }, $orderedTileIds);
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function tileSizeLabels(): array
    {
        return [
            'compact' => 'Compact (1/4)',
            'small' => 'Small (1/3)',
            'medium' => 'Medium (1/2)',
            'large' => 'Large (2/3)',
            'full' => 'Full (1/1)',
        ];
    }

    /**
     * @return list<string>
     */
    private function hideableTileIds(): array
    {
        return array_values(array_filter(
            $this->defaultTileOrder(),
            fn (string $tileId): bool => $tileId !== 'settings'
        ));
    }

    /**
     * @return array<string, array{title: string, description: string, size: string}>
     */
    private function tileDefinitions(): array
    {
        return [
            'snapshot' => [
                'title' => 'Live Snapshot',
                'description' => 'Countdown, lunch plan, and weather right now.',
                'size' => 'large',
            ],
            'crew' => [
                'title' => 'Crew Snapshot',
                'description' => 'At-a-glance status for the whole morning.',
                'size' => 'small',
            ],
            'settings' => [
                'title' => 'Dashboard Settings',
                'description' => 'Show or hide tiles and jump into admin pages.',
                'size' => 'small',
            ],
            'checklists' => [
                'title' => 'Mission Checklist',
                'description' => 'Tap tasks to keep each child moving toward done.',
                'size' => 'large',
            ],
            'events' => [
                'title' => 'Calendar Lane',
                'description' => 'Upcoming events and how soon they start.',
                'size' => 'full',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    private function defaultTileOrder(): array
    {
        return ['snapshot', 'crew', 'settings', 'checklists', 'events'];
    }

    /**
     * @return list<string>
     */
    private function normalizeTileOrder(mixed $tileOrder): array
    {
        $allowedTileOrder = $this->defaultTileOrder();
        $normalizedTileOrder = [];

        if (is_iterable($tileOrder)) {
            foreach ($tileOrder as $tileId) {
                if (! is_string($tileId) || ! in_array($tileId, $allowedTileOrder, true)) {
                    continue;
                }

                $normalizedTileOrder[$tileId] = $tileId;
            }
        }

        foreach ($allowedTileOrder as $tileId) {
            if (! array_key_exists($tileId, $normalizedTileOrder)) {
                $normalizedTileOrder[$tileId] = $tileId;
            }
        }

        return array_values($normalizedTileOrder);
    }

    /**
     * @return list<string>
     */
    private function normalizeHiddenTileIds(mixed $hiddenTileIds): array
    {
        $allowedHiddenTileIds = $this->hideableTileIds();
        $normalizedHiddenTileIds = [];

        if (! is_iterable($hiddenTileIds)) {
            return [];
        }

        foreach ($hiddenTileIds as $hiddenTileId) {
            if (! is_string($hiddenTileId) || ! in_array($hiddenTileId, $allowedHiddenTileIds, true)) {
                continue;
            }

            $normalizedHiddenTileIds[$hiddenTileId] = $hiddenTileId;
        }

        return array_values($normalizedHiddenTileIds);
    }

    /**
     * @return array<string, int>
     */
    private function tileSizeWidths(): array
    {
        return [
            'compact' => 3,
            'small' => 4,
            'medium' => 6,
            'large' => 8,
            'full' => 12,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function defaultTileSizes(): array
    {
        $definitions = $this->tileDefinitions();

        return array_map(
            fn (array $definition): string => $definition['size'],
            $definitions
        );
    }

    /**
     * @return array<string, string>
     */
    private function normalizeTileSizes(mixed $tileSizes): array
    {
        $validSizes = array_keys($this->tileSizeWidths());
        $defaults = $this->defaultTileSizes();
        $normalizedTileSizes = $defaults;

        if (! is_iterable($tileSizes)) {
            return $normalizedTileSizes;
        }

        foreach ($tileSizes as $tileId => $size) {
            if (! is_string($tileId) || ! array_key_exists($tileId, $defaults) || ! is_string($size) || ! in_array($size, $validSizes, true)) {
                continue;
            }

            $normalizedTileSizes[$tileId] = $size;
        }

        return $normalizedTileSizes;
    }

    private function spanClassForSize(string $size): string
    {
        return match ($size) {
            'compact' => 'lg:col-span-3',
            'small' => 'lg:col-span-4',
            'medium' => 'lg:col-span-6',
            'large' => 'lg:col-span-8',
            'full' => 'lg:col-span-12',
            default => 'lg:col-span-4',
        };
    }

    private function layoutIsValid(array $tileOrder, array $hiddenTileIds, array $tileSizes): bool
    {
        $visibleTileOrder = $this->visibleTileOrderFor($tileOrder, $hiddenTileIds);

        if ($visibleTileOrder === []) {
            return true;
        }

        $defaultTileSizes = $this->defaultTileSizes();
        $widths = $this->tileSizeWidths();
        $activeRowWidth = 0;

        foreach ($visibleTileOrder as $index => $tileId) {
            $size = $tileSizes[$tileId] ?? $defaultTileSizes[$tileId] ?? 'small';
            $tileWidth = $widths[$size] ?? null;

            if (! is_int($tileWidth)) {
                return false;
            }

            if ($activeRowWidth + $tileWidth > 12) {
                return false;
            }

            $activeRowWidth += $tileWidth;
            $isLastTile = $index === count($visibleTileOrder) - 1;

            if ($activeRowWidth === 12) {
                $activeRowWidth = 0;

                continue;
            }

            if (! $isLastTile) {
                $remainingWidths = 0;

                for ($remainingIndex = $index + 1; $remainingIndex < count($visibleTileOrder); $remainingIndex++) {
                    $remainingTileId = $visibleTileOrder[$remainingIndex];
                    $remainingSize = $tileSizes[$remainingTileId] ?? $defaultTileSizes[$remainingTileId] ?? 'small';
                    $remainingWidth = $widths[$remainingSize] ?? null;

                    if (! is_int($remainingWidth)) {
                        return false;
                    }

                    $remainingWidths += $remainingWidth;
                }

                if ($remainingWidths === 0 || ($remainingWidths + $activeRowWidth) < 12) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param  list<string>  $tileOrder
     * @param  list<string>  $hiddenTileIds
     * @return list<string>
     */
    private function visibleTileOrderFor(array $tileOrder, array $hiddenTileIds): array
    {
        return array_values(array_filter(
            $this->normalizeTileOrder($tileOrder),
            fn (string $tileId): bool => $tileId === 'settings' || ! in_array($tileId, $hiddenTileIds, true)
        ));
    }

    /**
     * @return list<string>
     */
    private function visibleTileOrder(): array
    {
        return $this->visibleTileOrderFor($this->tileOrder, $this->hiddenTileIds);
    }

    /**
     * @return list<string>
     */
    private function immovableTileIds(): array
    {
        return [];
    }

    private function moveTile(string $tileId, int $delta): void
    {
        if (! in_array($tileId, $this->visibleTileOrder(), true) || in_array($tileId, $this->immovableTileIds(), true)) {
            return;
        }

        $visibleTileOrder = $this->visibleTileOrder();
        $sourceVisibleIndex = array_search($tileId, $visibleTileOrder, true);

        if (! is_int($sourceVisibleIndex)) {
            return;
        }

        $targetVisibleIndex = $sourceVisibleIndex + $delta;

        if ($targetVisibleIndex < 0 || $targetVisibleIndex >= count($visibleTileOrder)) {
            return;
        }

        $targetTileId = $visibleTileOrder[$targetVisibleIndex];

        if (in_array($targetTileId, $this->immovableTileIds(), true)) {
            return;
        }

        $fullTileOrder = $this->normalizeTileOrder($this->tileOrder);
        $sourceIndex = array_search($tileId, $fullTileOrder, true);
        $targetIndex = array_search($targetTileId, $fullTileOrder, true);

        if (! is_int($sourceIndex) || ! is_int($targetIndex)) {
            return;
        }

        [$fullTileOrder[$sourceIndex], $fullTileOrder[$targetIndex]] = [$fullTileOrder[$targetIndex], $fullTileOrder[$sourceIndex]];

        if (! $this->layoutIsValid($fullTileOrder, $this->hiddenTileIds, $this->tileSizes)) {
            return;
        }

        $this->tileOrder = array_values($fullTileOrder);
    }
};
?>

<div class="mood-{{ $dashboardTheme }} relative min-h-screen overflow-hidden px-4 py-6 sm:px-6 lg:px-8">
    <div class="pointer-events-none absolute -top-20 left-1/3 h-80 w-80 rounded-full bg-orange-400/20 blur-3xl"></div>
    <div class="pointer-events-none absolute -left-20 top-48 h-72 w-72 rounded-full bg-rose-300/20 blur-3xl"></div>
    <div class="pointer-events-none absolute bottom-10 right-0 h-80 w-80 rounded-full bg-amber-300/25 blur-3xl"></div>

    <div class="relative mx-auto flex w-full max-w-7xl flex-col gap-6">
        <div wire:sort="sortTile" class="grid auto-rows-[minmax(180px,auto)] gap-4 lg:grid-cols-12">
            @foreach ($this->orderedTiles as $tile)
                @if ($tile['id'] === 'snapshot')
                    <x-dashboard.tile :tile-id="$tile['id']" :title="$tile['title']" :description="$tile['description']" :span="$tile['span']">
                        <x-slot:actions>
                            <x-dashboard.tile-actions
                                :tile-id="$tile['id']"
                                :can-move-left="$this->tileMoveAvailability[$tile['id']]['can_move_left'] ?? false"
                                :can-move-right="$this->tileMoveAvailability[$tile['id']]['can_move_right'] ?? false"
                            />
                        </x-slot:actions>

                        <livewire:dashboard.tiles.snapshot defer wire:key="tile-component-snapshot" />
                    </x-dashboard.tile>
                @elseif ($tile['id'] === 'crew')
                    <x-dashboard.tile :tile-id="$tile['id']" :title="$tile['title']" :description="$tile['description']" :span="$tile['span']">
                        <x-slot:actions>
                            <x-dashboard.tile-actions
                                :tile-id="$tile['id']"
                                :can-move-left="$this->tileMoveAvailability[$tile['id']]['can_move_left'] ?? false"
                                :can-move-right="$this->tileMoveAvailability[$tile['id']]['can_move_right'] ?? false"
                            />
                        </x-slot:actions>

                        <livewire:dashboard.tiles.crew defer wire:key="tile-component-crew" />
                    </x-dashboard.tile>
                @elseif ($tile['id'] === 'settings')
                    <x-dashboard.tile :tile-id="$tile['id']" :title="$tile['title']" :description="$tile['description']" :span="$tile['span']">
                        <x-slot:actions>
                            <x-dashboard.tile-actions
                                :tile-id="$tile['id']"
                                :can-move-left="$this->tileMoveAvailability[$tile['id']]['can_move_left'] ?? false"
                                :can-move-right="$this->tileMoveAvailability[$tile['id']]['can_move_right'] ?? false"
                            />
                        </x-slot:actions>

                        <livewire:dashboard.tiles.settings
                            defer
                            :hideable-tiles="$this->hideableTiles"
                            :configurable-tiles="$this->configurableTiles"
                            :tile-size-labels="$this->tileSizeLabels"
                            :show-completed-tasks="$showCompletedTasks"
                            :dashboard-theme="$dashboardTheme"
                            wire:key="tile-component-settings"
                        />
                    </x-dashboard.tile>
                @elseif ($tile['id'] === 'checklists')
                    <x-dashboard.tile :tile-id="$tile['id']" :title="$tile['title']" :description="$tile['description']" :span="$tile['span']">
                        <x-slot:actions>
                            <x-dashboard.tile-actions
                                :tile-id="$tile['id']"
                                :can-move-left="$this->tileMoveAvailability[$tile['id']]['can_move_left'] ?? false"
                                :can-move-right="$this->tileMoveAvailability[$tile['id']]['can_move_right'] ?? false"
                            />
                        </x-slot:actions>

                        <livewire:dashboard.tiles.checklists
                            :show-completed-tasks="$showCompletedTasks"
                            wire:key="tile-component-checklists"
                        />
                    </x-dashboard.tile>
                @elseif ($tile['id'] === 'events')
                    <x-dashboard.tile :tile-id="$tile['id']" :title="$tile['title']" :description="$tile['description']" :span="$tile['span']">
                        <x-slot:actions>
                            <x-dashboard.tile-actions
                                :tile-id="$tile['id']"
                                :can-move-left="$this->tileMoveAvailability[$tile['id']]['can_move_left'] ?? false"
                                :can-move-right="$this->tileMoveAvailability[$tile['id']]['can_move_right'] ?? false"
                            />
                        </x-slot:actions>

                        <livewire:dashboard.tiles.events defer wire:key="tile-component-events" />
                    </x-dashboard.tile>
                @endif
            @endforeach
        </div>
    </div>
</div>
