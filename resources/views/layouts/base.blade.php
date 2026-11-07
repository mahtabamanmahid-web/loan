{{--
    Root HTML shell shared by every layout (public, auth, customer, admin).
    - Dynamic title / SEO / favicon / brand colours from Admin → Website
    - Dark mode applied before paint (no flash)
--}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') | @endif{{ setting('seo_title') ?: site_name() }}</title>
    <meta name="description" content="@yield('meta_description', setting('seo_description') ?: setting('site_description'))">
    @if(setting('seo_keywords'))<meta name="keywords" content="{{ setting('seo_keywords') }}">@endif
    <meta property="og:title" content="@yield('title', site_name())">
    <meta property="og:description" content="{{ setting('seo_description') ?: setting('site_description') }}">
    <meta property="og:site_name" content="{{ site_name() }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    @if(setting('og_image'))<meta property="og:image" content="{{ public_asset(setting('og_image')) }}">@endif
    <meta name="theme-color" content="{{ setting('primary_color') }}">
    @if(setting('favicon'))
        <link rel="icon" href="{{ public_asset(setting('favicon')) }}">
    @else
        <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💳</text></svg>">
    @endif
    @yield('robots')

    <script>
        try {
            var t = localStorage.getItem('theme');
            if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) document.documentElement.classList.add('dark');
        } catch (e) {}
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset_v('vendor/aos.css') }}">
    <link rel="stylesheet" href="{{ asset_v('css/app.css') }}">
    <style>
        :root {
            --brand-primary: {{ hex_to_rgb((string) setting('primary_color')) }};
            --brand-secondary: {{ hex_to_rgb((string) setting('secondary_color')) }};
        }
    </style>
    @stack('head')
</head>
<body class="min-h-full @yield('body_class')">
    @yield('body')

    @include('partials.toasts')

    <script src="{{ asset_v('js/app.js') }}" defer></script>
    <script src="{{ asset_v('vendor/alpine-collapse.min.js') }}" defer></script>
    <script src="{{ asset_v('vendor/alpine.min.js') }}" defer></script>
    <script src="{{ asset_v('vendor/lucide.min.js') }}" defer></script>
    <script src="{{ asset_v('vendor/aos.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
