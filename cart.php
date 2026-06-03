<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/db.php';

// Force login to view cart
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$buyer_id = intval($_SESSION['user_id']);
$success_msg = "";

// --- ACTIONS: HANDLE ITEM REMOVAL ---
if (isset($_GET['remove'])) {
    $cart_id_to_remove = intval($_GET['remove']);
    $delete_stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    $delete_stmt->bind_param("ii", $cart_id_to_remove, $buyer_id);
    if ($delete_stmt->execute()) {
        $success_msg = "Item removed from your cart.";
    }
    $delete_stmt->close();
}

// --- ACTIONS: HANDLE QUANTITY UPDATES ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $cart_id = intval($_POST['cart_id']);
    $new_qty = intval($_POST['quantity']);
    
    if ($new_qty > 0) {
        $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
        $update_stmt->bind_param("iii", $new_qty, $cart_id, $buyer_id);
        $update_stmt->execute();
        $update_stmt->close();
    }
}

// --- FETCH ALL CART ITEMS JOINED WITH PRODUCTS ---
$cart_items = [];
$cart_query = "SELECT c.cart_id, c.quantity, p.product_id, p.title, p.price, p.category 
               FROM cart c 
               JOIN products p ON c.product_id = p.product_id 
               WHERE c.user_id = ?";
               
if ($cart_stmt = $conn->prepare($cart_query)) {
    $cart_stmt->bind_param("i", $buyer_id);
    $cart_stmt->execute();
    $cart_items = $cart_stmt->get_result();
}

$cart_subtotal = 0.00;
include 'includes/header.php';
?>

<div style="max-width: 1000px; margin: 40px auto; padding: 0 20px; font-family: 'Segoe UI', system-ui, sans-serif;">
    
    <div style="margin-bottom: 30px;">
        <h2 style="font-weight: 800; color: #0f172a; font-size: 1.8rem; display: flex; align-items: center; gap: 10px;">
            <i class="bi bi-cart3" style="color: #6f42c1;"></i> Your Shopping Cart
        </h2>
        <p style="color: #64748b; font-size: 0.95rem;">Review your selected items before proceeding to secure escrow checkout.</p>
    </div>

    <?php if (!empty($success_msg)): ?>
        <div style="background-color: #f1f5f9; color: #334155; padding: 12px 15px; border-radius: 8px; font-weight: 600; margin-bottom: 25px; font-size: 0.9rem; border-left: 4px solid #6f42c1;">
            <i class="bi bi-info-circle-fill"></i> <?php echo $success_msg; ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr; gap: 30px; align-items: start;">
        
        <?php if ($cart_items && $cart_items->num_rows > 0): ?>
            <div style="background: white; border-radius: 12px; border: 1px solid #eef0f2; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.01);">
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    
                    <?php 
                    while ($item = $cart_items->fetch_assoc()): 
                        $line_total = $item['price'] * $item['quantity'];
                        $cart_subtotal += $line_total;
                    ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px; flex-wrap: wrap; gap: 15px;">
                            
                            <div style="flex: 2; min-width: 250px;">
                                <span style="font-size: 0.75rem; background: #f1f5f9; color: #475569; padding: 3px 8px; border-radius: 4px; font-weight: 600; text-transform: uppercase;">
                                    <?php echo htmlspecialchars($item['category']); ?>
                                </span>
                                <h4 style="font-weight: 700; color: #0f172a; margin: 6px 0 4px 0; font-size: 1.1rem;">
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </h4>
                                <div style="color: #64748b; font-size: 0.9rem;">Unit Price: <strong>R <?php echo number_format($item['price'], 2, ',', ' '); ?></strong></div>
                            </div>

                            <div style="flex: 1; min-width: 120px;">
                                <form action="cart.php" method="POST" style="display: flex; align-items: center; gap: 6px;">
                                    <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="10" style="width: 60px; padding: 6px; border: 1px solid #cbd5e1; border-radius: 6px; text-align: center; font-size: 0.9rem;">
                                    <button type="submit" name="update_quantity" style="background: #f3ebff; color: #6f42c1; border: none; padding: 7px 10px; border-radius: 6px; cursor: pointer; font-size: 0.85rem; font-weight: 700;">
                                        Update
                                    </button>
                                </form>
                            </div>

                            <div style="text-align: right; min-width: 120px;">
                                <div style="font-size: 1.1rem; font-weight: 800; color: #0f172a;">
                                    R <?php echo number_format($line_total, 2, ',', ' '); ?>
                                </div>
                                <a href="cart.php?remove=<?php echo $item['cart_id']; ?>" style="color: #dc3545; font-size: 0.85rem; text-decoration: none; font-weight: 600; display: inline-block; margin-top: 5px;" onclick="return confirm('Remove item from cart?');">
                                    <i class="bi bi-trash"></i> Remove
                                </a>
                            </div>

                        </div>
                    <?php endwhile; ?>

                </div>

                <div style="margin-top: 30px; background: #fafafa; border-radius: 10px; padding: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
                    <div>
                        <div style="font-size: 0.9rem; color: #64748b; font-weight: 600;">Estimated Subtotal:</div>
                        <div style="font-size: 1.8rem; font-weight: 800; color: #0f172a; margin-top: 2px;">
                            R <?php echo number_format($cart_subtotal, 2, ',', ' '); ?>
                        </div>
                        <span style="font-size: 0.8rem; color: #94a3b8;">Excludes shipping fees calculated at checkout step channels</span>
                    </div>
                    
                    <div style="display: flex; gap: 12px;">
                        <a href="index.php" style="background: white; color: #475569; border: 1px solid #cbd5e1; text-decoration: none; padding: 12px 20px; border-radius: 8px; font-weight: 700; font-size: 0.9rem;">
                            Continue Shopping
                        </a>
                        <a href="checkout.php" style="background: #10b981; color: white; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 700; font-size: 0.9rem; box-shadow: 0 4px 12px rgba(16,185,129,0.2); display: inline-flex; align-items: center; gap: 8px;">
                            Proceed to Checkout <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>

            </div>
        <?php else: ?>
            <div style="border: 2px dashed #cbd5e1; border-radius: 12px; padding: 50px 20px; text-align: center; color: #64748b; background: white;">
                <i class="bi bi-cart-x" style="font-size: 3rem; color: #94a3b8; display: block; margin-bottom: 15px;"></i>
                <h3 style="font-weight: 700; color: #1e293b; margin-bottom: 5px;">Your cart is completely empty</h3>
                <p style="font-size: 0.9rem; color: #94a3b8; margin-bottom: 20px;">Explore the local market collection items to discover amazing listings.</p>
                <a href="index.php" style="background: #6f42c1; color: white; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 700; font-size: 0.9rem;">
                    Browse Marketplace Listings
                </a>
            </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>