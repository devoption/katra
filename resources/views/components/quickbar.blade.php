<div class="flex flex-col w-16 h-screen bg-primary">
    <div class="flex-1">
        <div class="relative">
            {{-- <x-katra-apptray-item></x-katra-apptray-item> --}}
            <a class="flex items-center justify-center w-16 h-16 text-slate-100 bg-primary filter hover:filter-brightness-85 hover:text-white" href="#" onclick="document.getElementById('appTray').classList.remove('hidden');">
                <x-fas-th class="h-6 filter filter-brightness-100" />
            </a>
            @include('katra::components.apptray')
        </div>
        <a class="flex items-center justify-center w-16 h-16 text-slate-100 bg-primary filter hover:filter-brightness-85 hover:text-white" href="{{ route('katra.home') }}">
            <x-fas-home class="h-6 filter filter-brightness-100" />
        </a>
    </div>

    <a class="flex items-center justify-center w-16 h-16 text-slate-100 bg-primary filter hover:filter-brightness-85 hover:text-white" href="{{ route('katra.profile') }}">
        <x-fas-user-circle class="h-6 filter filter-brightness-100" />
    </a>

    <a class="flex items-center justify-center w-16 h-16 text-slate-100 bg-primary filter hover:filter-brightness-85 hover:text-white" href="{{ route('katra.settings') }}">
        <x-fas-cog class="h-6 filter filter-brightness-100" />
    </a>
</div>
