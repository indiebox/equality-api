@extends('layouts.html')

@section('title')
    Equality - @yield('code')
@endsection

@push('scripts')
    <link type="text/css" rel="stylesheet" href="{{ mix('/css/style.css') }}">
@endpush

@section('all-content')
    @yield('pre-content')

    {{-- <div class="w-full absolute top-1/2 -translate-y-1/2">
        <div class="flex items-center justify-center py-4 sm:py-0 text-center">
            @hasSection('message')
                <div class="px-4 text-2xl text-white border-r border-white tracking-wider">@yield('code')</div>
                <div class="ml-4 text-lg text-white uppercase tracking-wider">@yield('message')</div>
            @else
                <div class="px-4 text-6xl text-white tracking-wider">@yield('code')</div>
            @endif
        </div>
    </div> --}}
@endsection
