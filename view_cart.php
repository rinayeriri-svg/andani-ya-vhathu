<?php
session_start();
include 'config/db.php';
include 'includes/header.php';

$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total = 0;
?>

<div class="container mt-5">
    <h2 class="purple-text fw-bold mb-4">Your Shopping Cart</h2>

    <?php if (empty($cart_items)): ?>
        <div class="card border-0 shadow-sm p-5 text-center">
            <h4>Your cart is empty</h4>
            <p class="text-muted">Looks like you haven't added any textbooks yet.</p>
            <a href="index.php" class="btn btn-purple mt-3">Start Shopping</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="list-group list-group-flush">
                        <?php
                        $ids = implode(',', array_map('intval', $cart_items));
                        $query = "SELECT * FROM products WHERE product_id IN ($ids)";
                        $result = $conn->query($query);
                        
                        while ($row = $result->fetch_assoc()):
                            $total += $row['price'];
                        ?>
                            <div class="list-group-item p-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($row['title']); ?></h5>
                                        <p class="text-muted small mb-0">Seller: #<?php echo $row['seller_id']; ?></p>
                                    </div>
                                    <div class="text-end">
                                        <h5 class="purple-text fw-bold">R<?php echo number_format($row['price'], 2); ?></h5>
                                        <a href="remove_from_cart.php?id=<?php echo $row['product_id']; ?>" class="text-danger small text-decoration-none">Remove</a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-4 sticky-top" style="top: 20px;">
                    <h4 class="fw-bold mb-4">Summary</h4>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Items (<?php echo count($cart_items); ?>):</span>
                        <span>R<?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-4">
                        <span>Shipping:</span>
                        <span class="text-success">FREE</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="h5 fw-bold">Total:</span>
                        <span class="h5 fw-bold purple-text">R<?php echo number_format($total, 2); ?></span>
                    </div>
                    <button class="btn btn-purple btn-lg w-100 fw-bold">Checkout</button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>