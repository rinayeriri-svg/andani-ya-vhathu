<?php
include '../config/db.php';
include 'includes/admin_header.php';

// Handle order status updates if needed (e.g., marking as shipped/completed)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['order_status'];
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();
    $stmt->close();
    
    
    exit;
}

// Fetch orders joined with product details and buyer/seller information
// Note: Adjust the table/column names if your database layout differs slightly
$orders_query = "SELECT o.*, p.title AS product_title, p.price, b.username AS buyer_name 
                 FROM orders o 
                 JOIN products p ON o.product_id = p.product_id
                 JOIN users b ON o.buyer_id = b.user_id 
                 ORDER BY o.order_id DESC";

$orders_result = $conn->query($orders_query);
?>

<div class="container">
    <div class="panel-card">
        <div style="border-bottom: 1px solid #edf2f7; padding-bottom: 15px; margin-bottom: 25px;">
            <h2 style="font-weight: 800; color: #0f172a; font-size: 1.4rem;">
                <i class="bi bi-receipt" style="color: #10b981; margin-right: 6px;"></i> Order & Transaction Tracking
            </h2>
            <p style="color: #64748b; font-size: 0.9rem; margin-top: 4px;">Monitor marketplace purchases, verify fulfillment status, and track sales revenue.</p>
        </div>

        <div style="overflow-x: auto;">
            <table class="admin-table-view">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Product Bought</th>
                        <th>Buyer Account</th>
                        <th>Price Mapping</th>
                        <th>Fulfillment Status</th>
                        <th style="text-align: center;">Controls</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orders_result && $orders_result->num_rows > 0): ?>
                        <?php while ($order = $orders_result->fetch_assoc()): ?>
                            <tr>
                                <td style="font-weight: 700; color: #64748b;">#<?php echo $order['order_id']; ?></td>
                                <td><div style="font-weight: 700; color: #0f172a;"><?php echo htmlspecialchars($order['product_title']); ?></div></td>
                                <td style="font-weight: 600;">@<?php echo htmlspecialchars($order['buyer_name']); ?></td>
                                <td style="font-weight: 700; color: #0f172a;">R <?php echo number_format($order['price'], 2, ',', ' '); ?></td>
                                <td>
                                    <?php 
                                    $status = $order['status'] ?? 'pending';
                                    $badge_color = ($status === 'completed') ? '#10b981' : (($status === 'shipped') ? '#3b82f6' : '#f59e0b');
                                    ?>
                                    <span style="background-color: <?php echo $badge_color; ?>20; color: <?php echo $badge_color; ?>; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 0.85rem; text-transform: uppercase;">
                                        <?php echo htmlspecialchars($status); ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <form action="orders.php" method="POST" style="display: inline-flex; gap: 8px; align-items: center;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                        <select name="order_status" style="padding: 4px 8px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 0.85rem; font-weight: 600;">
                                            <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="shipped" <?php echo $status === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        </select>
                                        <button type="submit" name="update_order_status" style="background-color: #334155; color: white; border: none; padding: 5px 10px; border-radius: 6px; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                                            Update
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="padding: 40px; text-align: center; color: #94a3b8; font-weight: 600;">
                                🛒 No transaction records found in the system yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>