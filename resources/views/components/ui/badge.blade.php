@props([
    'variant' => 'default',
    'size' => 'md',
])

@php
$baseClasses = 'inline-flex items-center font-medium rounded-full';

$variantClasses = [
    'default' => 'bg-nord4 dark:bg-nord3 text-nord0 dark:text-nord4',
    'primary' => 'bg-primary text-nord3',
    'success' => 'bg-nord14 text-nord3',
    'danger' => 'bg-nord11 text-nord4',
    'warning' => 'bg-nord13 text-nord3',
    'info' => 'bg-nord15 text-nord4',
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

