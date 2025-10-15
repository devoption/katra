@props([
    'type' => 'info',
    'dismissible' => false,
])

@php
$baseClasses = 'rounded-lg p-4 border transition-all duration-200';

$typeClasses = [
    'info' => 'bg-nord8 bg-black/5 dark:bg-black/10 border-nord8 border-black/10 dark:border-black/20 text-nord0 dark:text-nord6',
    'success' => 'bg-nord14 bg-black/5 dark:bg-black/10 border-nord14 border-black/10 dark:border-black/20 text-nord0 dark:text-nord6',
    'warning' => 'bg-nord13 bg-black/5 dark:bg-black/10 border-nord13 border-black/10 dark:border-black/20 text-nord0 dark:text-nord6',
    'danger' => 'bg-nord11 bg-black/5 dark:bg-black/10 border-nord11 border-black/10 dark:border-black/20 text-nord0 dark:text-nord6',
];

$iconPaths = [
    'info' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    'success' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    'warning' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
    'danger' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
];

$classes = $baseClasses . ' ' . ($typeClasses[$type] ?? $typeClasses['info']);
$iconPath = $iconPaths[$type] ?? $iconPaths['info'];
@endphp

<div x-data="{ show: true }" x-show="show" x-transition {{ $attributes->merge(['class' => $classes]) }}>
    <div class="flex items-start">
        <div class="shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}" />
            </svg>
        </div>
        <div class="ml-3 flex-1">
            {{ $slot }}
        </div>
        @if($dismissible)
            <button @click="show = false" class="ml-3 shrink-0 -mr-1 -mt-1 p-1 rounded-lg hover:bg-black/5 dark:hover:bg-black/10 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        @endif
    </div>
</div>

