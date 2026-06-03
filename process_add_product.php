<?php
include 'config/db.php';
session_start();

// 1. Upload the file
$target_dir = "uploads/";
if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
$image_path = $target_dir . time() . "_" . basename($_FILES["product_image"]["name"]);
move_uploaded_file($_FILES["product_image"]["tmp_name"], $image_path);

// 2. Save to database
$stmt = $conn->prepare("INSERT INTO products (title, category, price, description, seller_id, image_path) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssdsis", $_POST['title'], $_POST['category'], $_POST['price'], $_POST['description'], $_SESSION['user_id'], $image_path);
$stmt->execute();

header("Location: index.php");
?>