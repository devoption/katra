@extends('katra::layouts.base')

@section('content')
<div class="flex">
    @include('katra::components.quickbar')
    
    @include('katra::components.sidebar')
    
    <div id="canvas" class="flex-1 p-6">
        @yield('canvas')
    </div>
</div>
@endsection