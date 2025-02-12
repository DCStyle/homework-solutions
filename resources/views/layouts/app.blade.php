<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <x-seo::meta />

    @include('layouts.externalStylesheets')

    @vite('resources/css/app.css')

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        {{ config('app.name', 'Laravel') }}
    </title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Roboto" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/js/app.js'])

    @stack('styles')
</head>
<body>
    <div id="app" class="min-h-full flex flex-col items-stretch">
        <!-- Include the Header -->
        @include('layouts.header')

        <!-- Main Content -->
        <main class="py-4">
            @yield('content')
        </main>

        @include('modals.mobile-category-modal')

        <!-- Include the Footer -->
        @include('layouts.footer')
    </div>

    <x-modal-search :is-admin="false" />

    <!-- Include external scripts -->
    @include('layouts.externalScripts')

    @stack('scripts')
</body>
</html>
