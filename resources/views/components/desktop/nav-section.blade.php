@props([
    'label',
    'collapsible' => false,
    'open' => false,
    'actionLabel' => null,
    'actionDialogId' => null,
])

@php
    $sectionId = 'nav-section-'.md5($label.($actionDialogId ?? ''));
@endphp

@if ($collapsible)
    <div class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <p class="shell-text-faint font-mono text-[10px] uppercase tracking-[0.14em]">{{ $label }}</p>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    data-nav-section-toggle
                    data-nav-section-target="{{ $sectionId }}"
                    @class([
                        'shell-icon-button inline-flex h-7 w-7 items-center justify-center rounded-full transition-transform duration-150',
                        'rotate-180' => $open,
                    ])
                    aria-label="Toggle {{ $label }}"
                    aria-controls="{{ $sectionId }}"
                    aria-expanded="{{ $open ? 'true' : 'false' }}"
                >
                    <svg class="shell-text-info-strong h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M5 8 10 13 15 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>

                @if ($actionLabel && $actionDialogId)
                    <x-desktop.icon-button :label="$actionLabel" :dialog-id="$actionDialogId" />
                @endif
            </div>
        </div>

        <div id="{{ $sectionId }}" @class(['space-y-2', 'hidden' => ! $open])>
            {{ $slot }}
        </div>
    </div>
@else
    <div class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <p class="shell-text-faint font-mono text-[10px] uppercase tracking-[0.14em]">{{ $label }}</p>

            @if ($actionLabel && $actionDialogId)
                <x-desktop.icon-button :label="$actionLabel" :dialog-id="$actionDialogId" />
            @endif
        </div>
        <div class="space-y-2">
            {{ $slot }}
        </div>
    </div>
@endif
