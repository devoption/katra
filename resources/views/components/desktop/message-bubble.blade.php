@props([
    'speaker',
    'role',
    'meta',
    'tone' => 'plain',
])

@php
    $wrapperClasses = match ($tone) {
        'accent' => 'shell-surface',
        'subtle' => 'shell-panel',
        default => 'shell-input',
    };

    $roleClasses = match ($tone) {
        'accent' => 'text-[var(--shell-accent)]',
        'subtle' => 'shell-text-info',
        default => 'shell-text-soft',
    };
@endphp

<article {{ $attributes->class(['rounded-[22px] px-4 py-4', $wrapperClasses]) }}>
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <p class="shell-text text-sm font-semibold">{{ $speaker }}</p>
            <span class="font-mono text-[10px] uppercase tracking-[0.12em] {{ $roleClasses }}">{{ $role }}</span>
        </div>
        <span class="shell-text-faint font-mono text-[10px] uppercase tracking-[0.12em]">{{ $meta }}</span>
    </div>

    <div class="shell-text mt-3 text-sm leading-6">
        {{ $slot }}
    </div>
</article>
