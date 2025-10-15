@props([
    'label' => null,
    'name' => null,
    'checked' => false,
    'disabled' => false,
])

<div class="flex items-center">
    <input
        type="checkbox"
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $attributes->merge([
            'class' => 'w-4 h-4 rounded border-nord4 dark:border-nord2 text-nord8 focus:ring-2 focus:ring-nord8 focus:ring-offset-0 bg-nord6 dark:bg-nord0 transition-all duration-200',
            'checked' => $checked,
            'disabled' => $disabled,
        ]) }}
    >
    @if($label)
        <label for="{{ $name }}" class="ml-2 text-sm text-nord0 dark:text-nord4 cursor-pointer select-none">
            {{ $label }}
        </label>
    @endif
</div>

