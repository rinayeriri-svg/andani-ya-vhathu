<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/db.php';
include 'includes/header.php';

// Route anonymous traffic away back to the security gate if they try to access a profile without a session
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$current_session_uid = intval($_SESSION['user_id']);

// --- DYNAMICALLY RESOLVE USER PRIMARY KEY TO PREVENT PREPARE CRASHES ---
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

// 1. Fetch authenticated user data using the dynamically discovered column name
$user_data = null;
$profile_query = "SELECT * FROM users WHERE $user_pk_column = ? LIMIT 1";

if ($stmt = $conn->prepare($profile_query)) {
    $stmt->bind_param("i", $current_session_uid);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows === 1) {
        $user_data = $result->fetch_assoc();
    }
    $stmt->close();
}

// Redirect if profile row vanished unexpectedly
if (!$user_data) {
    header("Location: logout.php");
    exit;
}

// Track what tab section to view (Defaults to 'details')
$active_tab = isset($_GET['tab']) ? trim($_GET['tab']) : 'details';

// 2. NEW LOGIC: Fetch live records for the "My Orders" tab view if requested
$profile_orders = [];
if ($active_tab === 'orders') {
    $orders_query = "SELECT o.order_id, o.total_price, o.shipping_method, o.shipping_status 
                     FROM orders o 
                     WHERE o.buyer_id = ? 
                     ORDER BY o.order_id DESC";
    
    if ($stmt_orders = $conn->prepare($orders_query)) {
        $stmt_orders->bind_param("i", $current_session_uid);
        $stmt_orders->execute();
        $res_orders = $stmt_orders->get_result();
        while ($row = $res_orders->fetch_assoc()) {
            $profile_orders[] = $row;
        }
        $stmt_orders->close();
    }
}
?>

<div style="max-width: 1100px; margin: 40px auto; padding: 0 20px; font-family: 'Segoe UI', system-ui, sans-serif;">
    
    <div style="display: flex; gap: 30px; flex-wrap: wrap;">
        
        <div style="flex: 1; min-width: 280px; max-width: 320px; background: white; border-radius: 16px; border: 1px solid #eef0f2; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.01); height: fit-content;">
            
            <div style="display: flex; align-items: center; gap: 15px; padding-bottom: 20px; border-bottom: 1px solid #f1f5f9; margin-bottom: 20px;">
                <div style="background: linear-gradient(135deg, #6f42c1, #4338ca); color: white; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; font-weight: 700;">
                    <?php echo strtoupper(substr($user_data['username'], 0, 1)); ?>
                </div>
                <div>
                    <h4 style="margin: 0; color: #0f172a; font-weight: 700;">@<?php echo htmlspecialchars($user_data['username']); ?></h4>
                    <span style="font-size: 0.8rem; color: #64748b;">Account ID: #<?php echo $user_data[$user_pk_column]; ?></span>
                </div>
            </div>

            <ul style="list-style: none; padding: 0; margin: 0;">
                
                <li>
                    <a href="profile.php?tab=details" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 8px; text-decoration: none; font-size: 0.95rem; font-weight: 600; color: <?php echo $active_tab === 'details' ? '#6f42c1' : '#475569'; ?>; background-color: <?php echo $active_tab === 'details' ? '#f3ebff' : 'transparent'; ?>; transition: all 0.2s;">
                        <i class="bi bi-person-vcard" style="font-size: 1.1rem;"></i> My Details
                    </a>
                </li>
                <li>
                    <a href="profile.php?tab=orders" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 8px; text-decoration: none; font-size: 0.95rem; font-weight: 600; color: <?php echo $active_tab === 'orders' ? '#6f42c1' : '#475569'; ?>; background-color: <?php echo $active_tab === 'orders' ? '#f3ebff' : 'transparent'; ?>; transition: all 0.2s;">
                        <i class="bi bi-bag-check" style="font-size: 1.1rem;"></i> My Orders
                    </a>
                </li>
                <li>
                    <a href="profile.php?tab=returns" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 8px; text-decoration: none; font-size: 0.95rem; font-weight: 600; color: <?php echo $active_tab === 'returns' ? '#6f42c1' : '#475569'; ?>; background-color: <?php echo $active_tab === 'returns' ? '#f3ebff' : 'transparent'; ?>; transition: all 0.2s;">
                        <i class="bi bi-arrow-left-right" style="font-size: 1.1rem;"></i> Exchanges & Returns
                    </a>
                </li>
                <li>
                    <a href="profile.php?tab=reviews" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 8px; text-decoration: none; font-size: 0.95rem; font-weight: 600; color: <?php echo $active_tab === 'reviews' ? '#6f42c1' : '#475569'; ?>; background-color: <?php echo $active_tab === 'reviews' ? '#f3ebff' : 'transparent'; ?>; transition: all 0.2s;">
                        <i class="bi bi-star" style="font-size: 1.1rem;"></i> My Product Reviews
                    </a>
                </li>

                <li><hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 15px 0;"></li>

                <li>
                    <a href="profile.php?tab=payment" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 8px; text-decoration: none; font-size: 0.95rem; font-weight: 600; color: <?php echo $active_tab === 'payment' ? '#6f42c1' : '#475569'; ?>; background-color: <?php echo $active_tab === 'payment' ? '#f3ebff' : 'transparent'; ?>; transition: all 0.2s;">
                        <i class="bi bi-credit-card-2-front" style="font-size: 1.1rem;"></i> Payment Method
                    </a>
                </li>

                <li><hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 15px 0;"></li>

                <li>
                    <a href="profile.php?tab=delivery" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 8px; text-decoration: none; font-size: 0.95rem; font-weight: 600; color: <?php echo $active_tab === 'delivery' ? '#6f42c1' : '#475569'; ?>; background-color: <?php echo $active_tab === 'delivery' ? '#f3ebff' : 'transparent'; ?>; transition: all 0.2s;">
                        <i class="bi bi-truck" style="font-size: 1.1rem;"></i> Delivery Method
                    </a>
                </li>

            </ul>
        </div>

        <div style="flex: 2; min-width: 320px; background: white; border-radius: 16px; border: 1px solid #eef0f2; padding: 35px; box-shadow: 0 4px 12px rgba(0,0,0,0.01);">
            
            <?php if ($active_tab === 'details'): ?>
                <h3 style="font-weight: 800; color: #0f172a; margin-bottom: 8px;">My Details</h3>
                <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 25px;">Manage your account credentials and personal profiling data details.</p>
                
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 10px; border-left: 4px solid #6f42c1;">
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #8a92a6; text-transform: uppercase;">Platform Username</label>
                        <div style="font-weight: 600; color: #2d3748; margin-top: 4px;"><?php echo htmlspecialchars($user_data['username']); ?></div>
                    </div>
                    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 10px; border-left: 4px solid #6f42c1;">
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #8a92a6; text-transform: uppercase;">Registered Contact Email</label>
                        <div style="font-weight: 600; color: #2d3748; margin-top: 4px;"><?php echo htmlspecialchars($user_data['email']); ?></div>
                    </div>
                </div>

            <?php elseif ($active_tab === 'orders'): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <h3 style="font-weight: 800; color: #0f172a; margin: 0;">My Orders</h3>
                    <?php while ($order = $result->fetch_assoc()): ?>
    <a href="track_order.php?id=<?php echo $order['order_id']; ?>">Launch Order Hub</a>
