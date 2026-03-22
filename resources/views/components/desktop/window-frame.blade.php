@props([
    'label',
    'badge',
])

<section class="rounded-[24px] bg-[#3B4252] p-3">
    <div class="rounded-[22px] bg-[#3B4252] px-4 py-3">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex gap-2">
                    <span class="h-3 w-3 rounded-full bg-[#BF616A]"></span>
                    <span class="h-3 w-3 rounded-full bg-[#EBCB8B]"></span>
                    <span class="h-3 w-3 rounded-full bg-[#A3BE8C]"></span>
                </div>
                <p class="font-mono text-[11px] uppercase tracking-[0.16em] text-[#D8DEE9]/68">{{ $label }}</p>
            </div>

            <div class="rounded-full bg-[#434C5E] px-3 py-1 font-mono text-[10px] uppercase tracking-[0.14em] text-[#E5E9F0]/72">
                {{ $badge }}
            </div>
        </div>

        @if (trim($slot) !== '')
            <div class="mt-4">
                {{ $slot }}
            </div>
        @endif
    </div>
</section>
