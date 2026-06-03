<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Strict evaluation to determine if a valid logged-in session exists
$is_logged_in = !empty($_SESSION['user_id']) || !empty($_SESSION['username']);
$username = $is_logged_in ? $_SESSION['username'] : '';

// --- SECURE PRIVILEGE EVALUATION FOR TARGETED CONTENT MANAGEMENT ---
$is_administrator = false;
$is_seller = false;

if ($is_logged_in && isset($_SESSION['role'])) {
    $session_role = $_SESSION['role'];
    
    // Check for Admin (Role 3)
    if ($session_role == 3 || $session_role === '3' || strtolower(trim($session_role)) === 'admin') {
        $is_administrator = true;
    }
    
    // Check for Seller (Role 2)
    if ($session_role == 2 || $session_role === '2' || strtolower(trim($session_role)) === 'seller') {
        $is_seller = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Andani Ya Vhathu</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', system-ui, sans-serif; }
        body { background-color: #f8f9fa; color: #212529; }
        
        /* THE TOP NAVIGATION BAR */
        .top-navbar {
            background-color: #6f42c1; /* Purple Branding Accent */
            color: #ffffff;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        /* LEFT CORNER SECTION */
        .left-menu-section {
            display: flex;
            align-items: center;
            gap: 15px;
            flex: 1;
            max-width: 300px;
        }
        
        /* THE 3 LINES TOGGLE BUTTON */
        .menu-toggle-btn {
            background: none;
            border: none;
            color: #ffffff;
            font-size: 1.6rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            padding: 4px;
            border-radius: 4px;
            transition: background 0.2s;
        }
        .menu-toggle-btn:hover { background-color: rgba(255, 255, 255, 0.15); }

        /* HI GREETING DISPLAY */
        .user-greeting {
            font-size: 0.95rem;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.15);
            padding: 4px 12px;
            border-radius: 12px;
            white-space: nowrap;
        }

        /* INLINE LOGIN LINK */
        .inline-login-link {
            color: #ffffff;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 6px 12px;
            border-radius: 6px;
            transition: background 0.2s;
            white-space: nowrap;
        }
        .inline-login-link:hover {
            background-color: rgba(255, 255, 255, 0.15);
        }
        
        /* CENTER BRAND LOGO */
        .brand-logo {
            font-size: 1.4rem;
            font-weight: 800;
            color: #ffffff;
            text-decoration: none;
            letter-spacing: -0.3px;
            text-align: center;
            padding: 0 15px;
        }
        
        /* RIGHT CORNER SECTION: SEARCH BAR */
        .right-search-section {
            flex: 1;
            max-width: 400px;
            position: relative;
            display: flex;
            justify-content: flex-end;
        }
        .search-form {
            width: 100%;
            position: relative;
        }
        .search-input {
            width: 100%;
            padding: 8px 16px 8px 38px;
            border-radius: 20px;
            border: none;
            background-color: rgba(255, 255, 255, 0.9);
            font-size: 0.9rem;
            color: #212529;
            outline: none;
        }
        .search-input:focus { background-color: #ffffff; }
        .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 0.95rem;
            z-index: 2;
        }
        
        /* HIDDEN OVERLAY DRAWER SLIDING FROM LEFT */
        .menu-drawer {
            position: fixed;
            top: 0;
            left: -320px; 
            width: 300px;
            height: 100%;
            background-color: #ffffff;
            box-shadow: 4px 0 15px rgba(0,0,0,0.15);
            z-index: 2000;
            transition: left 0.3s ease-in-out;
            display: flex;
            flex-direction: column;
        }
        .menu-drawer.open { left: 0; }
        
        .drawer-header {
            background-color: #6f42c1;
            color: #ffffff;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .drawer-close-btn {
            background: none;
            border: none;
            color: #ffffff;
            font-size: 1.4rem;
            cursor: pointer;
        }
        
        /* DRAWER NAVIGATION LISTS */
        .drawer-content { padding: 20px 10px; overflow-y: auto; }
        .section-title {
            text-transform: uppercase;
            font-weight: 700;
            color: #8a92a6;
            font-size: 0.72rem;
            letter-spacing: 0.8px;
            margin-bottom: 12px;
            padding-left: 12px;
            font-family: monospace;
        }
        .drawer-links-list { list-style: none; margin-bottom: 25px; }
        .drawer-item-link {
            display: flex;
            align-items: center;
            padding: 10px 14px;
            color: #2d3748;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 500;
            margin-bottom: 3px;
            transition: all 0.15s;
        }
        .drawer-item-link:hover { background-color: #f3ebff; color: #6f42c1; }
        
        /* DRAWER RED LOGOUT BUTTON ACCENT */
        .drawer-logout-btn {
            display: flex;
            align-items: center;
            width: calc(100% - 24px);
            margin: 10px 12px;
            padding: 12px 14px;
            color: #ffffff;
            background-color: #dc3545;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 700;
            transition: background 0.2s;
        }
        .drawer-logout-btn:hover { background-color: #bb2d3b; }
        
        .drawer-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            z-index: 1500;
            display: none;
        }
        .drawer-overlay.show { display: block; }
        .link-icon { margin-right: 15px; font-size: 1.15rem; }
    </style>
</head>
<body>

<header class="top-navbar">
    <div class="left-menu-section">
        <button class="menu-toggle-btn" onclick="toggleMenuDrawer(true)">
            <i class="bi bi-list"></i>
        </button>

        <?php if ($is_logged_in): ?>
            <div class="user-greeting">
                Hi, <?php echo htmlspecialchars($username); ?> 👋
            </div>
        <?php else: ?>
            <a href="login.php" class="inline-login-link">Login</a>
        <?php endif; ?>
    </div>
    
    <a href="index.php" class="brand-logo">Andani Ya Vhathu</a>
    
    <div class="right-search-section">
        <form action="category.php" method="GET" class="search-form">
            <i class="bi bi-search search-icon"></i>
            <input type="text" name="search" class="search-input" placeholder="Search marketplace...">
        </form>
    </div>
</header>

<div class="drawer-overlay" id="menuOverlay" onclick="toggleMenuDrawer(false)"></div>
<nav class="menu-drawer" id="menuDrawer">
    <div class="drawer-header">
        <span style="font-weight: 700; font-size: 1.1rem;">Menu Options</span>
        <button class="drawer-close-btn" onclick="toggleMenuDrawer(false)">&times;</button>
    </div>
    
    <div class="drawer-content">
        
        <?php if ($is_logged_in): ?>
            <p class="section-title">Account</p>
            <ul class="drawer-links-list">
                <?php if ($is_administrator): ?>
                    <li>
                        <a href="admin/dashboard.php" class="drawer-item-link" style="background-color: #1e1b4b; color: #ffffff; border: 1px solid #4338ca; font-weight: 700;">
                            <span class="link-icon">⚙️</span> Admin Workspace
                        </a>
                    </li>
                <?php endif; ?>
                
                <li>
                    <a href="profile.php" class="drawer-item-link">
                        <span class="link-icon">👤</span> My Profile Dashboard
                    </a>
                </li>

                <?php if ($is_seller): ?>
                    <li>
                        <a href="seller_dashboard.php" class="drawer-item-link" style="background-color: #f8f9fa; border: 1px solid #eef0f2;">
                            <span class="link-icon">📈</span> Seller Statistics
                        </a>
                    </li>
                <?php elseif (!$is_administrator): ?>
                    <li>
                        <a href="become_seller.php" class="drawer-item-link" style="background-color: #fffbeb; border: 1px solid #fef3c7; color: #d97706; font-weight: 600;">
                            <span class="link-icon">✨</span> Become a Seller
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>

        <p class="section-title">Shop Categories</p>
        <ul class="drawer-links-list">
            <li>
                <a href="category.php?type=academic">Textbooks & Stationery</a>
                    <span class="link-icon">📖</span> 
                </a>
            </li>
            <li>
                <a href="category.php?type=fashion">Fashion & Styling</a>
                    <span class="link-icon">✨</span> 
                </a>
            </li>
            <li>
                <a href="category.php?type=tech">Electronics & Tech</a>
                    <span class="link-icon">💻</span> 
                </a>
            </li>
        </ul>

        <hr style="border: 0; border-top: 1px solid #f1f3f5; margin: 15px 0;">

        <p class="section-title">Support</p>
        <ul class="drawer-links-list">
            <li>
                <a href="escrow_info.php" class="drawer-item-link">
                    <span class="link-icon">❓</span> How Escrow Works
                </a>
            </li>
        </ul>
        
        <?php if ($is_logged_in): ?>
            <div style="margin-top: 30px; border-top: 1px solid #f1f3f5; padding-top: 15px;">
                <a href="logout.php" class="drawer-logout-btn">
                    <i class="bi bi-box-arrow-right" style="margin-right: 12px; font-size: 1.1rem;"></i> Logout Account
                </a>
            </div>
        <?php endif; ?>
        
    </div>
</nav>

<script>
    function toggleMenuDrawer(open) {
        const drawer = document.getElementById('menuDrawer');
        const overlay = document.getElementById('menuOverlay');
        if (open) {
            drawer.classList.add('open');
            overlay.classList.add('show');
        } else {
            drawer.classList.remove('open');
            overlay.classList.remove('show');
        }
    }
</script>