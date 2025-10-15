@props([
    'title' => null,
    'padding' => true,
])

<div {{ $attributes->merge(['class' => 'bg-nord5 dark:bg-nord1 rounded-xl border border-nord4 dark:border-nord2 overflow-hidden transition-colors duration-200']) }}>
    @if($title)
        <div class="px-6 py-4 border-b border-nord4 dark:border-nord2">
            <h3 class="text-lg font-semibold text-nord0 dark:text-nord6">{{ $title }}</h3>
        </div>
    @endif

    <div class="{{ $padding ? 'p-6' : '' }}">
        {{ $slot }}
    </div>
</div>

