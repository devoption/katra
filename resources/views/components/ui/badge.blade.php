@props([
    'variant' => 'default',
    'size' => 'md',
])

@php
$baseClasses = 'inline-flex items-center font-medium rounded-full';

$variantClasses = [
    'default' => 'bg-nord4 dark:bg-nord3 text-nord0 dark:text-nord4',
    'primary' => 'bg-nord8 bg-black/10 dark:bg-black/20 text-nord8',
    'success' => 'bg-nord14 bg-black/10 dark:bg-black/20 text-nord14',
    'danger' => 'bg-nord11 bg-black/10 dark:bg-black/20 text-nord11',
    'warning' => 'bg-nord13 bg-black/10 dark:bg-black/20 text-nord13',
    'info' => 'bg-nord15 bg-black/10 dark:bg-black/20 text-nord15',
];

$sizeClasses = [
    'sm' => 'px-2 py-0.5 text-xs',
    'md' => 'px-2.5 py-1 text-sm',
    'lg' => 'px-3 py-1.5 text-base',
];

$classes = $baseClasses . ' ' . ($variantClasses[$variant] ?? $variantClasses['default']) . ' ' . ($sizeClasses[$size] ?? $sizeClasses['md']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>

