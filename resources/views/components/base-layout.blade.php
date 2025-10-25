<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }} {{ isset($title) ? ' - ' . $title : '' }}</title>

    @filamentStyles
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    {{-- Font Awesome moved to npm package for better performance --}}
    @livewireStyles
    @livewireScripts

</head>

<body>

    {{ $slot }}

    @filamentScripts
    <script src="{{ asset('js/chart.min.js') }}"></script>
    @stack('scripts')

    @livewire('notifications')
</body>

</html>
