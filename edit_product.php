<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/db.php';

// Force redirect back to home if they aren't signed in as a merchant
if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] != 2 && $_SESSION['role'] !== '2' && strtolower($_SESSION['role']) !== 'seller')) {
    header("Location: index.php");
    exit;
}

$merchant_session_id = intval($_SESSION['user_id']);
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$error_msg = "";
$success_msg = "";

// --- DYNAMICALLY RESOLVE USER PRIMARY KEY TO PREVENT SECURITY ALIGNMENT CRASHES ---
$user_pk_column = "id"; // Default fallback
$columns_check = $conn->query("SHOW COLUMNS FROM users");
if ($columns_check) {
    while ($col = $columns_check->fetch_assoc()) {
        $field_name = strtolower($col['Field']);
        if ($field_name === 'user_id') {
            $user_pk_column = "user_id";
            break;
        }
    }
}

// 1. Fetch existing item details and check authorship ownership constraints
$product_data = null;
$product_query = "SELECT * FROM products WHERE product_id = ? AND seller_id = ? LIMIT 1";
if ($stmt = $conn->prepare($product_query)) {
    $stmt->bind_param("ii", $product_id, $merchant_session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows === 1) {
        $product_data = $result->fetch_assoc();
    }
    $stmt->close();
}

// Kick them back to dashboard if product doesn't exist or isn't theirs
if (!$product_data) {
    header("Location: seller_dashboard.php");
    exit;
}

// 2. Handle POST update data processing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);

    if (empty($title) || empty($category) || $price <= 0) {
        $error_msg = "Please fill in all mandatory parameters and provide a valid price mapping.";
    } else {
        // Update product record and flip status back to 'pending_review' for admin approval safety
        $update_query = "UPDATE products SET title = ?, description = ?, category = ?, price = ?, status = 'pending_review' WHERE product_id = ? AND seller_id = ?";
        if ($update_stmt = $conn->prepare($update_query)) {
            $update_stmt->bind_param("sssdii", $title, $description, $category, $price, $product_id, $merchant_session_id);
            if ($update_stmt->execute()) {
                $success_msg = "Listing updated successfully! Sent to admin review pipeline queue.";
                
                // Refresh records locally
                $product_data['title'] = $title;
                $product_data['description'] = $description;
                $product_data['category'] = $category;
                $product_data['price'] = $price;
                
                header("Refresh: 2; url=seller_dashboard.php");
            } else {
                $error_msg = "An structural update error occurred. Please verify your fields.";
            }
            $update_stmt->close();
        }
    }
}

include 'includes/header.php';
?>

<div style="max-width: 700px; margin: 40px auto; padding: 0 20px; font-family: 'Segoe UI', system-ui, sans-serif;">
    
    <div style="margin-bottom: 25px;">
        <a href="seller_dashboard.php" style="text-decoration: none; color: #6f42c1; font-weight: 700; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 4px;">
            <i class="bi bi-arrow-left"></i> Return to Seller Dashboard
        </a>
    </div>

    <div style="background: white; border-radius: 12px; border: 1px solid #eef0f2; padding: 35px; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
        <h3 style="font-weight: 800; color: #0f172a; font-size: 1.4rem; margin-bottom: 6px;">
            <i class="bi bi-pencil-square" style="color: #6f42c1;"></i> Modify Product Listing
        </h3>
        <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 25px;">Edits to titles, categories, or prices automatically request structural verification from system moderators before returning to public listing displays.</p>

        <?php if (!empty($success_msg)): ?>
            <div style="background-color: #d1fae5; color: #065f46; padding: 14px; border-radius: 8px; font-weight: 600; margin-bottom: 20px; font-size: 0.9rem;">
                <i class="bi bi-check-circle-fill"></i> <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div style="background-color: #fee2e2; color: #991b1b; padding: 14px; border-radius: 8px; font-weight: 600; margin-bottom: 20px; font-size: 0.9rem;">
                <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($product['image_path'])): ?>
    <div style="margin-bottom: 15px;">
        <label>Current Image:</label><br>
        <img src="<?php echo $product['image_path']; ?>" style="max-width: 150px; border-radius: 8px;">
    </div>
<?php endif; ?>

<div style="display: flex; flex-direction: column; gap: 6px;">
    <label>Change Product Image:</label>
    <input type="file" name="product_image" accept="image/*">
</div>

        <form action="edit_product.php?id=<?php echo $product_id; ?>" method="POST">
            <input type="hidden" name="update_product" value="1">

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.3px;">Product Listing Title</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($product_data['title']); ?>" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-size: 0.95rem; color: #1e293b;" required>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.3px;">Marketplace Category Block</label>
                <select name="category" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-size: 0.95rem; color: #1e293b; background: white;" required>
                    <option value="academic" <?php echo $product_data['category'] === 'academic' ? 'selected' : ''; ?>>📖 Textbooks & Stationery</option>
                    <option value="fashion" <?php echo $product_data['category'] === 'fashion' ? 'selected' : ''; ?>>✨ Fashion & Styling</option>
                    <option value="tech" <?php echo $product_data['category'] === 'tech' ? 'selected' : ''; ?>>💻 Electronics & Tech</option>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.3px;">Price Valuation Mapping (ZAR)</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-weight: 700; color: #64748b; font-size: 0.95rem;">R</span>
                    <input type="number" step="0.01" name="price" value="<?php echo floatval($product_data['price']); ?>" style="width: 100%; padding: 10px 14px 10px 32px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-size: 0.95rem; font-weight: 600; color: #1e293b;" required>
                </div>
            </div>

            <div style="margin-bottom: 30px;">
                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.3px;">Public Marketplace Item Description</label>
                <textarea name="description" rows="5" style="width: 100%; padding: 12px 14px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-size: 0.95rem; color: #1e293b; resize: vertical;" placeholder="Provide details about condition, usage history, meet-up details..."><?php echo htmlspecialchars($product_data['description']); ?></textarea>
            </div>

            <button type="submit" style="width: 100%; background: linear-gradient(135deg, #6f42c1, #4338ca); color: white; border: none; padding: 12px 24px; border-radius: 8px; font-size: 0.95rem; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(111,66,193,0.15); transition: opacity 0.15s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                Save and Request Catalog Sync
            </button>
        </form>
    </div>

</div>

</body>
</html>