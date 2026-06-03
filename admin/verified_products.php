<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || (int)$_SESSION['role'] !== 3) {
    header("Location: ../login.php");
    exit;
}

// Fetch only verified products
$verified_products = $conn->query("SELECT * FROM products WHERE status = 'verified'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Verified Products</title>
    <style>
        body { font-family: sans-serif; background: #f8fafc; padding: 40px; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; }
        th, td { padding: 15px; border-bottom: 1px solid #e2e8f0; text-align: left; }
    </style>
</head>
<body>
    <h2>Verified Products Inventory</h2>
    <table>
        <thead>
            <tr style="background: #f1f5f9;">
                <th>Product Name</th>
                <th>Price</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $verified_products->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['name'] ?? 'No Name Found'); ?></td>
                <td>R <?php echo number_format((float)($row['price'] ?? 0), 2); ?></td>
                <td><span style="color: green; font-weight: bold;">Verified</span></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <br><a href="dashboard.php">Back to Dashboard</a>
</body>
</html>