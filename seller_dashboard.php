<?php
// 1. SESSION & ROUTING GUARD
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/db.php';

if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] != 2 && $_SESSION['role'] !== '2')) {
    header("Location: index.php");
    exit;
}

$merchant_session_id = intval($_SESSION['user_id']);
$success_msg = "";
$website_fee_percentage = 10.00;

if (isset($_GET['success'])) {
    $success_msg = "Order successfully dispatched and removed from your active queue.";
}

// 2. REVENUE TRACKING LAYER
$total_gross_revenue = 0.00;
$revenue_query = "SELECT SUM(total_price) as gross FROM orders WHERE seller_id = ?";
if ($rev_stmt = $conn->prepare($revenue_query)) {
    $rev_stmt->bind_param("i", $merchant_session_id);
    $rev_stmt->execute();
    $rev_res = $rev_stmt->get_result()->fetch_assoc();
    $total_gross_revenue = floatval($rev_res['gross'] ?? 0.00);
    $rev_stmt->close();
}

$total_deductions = ($total_gross_revenue * $website_fee_percentage) / 100;
$net_merchant_earnings = $total_gross_revenue - $total_deductions;

// 3. LOGISTICS MANAGEMENT LAYER
$total_to_ship = 0;
$pending_shipments = null;

$ship_query = "SELECT o.*, u.username as customer_username 
               FROM orders o 
               LEFT JOIN users u ON o.buyer_id = u.user_id 
               WHERE o.seller_id = ? 
               ORDER BY o.order_id DESC";

$count_query = "SELECT COUNT(*) as count FROM orders WHERE seller_id = ? AND status = 'paid'";

if ($count_stmt = $conn->prepare($count_query)) {
    $count_stmt->bind_param("i", $merchant_session_id);
    $count_stmt->execute();
    $res = $count_stmt->get_result()->fetch_assoc();
    $total_to_ship = $res['count']; // This will now be 0 if all orders are marked 'shipped'
    $count_stmt->close();
}
// 4. MARKETPLACE INVENTORY LAYER
$my_products = null;
$list_query = "SELECT * FROM products WHERE seller_id = ? ORDER BY product_id DESC";
if ($list_stmt = $conn->prepare($list_query)) {
    $list_stmt->bind_param("i", $merchant_session_id);
    $list_stmt->execute();
    $my_products = $list_stmt->get_result();
}

include 'includes/header.php';
?>

<div style="max-width: 1200px; margin: 40px auto; padding: 0 20px; font-family: 'Segoe UI', Arial, sans-serif;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
        <div>
            <h2 style="font-weight: 800; color: #0f172a; margin: 0;">Merchant Command Center</h2>
            <p style="color: #64748b; margin-top: 5px; font-size: 0.9rem;">Manage your store catalog and fulfill client orders.</p>
        </div>
        <a href="add_product.php" style="background: #6f42c1; color: white; text-decoration: none; padding: 12px 20px; border-radius: 6px; font-weight: bold; font-size: 0.9rem;">
            + Add New Product
        </a>
    </div>

    <?php if (!empty($success_msg)): ?>
        <div style="background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin-bottom: 25px; font-weight: 600;">
            ✓ <?php echo htmlspecialchars($success_msg); ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div style="background: white; border: 1px solid #e2e8f0; padding: 20px; border-radius: 10px; border-left: 5px solid #6f42c1;">
            <div style="font-size: 0.75rem; font-weight: bold; color: #64748b; text-transform: uppercase;">Gross Sales</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #0f172a; margin-top: 5px;">R <?php echo number_format($total_gross_revenue, 2, ',', ' '); ?></div>
        </div>
        <div style="background: white; border: 1px solid #e2e8f0; padding: 20px; border-radius: 10px; border-left: 5px solid #dc3545;">
            <div style="font-size: 0.75rem; font-weight: bold; color: #64748b; text-transform: uppercase;">Platform Fees (10%)</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #dc3545; margin-top: 5px;">- R <?php echo number_format($total_deductions, 2, ',', ' '); ?></div>
        </div>
        <div style="background: white; border: 1px solid #e2e8f0; padding: 20px; border-radius: 10px; border-left: 5px solid #10b981;">
            <div style="font-size: 0.75rem; font-weight: bold; color: #065f46; text-transform: uppercase;">Net Earnings</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #10b981; margin-top: 5px;">R <?php echo number_format($net_merchant_earnings, 2, ',', ' '); ?></div>
        </div>
        <div style="background: white; border: 1px solid #e2e8f0; padding: 20px; border-radius: 10px; border-left: 5px solid #f59e0b;">
            <div style="font-size: 0.75rem; font-weight: bold; color: #64748b; text-transform: uppercase;">To Process</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #f59e0b; margin-top: 5px;"><?php echo $total_to_ship; ?> Orders</div>
        </div>
    </div>

