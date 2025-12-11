<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> - St. Peter Hospital</title>
    <link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Unified Sidebar for all roles -->
    <?= $this->include('partials/sidebar') ?>

    <!-- Main Content -->
    <div class="main-content">
        <?= $this->renderSection('content') ?>
    </div>

    <script>
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        const toggleBtn = document.querySelector('.toggle-btn');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('closed');
            mainContent.classList.toggle('zoomed');
        });

        // Submenu toggle function
        function toggleSubmenu(element) {
            const parentLi = element.parentElement;
            const submenu = parentLi.querySelector('.submenu');
            
            // Close other open submenus
            document.querySelectorAll('.nav-item.expandable').forEach(item => {
                if (item !== parentLi) {
                    item.classList.remove('expanded');
                    const otherSubmenu = item.querySelector('.submenu');
                    if (otherSubmenu) {
                        otherSubmenu.classList.remove('show');
                    }
                }
            });
            
            // Toggle current submenu
            parentLi.classList.toggle('expanded');
            if (submenu) {
                submenu.classList.toggle('show');
            }
        }

        // Active link highlight
        function setActiveMenuItem() {
            const currentPath = window.location.pathname.replace(/\/$/, ''); // Remove trailing slash
            let activeFound = false;
            
            // First, remove all active classes and collapse all submenus
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active', 'expanded');
            });
            document.querySelectorAll('.submenu').forEach(submenu => {
                submenu.classList.remove('show');
            });
            
            // Helper function to normalize path
            function normalizePath(url) {
                try {
                    return new URL(url, window.location.origin).pathname.replace(/\/$/, '');
                } catch (e) {
                    return url.replace(/\/$/, '');
                }
            }
            
            // Check submenu items first (more specific)
            document.querySelectorAll('.submenu a').forEach(item => {
                const linkPath = normalizePath(item.href);
                if (linkPath === currentPath) {
                    item.parentElement.classList.add('active');
                    // Mark parent expandable item as active and expand it
                    const parentExpandable = item.closest('.nav-item.expandable');
                    if (parentExpandable) {
                        parentExpandable.classList.add('active', 'expanded');
                        const submenu = parentExpandable.querySelector('.submenu');
                        if (submenu) {
                            submenu.classList.add('show');
                        }
                    }
                    activeFound = true;
                }
            });
            
            // If no submenu item is active, check main menu items
            if (!activeFound) {
                document.querySelectorAll('.nav-item:not(.expandable) a, .nav-item.expandable > a[href!="#"]').forEach(item => {
                    const linkPath = normalizePath(item.href);
                    if (linkPath === currentPath) {
                        item.parentElement.classList.add('active');
                        activeFound = true;
                    }
                });
            }
        }
        
        // Set active menu item on page load
        setActiveMenuItem();
    </script>
</body>
</html>
