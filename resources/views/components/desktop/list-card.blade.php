@props([
    'label',
    'meta',
    'summary',
])

<div {{ $attributes->class(['shell-input rounded-[22px] px-4 py-4']) }}>
    <div class="flex items-center justify-between gap-3">
        <p class="shell-text text-sm font-semibold">{{ $label }}</p>
        <span class="shell-text-info font-mono text-[10px] uppercase tracking-[0.12em]">{{ $meta }}</span>
    </div>

    <p class="shell-text-soft mt-3 text-sm leading-6">{{ $summary }}</p>
</div>
