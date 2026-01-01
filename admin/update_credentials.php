<?php
session_start();
include "../config.php";

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

$message = "";
$msg_type = ""; // success or error

if (isset($_POST['update_pass'])) {
    $uid = intval($_POST['user_id']);
    $password = trim($_POST['password']);
    
    if (!empty($uid) && !empty($password)) {
        $newpass = password_hash($password, PASSWORD_DEFAULT);

        // Using prepared statement for safety
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $newpass, $uid);
        
        if ($stmt->execute()) {
            $message = "Password updated successfully!";
            $msg_type = "success";
        } else {
            $message = "Error updating password: " . $stmt->error;
            $msg_type = "error";
        }

        $stmt->close();
    } else {
        $message = "Please select a user and enter a password.";
        $msg_type = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credentials - Admin</title>
    <link rel="stylesheet" href="../css/catalog_style.css">
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
            <img src="../images/logo.png" alt="Logo" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;">
            SpeakUp
        </div>

        <div class="menu-category">Menu</div>
        <a href="dashboard.php" class="menu-item">
            <div class="menu-icon icon-pink"><i class="fa-solid fa-house"></i></div>
            Dashboard
        </a>
        <a href="manage_roles.php" class="menu-item">
            <div class="menu-icon icon-blue"><i class="fa-solid fa-user-tag"></i></div>
            Roles & Permissions
        </a>
        <a href="update_credentials.php" class="menu-item active">
             <div class="menu-icon icon-purple"><i class="fa-solid fa-users-gear"></i></div>
            User Credentials
        </a>

        <div class="menu-category">Settings</div>
        <a href="../change_pass.php" class="menu-item">
            <div class="menu-icon icon-green"><i class="fa-solid fa-lock"></i></div>
            Password
        </a>
        
        <div class="menu-category">Session</div>
        <a href="../logout.php" class="menu-item">
            <div class="menu-icon" style="background:#eee; color:#333;"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
            Logout
        </a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        
        <!-- HERO SECTION -->
        <div class="hero-section">
            <div class="hero-text">
                <h1>User Credentials</h1>
                <p>Reset passwords and manage access credentials for registered users.</p>
            </div>
           
        </div>

        <!-- CENTERED PASSWORD FORM CARD -->
        <div style="max-width: 500px; margin: 0 auto; background: white; padding: 40px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
            <div style="text-align: center; margin-bottom: 30px;">
                <div style="width: 50px; height: 50px; background: #f3f0ff; color: #7950f2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 20px;">
                    <i class="fa-solid fa-lock-open"></i>
                </div>
                <h3 style="margin: 0; font-size: 20px;">Reset Password</h3>
                <p style="color: #666; font-size: 14px; margin-top: 5px;">Select a user to update their login credentials.</p>
            </div>

            <?php if (!empty($message)): ?>
                <div class="feedback-msg <?php echo $msg_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:8px; font-weight:600; font-size:13px;">Select User</label>
                    <select name="user_id" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; background: #fdfdfd; font-family: 'Inter', sans-serif;">
                        <option value="">-- Choose Account --</option>
                        <?php
                        $users = mysqli_query($conn, "SELECT id, username FROM users");
                        while ($u = mysqli_fetch_assoc($users)) {
                            echo "<option value='{$u['id']}'>{$u['username']} (ID: {$u['id']})</option>";
                        }
                        ?>
                    </select>
                </div>

                <div style="margin-bottom: 25px;">
                    <label style="display:block; margin-bottom:8px; font-weight:600; font-size:13px;">New Password</label>
                    <input type="password" name="password" placeholder="Enter new password" required 
                           style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; background: #fdfdfd; font-family: 'Inter', sans-serif; box-sizing: border-box;">
                </div>

                <button type="submit" name="update_pass" class="btn-large" style="width: 100%; background: #7950f2; color: white; border: none; padding: 14px; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Update Credentials
                </button>
            </form>
        </div>

    </div>

</body>
</html>
