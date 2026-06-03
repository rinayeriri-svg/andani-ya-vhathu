<?php
session_start();

if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Create cart if it doesn't exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add item if not already in cart
    if (!in_array($product_id, $_SESSION['cart'])) {
        $_SESSION['cart'][] = $product_id;
    }
}

// Redirect back to the page the user came from
header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
?>