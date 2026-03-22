@props([
    'label',
    'prefix' => '•',
    'meta' => null,
    'active' => false,
    'muted' => false,
    'href' => null,
    'tone' => 'room',
])

@php
    $containerClasses = match (true) {
        $active => 'shell-active-row',
        $muted => 'bg-transparent shell-text-faint',
        default => 'bg-transparent shell-text',
    };

    $prefixClasses = match (true) {
        $active => 'shell-accent-chip',
        $muted => 'shell-surface shell-text-faint',
        default => match ($tone) {
            'human' => 'shell-human-chip',
            'bot' => 'shell-bot-chip',
            default => 'shell-room-chip',
        },
    };

    $metaClasses = match (true) {
        $active => 'shell-accent-soft',
        $muted => 'shell-surface shell-text-faint',
        default => 'shell-surface shell-text-soft',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class(['shell-hover-elevated flex items-center gap-3 rounded-2xl px-3 py-2 transition-colors', $containerClasses]) }}>
        <span class="flex h-8 w-8 items-center justify-center rounded-xl font-mono text-xs uppercase {{ $prefixClasses }}">{{ $prefix }}</span>
        <div class="flex-1">
            <p class="text-sm font-medium">{{ $label }}</p>
        </div>
        @if ($meta)
            <span class="rounded-full px-2 py-1 font-mono text-[10px] uppercase tracking-[0.12em] {{ $metaClasses }}">{{ $meta }}</span>
        @endif
    </a>
@else
    <div {{ $attributes->class(['flex items-center gap-3 rounded-2xl px-3 py-2', $containerClasses]) }}>
        <span class="flex h-8 w-8 items-center justify-center rounded-xl font-mono text-xs uppercase {{ $prefixClasses }}">{{ $prefix }}</span>
        <div class="flex-1">
            <p class="text-sm font-medium">{{ $label }}</p>
        </div>
        @if ($meta)
            <span class="rounded-full px-2 py-1 font-mono text-[10px] uppercase tracking-[0.12em] {{ $metaClasses }}">{{ $meta }}</span>
        @endif
    </div>
@endif
