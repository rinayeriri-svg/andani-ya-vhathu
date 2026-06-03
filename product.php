<?php
session_start();
include '../config/db.php';

// Admin-only security check
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: ../index.php");
    exit();
}

$products = $conn->query("SELECT * FROM products ORDER BY product_id DESC");
include 'includes/admin_header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Marketplace Inventory</h2>
        <span class="badge bg-purple px-3 py-2">Escrow Protection Active</span>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Product & Category</th>
                        <th>Price</th>
                        <th>Meet-up Zone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $products->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $row['product_id']; ?></td>
                        <td>
                            <div class="fw-bold"><?php echo $row['title']; ?></div>
                            <small class="text-muted"><?php echo $row['category']; ?></small>
                        </td>
                        <td class="purple-text fw-bold">R<?php echo number_format($row['price'], 2); ?></td>
                        <td><i class="small">📍 <?php echo $row['meetup_zone']; ?></i></td>
                        <td><span class="badge bg-info text-dark"><?php echo $row['status']; ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-success">Verify</button>
                            <button class="btn btn-sm btn-outline-danger">Remove</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>