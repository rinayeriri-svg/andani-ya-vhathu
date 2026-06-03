<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}
$order_id = intval($_GET['id']);
include 'includes/header.php';
?>

<div style="max-width: 600px; margin: 80px auto; padding: 0 20px; font-family: 'Segoe UI', system-ui, sans-serif; text-align: center;">
    
    <div style="background: white; border: 1px solid #eef0f2; padding: 50px 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.025);">
        
        <div id="status-icon-box" style="margin-bottom: 30px;">
            <div id="spinner-element" style="width: 70px; height: 70px; border: 6px solid #f3ebff; border-top: 6px solid #6f42c1; border-radius: 50%; margin: 0 auto; animation: spin 1s linear infinite;"></div>
            
            <div id="success-element" style="display: none; width: 70px; height: 70px; background-color: #10b981; color: white; border-radius: 50%; margin: 0 auto; align-items: center; justify-content: center;">
                <i class="bi bi-check-lg" style="font-size: 2.5rem;"></i>
            </div>
        </div>

        <h2 id="status-heading" style="font-weight: 800; color: #0f172a; font-size: 1.6rem; margin: 0 0 10px 0;">
            Processing Payment...
        </h2>
        
        <p id="status-subtext" style="color: #64748b; font-size: 1rem; margin: 0; font-weight: 500; line-height: 1.5;">
            Verifying your payment credentials securely. Please do not close or refresh this tab.
        </p>

    </div>
</div>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script type="text/javascript">
    const orderId = <?php echo $order_id; ?>;
    
    // Step 1: Hold processing for 2.5 seconds, then flip to "Payment Completed"
    setTimeout(() => {
        document.getElementById('spinner-element').style.display = 'none';
        
        const successBox = document.getElementById('success-element');
        successBox.style.display = 'flex';
        
        document.getElementById('status-heading').innerText = "Payment Completed! 🎉";
        document.getElementById('status-heading').style.color = "#10b981";
        document.getElementById('status-subtext').innerText = "Authorization successful. Securing your order invoice manifest layout layers...";
        
        // Step 2: Hold for 1.5 more seconds, then push to the grand success tracker page
        setTimeout(() => {
            window.location.href = "order_success.php?id=" + orderId;
        }, 1500);

    }, 2500);
</script>

</body>
</html>