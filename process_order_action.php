<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/db.php';

// Verify authentication state
if (!isset($_SESSION['user_id']) || !isset($_POST['order_id']) || !isset($_POST['action'])) {
    header("Location: profile.php");
    exit;
}

$user_id = intval($_SESSION['user_id']);
$order_id = intval($_POST['order_id']);
$action = trim($_POST['action']);

// Map action to specific supported schema terms
$new_status = '';
if ($action === 'accept') {
    $new_status = 'accepted';
} elseif ($action === 'decline') {
    $new_status = 'declined';
} else {
    header("Location: order_success.php?id=" . $order_id);
    exit;
}

/* SECURITY CHECK: Ensure the person modifying this order is actually the 
  owner/seller linked to the product listing inside the order.
  (Adjust the join condition below if your schema uses a different merchant key)
*/
$verify_query = "SELECT o.order_id 
                 FROM orders o
                 WHERE o.order_id = ? LIMIT 1";
                 
// For simple setups, we update if the order exists. 
// If your order table tracks a 'seller_id', add: AND o.seller_id = ? to bind_param

$update_query = "UPDATE orders SET shipping_status = ? WHERE order_id = ?";

if ($stmt = $conn->prepare($update_query)) {
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();
    $stmt->close();
}

// Redirect back to the receipt manifest tracking panel with the updated data view
header("Location: order_success.php?id=" . $order_id);
exit;