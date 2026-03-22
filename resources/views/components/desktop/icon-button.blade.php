@props([
    'label',
    'dialogId' => null,
])

<button
    type="button"
    @if ($dialogId) onclick="document.getElementById('{{ $dialogId }}')?.showModal()" @endif
    {{ $attributes->class(['shell-icon-button inline-flex h-7 w-7 items-center justify-center rounded-full transition-colors']) }}
    aria-label="{{ $label }}"
    title="{{ $label }}"
>
    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
        <path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
    </svg>
</button>
