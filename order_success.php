<?php
session_start();
include 'config/db.php';

$order_id = $_GET['id'] ?? 0;

// Fetch order details
$query = "SELECT o.*, p.title 
          FROM orders o 
          JOIN products p ON o.product_id = p.product_id 
          WHERE o.order_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
?>


<div style="max-width: 500px; margin: 50px auto; font-family: sans-serif; padding: 20px; border: 1px solid #ddd; border-radius: 10px; box-shadow: 0 4px 15px rgba(128, 0, 128, 0.2);">
    <h1 style="color: #6a1b9a; text-align: center;">Order Confirmed!</h1>
    <p>Thank you for your purchase. We are now preparing your order.</p>
    
    <div style="background: #f3e5f5; padding: 15px; border-radius: 5px; border-left: 5px solid #6a1b9a;">
        <p><strong>Order ID:</strong> #<?php echo $order_id; ?></p>
        <p><strong>Item:</strong> <?php echo $order['title'] ?? 'N/A'; ?></p>
        <p><strong>Total Paid:</strong> R <?php echo number_format($order['total_price'], 2); ?></p>
        <p><strong>Delivery To:</strong> <?php echo $order['delivery_address']; ?></p>
    </div>

    <br>
    <a href="track_order.php?id=<?php echo $order_id; ?>" style="display: block; text-align: center; background: #6a1b9a; color: white; padding: 15px; text-decoration: none; border-radius: 5px; font-weight: bold;">
        Track My Order
       
    

        <a href="index.php" style="color: #6a1b9a; text-decoration: underline;">← Back to My Dashboard</a>
    </div>
</div>
    </a>
    
</div>
