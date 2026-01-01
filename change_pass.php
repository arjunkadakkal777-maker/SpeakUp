<?php
session_start();
include "config.php";

/* --- SECURITY CHECK --- */
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$message = "";
$msg_type = "";

/* --- RUN ONLY WHEN FORM IS SUBMITTED --- */
if (isset($_POST['change_password'])) {

    $new = trim($_POST['new']);

    if ($new == "") {
        $message = "Password cannot be empty";
        $msg_type = "error";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $id = $_SESSION['user']['id'];

        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $hashed, $id);
        
        if ($stmt->execute()) {
            $message = "Password updated successfully";
            $msg_type = "success";
        } else {
            $message = "Error updating password";
            $msg_type = "error";
        }
    }
}

// Determine dashboard link based on role
$role = $_SESSION['user']['role'] ?? 'student';
$dash_link = ($role == 'admin') ? 'admin/dashboard.php' : "user/$role.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="css/catalog_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .feedback-msg {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .success { background: #d3f9d8; color: #2b8a3e; }
        .error { background: #ffe3e3; color: #c92a2a; }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="logo">
            <img src="images/logo.png" alt="Logo" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;">
            SpeakUp
        </div>

        <div class="menu-category">Menu</div>
        <a href="<?php echo $dash_link; ?>" class="menu-item">
            <div class="menu-icon icon-pink"><i class="fa-solid fa-house"></i></div>
            Dashboard
        </a>
        
        <?php if($role == 'admin'): ?>
            <a href="admin/manage_roles.php" class="menu-item">
                <div class="menu-icon icon-blue"><i class="fa-solid fa-user-tag"></i></div>
                Roles & Permissions
            </a>
            <a href="admin/update_credentials.php" class="menu-item">
                 <div class="menu-icon icon-purple"><i class="fa-solid fa-users-gear"></i></div>
                User Credentials
            </a>
        <?php endif; ?>

        <div class="menu-category">Settings</div>
        <a href="change_pass.php" class="menu-item active">
            <div class="menu-icon icon-green"><i class="fa-solid fa-lock"></i></div>
            Password
        </a>
        
        <div class="menu-category">Session</div>
        <a href="logout.php" class="menu-item">
            <div class="menu-icon" style="background:#eee; color:#333;"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
            Logout
        </a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        
        <!-- HERO SECTION -->
        <div class="hero-section">
            <div class="hero-text">
                <h1>Security Settings</h1>
                <p>Update your personal account password.</p>
            </div>
            <div class="hero-card" style="background: linear-gradient(135deg, #e9fac8 0%, #c0eb75 100%);">
                <div style="flex:1;">
                    <h3>Privacy First</h3>
                    <p style="margin:0; color:#5c940d;">Keep it secure</p>
                </div>
                 <i class="fa-solid fa-shield-cat" style="font-size:60px; color: #82c91e;"></i>
            </div>
        </div>

        <!-- CENTERED PASSWORD FORM CARD -->
        <div style="max-width: 500px; margin: 0 auto; background: white; padding: 40px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
            <div style="text-align: center; margin-bottom: 30px;">
                <div style="width: 50px; height: 50px; background: #ebfbee; color: #40c057; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 20px;">
                    <i class="fa-solid fa-key"></i>
                </div>
                <h3 style="margin: 0; font-size: 20px;">Change Password</h3>
                <p style="color: #666; font-size: 14px; margin-top: 5px;">Enter a new password for your account.</p>
            </div>

            <?php if (!empty($message)): ?>
                <div class="feedback-msg <?php echo $msg_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                
                <div style="margin-bottom: 25px;">
                    <label style="display:block; margin-bottom:8px; font-weight:600; font-size:13px;">New Password</label>
                    <input type="password" name="new" placeholder="••••••••" required 
                           style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; background: #fdfdfd; font-family: 'Inter', sans-serif; box-sizing: border-box;">
                </div>

                <button type="submit" name="change_password" class="btn-large" style="width: 100%; background: #000; color: white; border: none; padding: 14px; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Confirm Change
                </button>
            </form>
            
            <div style="text-align: center; margin-top: 20px;">
                 <a href="<?php echo $dash_link; ?>" style="color: #999; text-decoration: none; font-size: 13px;">Cancel & Go Back</a>
            </div>
        </div>

    </div>

</body>
</html>
