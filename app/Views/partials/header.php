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
        document.querySelectorAll('.nav-item a').forEach(item => {
            if (item.href === window.location.href) {
                item.parentElement.classList.add('active');
            }
        });
    </script>
</body>
</html>
