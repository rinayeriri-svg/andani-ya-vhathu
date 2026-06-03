<?php
session_start();
include 'config/db.php';

$project_root = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$type = isset($_GET['type']) ? $_GET['type'] : 'all';

$category_map = [
    'academic' => 'Academic Materials',
    'fashion'  => 'Apparel & Styling',
    'tech'     => 'General Goods' 
];

if (array_key_exists($type, $category_map)) {
    $db_category = $category_map[$type];
    $display_title = ($type == 'academic') ? 'Textbooks & Stationery' : (($type == 'fashion') ? 'Fashion & Styling' : 'Student Tech');
    
    $query = "SELECT products.*, users.username FROM products 
              JOIN users ON products.seller_id = users.user_id 
              WHERE products.category = '$db_category' AND products.status = 'verified'
              ORDER BY products.product_id DESC";
} else {
    $display_title = "All Marketplace Items";
    $query = "SELECT products.*, users.username FROM products 
              JOIN users ON products.seller_id = users.user_id 
              WHERE products.status = 'verified'
              ORDER BY products.product_id DESC";
}

$result = $conn->query($query);
include 'includes/header.php';
?>

<div style="max-width: 1200px; margin: 30px auto; padding: 0 20px; font-family: 'Segoe UI', sans-serif;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 15px; border-bottom: 2px solid #eef0f2; margin-bottom: 30px;">
        <div>
            <h1 style="font-weight: 800; font-size: 2rem; color: #1a202c; margin: 0;">
                <?php echo $display_title; ?>
            </h1>
        </div>
        <div>
            <span style="background-color: #6f42c1; color: #ffffff; padding: 6px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 700;">
                <?php echo ($result) ? $result->num_rows : 0; ?> Items Found
            </span>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 24px;">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div style="background: #ffffff; border: 1px solid #eef0f2; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.01);">
                    
                    <div style="background-color: #f7fafc; height: 180px; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;">
                        <?php if (!empty($row['image_path']) && file_exists($row['image_path'])): ?>
                            <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="Product" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <span style="font-size: 3rem;">
                                <?php if ($type == 'academic'): ?>📖<?php elseif ($type == 'fashion'): ?>✨<?php else: ?>💻<?php endif; ?>
                            </span>
                        <?php endif; ?>
                        
                        <span style="position: absolute; bottom: 12px; right: 12px; background-color: #6f42c1; color: #ffffff; font-weight: 700; font-size: 0.85rem; padding: 4px 10px; border-radius: 6px;">
                            R<?php echo number_format($row['price'], 2); ?>
                        </span>
                    </div>

                    <div style="padding: 16px;">
                        <h3 style="font-weight: 700; color: #2d3748; font-size: 1.1rem; margin: 0 0 6px 0;">
                            <?php echo htmlspecialchars($row['title']); ?>
                        </h3>
                        <p style="color: #718096; font-size: 0.85rem;">Seller: @<?php echo htmlspecialchars($row['username']); ?></p>
                        <a href="<?php echo $project_root; ?>/product_details.php?id=<?php echo $row['product_id']; ?>" style="color: #6f42c1; font-weight: 700; text-decoration: none;">Inspect Item &rarr;</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 60px;">
                <h2 style="color: #1a202c;">No items available right now</h2>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>