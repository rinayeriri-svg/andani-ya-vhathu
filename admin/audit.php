<?php
session_start();
require_once '../config/db.php';

// 1. Security Check
if (!isset($_SESSION['user_id']) || (int)$_SESSION['role'] !== 3) {
    header("Location: ../login.php");
    exit;
}

// 2. Fetch Audit Logs
// We check if the table exists first to prevent crashes
$logs = $conn->query("SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 50");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        body { font-family: sans-serif; background: #f8fafc; padding: 40px; }
        .log-container { background: #1e293b; color: #f1f5f9; padding: 20px; border-radius: 8px; font-family: monospace; }
    </style>
</head>
<body>

    <h2>Audit Logs</h2>

    <div class="log-container">
        <?php 
        if ($logs && $logs->num_rows > 0) {
            while ($log = $logs->fetch_assoc()) {
                echo "<p style='border-bottom: 1px solid #334155; padding: 5px 0;'>";
                echo "[" . htmlspecialchars($log['created_at']) . "] ";
                echo "Admin ID: " . htmlspecialchars($log['admin_id']) . " | ";
                echo "Action: " . htmlspecialchars($log['action']) . " | ";
                echo "Target: " . htmlspecialchars($log['target_id']);
                echo "</p>";
            }
        } else {
            echo "<p>No logs found yet. Perform an action to see it recorded here.</p>";
        }
        ?>
    </div>

    <br>
    <a href="dashboard.php">Back to Dashboard</a>

</body>
</html>