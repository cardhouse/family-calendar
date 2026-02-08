@props([
    'tileId',
    'canMoveLeft' => false,
    'canMoveRight' => false,
])

<div wire:sort:ignore class="flex items-center gap-1">
    <flux:button
        variant="ghost"
        size="sm"
        wire:click="moveTileLeft('{{ $tileId }}')"
        :disabled="! $canMoveLeft"
        title="Move tile left"
        aria-label="Move tile left"
        class="h-9 w-9"
    >
        <flux:icon name="chevron-left" variant="solid" class="size-4" />
    </flux:button>

    <flux:button
        variant="ghost"
        size="sm"
        wire:click="moveTileRight('{{ $tileId }}')"
        :disabled="! $canMoveRight"
        title="Move tile right"
        aria-label="Move tile right"
        class="h-9 w-9"
    >
        <flux:icon name="chevron-right" variant="solid" class="size-4" />
    </flux:button>
</div>
