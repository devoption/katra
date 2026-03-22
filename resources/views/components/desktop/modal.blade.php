@props([
    'id',
    'title',
    'description' => null,
])

<dialog id="{{ $id }}" data-shell-modal {{ $attributes->class(['shell-panel shell-text shell-shadow shell-border m-auto w-full max-w-md rounded-[24px] border p-0']) }}>
    <div class="space-y-5 p-5">
        <header class="space-y-2">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="shell-text text-lg font-semibold tracking-[-0.02em]">{{ $title }}</h2>
                    @if ($description)
                        <p class="shell-text-soft mt-2 text-sm leading-6">{{ $description }}</p>
                    @endif
                </div>

                <form method="dialog">
                    <button type="submit" class="shell-icon-button inline-flex h-8 w-8 items-center justify-center rounded-full transition-colors" aria-label="Close modal">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M6 6 14 14M14 6 6 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        </svg>
                    </button>
                </form>
            </div>
        </header>

        {{ $slot }}
    </div>
</dialog>
