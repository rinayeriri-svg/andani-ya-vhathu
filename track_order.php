<?php
// 1. Session start to verify user is logged in
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit; 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Status</title>
    <style>
        body { font-family: sans-serif; background: #f8fafc; padding: 20px; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .tracker-box { background: white; border: 1px solid #e2e8f0; padding: 40px; text-align: center; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .status-badge { padding: 15px 30px; background: #059669; color: white; display: inline-block; border-radius: 8px; font-weight: bold; margin: 20px 0; font-size: 1.2em; }
        .btn-back { display: inline-block; text-decoration: none; padding: 10px 20px; background: #64748b; color: white; border-radius: 5px; font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>

    <div class="tracker-box">
        <h2 style="color: #0f172a;">Order Update</h2>
        <p>Great news!</p>
        <div class="status-badge">Your order is on the way!</div>
        <br>
        <a href="index.php" class="btn-back">&larr; Back to Dashboard</a>
    </div>

</body>
</html>