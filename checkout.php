<?php
// 1. Force errors to show on the screen for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'config/db.php';

if (!isset($_SESSION['user_id'])) { die("User not logged in."); }
$user_id = intval($_SESSION['user_id']);

// 2. Fetch Cart Items with Product Details
$query = "SELECT c.quantity, p.product_id, p.title, p.price, p.seller_id 
          FROM cart c 
          JOIN products p ON c.product_id = p.product_id 
          WHERE c.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($cart_items)) { 
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'><h2>Your cart is empty.</h2><a href='index.php'>Return to Shopping</a></div>"); 
}

// 3. Calculate Cart Total
$cart_subtotal = 0;
foreach($cart_items as $item) {
    $cart_subtotal += ($item['price'] * $item['quantity']);
}

// 4. Process Order Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order_submit'])) {
    $phone = $_POST['customer_phone'] ?? '0000000000';
    $address = $_POST['delivery_address'] ?? 'No address';
    $method = $_POST['delivery_method'] ?? 'Paxi';
    $payment_method = $_POST['payment_method'] ?? 'EFT'; // Captured chosen method
    
    $conn->begin_transaction();
    try {
        foreach ($cart_items as $item) {
            $total = $item['price'] * $item['quantity'];
            
            // Log the order with its pending flags
            $sql = "INSERT INTO orders (buyer_id, product_id, seller_id, total_price, customer_phone, delivery_address, shipping_method, shipping_status, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiidsss", 
                $user_id, 
                $item['product_id'], 
                $item['seller_id'], 
                $total, 
                $phone, 
                $address, 
                $method
            );
            $stmt->execute();
        }
        
        // Clear cart after successful order placement
        $conn->query("DELETE FROM cart WHERE user_id = $user_id");
        $conn->commit();
        echo "<script>alert('Order placed successfully using " . htmlspecialchars($payment_method) . "!'); window.location.href='track_order.php';</script>";
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        die("Error processing order: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Andani Ya Vhatu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --success: #059669;
            --bg: #f3f4f6;
            --text: #1f2937;
            --border: #d1d5db;
        }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg); color: var(--text); margin: 0; padding: 40px 20px; }
        .checkout-wrapper { max-width: 1050px; margin: 0 auto; display: grid; grid-template-columns: 1fr 380px; gap: 25px; }
        
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); height: fit-content; margin-bottom: 25px; }
        h2 { margin-top: 0; font-weight: 700; font-size: 1.3rem; margin-bottom: 20px; color: #111827; display: flex; align-items: center; gap: 10px; }
        
        /* Form Styling */
        label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: #4b5563; }
        input[type="text"], input[type="tel"], textarea, select { 
            width: 100%; padding: 12px 14px; margin-bottom: 20px; 
            border: 1px solid var(--border); border-radius: 8px; box-sizing: border-box;
            font-family: inherit; font-size: 0.95rem; color: var(--text);
        }
        input:focus, textarea:focus, select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15); }
        textarea { resize: vertical; }

        /* Payment Methods Grid */
        .payment-methods-grid { display: grid; grid-template-columns: 1fr; gap: 12px; margin-bottom: 10px; }
        .payment-option {
            border: 2px solid #e5e7eb; border-radius: 10px; padding: 16px;
            display: flex; align-items: center; gap: 15px; cursor: pointer;
            transition: all 0.2s ease; position: relative;
        }
        .payment-option:hover { border-color: #cbd5e1; background-color: #f9fafb; }
        .payment-option input[type="radio"] { width: 18px; height: 18px; accent-color: var(--primary); margin: 0; }
        
        /* Highlight selected option state via JavaScript alternative/pure CSS trigger */
        .payment-option input[type="radio"]:checked { border-color: var(--primary); }
        
        .method-icon { font-size: 1.5rem; color: #4b5563; min-width: 30px; text-align: center; }
        .method-text strong { display: block; font-size: 0.95rem; color: #111827; }
        .method-text span { display: block; font-size: 0.8rem; color: #6b7280; margin-top: 2px; }

        /* Active highlight styling helper classes */
        .payment-option.active { border-color: var(--primary); background-color: #eff6ff; }
        .payment-option.active .method-icon { color: var(--primary); }

        /* Order Summary Layout */
        .summary-item { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 0.95rem; color: #4b5563; }
        .total-row { border-top: 2px dashed #e5e7eb; margin-top: 15px; padding-top: 15px; font-weight: 700; font-size: 1.25rem; color: #111827; display: flex; justify-content: space-between; }
        
        .btn-submit { 
            background: var(--success); color: white; padding: 15px; border: none; 
            width: 100%; font-size: 1.05rem; font-weight: 700; cursor: pointer; 
            border-radius: 8px; transition: background 0.2s; margin-top: 10px;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-submit:hover { background: #047857; }

        @media (max-width: 768px) {
            .checkout-wrapper { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<form id="checkout-form" method="POST">
    <div class="checkout-wrapper">
        
        <!-- Left Column: Fields and Options Container -->
        <div>
            <!-- Section A: Delivery Details -->
            <div class="card">
                <h2><i class="bi bi-truck text-primary"></i> Secure Delivery Details</h2>
                
                <label for="delivery_address">Delivery Address</label>
                <textarea name="delivery_address" id="delivery_address" rows="3" placeholder="Street address, campus residence, or local collection point" required></textarea>
                
                <label for="customer_phone">Contact Phone Number</label>
                <input type="tel" name="customer_phone" id="customer_phone" placeholder="e.g., 071 234 5678" required>
                
                <label for="delivery_method">Shipping Method</label>
                <select name="delivery_method" id="delivery_method">
                    <option value="Paxi">Paxi - Pep Store Collection (R150)</option>
                    <option value="Courier">Door-to-Door Courier Delivery (R200)</option>
                </select>
            </div>

            <!-- Section B: Payment Selection -->
            <div class="card">
                <h2><i class="bi bi-shield-check" style="color: var(--success);"></i> Select Payment Method</h2>
                
                <div class="payment-methods-grid">
                    
                    <!-- Option 1: Capitec Pay -->
                    <label class="payment-option active" onclick="toggleActivePayment(this)">
                        <input type="radio" name="payment_method" value="CapitecPay" checked>
                        <div class="method-icon"><i class="bi bi-phone-vibrate"></i></div>
                        <div class="method-text">
                            <strong>Capitec Pay</strong>
                            <span>Approve instantly via your banking app notification.</span>
                        </div>
                    </label>

                    <!-- Option 2: Instant EFT -->
                    <label class="payment-option" onclick="toggleActivePayment(this)">
                        <input type="radio" name="payment_method" value="InstantEFT">
                        <div class="method-icon"><i class="bi bi-lightning-charge"></i></div>
                        <div class="method-text">
                            <strong>Instant EFT</strong>
                            <span>Secure link connection to Standard Bank, FNB, Absa, Nedbank etc.</span>
                        </div>
                    </label>

                    <!-- Option 3: Credit/Debit Card -->
                    <label class="payment-option" onclick="toggleActivePayment(this)">
                        <input type="radio" name="payment_method" value="Card">
                        <div class="method-icon"><i class="bi bi-credit-card"></i></div>
                        <div class="method-text">
                            <strong>Visa / Mastercard</strong>
                            <span>Standard secure transactional credit or debit card input.</span>
                        </div>
                    </label>

                </div>
            </div>
        </div>

        <!-- Right Column: Order Summary & Action -->
        <div class="card">
            <h2>Order Summary</h2>
            <?php foreach ($cart_items as $item): ?>
                <div class="summary-item">
                    <span><?php echo htmlspecialchars($item['title'] ?? 'Marketplace Item'); ?> <strong>(x<?php echo $item['quantity']; ?>)</strong></span>
                    <span>R<?php echo number_format($item['price'] * $item['quantity'], 2, ',', ' '); ?></span>
                </div>
            <?php endforeach; ?>
            
            <div class="total-row">
                <span>Total Basket Cost</span>
                <span>R<?php echo number_format($cart_subtotal, 2, ',', ' '); ?></span>
            </div>
            
            <button type="submit" name="place_order_submit" class="btn-submit">
                <i class="bi bi-lock-fill"></i> Complete Secure Purchase
            </button>
            
            <p style="font-size: 0.8rem; color: #6b7280; margin-top: 25px; line-height: 1.4; text-align: center;">
                🔒 Payment processed securely using encrypted connection layouts.
            </p>
        </div>
        
    </div>
</form>

<script>
    // Visual toggler script to make radio cards react gracefully upon selection clicks
    function toggleActivePayment(element) {
        // Remove active class from all options
        document.querySelectorAll('.payment-option').forEach(option => {
            option.classList.remove('active');
        });
        // Add active class to clicked option
        element.classList.add('active');
        
        // Ensure the internal radio button gets checked if clicking text context areas
        const radio = element.querySelector('input[type="radio"]');
        if(radio) radio.checked = true;
    }
</script>

</body>
</html>