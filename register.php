<?php
include 'config/db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role_id'];

    // FIX: Define the $sql variable FIRST
    $sql = "INSERT INTO users (username, email, password, role_id) 
            VALUES ('$user', '$email', '$pass', '$role')";
    
    // NOW run the query (This is where Line 4 was failing)
    if ($conn->query($sql) === TRUE) {
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['username'] = $user;
        $_SESSION['role_id'] = $role;

        // Redirect based on role selection
        if ($role == '2') {
            header("Location: seller_dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=106">
    <title>Join Andani Ya Vhatu</title>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container mt-5">
        <div class="card mx-auto shadow-sm border-0" style="max-width: 400px;">
            <div class="card-body p-4">
                <h3 class="purple-text text-center mb-4">Create Account</h3>
                <form method="POST">
                    <input type="text" name="username" class="form-control mb-3 border-purple" placeholder="Username" required>
                    <input type="email" name="email" class="form-control mb-3 border-purple" placeholder="Email" required>
                    <input type="password" name="password" class="form-control mb-3 border-purple" placeholder="Password" required>
                    <select name="role_id" class="form-select mb-4 border-purple">
                        <option value="3">I want to Buy</option>
                        <option value="2">I want to Sell</option>
                    </select>
                    <button type="submit" class="btn btn-purple w-100">Register</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>