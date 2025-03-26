<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @hasSection('seo')
        @yield('seo')
    @else
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
            @include('includes.metadata')
        @endif
    @endif

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta property="og:image" content="@yield('image', setting('site_og_image') ? asset(Storage::url(setting('site_og_image'))) : 'https://placehold.co/126')">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="@yield('image', setting('site_og_image') ? asset(Storage::url(setting('site_og_image'))) : 'https://placehold.co/126')">
    <meta name="twitter:creator" content="{{ setting('site_creator') }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ setting('site_favicon') ? asset(Storage::url(setting('site_favicon'))) : 'https://placehold.co/16' }}">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    @include('layouts.externalStylesheets')
    @vite('resources/css/app.css')

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
        <main class="w-full overflow-x-hidden">
            @yield('content')
        </main>

        <!-- Include the Custom Footer Links -->
        <x-partials.footer-links />

        <!-- Include the Footer -->
        @include('layouts.footer')
    </div>

    <!-- Include external scripts -->
    @include('layouts.externalScripts')
    @stack('scripts')
</body>
</html>
