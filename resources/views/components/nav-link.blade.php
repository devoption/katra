@props(['active' => false, 'href' => '#'])

<a href="{{ $href }}" {{ $attributes->merge([
    'class' => $active 
        ? 'flex items-center space-x-3 px-3 py-2 rounded-lg bg-nord8 bg-black/5 dark:bg-black/20 text-nord0 dark:text-nord6 font-medium transition-all duration-200' 
        : 'flex items-center space-x-3 px-3 py-2 rounded-lg text-nord3 dark:text-nord4 hover:bg-nord4 dark:hover:bg-nord2 hover:text-nord0 dark:hover:text-nord6 transition-all duration-200'
]) }}>
    @isset($icon)
        <span class="{{ $active ? 'text-nord8' : '' }}">
            {{ $icon }}
        </span>
    @endisset
    <span>{{ $slot }}</span>
</a>