<div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; margin-bottom: 40px;">
    
    <h3 style="margin-top: 0; color: #1e293b; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">Orders Awaiting Courier Dispatch</h3>
    <?php 
    $awaiting = $conn->query("SELECT o.*, u.username as customer_username FROM orders o LEFT JOIN users u ON o.buyer_id = u.user_id WHERE o.seller_id = $merchant_session_id AND o.status = 'paid'"); 
    if ($awaiting && $awaiting->num_rows > 0):
        while ($ord = $awaiting->fetch_assoc()): ?>
            <form action="order.php" method="POST" style="padding: 10px 0; border-bottom: 1px solid #f8fafc; display: flex; gap: 15px; align-items: center;">
                <input type="hidden" name="set_carrier" value="1">
                <input type="hidden" name="order_id" value="<?php echo $ord['order_id']; ?>">
                <div style="flex-grow: 1;"><strong>Order #<?php echo $ord['order_id']; ?></strong><br><small>Buyer: <?php echo htmlspecialchars($ord['customer_username']); ?></small></div>
                <select name="assigned_delivery_method" required style="padding: 5px;">
                    <option value="Paxi">Paxi</option>
                    <option value="Fastway">Fastway</option>
                </select>
                <button type="submit" style="background: #10b981; color: white; border: none; padding: 5px 10px; cursor: pointer;">Dispatch</button>
            </form>
    <?php endwhile; else: echo "<p style='color:#94a3b8;'>No new orders to dispatch.</p>"; endif; ?>

    <h3 style="margin-top: 30px; color: #1e293b;">Pending Shipments</h3>
    <?php 
    $pending = $conn->query("SELECT o.*, u.username as customer_username FROM orders o LEFT JOIN users u ON o.buyer_id = u.user_id WHERE o.seller_id = $merchant_session_id AND o.status = 'pending_shipping'"); 
    if ($pending && $pending->num_rows > 0): 
        while ($p = $pending->fetch_assoc()): ?>
            <div style="padding: 10px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between;">
                <span>Order #<?php echo $p['order_id']; ?> | Carrier: <?php echo htmlspecialchars($p['delivery_method']); ?></span>
                <form action="order.php" method="POST">
                    <input type="hidden" name="mark_shipped" value="1">
                    <input type="hidden" name="order_id" value="<?php echo $p['order_id']; ?>">
                    <button type="submit" style="background: #059669; color: white; border: none; padding: 2px 8px; cursor: pointer;">Confirm Shipment</button>
                </form>
            </div>
    <?php endwhile; else: echo "<p style='color:#94a3b8;'>No shipments currently pending.</p>"; endif; ?>

    <h3 style="margin-top: 30px; color: #1e293b;">Processed Shipments</h3>
    <?php 
    $shipped = $conn->query("SELECT * FROM orders WHERE seller_id = $merchant_session_id AND status = 'shipped' ORDER BY order_id DESC LIMIT 5");
    while ($s = $shipped->fetch_assoc()): ?>
        <p style="font-size: 0.85rem; color: #475569;">Order #<?php echo $s['order_id']; ?> | Dispatched via: <?php echo htmlspecialchars($s['delivery_method']); ?> <strong style="color: green;">[SHIPPED]</strong></p>
    <?php endwhile; ?>

<h3>New Orders to Dispatch</h3>
<?php 
// Fixed Query: Removed invalid columns. Update 'phone_number' and 'address' to match your database structure.
$orders = $conn->query("SELECT o.*, u.username 
                        FROM orders o 
                        LEFT JOIN users u ON o.buyer_id = u.user_id 
                        WHERE o.seller_id = $merchant_session_id AND o.status = 'paid'"); 

while ($ord = $orders->fetch_assoc()): ?>
    <div style="border: 1px solid #ddd; padding: 15px; margin-bottom: 10px;">
        <p><strong>Order #<?php echo $ord['order_id']; ?></strong> | Buyer: <?php echo htmlspecialchars($ord['username']); ?></p>
        <form action="order.php" method="POST">
            <input type="hidden" name="order_id" value="<?php echo $ord['order_id']; ?>">
            <button type="submit" name="mark_shipped">Mark as Shipped</button>
        </form>
    </div>
<?php endwhile; ?>
</div>

    <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px;">
        <h3 style="margin-top: 0; color: #1e293b; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">Listed Products Directory</h3>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
                <thead>
                    <tr style="border-bottom: 2px solid #edf2f7; color: #64748b;">
                        <th style="padding: 12px 10px;">ID</th>
                        <th style="padding: 12px 10px;">Item Title</th>
                        <th style="padding: 12px 10px;">Category</th>
                        <th style="padding: 12px 10px;">Price</th>
                        <th style="padding: 12px 10px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($my_products && $my_products->num_rows > 0): ?>
                        <?php while ($prod = $my_products->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 14px 10px; font-weight: bold; color: #94a3b8;">#<?php echo $prod['product_id']; ?></td>
                                <td style="padding: 14px 10px; font-weight: bold; color: #0f172a;"><?php echo htmlspecialchars($prod['title']); ?></td>
                                <td style="padding: 14px 10px;"><span style="background: #f1f5f9; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; color: #475569;"><?php echo htmlspecialchars($prod['category']); ?></span></td>
                                <td style="padding: 14px 10px; font-weight: bold; color: #0f172a;">R <?php echo number_format($prod['price'], 2, ',', ' '); ?></td>
                                <td style="padding: 14px 10px; text-align: center;">
                                    <a href="edit_product.php?id=<?php echo $prod['product_id']; ?>" style="color: #6f42c1; font-weight: bold; text-decoration: none; font-size: 0.85rem;">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="padding: 30px; text-align: center; color: #94a3b8;">📦 No items uploaded to your marketplace catalog yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>