@props([
    'eyebrow' => null,
    'title' => null,
    'subtitle' => null,
    'tone' => 'slate',
    'contentClass' => 'space-y-4',
])

@php
    $toneClasses = match ($tone) {
        'cyan' => 'shell-panel',
        'sky' => 'shell-panel',
        'emerald' => 'shell-panel',
        'fuchsia' => 'shell-panel',
        'amber' => 'shell-panel',
        default => 'shell-panel',
    };

    $eyebrowClasses = match ($tone) {
        'cyan' => 'shell-text-info',
        'sky' => 'shell-text-info-strong',
        'emerald' => 'text-emerald-300',
        'fuchsia' => 'text-[var(--shell-accent)]',
        'amber' => 'text-amber-300',
        default => 'shell-text-faint',
    };
@endphp

<section {{ $attributes->class(['rounded-[22px] p-4', $toneClasses]) }}>
    @if ($eyebrow || $title || $subtitle)
        <header>
            @if ($eyebrow)
                <p class="font-mono text-[10px] uppercase tracking-[0.14em] {{ $eyebrowClasses }}">{{ $eyebrow }}</p>
            @endif
            @if ($title)
                <h2 class="shell-text mt-2 text-xl font-semibold tracking-[-0.03em]">{{ $title }}</h2>
            @endif
            @if ($subtitle)
                <p class="shell-text-soft mt-2 max-w-2xl text-sm leading-6">{{ $subtitle }}</p>
            @endif
        </header>
    @endif

    <div class="@if ($eyebrow || $title || $subtitle) mt-4 @endif {{ $contentClass }}">
        {{ $slot }}
    </div>
</section>
