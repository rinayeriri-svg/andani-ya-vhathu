<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- SECURE LEVEL-2 GATE ACCESS VALIDATION RESTRAINT (NUMERIC CHECK) ---
if (!isset($_SESSION['user_id']) || intval($_SESSION['role']) !== 3) {
    // Drop back down to application root login script context if not an admin (Role 3)
    header("Location: ../login.php");
    exit;
}

$admin_username = $_SESSION['username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Andani Portal - System Administration Command</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', system-ui, sans-serif; }
        body { background-color: #f1f5f9; color: #1e293b; padding-top: 70px; }
        
        .admin-navbar {
            background-color: #0c0290; /* Deep Indigo Theme Matrix Palette */
            color: #ffffff;
            position: fixed;
            top: 0; left: 0; right: 0; height: 70px;
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            z-index: 1000;
        }

        .nav-brand-group { display: flex; align-items: center; gap: 15px; }
        .nav-brand-title { font-weight: 800; font-size: 1.25rem; color: #ffffff; text-decoration: none; }
        .nav-badge { background: #9792cf; font-size: 0.75rem; padding: 3px 8px; border-radius: 4px; font-weight: 700; letter-spacing: 0.5px; }

        .nav-links-cluster { display: flex; align-items: center; gap: 8px; list-style: none; }
        .nav-link-item { color: #cbd5e1; text-decoration: none; font-weight: 600; font-size: 0.95rem; padding: 8px 14px; border-radius: 6px; transition: all 0.2s; display: flex; align-items: center; gap: 6px; }
        .nav-link-item:hover, .nav-link-item.active { background-color: rgba(255, 255, 255, 0.1); color: #ffffff; }

        .admin-profile-zone { display: flex; align-items: center; gap: 20px; font-size: 0.95rem; }
        .exit-link { color: #fda4af; text-decoration: none; font-weight: 700; transition: color 0.2s; display: inline-flex; align-items: center; gap: 4px; }
        .exit-link:hover { color: #91132c; }

        .container { max-width: 1250px; margin: 40px auto; padding: 0 25px; }
        .panel-card { background: #ffffff; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; }
        
        /* Global Framework Database Tables Typography Formats */
        .admin-table-view { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem; }
        .admin-table-view th { padding: 14px 16px; border-bottom: 2px solid #e2e8f0; color: #64748b; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; }
        .admin-table-view td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; color: #334155; }
        .admin-table-view tr:hover { background-color: #f8fafc; }
    </style>
</head>
<body>

    <header class="admin-navbar">
        <div class="nav-brand-group">
            <a href="dashboard.php" class="nav-brand-title">🌙 Andani Platform</a>
            <span class="nav-badge">ADMIN</span>
        </div>

        <nav>
            <ul class="nav-links-cluster">
                <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
                <a href="dashboard.php"><i class="bi bi-grid-fill"></i> Dashboard</a>
<a href="product_verifications.php"><i class="bi bi-check-circle"></i> Pending Reviews</a>
<a href="verified_products.php"><i class="bi bi-bag-check-fill"></i> Verified Products</a>
<a href="orders.php"><i class="bi bi-cart-fill"></i> Manage Orders</a>
<a href="audit.php"><i class="bi bi-journal-text"></i> Audit Logs</a>
            </ul>
        </nav>

        <div class="admin-profile-zone">
            <a href="../index.php" style="text-decoration: none; color: #cbd5e1; background-color: rgba(255,255,255,0.1); padding: 7px 14px; border-radius: 6px; font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; border: 1px solid rgba(255,255,255,0.15); transition: background 0.2s;"
               onmouseover="this.style.backgroundColor='rgba(255,255,255,0.2)'" onmouseout="this.style.backgroundColor='rgba(255,255,255,0.1)'">
                <i class="bi bi-globe"></i> View Main Storefront
            </a>

            <span style="color: #f8fafc;">Manager: <strong>@<?php echo htmlspecialchars($admin_username); ?></strong></span>
            <a href="../logout.php" class="exit-link"><i class="bi bi-box-arrow-right"></i> Log Out</a>
        </div>
    </header>