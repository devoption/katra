@props([
    'status',
    'message',
    'details' => [],
])

@php
    $statusTone = match ($status) {
        'connected' => 'text-emerald-200',
        'runtime-ready' => 'text-cyan-200',
        default => 'text-amber-200',
    };
@endphp

<div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
    <p class="font-mono text-[11px] uppercase tracking-[0.28em] text-slate-300/70">Surreal Foundation</p>
    <p class="mt-2">
        <span class="font-mono uppercase tracking-[0.2em] {{ $statusTone }}">{{ str_replace('-', ' ', $status) }}</span>
    </p>
    <p class="mt-2 text-sm text-slate-200/80">{{ $message }}</p>
    <dl class="mt-4 grid gap-3 text-xs text-slate-200/78 sm:grid-cols-2">
        @foreach ($details as $detail)
            <div class="rounded-xl border border-white/8 bg-white/4 px-3 py-2">
                <dt class="font-mono uppercase tracking-[0.24em] text-slate-400/80">{{ $detail['label'] }}</dt>
                <dd class="mt-2 break-all font-mono text-[11px] text-sky-100/88">{{ $detail['value'] }}</dd>
            </div>
        @endforeach
    </dl>
</div>
