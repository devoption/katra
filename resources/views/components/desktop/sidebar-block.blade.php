@props([
    'eyebrow',
    'title' => null,
    'tone' => 'slate',
    'contentClass' => 'text-sm leading-6 text-slate-200/78',
])

@php
    $toneClasses = match ($tone) {
        'cyan' => 'border-cyan-200/12 bg-cyan-300/8',
        'fuchsia' => 'border-fuchsia-200/15 bg-fuchsia-300/10',
        'amber' => 'border-amber-200/12 bg-amber-300/8',
        default => 'border-white/10 bg-white/5',
    };

    $eyebrowClasses = match ($tone) {
        'cyan' => 'text-cyan-100/72',
        'fuchsia' => 'text-fuchsia-100/78',
        'amber' => 'text-amber-100/78',
        default => 'text-slate-300/72',
    };
@endphp

<div {{ $attributes->class(['rounded-[28px] border p-5', $toneClasses]) }}>
    <p class="font-mono text-[11px] uppercase tracking-[0.28em] {{ $eyebrowClasses }}">{{ $eyebrow }}</p>
    @if ($title)
        <h2 class="mt-3 text-2xl font-semibold tracking-[-0.04em] text-white">{{ $title }}</h2>
    @endif
    <div class="@if($title) mt-3 @else mt-4 @endif {{ $contentClass }}">
        {{ $slot }}
    </div>
</div>
