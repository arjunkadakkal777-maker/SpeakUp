<?php include "../config.php";
$user_name = $_SESSION['username'] ?? 'Warden';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warden Dashboard</title>
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
        <a href="#" class="menu-item">
            <div class="menu-icon icon-orange"><i class="fa-solid fa-bed"></i></div>
            Hostel Issues
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
                <h1>Warden<br>Dashboard</h1>
                <p>Welcome, <strong><?php echo $user_name; ?></strong>. Manage hostel facilities and resident grievances.</p>
            </div>
            <div class="hero-card">
                <div style="flex:1;">
                    <h3>Hostel Status</h3>
                    <p style="margin:0; color:#666;">3 Urgent Issues reported.</p>
                </div>
                 <i class="fa-solid fa-building" style="font-size:60px; color: #ddd;"></i>
            </div>
        </div>

        <!-- GRID OF CARDS -->
        <div class="grid-container">
            
            <a href="#" class="card-item">
                <div class="card-preview" style="background:#fff9db; color:#f08c00;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div class="card-title">Urgent Griefs</div>
                <div class="card-meta">
                    <span class="card-author">Priority: High</span>
                    <i class="fa-solid fa-arrow-right" style="font-size:12px;"></i>
                </div>
            </a>

            <a href="#" class="card-item">
                <div class="card-preview" style="background:#e6fcf5; color:#0ca678;">
                    <i class="fa-solid fa-check"></i>
                </div>
                <div class="card-title">Resolved Issues</div>
                <div class="card-meta">
                    <span class="card-author">History</span>
                    <i class="fa-solid fa-arrow-right" style="font-size:12px;"></i>
                </div>
            </a>

        </div>

    </div>

</body>
</html>
