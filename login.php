
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

    <form method="post" action="authenticate.php">
        <div style="text-align: left;">
            <label style="display:block; margin-bottom:8px; font-weight:600; font-size:13px;">Username</label>
            <input type="text" name="username" placeholder="Enter your username" required>
        </div>
        
        <div style="text-align: left;">
            <label style="display:block; margin-bottom:8px; font-weight:600; font-size:13px;">Password</label>
            <input type="password" name="password" placeholder="Enter your password" required>
        </div>

        <button type="submit" class="btn-large">Sign In</button>
    </form>
    
    <div style="margin-top: 24px; font-size: 13px; color: #999;">
        <a href="#" style="color: inherit; text-decoration: none;">Forgot Password?</a>
    </div>
</div>

</body>
</html>
