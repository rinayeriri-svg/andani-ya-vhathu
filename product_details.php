<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/db.php';

// --- PART 1: THE ADD TO CART PROCESSOR ---
// FIXED: Updated form submission self-referencing links to product_details.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart_submit'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
    
    $p_id = intval($_POST['product_id']);
    $u_id = intval($_SESSION['user_id']);
    $qty = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;
    
    // Safety check: Prevent a merchant from buying their own items
    $check_owner = $conn->prepare("SELECT seller_id FROM products WHERE product_id = ?");
    $check_owner->bind_param("i", $p_id);
    $check_owner->execute();
    $prod_data = $check_owner->get_result()->fetch_assoc();
    $check_owner->close();
    
    if ($prod_data && intval($prod_data['seller_id']) === $u_id) {
        $error_msg = "You cannot add your own product to the cart!";
    } else {
        $check_cart = $conn->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $check_cart->bind_param("ii", $u_id, $p_id);
        $check_cart->execute();
        $cart_res = $check_cart->get_result();
        
        if ($cart_res->num_rows > 0) {
            $cart_item = $cart_res->fetch_assoc();
            $new_qty = $cart_item['quantity'] + $qty;
            $update_cart = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
            $update_cart->bind_param("ii", $new_qty, $cart_item['cart_id']);
            $update_cart->execute();
            $update_cart->close();
        } else {
            $ins_cart = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $ins_cart->bind_param("iii", $u_id, $p_id, $qty);
            $ins_cart->execute();
            $ins_cart->close();
        }
        $check_cart->close();
        
        // Heads up: Ensure your cart file is named cart.php (or update this to cart_details.php if needed!)
        header("Location: cart.php");
        exit;
    }
}

// --- PART 2: FETCH THE PRODUCT DETAILS ---
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$product_id = intval($_GET['id']);

$query = "SELECT p.*, u.username as seller_name FROM products p JOIN users u ON p.seller_id = u.user_id WHERE p.product_id = ?";
$product = null;

if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        header("Location: index.php");
        exit;
    }
    $product = $result->fetch_assoc();
    $stmt->close();
}

include 'includes/header.php';
?>

<div style="max-width: 1100px; margin: 40px auto; padding: 0 20px; font-family: 'Segoe UI', system-ui, sans-serif;">
    
    <a href="index.php" style="color: #64748b; text-decoration: none; font-weight: 600; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 25px;">
        <i class="bi bi-arrow-left"></i> Back to Marketplace Showcase
    </a>

    <?php if (isset($error_msg)): ?>
        <div style="background-color: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; font-weight: 600; font-size: 0.95rem; margin-bottom: 25px;">
            <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error_msg; ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 40px; background: white; border: 1px solid #eef0f2; padding: 30px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.02);">
        
       <div style="display: flex; align-items: center; justify-content: center; background-color: #f8fafc; border-radius: 12px; padding: 20px; min-height: 350px; border: 1px solid #f1f5f9;">
    <?php if (!empty($product['image_path']) && file_exists($product['image_path'])): ?>
        <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>" style="max-width: 100%; max-height: 400px; object-fit: contain; border-radius: 8px;">
    <?php else: ?>
        <div style="text-align: center; color: #94a3b8;">
            <i class="bi bi-image" style="font-size: 4rem; display: block; margin-bottom: 10px;"></i>
            <span style="font-size: 0.9rem; font-weight: 600;">No Product Image Available</span>
        </div>
    <?php endif; ?>
</div>

        <div style="display: flex; flex-direction: column; justify-content: center;">
            <span style="background-color: #f3ebff; color: #6f42c1; font-size: 0.8rem; font-weight: 700; padding: 4px 12px; border-radius: 20px; text-transform: uppercase; width: max-content; margin-bottom: 12px;">
                <?php echo htmlspecialchars($product['category'] ?? 'General Marketplace'); ?>
            </span>
            
            <h1 style="font-weight: 800; color: #0f172a; font-size: 2rem; margin: 0 0 10px 0; line-height: 1.2;">
                <?php echo htmlspecialchars($product['title']); ?>
            </h1>

            <div style="display: flex; align-items: center; gap: 8px; color: #64748b; font-size: 0.9rem; margin-bottom: 20px;">
                <i class="bi bi-shop" style="color: #6f42c1;"></i> Listed by: <strong style="color: #334155;">@<?php echo htmlspecialchars($product['seller_name']); ?></strong>
            </div>

            <hr style="border: 0; border-top: 1px solid #f1f5f9; margin-bottom: 20px;">

            <div style="margin-bottom: 25px;">
                <span style="font-size: 0.85rem; color: #8a92a6; font-weight: 700; text-transform: uppercase; display: block;">Price</span>
                <div style="font-size: 2.2rem; font-weight: 800; color: #10b981; margin-top: 2px;">
                    R <?php echo number_format($product['price'], 2, ',', ' '); ?>
                </div>
            </div>

            <div style="margin-bottom: 30px;">
                <span style="font-size: 0.85rem; color: #8a92a6; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 6px;">Product Description</span>
                <p style="color: #475569; font-size: 1rem; line-height: 1.6; margin: 0;">
                    <?php echo nl2br(htmlspecialchars($product['description'] ?? 'No explicit description provided by the merchant seller for this listing item entry field.')); ?>
                </p>
            </div>

            <form action="product_details.php?id=<?php echo $product_id; ?>" method="POST" style="background-color: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; display: flex; flex-direction: column; gap: 15px; max-width: 400px;">
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                
                <div style="display: flex; align-items: center; gap: 12px;">
                    <label style="font-size: 0.85rem; font-weight: 700; color: #475569; text-transform: uppercase;">Select Quantity:</label>
                    <input type="number" name="quantity" value="1" min="1" max="10" style="width: 75px; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; text-align: center; font-weight: 700; background: white; color: #0f172a;">
                </div>

                <button type="submit" name="add_to_cart_submit" style="background-color: #6f42c1; color: white; border: none; padding: 14px 20px; border-radius: 8px; font-weight: 700; font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 4px 12px rgba(111,66,193,0.25); transition: background 0.2s;">
                    <i class="bi bi-cart-plus-fill" style="font-size: 1.15rem;"></i> Add To Shopping Cart
                </button>
            </form>
            <form action="report_product.php" method="POST" style="margin-top: 10px;">
    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
    <button type="submit" style="background: #fee2e2; color: #b91c1c; border: 1px solid #f87171; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 0.8rem;">
        <i class="bi bi-flag"></i> Report This Listing
    </button>
</form>

        </div>
    </div>
</div>

</body>
</html>