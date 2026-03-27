<div class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(180,142,173,0.18),_transparent_34%),linear-gradient(180deg,_var(--color-nord1),_var(--color-nord0))] px-6 py-12 sm:px-10 lg:px-14">
    <div class="mx-auto flex max-w-6xl flex-col gap-8">
        <div class="max-w-3xl">
            <p class="text-[11px] font-medium uppercase tracking-[0.32em] text-nord8">Frontend foundation</p>
            <h1 class="mt-4 text-4xl font-semibold tracking-tight text-nord6 sm:text-5xl">Katra UI Foundation</h1>
            <p class="mt-4 max-w-2xl text-base leading-7 text-nord4/80 sm:text-lg">
                Livewire 4 and Tailwind CSS v4 are now part of Katra's frontend foundation, sitting on top of the
                existing brand assets and Nord color system.
            </p>
        </div>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(0,0.95fr)]">
            <section class="rounded-[32px] border border-nord3/60 bg-nord1/90 p-7 shadow-[0_28px_80px_rgba(15,23,42,0.28)] backdrop-blur">
                <div class="flex flex-col gap-8 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-xl">
                        <img src="{{ asset('katra-logo.svg') }}" alt="Katra" class="h-12 w-auto" />
                        <p class="mt-6 text-sm uppercase tracking-[0.28em] text-nord8">Laravel + NativePHP + SurrealDB</p>
                        <h2 class="mt-4 text-3xl font-semibold text-nord6">A branded shell foundation that can grow in every runtime.</h2>
                        <p class="mt-4 text-sm leading-7 text-nord4/80 sm:text-base">
                            The current UI layer stays intentionally small: one Livewire-driven preview surface, the existing
                            Katra logo and wordmark, and the Nord palette tokens with <span class="font-semibold text-nord15">nord15</span>
                            carrying the primary accent role.
                        </p>
                    </div>

                    <div class="grid min-w-0 gap-3 sm:grid-cols-3 lg:grid-cols-1">
                        <div class="rounded-[24px] border border-nord3/60 bg-nord0/70 p-4">
                            <p class="text-[11px] uppercase tracking-[0.22em] text-nord8">Stack</p>
                            <p class="mt-3 text-lg font-semibold text-nord6">Livewire 4</p>
                            <p class="mt-1 text-sm text-nord4/75">Interactive Laravel components without leaving the product core.</p>
                        </div>
                        <div class="rounded-[24px] border border-nord3/60 bg-nord0/70 p-4">
                            <p class="text-[11px] uppercase tracking-[0.22em] text-nord8">Styling</p>
                            <p class="mt-3 text-lg font-semibold text-nord6">Tailwind CSS v4</p>
                            <p class="mt-1 text-sm text-nord4/75">CSS-first theme tokens backed by the Nord palette.</p>
                        </div>
                        <div class="rounded-[24px] border border-nord3/60 bg-nord0/70 p-4">
                            <p class="text-[11px] uppercase tracking-[0.22em] text-nord8">Preview</p>
                            <p class="mt-3 text-lg font-semibold text-nord6">/foundation-preview</p>
                            <p class="mt-1 text-sm text-nord4/75">A minimal landing surface to smoke-test the stack.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-[32px] border border-nord3/60 bg-nord2/90 p-7 shadow-[0_28px_80px_rgba(15,23,42,0.24)]">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] uppercase tracking-[0.28em] text-nord8">Livewire preview</p>
                        <h2 class="mt-3 text-2xl font-semibold text-nord6">Runtime surfaces</h2>
                        <p class="mt-3 text-sm leading-7 text-nord4/80">
                            Cycle through the intended Katra runtime targets to confirm the Livewire layer is active.
                        </p>
                    </div>

                    <button
                        type="button"
                        wire:click="cycleSurface"
                        class="inline-flex items-center rounded-full bg-nord15 px-4 py-2 text-sm font-semibold text-nord0 transition hover:brightness-110 focus:outline-none focus:ring-2 focus:ring-nord8/60 focus:ring-offset-2 focus:ring-offset-nord2"
                    >
                        Cycle surface
                    </button>
                </div>

                <div class="mt-6 grid gap-3 sm:grid-cols-3">
                    @foreach ($surfaces as $index => $surface)
                        <div class="@class([
                            'rounded-[22px] border px-4 py-4 transition',
                            'border-nord15 bg-nord15/12 shadow-[0_0_0_1px_rgba(180,142,173,0.18)]' => $surfaceIndex === $index,
                            'border-nord3/60 bg-nord0/55' => $surfaceIndex !== $index,
                        ])">
                            <p class="text-[11px] uppercase tracking-[0.22em] text-nord8">{{ $surface['label'] }}</p>
                            <p class="mt-3 text-base font-semibold text-nord6">{{ $surface['title'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 rounded-[24px] border border-nord3/60 bg-nord0/70 p-5">
                    <p class="text-[11px] uppercase tracking-[0.22em] text-nord8">Current focus</p>
                    <h3 class="mt-3 text-xl font-semibold text-nord6">{{ $activeSurface['title'] }}</h3>
                    <p class="mt-3 text-sm leading-7 text-nord4/80">{{ $activeSurface['detail'] }}</p>
                </div>
            </section>
        </div>
    </div>
</div>
