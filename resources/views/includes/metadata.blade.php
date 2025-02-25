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
