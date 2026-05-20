/**
 * Premium Admin Dashboard - Core JavaScript
 * Handles Sidebar, Theme Switching, Charts, and UI Interactions
 */

$(document).ready(function () {
    "use strict";

    // 1. Sidebar Toggle Logic
    $('.sidebar-toggler').on('click', function () {
        if ($(window).width() > 768) {
            $('#sidebar').toggleClass('collapsed');
            $('#content').toggleClass('expanded');
        } else {
            $('#sidebar').toggleClass('active');
        }
    });

    // Close sidebar on mobile when clicking outside or on a link
    $(document).on('click', function (e) {
        if ($(window).width() <= 768) {
            if (!$(e.target).closest('#sidebar, .sidebar-toggler').length && $('#sidebar').hasClass('active')) {
                $('#sidebar').removeClass('active');
            }
        }
    });

    // 2. Theme Switcher Logic
    const themeToggle = $('#themeToggle');
    const htmlTag = $('html');

    // Initialize theme based on local storage or system preference
    const savedTheme = localStorage.getItem('theme') || 'light';
    htmlTag.attr('data-bs-theme', savedTheme);
    updateThemeIcon(savedTheme);

    themeToggle.on('click', function () {
        const currentTheme = htmlTag.attr('data-bs-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';

        htmlTag.attr('data-bs-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeIcon(newTheme);

        // Update Chart.js defaults if charts exist
        if (typeof Chart !== 'undefined') {
            updateChartTheme(newTheme);
        }
    });

    function updateThemeIcon(theme) {
        const icon = themeToggle.find('i');
        if (theme === 'dark') {
            icon.removeClass('bi-moon-fill').addClass('bi-sun-fill');
        } else {
            icon.removeClass('bi-sun-fill').addClass('bi-moon-fill');
        }
    }

    // 3. Fullscreen Toggle
    $('#fullscreenToggle').on('click', function () {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(err => {
                console.error(`Error attempting to enable full-screen mode: ${err.message}`);
            });
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            }
        }
    });

    // 4. Password Visibility Toggle
    $('.password-toggle').on('click', function () {
        const input = $(this).siblings('input');
        const icon = $(this).find('i');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });

    // 5. Chart Initialization
    if ($('#revenueChart').length) {
        const ctx = $('#revenueChart')[0].getContext('2d');
        window.revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Revenue',
                    data: [12000, 19000, 15000, 25000, 22000, 30000],
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: getGridColor() }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    if ($('#deviceChart').length) {
        const ctx = $('#deviceChart')[0].getContext('2d');
        window.deviceChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Desktop', 'Mobile', 'Tablet'],
                datasets: [{
                    data: [55, 30, 15],
                    backgroundColor: ['#6366f1', '#10b981', '#f59e0b'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                },
                cutout: '70%'
            }
        });
    }

    function getGridColor() {
        return $('html').attr('data-bs-theme') === 'dark' ? '#334155' : '#e2e8f0';
    }

    function updateChartTheme(theme) {
        const gridColor = theme === 'dark' ? '#334155' : '#e2e8f0';
        const textColor = theme === 'dark' ? '#94a3b8' : '#64748b';

        Chart.defaults.color = textColor;
        Chart.defaults.borderColor = gridColor;

        if (window.revenueChart) {
            window.revenueChart.options.scales.y.grid.color = gridColor;
            window.revenueChart.options.scales.x.ticks.color = textColor;
            window.revenueChart.options.scales.y.ticks.color = textColor;
            window.revenueChart.update();
        }

        if (window.deviceChart) {
            window.deviceChart.options.plugins.legend.labels.color = textColor;
            window.deviceChart.update();
        }
    }
});
