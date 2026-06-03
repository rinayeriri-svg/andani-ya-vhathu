<?php
include 'config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $p_id = intval($_POST['product_id']);
    $user_id = $_SESSION['user_id']; // The user making the report

    $stmt = $conn->prepare("INSERT INTO reports (product_id, reporter_user_id, status) VALUES (?, ?, 'pending')");
    $stmt->bind_param("ii", $p_id, $user_id);
    $stmt->execute();
    
    echo "<script>alert('Report submitted for review.'); window.location.href='index.php';</script>";
}
?>