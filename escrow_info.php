<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/header.php';
?>

<div style="max-width: 900px; margin: 40px auto; padding: 0 20px; font-family: 'Segoe UI', system-ui, sans-serif;">
    
    <div style="margin-bottom: 40px; padding-left: 15px; border-left: 5px solid #10b981;">
        <h1 style="font-weight: 800; color: #0f172a; font-size: 2.2rem; margin: 0 0 6px 0; letter-spacing: -0.5px;">Safe & Secure Escrow Protection</h1>
        <p style="color: #64748b; font-size: 1.05rem; margin: 0; font-weight: 500;">
            How Andani Ya Vhathu ensures secure, scam-free trading for our whole community.
        </p>
    </div>

    <div style="background: white; border: 1px solid #eef0f2; padding: 30px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.01); margin-bottom: 35px;">
        <h3 style="font-weight: 800; color: #1e293b; margin: 0 0 12px 0; font-size: 1.3rem; display: flex; align-items: center; gap: 10px;">
            <i class="bi bi-shield-shaded" style="color: #10b981;"></i> What is Escrow?
        </h3>
        <p style="color: #475569; font-size: 1rem; line-height: 1.6; margin: 0;">
            An escrow account is a secure neutral holding area. When a buyer pays for an item, the money doesn’t go straight to the seller. Instead, <strong>Andani Ya Vhathu securely holds the funds</strong>. The money is only transferred out to the seller's account once the buyer successfully receives, inspects, and confirms the condition of their package.
        </p>
    </div>

    <h3 style="font-weight: 800; color: #1e293b; margin: 0 0 25px 5px; font-size: 1.25rem;">The Step-by-Step Process</h3>
    
    <div style="display: flex; flex-direction: column; gap: 20px; margin-bottom: 40px;">
        
        <div style="display: flex; gap: 20px; background: white; border: 1px solid #eef0f2; padding: 20px; border-radius: 12px;">
            <div style="background: #f3ebff; color: #6f42c1; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; flex-shrink: 0;">1</div>
            <div>
                <h4 style="margin: 0 0 4px 0; color: #0f172a; font-weight: 700; font-size: 1.05rem;">Buyer Securely Pays</h4>
                <p style="margin: 0; color: #64748b; font-size: 0.92rem; line-height: 1.5;">The buyer selects a product and completes payment through our checkout gateway. The system safely intercepts and logs the funds inside the secure marketplace vault.</p>
            </div>
        </div>

        <div style="display: flex; gap: 20px; background: white; border: 1px solid #eef0f2; padding: 20px; border-radius: 12px;">
            <div style="background: #e0f2fe; color: #0369a1; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; flex-shrink: 0;">2</div>
            <div>
                <h4 style="margin: 0 0 4px 0; color: #0f172a; font-weight: 700; font-size: 1.05rem;">Seller Ships the Item</h4>
                <p style="margin: 0; color: #64748b; font-size: 0.92rem; line-height: 1.5;">Knowing the payment is guaranteed and locked in, the seller ships the item or sets up delivery using the designated courier system channels.</p>
            </div>
        </div>

        <div style="display: flex; gap: 20px; background: white; border: 1px solid #eef0f2; padding: 20px; border-radius: 12px;">
            <div style="background: #d1fae5; color: #10b981; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; flex-shrink: 0;">3</div>
            <div>
                <h4 style="margin: 0 0 4px 0; color: #0f172a; font-weight: 700; font-size: 1.05rem;">Verification & Release</h4>
                <p style="margin: 0; color: #64748b; font-size: 0.92rem; line-height: 1.5;">Once the package lands safely, the buyer approves it via their dashboard profile. The escrow account unlocks, and the system forwards the earnings straight to the seller.</p>
            </div>
        </div>

    </div>

    <div style="background: #0f172a; padding: 30px; border-radius: 16px; color: white; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
        <div>
            <h4 style="margin: 0 0 4px 0; font-weight: 700; font-size: 1.1rem;">Ready to track your purchases?</h4>
            <p style="margin: 0; color: #94a3b8; font-size: 0.9rem;">View live orders under secure escrow custody right now.</p>
        </div>
        <!-- Open your root escrow.php file and check the anchor tag. It should look like this: -->
<a href="track_order.php" class="btn">View My Orders</a>
    </div>

</div>

</body>
</html>