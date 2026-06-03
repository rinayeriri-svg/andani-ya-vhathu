<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'includes/header.php';
?>

<div style="max-width: 600px; margin: 40px auto; padding: 0 20px; font-family: 'Segoe UI', system-ui, sans-serif;">
    <div style="background: #ffffff; border: 1px solid #eef0f2; border-radius: 16px; padding: 35px; box-shadow: 0 4px 20px rgba(0,0,0,0.01);">
        <h2 style="font-weight: 800; color: #1a202c; font-size: 1.6rem; margin-bottom: 6px;">List a New Item</h2>
        
        <form action="process_add_product.php" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 20px;">
            
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-weight: 600; font-size: 0.85rem; color: #4a5568;">Product Title</label>
                <input type="text" name="title" required placeholder="e.g., Prescribed Information Systems Textbook" style="width: 100%; padding: 11px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; color: #2d3748; outline: none;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-weight: 600; font-size: 0.85rem; color: #4a5568;">Product Image</label>
                <input type="file" name="product_image" accept="image/*" required style="padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-weight: 600; font-size: 0.85rem; color: #4a5568;">Marketplace Category</label>
                <select name="category" required style="width: 100%; padding: 11px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; color: #2d3748; background-color: #ffffff; outline: none;">
                    <option value="Academic Materials">Academic Materials (Books & Stationery)</option>
                    <option value="Apparel & Styling">Apparel & Styling (Fashion)</option>
                    <option value="General Goods">General Goods (Electronics & Tech)</option>
                </select>
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-weight: 600; font-size: 0.85rem; color: #4a5568;">Price (Rands)</label>
                <input type="number" name="price" step="0.01" min="1" required placeholder="0.00" style="width: 100%; padding: 11px 14px; border: 1px solid #cbd5e1; border-radius: 8px;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-weight: 600; font-size: 0.85rem; color: #4a5568;">Item Description</label>
                <textarea name="description" rows="4" style="width: 100%; padding: 11px 14px; border: 1px solid #cbd5e1; border-radius: 8px;"></textarea>
            </div>

            <button type="submit" style="background-color: #6f42c1; color: #ffffff; border: none; font-weight: 700; padding: 13px; border-radius: 8px; cursor: pointer;">
                Publish Listing
            </button>
        </form>
    </div>
</div>
</body>
</html>