<?php
include "config.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    
    // Check if submitting email
    if (isset($_POST['check_email'])) {
        
        if (empty($email)) {
            $error = "Please enter your email address.";
        } else {
            // Check if user exists
            $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($user = $result->fetch_assoc()) {
                
                // Send Email Flow
                $token = bin2hex(random_bytes(16));
                
                if ($conn->query("UPDATE users SET reset_token='$token' WHERE id=" . $user['id'])) {
                    // Dynamically get the current host
                    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                    $host = $_SERVER['HTTP_HOST'];
                    
                    // Fix path slashes for Windows/XAMPP
                    $path = str_replace('\\', '/', dirname($_SERVER['PHP_SELF']));
                    $path = rtrim($path, '/');
                    
                    $reset_link = $protocol . "://" . $host . $path . "/reset_password.php?token=" . $token;
                    
                    $to = $email;
                    $subject = "Password Reset Request";
                    $headers = "From: SpeakUp Admin <reset@speakup.local>\r\n";
                    $headers .= "Content-type: text/html\r\n";
                    $headers .= "Reply-To: no-reply@speakup.local\r\n";
                    $body = "Hi " . htmlspecialchars($user['username']) . ",<br><br>Click the link below to reset your password:<br><a href='$reset_link'>$reset_link</a>";
                    
                    if (@mail($to, $subject, $body, $headers)) {
                        $success = "A reset link has been sent to your email.";
                    } else {
                        // FALLBACK for Dev
                        $success = "<div style='text-align:left;'>
                                    <strong>(Development Mode)</strong> Email failed to send (no mail server).<br>
                                    <div style='margin-top:10px; padding:10px; background:#fff; border:1px solid #ddd; word-break:break-all; font-family:monospace; color:#333;'>
                                        <a href='$reset_link'>$reset_link</a>
                                    </div>
                                    <div style='font-size:12px; margin-top:5px; color:#666;'>Click the link above to reset your password.</div>
                                    </div>";
                    }
                } else {
                    $error = "Database error: Could not save reset token.";
                }

            } else {
                $error = "User not found.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - SpeakUp</title>
    <link rel="stylesheet" href="css/catalog_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            text-align: center;
        }
        .icon-circle {
            width: 60px;
            height: 60px;
            background: #fff0f6;
            color: #d6336c;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin: 0 auto 20px;
        }
    </style>
</head>
<body>

<div class="login-container">

        <div class="icon-circle">
            <i class="fa-solid fa-lock"></i>
        </div>
        <h2 style="margin-top: 0; margin-bottom: 10px;">Trouble Logging In?</h2>
        <p style="color: #666; margin-bottom: 30px; font-size: 14px;">Enter your email to reset your password.</p>

        <?php if($error): ?>
            <div style="background: #ffe3e3; color: #c92a2a; padding: 12px; border-radius: 8px; font-size: 14px; margin-bottom: 20px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div style="background: #e6fcf5; color: #0ca678; padding: 12px; border-radius: 8px; font-size: 14px; margin-bottom: 20px;">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <input type="email" name="email" placeholder="Enter your registered email" required style="text-align: center;">
            
            <div style="margin-top: 20px; display: flex; gap: 10px; flex-direction: column;">
                
                <button type="submit" name="check_email" class="btn-large" style="width: 100%;">
                    Send Email Link
                </button>
                

            </div>
        </form>


    
    <div style="margin-top: 20px;">
        <a href="login.php" style="color: #666; font-size: 14px; text-decoration: none;"><i class="fa-solid fa-arrow-left"></i> Back to Login</a>
    </div>
</div>

</body>
</html>
