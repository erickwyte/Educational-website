<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel Header</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-green: #003300;
            --primary-green-hover: #006400;
            --light-green: #e8f5e8;
            --text-dark: #222;
            --text-medium: #444;
            --text-light: #666;
            --background: #f8f9fa;
            --card-bg: #ffffff;
            --border-color: #e0e0e0;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 6px 16px rgba(0, 0, 0, 0.12);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--background);
            color: var(--text-dark);
            line-height: 1.6;
            padding: 0;
            margin: 0;
            padding-top: 70px; /* Account for fixed header */
        }

        /* Header Styles */
        .header {
            background: linear-gradient(to right, var(--primary-green), var(--primary-green-hover));
            color: white;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
        }

        .header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }

        .header h2 a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition);
        }

        .header h2 a:hover {
            color: var(--light-green);
        }

        .header h2 a i {
            font-size: 26px;
        }

        .menu-toggle {
            display: none;
            font-size: 24px;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.1);
            padding: 5px 10px;
            border-radius: 4px;
            transition: var(--transition);
        }

        .menu-toggle:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .nav-menu {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            align-items: center;
            gap: 5px;
        }

        .nav-menu li {
            position: relative;
        }

        .nav-menu li a {
            color: white;
            text-decoration: none;
            padding: 12px 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            border-radius: 6px;
            transition: var(--transition);
            font-weight: 500;
        }

        .nav-menu li a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-menu li a i {
            font-size: 18px;
        }

        .nav-menu li a.active {
            background: rgba(255, 255, 255, 0.2);
        }

        .logout-btn {
            background-color: #e74c3c !important;
            color: white !important;
            padding: 10px 15px !important;
            font-weight: 600 !important;
            border: none;
            border-radius: 6px !important;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logout-btn:hover {
            background-color: #c0392b !important;
            transform: translateY(-2px);
        }

        /* Content area for demonstration */
        .content {
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .content-section {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: var(--shadow);
        }

        .content-section h1 {
            color: var(--primary-green);
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light-green);
        }

        .content-section h2 {
            color: var(--primary-green);
            margin: 25px 0 15px;
        }

        .content-section p {
            color: var(--text-medium);
            margin-bottom: 15px;
            line-height: 1.7;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 25px 0;
        }

        .stat-card {
            background: var(--light-green);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary-green);
            margin-bottom: 10px;
        }

        .stat-label {
            color: var(--text-medium);
            font-weight: 500;
        }

        .recent-activity {
            list-style: none;
            margin-top: 20px;
        }

        .recent-activity li {
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .recent-activity li:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            background: var(--light-green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-green);
            font-size: 18px;
        }

        .activity-content {
            flex: 1;
        }

        .activity-content p {
            margin: 0;
        }

        .activity-time {
            color: var(--text-light);
            font-size: 14px;
        }

        /* Responsive styles */
        @media (max-width: 992px) {
            .nav-menu {
                gap: 0;
            }
            
            .nav-menu li a {
                padding: 10px 12px;
                font-size: 14px;
            }
            
            .nav-menu li a i {
                font-size: 16px;
            }
        }

        @media (max-width: 768px) {
            .header {
                padding: 0 15px;
            }
            
            .menu-toggle {
                display: block;
            }
            
            .nav-menu {
                position: fixed;
                top: 70px;
                left: 0;
                width: 100%;
                background: linear-gradient(to bottom, var(--primary-green), var(--primary-green-hover));
                flex-direction: column;
                padding: 20px;
                gap: 10px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
                z-index: 1000;
                transform: translateY(-100%);
                opacity: 0;
                visibility: hidden;
                transition: var(--transition);
            }
            
            .nav-menu.active {
                transform: translateY(0);
                opacity: 1;
                visibility: visible;
            }
            
            .nav-menu li {
                width: 100%;
            }
            
            .nav-menu li a {
                padding: 15px;
                justify-content: center;
                border-radius: 6px;
                background: rgba(255, 255, 255, 0.05);
            }
            
            .logout-btn {
                justify-content: center;
                margin-top: 10px;
            }

            body {
                padding-top: 70px;
            }
        }

        @media (max-width: 480px) {
            .header h2 {
                font-size: 20px;
            }
            
            .header h2 a i {
                font-size: 22px;
            }
            
            .content {
                padding: 20px 15px;
            }
            
            .content-section {
                padding: 20px;
            }
            
            .content-section h1 {
                font-size: 24px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Animation for menu items */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .nav-menu.active li {
            animation: fadeIn 0.4s ease forwards;
        }

        .nav-menu.active li:nth-child(1) { animation-delay: 0.1s; }
        .nav-menu.active li:nth-child(2) { animation-delay: 0.2s; }
        .nav-menu.active li:nth-child(3) { animation-delay: 0.3s; }
        .nav-menu.active li:nth-child(4) { animation-delay: 0.4s; }
        .nav-menu.active li:nth-child(5) { animation-delay: 0.5s; }
        .nav-menu.active li:nth-child(6) { animation-delay: 0.6s; }
        .nav-menu.active li:nth-child(7) { animation-delay: 0.7s; }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <h2><a href="admin_panel.php"><i class="fas fa-cogs"></i> Admin Panel</a></h2>
        <span class="menu-toggle" onclick="toggleMenu()">☰</span>
        <ul class="nav-menu">
            <li><a href="admin_panel.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="notes_admin.php"><i class="fas fa-file-pdf"></i> Notes</a></li>
            <li><a href="questions_admin.php"><i class="fas fa-question-circle"></i> Questions</a></li>
            <li><a href="admin_blog.php"><i class="fas fa-blog"></i> Blog</a></li>
            <li><a href="content_management.php"><i class="fas fa-tasks"></i> Management</a></li>
            <li><a href="../index.php"><i class="fas fa-globe"></i> Website</a></li>
            <li>
                <button class="logout-btn" onclick="window.location.href='logout.php'">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </li>
        </ul>
    </header>


    <script>
        function toggleMenu() {
            document.querySelector(".nav-menu").classList.toggle("active");
        }
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.querySelector('.nav-menu');
            const toggle = document.querySelector('.menu-toggle');
            
            if (!menu.contains(event.target) && !toggle.contains(event.target) && menu.classList.contains('active')) {
                menu.classList.remove('active');
            }
        });
        
        // Add active class to current page
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const links = document.querySelectorAll('.nav-menu a');
            
            links.forEach(link => {
                const href = link.getAttribute('href');
                if (href === currentPage) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>