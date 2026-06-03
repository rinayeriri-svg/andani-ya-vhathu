<?php
session_start();

// Read error message flashes from the session if they exist, then clear them out instantly
$error_msg = isset($_SESSION['error_msg']) ? $_SESSION['error_msg'] : "";
unset($_SESSION['error_msg']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Andani Ya Vhathu | Platform Login Gate</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', system-ui, sans-serif; }
        body { background: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
        
        .login-card { 
            background: #ffffff; 
            border: 1px solid #e2e8f0; 
            width: 100%; 
            max-width: 440px; 
            padding: 40px; 
            border-radius: 20px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.03); 
        }
        
        .form-group { display: flex; flex-direction: column; gap: 8px; margin-bottom: 22px; }
        
        .form-label { 
            font-size: 0.75rem; 
            font-weight: 800; 
            color: #475569; 
            text-transform: uppercase; 
            letter-spacing: 0.8px; 
        }
        
        .form-control { 
            width: 100%; 
            padding: 14px 16px; 
            border: 1px solid #cbd5e1; 
            background-color: #eff6ff; 
            border-radius: 10px; 
            font-size: 0.95rem; 
            color: #1e293b;
            outline: none; 
            transition: all 0.2s ease; 
        }
        
        .form-control:focus { 
            border-color: #6f42c1; 
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(111,66,193,0.15); 
        }
        
        .btn-submit { 
            background-color: #6f42c1; 
            color: white; 
            border: none; 
            padding: 14px; 
            width: 100%; 
            border-radius: 10px; 
            font-size: 1.05rem; 
            font-weight: 700; 
            cursor: pointer; 
            transition: background 0.2s, transform 0.1s; 
            margin-top: 10px; 
            box-shadow: 0 4px 12px rgba(111, 66, 193, 0.2);
        }
        
        .btn-submit:hover { background-color: #5a32a3; }
        .btn-submit:active { transform: scale(0.99); }
        
        .alert-error { 
            background-color: #fff5f5; 
            color: #c53030; 
            border: 1px solid #fecaca; 
            padding: 14px; 
            border-radius: 10px; 
            font-size: 0.9rem; 
            font-weight: 600; 
            margin-bottom: 24px; 
            text-align: center; 
            line-height: 1.4;
        }

        .register-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
            font-size: 0.9rem;
            color: #64748b;
        }

        .register-link {
            color: #6f42c1;
            text-decoration: none;
            font-weight: 700;
            transition: color 0.2s;
            margin-left: 4px;
        }

        .register-link:hover {
            color: #5a32a3;
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="font-weight: 900; color: #1e1b4b; font-size: 1.9rem; letter-spacing: -0.5px;">Welcome Back</h1>
        <p style="color: #64748b; font-size: 0.95rem; margin-top: 6px;">Sign into your Andani Ya Vhathu account</p>
    </div>

    <?php if (!empty($error_msg)): ?>
        <div class="alert-error">
            <i class="bi bi-exclamation-triangle-fill" style="margin-right: 4px;"></i> <?php echo $error_msg; ?>
        </div>
    <?php endif; ?>

    <form action="login_process.php" method="POST">
        <div class="form-group">
            <label class="form-label">Username or Email</label>
            <input type="text" name="username" required class="form-control" placeholder="Enter your credentials..." autocomplete="off">
        </div>
        
        <div class="form-group">
            <label class="form-label">Password Asset</label>
            <input type="password" name="password" required class="form-control" placeholder="•••••">
        </div>

        <button type="submit" class="btn-submit">Sign In</button>
    </form>

    <div class="register-footer">
        Don't have an account yet? <a href="register.php" class="register-link">Register here</a>
    </div>
</div>

</body>
</html>