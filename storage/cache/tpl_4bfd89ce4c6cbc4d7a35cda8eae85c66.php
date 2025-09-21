<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags (includes charset, viewport, and SEO meta) -->
    <?php echo $this->renderMeta(); ?>
    
    <!-- Page title -->
    <title><?php echo $this->escape($this->getTitle()); ?></title>
    
    <!-- Favicon and app icons -->
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/android-chrome-512x512.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="shortcut icon" href="/favicon.ico">
    <link rel="manifest" href="/site.webmanifest">
    
    <!-- Additional head content from pages -->
    <?php $this->content('head'); ?>
    
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
    <link href="/dist/css/style.css" rel="stylesheet">
    
    <!-- Page-specific styles -->
    <?php echo $this->renderStyles(); ?>
    <?php $this->content('styles'); ?>
    
    <!-- Critical CSS for above-the-fold content (inline for performance) -->
    <style>
        /* Critical CSS - Add your most important styles here for faster loading */
        .flash-message { position: relative; z-index: 1050; }
        body { font-family: 'Montserrat', sans-serif; }
    </style>
</head>
<body>
    <!-- Skip to main content link for accessibility -->
    <a class="visually-hidden-focusable" href="#main-content">Skip to main content</a>
    
    <!-- Flash messages -->
    <?php echo display_flash_message(); ?>
    
    <!-- Main content wrapper -->
    <div id="main-content">
        <?php $this->content('content'); ?>
    </div>
    
    <!-- Core JavaScript -->
    <script src="/dist/js/jquery-3.7.1.min.js"></script>
    <script src="/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Glide.js -->
    <script src="https://cdn.jsdelivr.net/npm/@glidejs/glide/dist/glide.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="/dist/js/trees.js"></script>
    
    <!-- Page-specific scripts -->
    <?php echo $this->renderScripts(); ?>
    <?php $this->content('scripts'); ?>
    
    <!-- Analytics or tracking scripts (place at end for performance) -->
    <?php $this->content('analytics'); ?>
</body>
</html>