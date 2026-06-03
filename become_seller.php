<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/db.php';

// Kick unauthorized guests out back to security gate if they aren't signed in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$current_user_id = intval($_SESSION['user_id']);
$error_msg = "";
$success_msg = "";

// --- DYNAMICALLY RESOLVE COLUMN NAMES TO ASSURE CRASH-FREE UPDATES ---
$user_pk_column = "id";       // Default fallback
$role_column    = "role";     // Default fallback

$columns_check = $conn->query("SHOW COLUMNS FROM users");
if ($columns_check) {
    while ($col = $columns_check->fetch_assoc()) {
        $field_name = strtolower($col['Field']);
        if ($field_name === 'user_id') {
            $user_pk_column = "user_id";
        } elseif ($field_name === 'role_id') {
            $role_column = "role_id";
        } elseif ($field_name === 'user_type') {
            $role_column = "user_type";
        }
    }
}

// Handle the upgrade request post submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_upgrade'])) {
    
    // Value mapping determination based on column profile strings vs numeric flags
    // If the role column type uses words, assign 'seller', otherwise assign numeric value 2
    $role_data_type_query = $conn->query("DESCRIBE users $role_column");
    $target_role_value = 2; // Default numeric role ID for merchants
    
    if ($role_data_type_query) {
        $type_info = $role_data_type_query->fetch_assoc();
        if (strpos(strtolower($type_info['Type']), 'varchar') !== false || strpos(strtolower($type_info['Type']), 'char') !== false) {
            $target_role_value = 'seller';
        }
    }

    // Prepare dynamic update targeting the correct customer mapping index
    $update_query = "UPDATE users SET $role_column = ? WHERE $user_pk_column = ?";
    if ($stmt = $conn->prepare($update_query)) {
        if (is_numeric($target_role_value)) {
            $stmt->bind_param("ii", $target_role_value, $current_user_id);
        } else {
            $stmt->bind_param("si", $target_role_value, $current_user_id);
        }
        
        if ($stmt->execute()) {
            // CRITICAL STEP: Live reload active session footprint flags so header switches instantly!
            $_SESSION['role'] = $target_role_value;
            $success_msg = "Success! Your merchant space application is complete. Welcome to the vendor circle!";
            
            // Redirect smoothly into their newly opened seller analytics room after a brief moment
            header("Refresh: 2; url=seller_dashboard.php");
        } else {
            $error_msg = "An architectural update anomaly occurred. Please try again.";
        }
        $stmt->close();
    }
}

include 'includes/header.php';
?>

<div style="max-width: 600px; margin: 60px auto; padding: 0 20px; font-family: 'Segoe UI', system-ui, sans-serif;">
    <div style="background: white; border-radius: 16px; border: 1px solid #eef0f2; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.04); text-align: center;">
        
        <div style="font-size: 3.5rem; color: #6f42c1; margin-bottom: 20px;">
            <i class="bi bi-shop-window"></i>
        </div>
        
        <h2 style="font-weight: 800; color: #0f172a; font-size: 1.6rem; margin-bottom: 10px;">Open Your Merchant Stand</h2>
        <p style="color: #64748b; font-size: 1rem; line-height: 1.6; margin-bottom: 30px;">
            Transform your buyer profile into a registered vendor channel. Upload your catalog inventory, coordinate direct trades, and keep track of your active escrow balances safely.
        </p>

        <?php if (!empty($success_msg)): ?>
            <div style="background-color: #d1fae5; color: #065f46; padding: 15px; border-radius: 10px; font-weight: 600; margin-bottom: 25px; font-size: 0.95rem;">
                <i class="bi bi-check-circle-fill"></i> <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div style="background-color: #fee2e2; color: #991b1b; padding: 15px; border-radius: 10px; font-weight: 600; margin-bottom: 25px; font-size: 0.95rem;">
                <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($success_msg)): ?>
            <form action="become_seller.php" method="POST">
                <input type="hidden" name="confirm_upgrade" value="1">
                
                <button type="submit" style="width: 100%; background: linear-gradient(135deg, #6f42c1, #4338ca); color: white; border: none; padding: 14px 28px; border-radius: 10px; font-size: 1rem; font-weight: 700; cursor: pointer; box-shadow: 0 4px 14px rgba(111, 66, 193, 0.25); transition: transform 0.2s, opacity 0.2s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                    Activate My Seller Space <i class="bi bi-arrow-right-short" style="font-size: 1.2rem; vertical-align: middle;"></i>
                </button>
                
                <a href="index.php" style="display: block; text-decoration: none; color: #64748b; font-size: 0.9rem; font-weight: 600; margin-top: 20px;">
                    Cancel and Keep Buying
                </a>
            </form>
        <?php endif; ?>

    </div>
</div>

</body>
</html>