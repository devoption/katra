@props([
    'label',
    'tone' => 'slate',
])

@php
    $toneClasses = match ($tone) {
        'fuchsia' => 'bg-[#B48EAD]/16 text-[#ECEFF4]',
        'cyan' => 'bg-[#88C0D0]/14 text-[#E5E9F0]',
        'sky' => 'bg-[#81A1C1]/16 text-[#E5E9F0]',
        'emerald' => 'bg-[#A3BE8C]/14 text-[#E5E9F0]',
        'amber' => 'bg-[#EBCB8B]/14 text-[#E5E9F0]',
        default => 'shell-surface shell-text-soft',
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-full px-3 py-1 font-mono text-[10px] uppercase tracking-[0.12em]', $toneClasses]) }}>
    {{ $label }}
</span>
