<?php
session_start();
include 'config/db.php';

if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
    $id = $_GET['id'];
    $seller_id = $_SESSION['user_id'];

    // Only delete if the product belongs to the logged-in user
    $sql = "DELETE FROM products WHERE product_id = '$id' AND seller_id = '$seller_id'";
    
    if ($conn->query($sql)) {
        header("Location: seller_dashboard.php?msg=deleted");
    } else {
        echo "Error deleting record: " . $conn->error;
    }
} else {
    header("Location: seller_dashboard.php");
}
?>