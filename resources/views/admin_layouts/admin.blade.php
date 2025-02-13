<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="overflow-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @include('layouts.externalStylesheets')

    @vite('resources/css/app.css')

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ setting('site_name', 'Homework Solutions') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Roboto" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/js/app.js'])

    <!-- Styles -->
    @stack('styles')
</head>
<body class="bg-gray-100">
    <div id="app">
        <div class="flex h-screen overflow-hidden">
            @include('admin_layouts.sidebar')

            <!-- ===== Content Area Start ===== -->
            <div class="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden">
                @include('admin_layouts.header')

                <!-- ===== Main Content Start ===== -->
                <main>
                    <div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
                        @yield('content')
                    </div>
                </main>
                <!-- ===== Main Content End ===== -->
            </div>
            <!-- ===== Content Area End ===== -->
        </div>
    </div>

    <!-- Include external scripts -->
    @include('layouts.externalScripts');

    @stack('scripts')
</body>
</html>
