<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $this->escape($this->getTitle()); ?></title>
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/android-chrome-512x512.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="shortcut icon" href="/favicon.ico">
    <?php $this->content('header'); ?>
    <link rel="stylesheet" href="/dist/css/bootstrap-icons.css">
    <!-- Bootstrap CSS -->
    <link href="/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Orbitron:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="/dist/css/admin.css" rel="stylesheet">
    <?php $this->content('styles'); ?>
</head>

<body>
    <?php $this->partial('admin-nav'); ?>
    <?php echo $this->escape(display_flash_message()); ?>

    <div class="d-flex">
        <?php $this->partial('admin-sidebar'); ?>
        <main class="main-content">
            <?php $this->content('content'); ?>
        </main>
    </div>

    <script src="/dist/js/jquery-3.7.1.min.js"></script>
    <script src="/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/dist/js/trees.js"></script>
    <script src="/dist/js/admin.js"></script>
    <?php $this->content('scripts'); ?>
</body>

</html>