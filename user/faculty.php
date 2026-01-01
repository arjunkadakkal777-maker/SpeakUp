<?php include "../config.php";
$user_name = $_SESSION['username'] ?? 'Faculty Member';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard</title>
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
            <div class="menu-icon icon-blue"><i class="fa-solid fa-book"></i></div>
            Academic Issues
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
                <h1>Faculty<br>Dashboard</h1>
                <p>Welcome, <strong><?php echo $user_name; ?></strong>. View and address academic-related grievances from students.</p>
            </div>
            <div class="hero-card">
                <div style="flex:1;">
                    <h3>Course Issues</h3>
                    <p style="margin:0; color:#666;">No new updates.</p>
                </div>
                 <i class="fa-solid fa-chalkboard-user" style="font-size:60px; color: #ddd;"></i>
            </div>
        </div>

        <!-- GRID OF CARDS -->
        <div class="grid-container">
            
            <a href="#" class="card-item">
                <div class="card-preview" style="background:#fff0f6; color:#a61e4d;">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <div class="card-title">Student Queries</div>
                <div class="card-meta">
                    <span class="card-author">Academic</span>
                    <i class="fa-solid fa-arrow-right" style="font-size:12px;"></i>
                </div>
            </a>

        </div>

    </div>

</body>
</html>
