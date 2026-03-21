@props([
    'workspace' => null,
])

<div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
    <p class="font-mono text-[11px] uppercase tracking-[0.28em] text-slate-300/70">Preview Workspace</p>
    @if ($workspace)
        <p class="mt-2 font-semibold text-white">{{ $workspace->name }}</p>
        <p class="mt-2 font-mono text-xs uppercase tracking-[0.22em] text-cyan-100/72">{{ $workspace->id }}</p>
        <p class="mt-2 text-sm text-slate-200/80">{{ $workspace->summary }}</p>
    @else
        <p class="mt-2 text-sm text-slate-200/80">The runtime is visible now, but no Surreal-backed preview workspace has been materialized on this machine yet.</p>
    @endif
</div>
