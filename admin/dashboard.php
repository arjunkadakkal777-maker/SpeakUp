<?php
include "../config.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/catalog_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="logo">
            <img src="../images/logo.png" alt="Logo" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;">
            SpeakUp
        </div>

        <div class="menu-category">Menu</div>
        <a href="#" class="menu-item active">
            <div class="menu-icon icon-pink"><i class="fa-solid fa-house"></i></div>
            Dashboard
        </a>
        <a href="manage_roles.php" class="menu-item">
            <div class="menu-icon icon-blue"><i class="fa-solid fa-user-tag"></i></div>
            Roles & Permissions
        </a>
        <a href="update_credentials.php" class="menu-item">
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
                <h1>System<br>Administration</h1>
                <p>Manage users, roles, and system configurations for the SpeakUp platform.</p>
                <button class="subscribe-btn" onclick="location.href='add_user.php'">Add New User</button>
            </div>
            <!-- <div class="hero-card">
                <div style="flex:1;">
                    <h3>System Health</h3>
                    <p style="margin:0; color:#666;">Database connected. All systems go.</p>
                </div>
                 <i class="fa-solid fa-server" style="font-size:60px; color: #ddd;"></i>
            </div> -->
        </div>

        <!-- GRID OF CARDS -->
        <div class="grid-container">
            
            <a href="add_user.php" class="card-item">
                <div class="card-preview" style="background:#fff9db; color:#e09f3e;">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                <div class="card-title">Add User</div>
                <div class="card-meta">
                    <span class="card-author">Registration</span>
                    <i class="fa-solid fa-arrow-right" style="font-size:12px;"></i>
                </div>
            </a>

            <a href="manage_roles.php" class="card-item">
                <div class="card-preview" style="background:#e7f5ff; color:#228be6;">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div class="card-title">Manage Roles</div>
                <div class="card-meta">
                    <span class="card-author">Security</span>
                    <i class="fa-solid fa-arrow-right" style="font-size:12px;"></i>
                </div>
            </a>

            <a href="update_credentials.php" class="card-item">
                <div class="card-preview" style="background:#f3f0ff; color:#7950f2;">
                    <i class="fa-solid fa-key"></i>
                </div>
                <div class="card-title">Credentials</div>
                <div class="card-meta">
                    <span class="card-author">Access</span>
                    <i class="fa-solid fa-arrow-right" style="font-size:12px;"></i>
                </div>
            </a>

        </div>

    </div>

</body>
</html>
