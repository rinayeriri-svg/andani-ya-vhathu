<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/db.php';

// Route any unauthorized direct browser URL access attempts straight back to the login gateway
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$username_input = isset($_POST['username']) ? trim($_POST['username']) : '';
$password_input = isset($_POST['password']) ? trim($_POST['password']) : '';

// 1. Initial Empty Validation Check
if (empty($username_input) || empty($password_input)) {
    $_SESSION['error_msg'] = "Please fill in all identity fields.";
    header("Location: login.php");
    exit;
}

// 2. Prepare Database Selection Statement
$query = "SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1";

if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("ss", $username_input, $username_input);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // 3. Cryptographic Password Verification
        if (password_verify($password_input, $user['password'])) {
            
            // Wipe out historic session errors upon authentication success
            unset($_SESSION['error_msg']);
            
            // --- FLEXIBLE COLUMN CAPTURE NETS ---
            // Safely detects if column is 'user_id' or 'id'
            $resolved_id = isset($user['user_id']) ? $user['user_id'] : ($user['id'] ?? 0);
            
            // Safely detects if column is 'role_id', 'role', or 'user_type'
            $resolved_role = 0;
            if (isset($user['role_id'])) {
                $resolved_role = $user['role_id'];
            } elseif (isset($user['role'])) {
                $resolved_role = $user['role'];
            } elseif (isset($user['user_type'])) {
                $resolved_role = $user['user_type'];
            }
            
            // 4. ASSIGN GLOBAL SESSIONS
            $_SESSION['user_id']  = $resolved_id;
            $_SESSION['username'] = $user['username'] ?? 'User';
            $_SESSION['email']    = $user['email'] ?? '';
            $_SESSION['role']     = $resolved_role; 
            
            // 5. RELIABLE ADMINISTRATIVE TRAFFIC SPLITTER ROUTER
            if ($resolved_role == 3 || $resolved_role === '3' || strtolower(trim($resolved_role)) === 'admin') {
                // Sent straight into your administration panel workspace!
                header("Location: admin/dashboard.php");
                exit;
            } else {
                // Regular members are directed to the marketplace storefront home
                header("Location: index.php");
                exit;
            }
            
        } else {
            $_SESSION['error_msg'] = "Invalid identification tokens. Check your password entry.";
            header("Location: login.php");
            exit;
        }
    } else {
        $_SESSION['error_msg'] = "No user profile matched into system registries.";
        header("Location: login.php");
        exit;
    }
    $stmt->close();
} else {
    $_SESSION['error_msg'] = "Database parsing error. Request failed structural execution.";
    header("Location: login.php");
    exit;
}