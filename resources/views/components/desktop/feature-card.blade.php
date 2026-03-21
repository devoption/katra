@props([
    'eyebrow',
    'title',
    'tone' => 'slate',
])

@php
    $toneClasses = match ($tone) {
        'cyan' => 'border-cyan-200/12 bg-cyan-300/8 text-cyan-100/70',
        'sky' => 'border-sky-200/12 bg-sky-300/8 text-sky-100/70',
        'emerald' => 'border-emerald-200/12 bg-emerald-300/8 text-emerald-100/70',
        default => 'border-white/10 bg-white/5 text-slate-300/72',
    };
@endphp

<article {{ $attributes->class(['rounded-[28px] border p-5', $toneClasses]) }}>
    <p class="font-mono text-[11px] uppercase tracking-[0.28em]">{{ $eyebrow }}</p>
    <h2 class="mt-4 text-2xl font-semibold text-white">{{ $title }}</h2>
    <div class="mt-3 text-sm leading-7 text-slate-200/78">
        {{ $slot }}
    </div>
</article>
