<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $this->getTitle() }}</title>
    @yield('header')
    <!-- Font Awesome -->
    <link rel="stylesheet" href="/dist/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Orbitron:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Summernote WYSIWYG editor -->
    <link href="/dist/css/summernote-lite.min.css" rel="stylesheet">
    <link href="/dist/css/admin.css" rel="stylesheet">
    @yield('styles')
</head>
<body>
    @include('admin-sidebar')
    <!-- Main Content -->
    <div class="main-content">
        {{ display_flash_message() }}
        @include('admin-nav')
        <!-- Page Content -->
        <div class="container-fluid">
            @yield('content')
        </div>
    </div>

    <script src="/dist/js/jquery-3.7.1.min.js"></script>
    <script src="/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/dist/js/aos.js"></script>
    <!-- Summernote WYSIWYG editor -->
    <script src="/dist/js/summernote-lite.min.js"></script>
    <script src="/dist/js/trees.js"></script>

    <script>
        // Toggle sidebar on mobile
        document.querySelector('.sidebar-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const sidebarToggle = document.querySelector('.sidebar-toggle');

            if (window.innerWidth < 992 &&
                !sidebar.contains(event.target) &&
                event.target !== sidebarToggle &&
                !sidebarToggle.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        });

        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            const revenueChart = new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Revenue',
                        data: [8500, 12500, 9800, 15000, 12000, 18000, 14500, 21000, 17500, 23000, 19500, 25000],
                        borderColor: '#6c5ce7',
                        backgroundColor: 'rgba(108, 92, 231, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Category Chart
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            const categoryChart = new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Smartphones', 'Laptops', 'Audio', 'Wearables', 'Accessories'],
                    datasets: [{
                        data: [35, 25, 20, 15, 5],
                        backgroundColor: [
                            '#6c5ce7',
                            '#00b894',
                            '#fd79a8',
                            '#fdcb6e',
                            '#0984e3'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
    </script>
    @yield('scripts')
</body>
</html>