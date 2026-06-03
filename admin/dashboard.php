<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../config/db.php';

// Security Check: Ensure user is logged in and is an Admin (Role 3)
if (!isset($_SESSION['user_id']) || (int)$_SESSION['role'] !== 3) {
    header("Location: ../login.php");
    exit;
}

// Metrics Logic
$unverified_count = $conn->query("SELECT COUNT(*) as total FROM products WHERE status IN ('pending_review', '', NULL)")->fetch_assoc()['total'] ?? 0;
$escrow_held = $conn->query("SELECT SUM(price) as total FROM products WHERE status = 'pending_escrow'")->fetch_assoc()['total'] ?? 0.00;

$role_col = "role";
$res = $conn->query("SHOW COLUMNS FROM users");
while ($row = $res->fetch_assoc()) {
    if (in_array($row['Field'], ['role_id', 'user_type'])) { $role_col = $row['Field']; break; }
}
$total_members = $conn->query("SELECT COUNT(*) as total FROM users WHERE $role_col != 3")->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Control Center | Andaniyavhathu</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: #f8fafc; display: flex; }
        .sidebar { width: 260px; background: #1e293b; height: 100vh; color: #cbd5e1; padding: 20px; position: fixed; }
        .sidebar a { color: #cbd5e1; display: block; padding: 12px; text-decoration: none; border-radius: 6px; margin-bottom: 4px; }
        .sidebar a:hover { background: #334155; color: white; }
        .main { margin-left: 260px; padding: 40px; width: calc(100% - 260px); }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px; }
        .card { background: white; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0; }
        .table-wrap { background: white; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0; }
    </style>
</head>
<body>

<nav class="sidebar">
    <h2 style="font-size: 0.8rem; color: #94a3b8; margin-bottom: 20px;">CONTROL CENTER</h2>
    <a href="dashboard.php"><i class="bi bi-grid-fill"></i> Dashboard</a>
    <a href="orders.php"><i class="bi bi-cart-fill"></i> Manage Orders</a>
    <a href="users.php"><i class="bi bi-people-fill"></i> Users</a>
    <a href="audit.php"><i class="bi bi-journal-text"></i> Audit Logs</a>
    <a href="../logout.php" style="margin-top: 50px; color: #f87171;"><i class="bi bi-box-arrow-left"></i> Logout</a>
</nav>

<main class="main">
    <h2>System Overview Metrics</h2>
    <div class="grid">
        <div class="card" style="border-top: 4px solid #f59e0b;">
            <p style="font-size: 0.7rem; color: #64748b; font-weight: bold;">PENDING SAFETY REVIEW</p>
            <div style="font-size: 2rem; font-weight: 800;"><?php echo $unverified_count; ?></div>
        </div>
        <div class="card" style="border-top: 4px solid #10b981;">
            <p style="font-size: 0.7rem; color: #64748b; font-weight: bold;">COLLATERAL IN ESCROW</p>
            <div style="font-size: 2rem; font-weight: 800;">R <?php echo number_format((float)$escrow_held, 2); // Change 'pending_escrow' to whatever value showed up in your database
$escrow_held = $conn->query("SELECT SUM(price) as total FROM products WHERE status = 'escrow'")->fetch_assoc()['total'] ?? 0.00; ?></div>
        </div>
        <div class="card" style="border-top: 4px solid #3b82f6;">
            <p style="font-size: 0.7rem; color: #64748b; font-weight: bold;">REGISTERED ACCOUNTS</p>
            <div style="font-size: 2rem; font-weight: 800;"><?php echo $total_members; ?></div>
        </div>
    </div>

    <div class="table-wrap">
        <h3>Management Pipelines</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid #e2e8f0; text-align: left;">
                <th style="padding: 12px;">Module</th>
                <th style="padding: 12px;">Status</th>
                <th style="padding: 12px;">Actions</th>
            </tr>
            <tr>
                <td style="padding: 12px;">Product Verifications</td>
                <td style="padding: 12px;"><span style="background: #fef3c7; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">Requires Attention</span></td>
                <td style="padding: 12px;"><a href="product_verifications.php" style="color: #4338ca; font-weight: 600;">Execute CRUD &rarr;</a></td>
            </tr>
        </table>
    </div>
</main>
</body>
</html>