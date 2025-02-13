<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @if(isset($metadata))
        <title>{{ $metadata['title'] }}</title>

        @if($metadata['description'])
            <meta name="description" content="{{ $metadata['description'] }}">
        @endif

        @if($metadata['keywords'])
            <meta name="keywords" content="{{ $metadata['keywords'] }}">
        @endif

        @if($metadata['canonical'])
            <link rel="canonical" href="{{ $metadata['canonical'] }}">
        @endif

        @foreach($metadata['og_tags'] ?? [] as $property => $content)
            <meta property="{{ $property }}" content="{{ $content }}">
        @endforeach

        @foreach($metadata['twitter_tags'] ?? [] as $name => $content)
            <meta name="{{ $name }}" content="{{ $content }}">
        @endforeach
    @else
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
    @endif

    <x-seo::meta />

    <meta property="og:image" content="@yield('image', setting('site_og_image') ? asset(Storage::url(setting('site_og_image'))) : 'https://placehold.co/126')">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="@yield('image', setting('site_og_image') ? asset(Storage::url(setting('site_og_image'))) : 'https://placehold.co/126')">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ setting('site_favicon') ? asset(Storage::url(setting('site_favicon'))) : 'https://placehold.co/16' }}">

    <meta name="robots" content="noindex,nofollow">
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
