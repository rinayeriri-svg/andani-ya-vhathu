<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_fulfillment'])) {
    $order_id = intval($_POST['order_id']);
    $chosen_courier = trim($_POST['assigned_delivery_method']);

    $conn->begin_transaction();
    try {
        // 1. Update status to 'Dispatched' in active orders
        $stmt = $conn->prepare("UPDATE orders SET shipping_status = 'Dispatched', shipping_method = ? WHERE order_id = ?");
        $stmt->bind_param("si", $chosen_courier, $order_id);
        $stmt->execute();

        // 2. Archive to delivered_orders 
        // Ensure column names match your database structure exactly
        $move_stmt = $conn->prepare("
            INSERT INTO delivered_orders (order_id, buyer_id, product_name, total_amount, shipping_status, shipping_method)
            SELECT order_id, buyer_id, product_name, total_amount, 'Dispatched', shipping_method
            FROM orders 
            WHERE order_id = ?
        ");
        $move_stmt->bind_param("i", $order_id);
        $move_stmt->execute();

        // 3. Remove from active dashboard
        $delete_stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
        $delete_stmt->bind_param("i", $order_id);
        $delete_stmt->execute();

        $conn->commit();
        header("Location: seller_dashboard.php?success=1");
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: seller_dashboard.php?error=db_fail");
    }
    exit;
}
// Ensure only authorized sellers/admins can process this
if (isset($_POST['update_fulfillment']) && $_SESSION['role'] == 3) {
    $order_id = (int)$_POST['order_id'];
    $method = $conn->real_escape_string($_POST['assigned_delivery_method']);

    // Update the database status to 'shipped'
    $stmt = $conn->prepare("UPDATE orders SET status = 'shipped', delivery_method = ? WHERE order_id = ?");
    $stmt->bind_param("si", $method, $order_id);
    
    if ($stmt->execute()) {
        header("Location: seller_dashboard.php?status=success");
        exit;
    }
}
// Update this part in order.php
if (isset($_POST['update_fulfillment'])) {
    $order_id = (int)$_POST['order_id'];
    $method = $conn->real_escape_string($_POST['assigned_delivery_method']);

    // Change status to 'pending_shipping' so it moves to your Pending section
    $stmt = $conn->prepare("UPDATE orders SET status = 'pending_shipping', delivery_method = ? WHERE order_id = ?");
    $stmt->bind_param("si", $method, $order_id);
    
    $stmt->execute();
    header("Location: seller_dashboard.php");
    exit;
}


if (isset($_POST['mark_shipped'])) {
    $order_id = (int)$_POST['order_id'];
    
    // Update status directly to 'shipped'
    $stmt = $conn->prepare("UPDATE orders SET status = 'shipped' WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    
    header("Location: seller_dashboard.php");
    exit;
}

// STAGE 1: Move to Pending Shipments
if (isset($_POST['set_carrier'])) {
    $order_id = (int)$_POST['order_id'];
    $method = $conn->real_escape_string($_POST['assigned_delivery_method']);
    $conn->query("UPDATE orders SET status = 'pending_shipping', delivery_method = '$method' WHERE order_id = $order_id");
}

// STAGE 2: Move to Shipped
if (isset($_POST['mark_shipped'])) {
    $order_id = (int)$_POST['order_id'];
    $conn->query("UPDATE orders SET status = 'shipped' WHERE order_id = $order_id");
}

header("Location: seller_dashboard.php");
exit;
?>
?>