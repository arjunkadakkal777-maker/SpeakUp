<?php
include "config.php";

$token = $_GET['token'] ?? '';
$error = "";
$success = "";

if (!$token) {
    die("Invalid link.");
}

// Validate Token
$stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    echo "<div style='text-align:center; padding:50px; font-family:sans-serif;'>";
    echo "<h2 style='color:red;'>Link Invalid or Expired</h2>";
    echo "<p>The reset token could not be found in our database.</p>";
    echo "<p><strong>Debug:</strong> You provided token: " . htmlspecialchars($token) . "</p>";
    echo "<p><a href='forgot_password.php'>Request a new link</a></p>";
    echo "</div>";
    exit;
}

$user = $res->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pass = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    
    if ($pass !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($pass) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $hashed = password_hash($pass, PASSWORD_DEFAULT);
        
        $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, otp_code = NULL WHERE id = ?");
        $update->bind_param("si", $hashed, $user['id']);
        
        if ($update->execute()) {
            header("Location: login.php?resetted=1");
            exit;
        } else {
            $error = "Error updating password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - SpeakUp</title>
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
    </style>
</head>
<body>

<div class="login-container">
    <h2 style="margin-top: 0;">Set New Password</h2>
    
    <?php if($error): ?>
        <div style="background: #ffe3e3; color: #c92a2a; padding: 12px; border-radius: 8px; font-size: 14px; margin-bottom: 20px;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="password" name="password" placeholder="New Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
        <button type="submit" class="btn-large" style="width: 100%;">Change Password</button>
    </form>
</div>

</body>
</html>
