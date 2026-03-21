@props([
    'label',
    'badge',
])

<section class="rounded-[32px] border border-white/10 bg-white/6 p-3 shadow-2xl shadow-slate-950/40 backdrop-blur">
    <div class="rounded-[26px] border border-white/10 bg-slate-950/65 px-5 py-4">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex gap-2">
                    <span class="h-3 w-3 rounded-full bg-rose-400"></span>
                    <span class="h-3 w-3 rounded-full bg-amber-300"></span>
                    <span class="h-3 w-3 rounded-full bg-emerald-400"></span>
                </div>
                <p class="font-mono text-xs uppercase tracking-[0.32em] text-sky-100/70">{{ $label }}</p>
            </div>

            <div class="rounded-full border border-white/10 bg-white/6 px-3 py-1 font-mono text-[11px] uppercase tracking-[0.28em] text-cyan-100/70">
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
