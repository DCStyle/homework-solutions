<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <x-seo::meta />

    <title>@yield('title', setting('site_name'))</title>

    <meta charset="utf-8">
    <meta name="description" content="@yield('description', setting('site_description'))">
    <meta name="keywords" content="@yield('keywords', setting('site_keywords'))">
    <meta name="theme-color" content="#ffffff">

    <meta property="og:title" content="@yield('title', setting('site_name'))">
    <meta property="og:description" content="@yield('description', setting('site_description'))">

    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ setting('site_name') }}">
    <meta property="og:locale" content="vi_VN">
    <meta property="og:locale:alternate" content="en_US">

    <meta property="og:image" content="@yield('image', setting('site_og_image') ? asset(Storage::url(setting('site_og_image'))) : 'https://placehold.co/126')">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="@yield('image', setting('site_og_image') ? asset(Storage::url(setting('site_og_image'))) : 'https://placehold.co/126')">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ setting('site_favicon') ? asset(Storage::url(setting('site_favicon'))) : 'https://placehold.co/16' }}">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    @include('layouts.externalStylesheets')

    @vite('resources/css/app.css')

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        {{ setting('site_name', 'Homework Solutions') }}
    </title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Roboto" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/js/app.js'])

    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-QYXLDJP7G6"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-QYXLDJP7G6');
    </script>

    @stack('styles')
</head>
<body>
    <div id="app" class="min-h-full flex flex-col items-stretch">
        <!-- Include the Header -->
        @include('layouts.header')

        <!-- Main Content -->
        <main class="py-4 w-full overflow-x-hidden">
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
