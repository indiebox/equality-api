<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        {{-- Page description --}}
        <meta name="description" content="@yield('description', 'Equality - Desktop app & API for tasks management.')">

        {{-- Site icon --}}
        {{-- <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
        <link rel="manifest" href="{{ asset('site.webmanifest') }}">
        <link rel="mask-icon" href="{{ asset('safari-pinned-tab.svg') }}" color="#1d1d1d">
        <meta name="msapplication-TileColor" content="#1d1d1d">
        <meta name="theme-color" content="#ffffff"> --}}

        <title>@yield('title', 'Equality')</title>

        @include('inc.scripts')
        @stack('scripts')
    </head>

    <body>
        {{-- Main content --}}
        @yield('content')
    </body>
</html>
