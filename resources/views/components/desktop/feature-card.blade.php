@props([
    'eyebrow',
    'title',
    'tone' => 'slate',
])

@php
    $toneClasses = match ($tone) {
        'cyan' => 'bg-[#3B4252] text-[#88C0D0]',
        'sky' => 'bg-[#3B4252] text-[#81A1C1]',
        'emerald' => 'bg-[#3B4252] text-[#A3BE8C]',
        default => 'bg-[#3B4252] text-[#D8DEE9]/72',
    };
@endphp

<article {{ $attributes->class(['rounded-[22px] p-4', $toneClasses]) }}>
    <p class="font-mono text-[10px] uppercase tracking-[0.12em]">{{ $eyebrow }}</p>
    <h2 class="mt-2 text-xl font-semibold text-white">{{ $title }}</h2>
    <div class="mt-2 text-sm leading-6 text-slate-200/78">
        {{ $slot }}
    </div>
</article>
