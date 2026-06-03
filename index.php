<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/db.php';

// --- CONFIGURATION: FETCH TOP/NEWEST PRODUCTS SIMPLIFIED ---
$sql_query = "SELECT p.*, u.username as seller_name 
              FROM products p 
              JOIN users u ON p.seller_id = u.user_id 
              ORDER BY p.product_id DESC";

$all_products = $conn->query($sql_query);

include 'includes/header.php';
?>

<div style="max-width: 1200px; margin: 40px auto; padding: 0 20px; font-family: 'Segoe UI', system-ui, sans-serif; position: relative;">
    
    <a href="cart.php" style="position: fixed; right: 30px; top: 50%; transform: translateY(-50%); background-color: #6f42c1; color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(111,66,193,0.35); text-decoration: none; z-index: 9999; transition: transform 0.2s, background-color 0.2s;" onmouseover="this.style.transform='translateY(-50%) scale(1.1)'; this.style.backgroundColor='#5b32a1';" onmouseout="this.style.transform='translateY(-50%) scale(1)'; this.style.backgroundColor='#6f42c1';">
        <i class="bi bi-cart3" style="font-size: 1.6rem;"></i>
    </a>

    <div style="margin-bottom: 45px; padding-left: 18px; border-left: 6px solid #6f42c1;">
        <h1 style="font-weight: 900; color: #0f172a; font-size: 2.4rem; margin: 0 0 6px 0; letter-spacing: -1px; text-transform: uppercase;">
            KHA RI SHUME ROTHE RO VHARANA 🤝
        </h1>
        <p style="color: #64748b; font-size: 1.1rem; margin: 0; font-weight: 500; line-height: 1.4;">
            Let us all work together in unity. Building our community through shared growth and trading.
        </p>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h3 style="font-weight: 800; color: #1e293b; font-size: 1.4rem; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i class="bi bi-stars" style="color: #6f42c1;"></i> Top Featured Products
        </h3>
        <span style="font-size: 0.85rem; color: #94a3b8; font-weight: 600; background: #f8fafc; padding: 6px 14px; border-radius: 20px; border: 1px solid #f1f5f9;">
            Live Listings
        </span>
    </div>

    <?php if ($all_products && $all_products->num_rows > 0): ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 30px;">
            
            <?php while ($product = $all_products->fetch_assoc()): ?>
                <div style="background: white; border: 1px solid #eef0f2; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.015); display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s, box-shadow 0.2s;">
                    
                    <a href="product_details.php?id=<?php echo $product['product_id']; ?>" style="text-decoration: none; color: inherit; display: block;">
                        
                        <div style="background-color: #f8fafc; height: 200px; display: flex; align-items: center; justify-content: center; padding: 15px; border-bottom: 1px solid #f1f5f9; overflow: hidden;">
                            <?php if (!empty($product['image_path']) && file_exists($product['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="Product" style="max-width: 100%; height: auto;">
                            <?php else: ?>
                                <div style="text-align: center; color: #94a3b8;">
                                    <i class="bi bi-image" style="font-size: 2.5rem; display: block; margin-bottom: 5px;"></i>
                                    <span style="font-size: 0.8rem; font-weight: 600;">No Image Provided</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div style="padding: 18px;">
                            <span style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: #6f42c1; display: inline-block; margin-bottom: 6px; background: #f3ebff; padding: 2px 8px; border-radius: 4px;">
                                <?php echo htmlspecialchars($product['category'] ?? 'General'); ?>
                            </span>
                            
                            <h4 style="font-weight: 700; color: #0f172a; margin: 0 0 6px 0; font-size: 1.05rem; line-height: 1.3; height: 42px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                <?php echo htmlspecialchars($product['title']); ?>
                            </h4>
                            
                            <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 12px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <i class="bi bi-person"></i> Seller: <strong>@<?php echo htmlspecialchars($product['seller_name']); ?></strong>
                            </div>
                        </div>
                    </a>

                    <div style="padding: 0 18px 18px 18px; display: flex; align-items: center; justify-content: space-between; border-top: 1px dashed #f1f5f9; padding-top: 12px; margin-top: auto;">
                        <div style="font-size: 1.25rem; font-weight: 800; color: #10b981;">
                            R <?php echo number_format($product['price'], 2, ',', ' '); ?>
                        </div>
                        <a href="product_details.php?id=<?php echo $product['product_id']; ?>" style="background-color: #f3ebff; color: #6f42c1; text-decoration: none; padding: 8px 14px; border-radius: 6px; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 5px;">
                            View Item <i class="bi bi-chevron-right" style="font-size: 0.75rem;"></i>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>

        </div>
    <?php else: ?>
        <div style="border: 2px dashed #cbd5e1; border-radius: 12px; padding: 60px 20px; text-align: center; color: #64748b; background: white;">
            <i class="bi bi-shop-window" style="font-size: 2.5rem; color: #94a3b8; display: block; margin-bottom: 15px;"></i>
            <h3 style="font-weight: 700; color: #1e293b; margin-bottom: 5px;">Marketplace is quiet</h3>
            <p style="font-size: 0.9rem; color: #94a3b8;">No community products have been uploaded to the dashboard catalog yet.</p>
        </div>
    <?php endif; ?>

</div>

</body>
</html>