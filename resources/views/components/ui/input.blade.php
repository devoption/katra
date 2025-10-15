@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'error' => null,
    'help' => null,
    'required' => false,
    'disabled' => false,
])

<div {{ $attributes->only('class') }}>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-nord0 dark:text-nord4 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-nord11">*</span>
            @endif
        </label>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $attributes->except('class')->merge([
            'class' => 'w-full px-4 py-2 rounded-lg border transition-all duration-200 focus:outline-none focus:ring-2 ' .
                ($error
                    ? 'border-nord11 focus:ring-nord11 focus:border-nord11 bg-nord11/5'
                    : 'border-nord4 dark:border-nord2 bg-nord6 dark:bg-nord0 text-nord0 dark:text-nord4 focus:ring-nord8 focus:border-nord8'),
            'disabled' => $disabled,
            'required' => $required,
        ]) }}
    >

    @if($help && !$error)
        <p class="mt-1 text-sm text-nord3 dark:text-nord4">{{ $help }}</p>
    @endif

    @if($error)
        <p class="mt-1 text-sm text-nord11">{{ $error }}</p>
    @endif
</div>

