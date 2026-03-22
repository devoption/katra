@props([
    'name',
    'email',
    'initials',
])

<details data-profile-menu class="group relative mt-4 border-t pt-4 shell-border">
    <summary class="shell-surface flex cursor-pointer list-none items-center gap-3 rounded-[20px] px-3 py-3 marker:hidden">
        <span class="shell-accent-chip flex h-10 w-10 items-center justify-center rounded-2xl text-sm font-semibold tracking-[0.02em]">
            {{ $initials }}
        </span>

        <div class="min-w-0 flex-1">
            <p class="shell-text truncate text-sm font-semibold">{{ $name }}</p>
            <p class="shell-text-subtle truncate text-sm">{{ $email }}</p>
        </div>

        <div class="flex items-center gap-2">
            <svg class="shell-text-info-strong h-3.5 w-3.5 shrink-0 transition-transform duration-150 group-open:rotate-180" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <path d="M5 8 10 13 15 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>
    </summary>

    <div class="shell-panel shell-shadow shell-border absolute inset-x-0 bottom-full z-40 mb-3 rounded-[24px] border p-3">
        <div class="space-y-3">
            <div class="shell-surface flex items-center gap-3 rounded-[20px] p-4">
                <span class="shell-accent-chip flex h-12 w-12 items-center justify-center rounded-2xl text-base font-semibold tracking-[0.02em]">
                    {{ $initials }}
                </span>
                <div class="min-w-0">
                    <p class="shell-text truncate text-sm font-semibold">{{ $name }}</p>
                    <p class="shell-text-subtle mt-1 truncate text-sm">{{ $email }}</p>
                </div>
            </div>

            <div class="space-y-1">
                <button type="button" class="shell-text shell-hover-surface flex w-full items-center rounded-[18px] px-4 py-2.5 text-left text-sm transition-colors">
                    <span>Profile settings</span>
                </button>
                <button type="button" class="shell-text shell-hover-surface flex w-full items-center rounded-[18px] px-4 py-2.5 text-left text-sm transition-colors">
                    <span>Workspace settings</span>
                </button>
                <button type="button" class="shell-text shell-hover-surface flex w-full items-center rounded-[18px] px-4 py-2.5 text-left text-sm transition-colors">
                    <span>Administration</span>
                </button>
                <button type="button" class="shell-text shell-hover-surface flex w-full items-center rounded-[18px] px-4 py-2.5 text-left text-sm transition-colors">
                    <span>Manage connections</span>
                </button>
                <button type="button" class="shell-text shell-hover-surface flex w-full items-center rounded-[18px] px-4 py-2.5 text-left text-sm transition-colors">
                    <span>Notifications</span>
                </button>
            </div>

            <div class="shell-surface rounded-[20px] p-3">
                <p class="shell-text-faint font-mono text-[10px] uppercase tracking-[0.12em]">Theme</p>

                <div class="shell-theme-track mt-3 grid grid-cols-3 gap-2 rounded-[16px] p-1.5">
                    <button type="button" data-theme-option="light" class="shell-theme-option rounded-[12px] px-3 py-2 text-sm font-medium transition-colors">
                        Light
                    </button>
                    <button type="button" data-theme-option="dark" data-theme-active="true" class="shell-theme-option rounded-[12px] px-3 py-2 text-sm font-medium transition-colors">
                        Dark
                    </button>
                    <button type="button" data-theme-option="system" class="shell-theme-option rounded-[12px] px-3 py-2 text-sm font-medium transition-colors">
                        System
                    </button>
                </div>
            </div>

            <button type="button" class="shell-danger-button flex w-full items-center justify-center rounded-[18px] px-5 py-2.5 text-sm font-medium transition-colors">
                <span>Log out</span>
            </button>
        </div>
    </div>
</details>
