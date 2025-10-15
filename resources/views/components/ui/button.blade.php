@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'disabled' => false,
])

@php
$baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

$variantClasses = [
    'primary' => 'bg-nord8 text-nord6 hover:bg-nord7 focus:ring-nord8 dark:bg-nord8 dark:text-nord0 dark:hover:bg-nord7',
    'secondary' => 'bg-nord9 text-nord6 hover:bg-nord10 focus:ring-nord9 dark:bg-nord9 dark:text-nord0 dark:hover:bg-nord10',
    'success' => 'bg-nord14 text-nord6 hover:bg-nord14/90 focus:ring-nord14 dark:bg-nord14 dark:text-nord0',
    'danger' => 'bg-nord11 text-nord6 hover:bg-nord11/90 focus:ring-nord11 dark:bg-nord11 dark:text-nord0',
    'warning' => 'bg-nord13 text-nord0 hover:bg-nord13/90 focus:ring-nord13 dark:bg-nord13 dark:text-nord0',
    'ghost' => 'bg-transparent text-nord0 hover:bg-nord4 dark:text-nord4 dark:hover:bg-nord2 focus:ring-nord8',
    'outline' => 'border-2 border-nord8 text-nord8 hover:bg-nord8 hover:text-nord6 focus:ring-nord8 dark:border-nord8 dark:text-nord8 dark:hover:bg-nord8 dark:hover:text-nord0',
];

$sizeClasses = [
    'sm' => 'px-3 py-1.5 text-sm',
    'md' => 'px-4 py-2 text-base',
    'lg' => 'px-6 py-3 text-lg',
    'xl' => 'px-8 py-4 text-xl',
];

$classes = $baseClasses . ' ' . ($variantClasses[$variant] ?? $variantClasses['primary']) . ' ' . ($sizeClasses[$size] ?? $sizeClasses['md']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes, 'disabled' => $disabled]) }}>
        {{ $slot }}
    </button>
@endif

