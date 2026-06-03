<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || (int)$_SESSION['role'] !== 3) header("Location: ../login.php");

// Handle inbound execution actions from administrative approval buttons
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_type'])) {
    $target_id = intval($_POST['target_product_id']);
    
    if ($_POST['action_type'] === 'approve') {
        $update_stmt = $conn->prepare("UPDATE products SET status = 'verified' WHERE product_id = ?");
        $update_stmt->bind_param("i", $target_id);
        $update_stmt->execute();
        $update_stmt->close();
    } elseif ($_POST['action_type'] === 'reject') {
        $delete_stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $delete_stmt->bind_param("i", $target_id);
        $delete_stmt->execute();
        $delete_stmt->close();
    }
    header("Location: products.php");
    exit;
}

// --- DYNAMICALLY RESOLVE USER PRIMARY KEY TO PREVENT JOIN CRASHES ---
$user_pk_column = "id"; // Default fallback
$columns_check = $conn->query("SHOW COLUMNS FROM users");
if ($columns_check) {
    while ($col = $columns_check->fetch_assoc()) {
        $field_name = strtolower($col['Field']);
        if ($field_name === 'user_id') {
            $user_pk_column = "user_id";
            break;
        }
    }
}

// Update your query in products.php to this:
$review_result = $conn->query("
    SELECT p.*, u.username FROM products p 
    JOIN users u ON p.seller_id = u.$user_pk_column 
    WHERE p.status IN ('pending', 'pending_review', '', NULL) 
    OR p.status IS NULL 
    ORDER BY p.product_id ASC
");
include 'includes/admin_header.php';
?>

<div class="container">
    <div class="panel-card">
        <div style="border-bottom: 1px solid #edf2f7; padding-bottom: 15px; margin-bottom: 25px;">
            <h2 style="font-weight: 800; color: #0f172a; font-size: 1.4rem;"><i class="bi bi-shield-check" style="color: #4338ca; margin-right: 6px;"></i> Product Verification Pipeline</h2>
            <p style="color: #64748b; font-size: 0.9rem; margin-top: 4px;">Vet product titles, metrics data, and catalog listing constraints before going public.</p>
        </div>

        <div style="overflow-x: auto;">
            <table class="admin-table-view">
                <thead>
    <tr>
        <th>ID</th>
        <th>Preview</th> <th>Product Details Specification</th>
        <th>Category</th>
        <th>Vendor</th>
        <th>Price</th>
        <th style="text-align: center;">Controls</th>
    </tr>
</thead>
               <tbody>
    <?php while ($prod = $review_result->fetch_assoc()): ?>
    <tr>
        <td style="font-weight: 700; color: #64748b;">#<?php echo $prod['product_id']; ?></td>
        
        <td>
            <?php if (!empty($prod['image_path']) && file_exists('../' . $prod['image_path'])): ?>
                <img src="../<?php echo htmlspecialchars($prod['image_path']); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px;">
            <?php else: ?>
                <div style="width: 60px; height: 60px; background: #e2e8f0; display: flex; align-items: center; justify-content: center; border-radius: 6px; color: #94a3b8;">
                    <i class="bi bi-image"></i>
                </div>
            <?php endif; ?>
        </td>

        <td>
            <div style="font-weight: 700; color: #0f172a;"><?php echo htmlspecialchars($prod['title']); ?></div>
            <div style="font-size: 0.85rem; color: #64748b; max-width: 300px;"><?php echo htmlspecialchars($prod['description']); ?></div>
        </td>
        
        <td><?php echo htmlspecialchars($prod['category'] ?? 'N/A'); ?></td>
        <td><?php echo htmlspecialchars($prod['username'] ?? 'Unknown'); ?></td>
        <td>R <?php echo number_format($prod['price'], 2); ?></td>

        <!-- THIS IS THE MISSING PART: Controls -->
        <td style="text-align: center;">
            <form method="POST" style="display: flex; gap: 5px; justify-content: center;">
                <input type="hidden" name="target_product_id" value="<?php echo $prod['product_id']; ?>">
                
                <button type="submit" name="action_type" value="approve" style="background: #27ae60; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">
                    <i class="bi bi-check-lg"></i>
                </button>
                
                <button type="submit" name="action_type" value="reject" style="background: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</tbody>
            </table>
        </div>
    </div>
    
</div>
</body>
</html>