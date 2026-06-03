<?php
include 'config/db.php';

if (isset($_POST['submit_product'])) {
    $title = $_POST['title'];
    $price = $_POST['price'];
    $seller_id = $_SESSION['user_id'];

    // Handle Image Upload
    $target_dir = "uploads/";
    $image_name = time() . "_" . basename($_FILES["product_image"]["name"]);
    $target_file = $target_dir . $image_name;

    if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
        // Save to Database
        $sql = "INSERT INTO products (title, price, seller_id, image_path) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdis", $title, $price, $seller_id, $target_file);
        
        if ($stmt->execute()) {
            echo "Product posted successfully!";
        }
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
?>