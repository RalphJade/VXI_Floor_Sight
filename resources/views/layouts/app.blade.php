<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Built Assets - Production CSS -->
        @if (file_exists(public_path('build/manifest.json')))
            @php $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true); @endphp
            @if (isset($manifest['resources/css/app.css']))
                <link rel="stylesheet" href="{{ asset('build/' . $manifest['resources/css/app.css']['file']) }}">
            @endif
        @else
            @vite(['resources/css/app.css'])
        @endif
    </head>
    <body class="font-sans antialiased bg-slate-900 text-gray-100">
        <div class="min-h-screen bg-slate-900 flex flex-col">
            @include('layouts.navigation')

            <!-- Page Content -->
            <main class="flex-1">
                @yield('content')
            </main>


        <!-- Built Assets - Production JS -->
        @if (file_exists(public_path('build/manifest.json')))
            @php $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true); @endphp
            @if (isset($manifest['resources/js/app.js']))
                <script defer src="{{ asset('build/' . $manifest['resources/js/app.js']['file']) }}"></script>
            @endif
        @else
            @vite(['resources/js/app.js'])
        @endif
    </body>
</html>