<?php endwhile; ?>
                </div>
                <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 25px;">Track your outgoing marketplace transactions and pending items.</p>
                
                <?php if (!empty($profile_orders)): ?>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <?php foreach ($profile_orders as $order_row): ?>
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; border: 1px solid #f1f5f9; border-radius: 12px; background-color: #f8fafc; gap: 10px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="font-weight: 800; color: #6f42c1; font-size: 0.85rem; background: white; border: 1px solid #e2e8f0; padding: 6px 10px; border-radius: 6px;">
                                        #<?php echo $order_row['order_id']; ?>
                                    </div>
                                    <div>
                                        <strong style="color: #0f172a; font-size: 0.9rem; display: block;">Ref #AV-<?php echo str_pad($order_row['order_id'], 5, '0', STR_PAD_LEFT); ?></strong>
                                        <span style="font-size: 0.75rem; color: #64748b;">Via: <?php echo htmlspecialchars($order_row['shipping_method']); ?></span>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <strong style="color: #10b981; font-size: 0.95rem;">R <?php echo number_format($order_row['total_price'], 2, ',', ' '); ?></strong>
                                    
                                    <?php if ($order_row['shipping_status'] === 'collected'): ?>
                                        <span style="background: #e6fbf1; color: #065f46; font-size: 0.7rem; font-weight: 700; padding: 4px 8px; border-radius: 12px;">Released</span>
                                    <?php else: ?>
                                        <span style="background: #fff7ed; color: #c2410c; font-size: 0.7rem; font-weight: 700; padding: 4px 8px; border-radius: 12px;">In Escrow</span>
                                    <?php endif; ?>

                                    <a href="order_success.php?id=<?php echo $order_row['order_id']; ?>" style="color: #475569; font-size: 1.1rem;"><i class="bi bi-arrow-right-short"></i></a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div style="border: 2px dashed #e2e8f0; border-radius: 12px; padding: 40px; text-align: center; color: #94a3b8; font-weight: 600;">
                        <i class="bi bi-box-seam" style="font-size: 2rem; color: #cbd5e1; display: block; margin-bottom: 10px;"></i>
                        No active transaction records mapped to your account yet.
                    </div>
                <?php endif; ?>

            <?php elseif ($active_tab === 'returns'): ?>
                <h3 style="font-weight: 800; color: #0f172a; margin-bottom: 8px;">Exchanges & Returns</h3>
                <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 25px;">Initiate item logs if tracking discrepancies occur within shipping corridors.</p>
                <div style="border: 2px dashed #e2e8f0; border-radius: 12px; padding: 40px; text-align: center; color: #94a3b8; font-weight: 600;">
                    <i class="bi bi-arrow-counterclockwise" style="font-size: 2rem; color: #cbd5e1; display: block; margin-bottom: 10px;"></i>
                    No active return files or logged product disputes discovered.
                </div>

            <?php elseif ($active_tab === 'reviews'): ?>
                <h3 style="font-weight: 800; color: #0f172a; margin-bottom: 8px;">My Product Reviews</h3>
                <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 25px;">Feedback logs left across checked merchant directories.</p>
                <div style="border: 2px dashed #e2e8f0; border-radius: 12px; padding: 40px; text-align: center; color: #94a3b8; font-weight: 600;">
                    <i class="bi bi-chat-heart" style="font-size: 2rem; color: #cbd5e1; display: block; margin-bottom: 10px;"></i>
                    You haven't posted any feedback scores on inventory items yet.
                </div>

            <?php elseif ($active_tab === 'payment'): ?>
                <h3 style="font-weight: 800; color: #0f172a; margin-bottom: 8px;">Payment Method</h3>
                <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 25px;">Verify secure commercial funding gateways active on your transaction channels.</p>
                
                <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; border-left: 5px solid #6f42c1;">
                    <div style="display: flex; align-items: start; gap: 15px;">
                        <div style="font-size: 1.5rem; color: #6f42c1; background: #f3ebff; width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0 0 6px 0; color: #0f172a; font-weight: 700;">Andani Ya Vhathu Secure Escrow Guard</h4>
                            <p style="margin: 0; color: #475569; font-size: 0.9rem; line-height: 1.6;">
                                Funds remain vault-held by the clearing framework during dispatch windows. Money transfers release to the respective merchant only upon package verification scan arrival.
                            </p>
                            <div style="margin-top: 15px; display: inline-flex; align-items: center; gap: 6px; background: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 0.75rem; padding: 4px 10px; border-radius: 20px; text-transform: uppercase;">
                                <i class="bi bi-truck"></i> Delivery-Only Logistics Active
                            </div>
                        </div>
                    </div>
                </div>

            <?php elseif ($active_tab === 'delivery'): ?>
                <h3 style="font-weight: 800; color: #0f172a; margin-bottom: 8px;">Delivery Methods</h3>
                <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 25px;">Supported standard logistics routes operational across local trading corridors.</p>
                
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; border: 1px solid #e2e8f0; padding: 16px; border-radius: 10px; background: white;">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <span style="font-size: 1.4rem; background: #fff1f2; width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">📦</span>
                            <div>
                                <div style="font-weight: 700; color: #0f172a;">Paxxi (PEP to PEP Counter)</div>
                                <div style="font-size: 0.8rem; color: #64748b;">Collect or send items conveniently from your nearest PEP store outlet.</div>
                            </div>
                        </div>
                        <span style="font-size: 0.75rem; background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 12px; font-weight: 700;">Supported</span>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: space-between; border: 1px solid #e2e8f0; padding: 16px; border-radius: 10px; background: white;">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <span style="font-size: 1.4rem; background: #eff6ff; width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">🚚</span>
                            <div>
                                <div style="font-weight: 700; color: #0f172a;">Fastway Couriers</div>
                                <div style="font-size: 0.8rem; color: #64748b;">Direct door-to-door transit deliveries routed straight to your tracking marker.</div>
                            </div>
                        </div>
                        <span style="font-size: 0.75rem; background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 12px; font-weight: 700;">Supported</span>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: space-between; border: 1px solid #e2e8f0; padding: 16px; border-radius: 10px; background: white;">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <span style="font-size: 1.4rem; background: #f0fdf4; width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">📮</span>
                            <div>
                                <div style="font-weight: 700; color: #0f172a;">PostNet Suite Drop</div>
                                <div style="font-size: 0.8rem; color: #64748b;">Counter-to-counter tracking infrastructure running via countrywide networks.</div>
                            </div>
                        </div>
                        <span style="font-size: 0.75rem; background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 12px; font-weight: 700;">Supported</span>
                    </div>
                </div>
            <?php endif; ?>

        </div>

    </div>

</div>

</body>
</html>