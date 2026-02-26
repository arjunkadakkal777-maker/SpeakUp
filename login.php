
<?php
include "config.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpeakUp Login</title>
    <link rel="stylesheet" href="css/catalog_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f8f9fa;
            background-image: radial-gradient(#e9ecef 1px, transparent 1px);
            background-size: 24px 24px;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            background: white;
            padding: 48px;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.04);
            text-align: center;
        }

        .brand-logo {
            width: 64px;
            height: 64px;
            background: #000;
            border-radius: 16px;
            margin: 0 auto 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
        }

        .login-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 8px 0;
            letter-spacing: -0.5px;
        }

        .login-header p {
            color: #666;
            font-size: 15px;
            margin: 0 0 32px 0;
        }

        /* Override/Enhance Catalog Inputs for Login */
        input[type="text"], input[type="password"] {
            background: #fdfdfd;
            border: 1px solid #eee;
            margin-bottom: 16px;
        }
        
        input[type="text"]:focus, input[type="password"]:focus {
            background: #fff;
            border-color: #000;
        }

        .btn-large {
            width: 100%;
            margin-top: 10px;
            justify-content: center;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="brand-logo" style="background: transparent;">
        <img src="images/logo.png" alt="SpeakUp Logo" style="width: 100%; height: 100%; object-fit: contain; border-radius: 50%;">
    </div>
    
    <div class="login-header">
        <h1>Welcome to SpeakUp</h1>
        <p>Sign in to the SpeakUp Portal</p>
    </div>

    <?php if (isset($_GET['error']) && $_GET['error'] == 'invalid'): ?>
        <div style="background: #ffe3e3; color: #c92a2a; padding: 12px; border-radius: 8px; font-size: 14px; margin-bottom: 20px; display: flex; align-items: center; justify-content: center; gap: 8px;">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <div>Invalid username or password</div>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['resetted'])): ?>
        <div style="background: #e6fcf5; color: #0ca678; padding: 12px; border-radius: 8px; font-size: 14px; margin-bottom: 20px; display: flex; align-items: center; justify-content: center; gap: 8px;">
            <i class="fa-solid fa-check-circle"></i>
            <div>Password updated. Please login.</div>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['created'])): ?>
        <div style="background: #e6fcf5; color: #0ca678; padding: 12px; border-radius: 8px; font-size: 14px; margin-bottom: 20px; display: flex; align-items: center; justify-content: center; gap: 8px;">
            <i class="fa-solid fa-check-circle"></i>
            <div>Account created successfully. Please login.</div>
        </div>
    <?php endif; ?>

    <form method="post" action="authenticate.php">
        <div style="text-align: left;">
            <label style="display:block; margin-bottom:8px; font-weight:600; font-size:13px;">Username</label>
            <input type="text" name="username" placeholder="Enter username" required>
        </div>
        
        <div style="text-align: left;">
            <label style="display:block; margin-bottom:8px; font-weight:600; font-size:13px;">Password</label>
            <div style="position: relative; margin-bottom: 16px;">
                <input type="password" name="password" id="passwordField" placeholder="Enter password" required style="padding-right: 40px; margin-bottom: 0; width: 100%;">
                <i class="fa-solid fa-eye" id="togglePassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #999;" onclick="togglePass()"></i>
            </div>
        </div>

        <script>
            function togglePass() {
                var passField = document.getElementById("passwordField");
                var icon = document.getElementById("togglePassword");
                if (passField.type === "password") {
                    passField.type = "text";
                    icon.classList.remove("fa-eye");
                    icon.classList.add("fa-eye-slash");
                } else {
                    passField.type = "password";
                    icon.classList.remove("fa-eye-slash");
                    icon.classList.add("fa-eye");
                }
            }
        </script>

        <button type="submit" class="btn-large">Sign In</button>
    </form>
    
    <div style="margin-top: 24px; font-size: 14px; color: #666; display: flex; flex-direction: column; gap: 8px;">
        <a href="forgot_password.php" style="color: #666; text-decoration: none;">Forgot Password?</a>
        <span>Don't have an account? <a href="register.php" style="color: #000; font-weight: 600; text-decoration: none;">Register here</a></span>
    </div>
</div>

</div>


</body>
</html>
