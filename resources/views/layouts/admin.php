<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags (includes charset, viewport, and SEO meta) -->
    @meta
    
    <!-- Page title -->
    <title>@title</title>
    
    <!-- Favicon and app icons -->
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/android-chrome-512x512.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="shortcut icon" href="/favicon.ico">
    <link rel="manifest" href="/site.webmanifest">
    
    <!-- Additional head content from pages -->
    @yield('head')
    
    <!-- Core CSS -->
    <link rel="stylesheet" href="/dist/css/bootstrap-icons.css">
    
    <!-- Bootstrap CSS -->
    <link href="/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Orbitron:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Glide.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@glidejs/glide/dist/css/glide.core.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@glidejs/glide/dist/css/glide.theme.min.css">
    
    <!-- Custom CSS -->
    <link href="/dist/css/admin.css" rel="stylesheet">
    
    <!-- Page-specific styles -->
    @styles
    @yield('styles')

    <!-- Critical CSS for above-the-fold content (inline for performance) -->
    <style>
        /* Critical CSS - Add your most important styles here for faster loading */
        .flash-message { position: relative; z-index: 1050; }
        /* body { font-family: 'Montserrat', sans-serif; } */
    </style>
</head>
<body>
    <!-- Skip to main content link for accessibility -->
    <a class="visually-hidden-focusable" href="#main-content">Skip to main content</a>

    @include('admin-nav')
    {{{ display_flash_message() }}}

    <div class="d-flex">
        @include('admin-sidebar')
        <main id="main-content" class="main-content">
            @yield('content')
        </main>
    </div>

    <!-- Core JavaScript -->
    <script src="/dist/js/jquery-3.7.1.min.js"></script>
    <script src="/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script src="/dist/js/trees.js"></script>
    <script src="/dist/js/admin.js"></script>
    
    <!-- Page-specific scripts -->
    @scripts
    @yield('scripts')
    
    <!-- Analytics or tracking scripts (place at end for performance) -->
    @yield('analytics')
</body>

</html>