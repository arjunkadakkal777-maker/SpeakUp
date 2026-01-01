<?php include "../config.php";
$user_name = $_SESSION['username'] ?? 'Committee Member';
$role = "Committee";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Committee Dashboard</title>
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
            <div class="menu-icon icon-orange"><i class="fa-solid fa-layer-group"></i></div>
            All Grievances
        </a>
        <a href="#" class="menu-item">
            <div class="menu-icon icon-blue"><i class="fa-solid fa-chart-pie"></i></div>
            Reports
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
                <h1>Committee<br>Dashboard</h1>
                <p>Welcome, <strong><?php echo $user_name; ?></strong>. Oversee campus grievances and ensure timely resolutions.</p>
                <button class="subscribe-btn">View Reports</button>
            </div>
            <div class="hero-card">
                <div style="flex:1;">
                    <h3>Pending Cases</h3>
                    <p style="margin:0; color:#666;">There are 12 open grievances.</p>
                </div>
                 <i class="fa-solid fa-scale-balanced" style="font-size:60px; color: #ddd;"></i>
            </div>
        </div>

        <!-- FILTERS -->
        <div class="filter-bar">
            <div class="filter-chip">Status <i class="fa-solid fa-chevron-down" style="font-size:10px;"></i></div>
            <div class="filter-chip">Priority <i class="fa-solid fa-chevron-down" style="font-size:10px;"></i></div>
            <div class="filter-chip">Date</div>
        </div>

        <!-- GRID OF CARDS -->
        <div class="grid-container">
            
            <a href="#" class="card-item">
                <div class="card-preview" style="background:#fff0f6; color:#be4bdb;">
                    <i class="fa-solid fa-inbox"></i>
                </div>
                <div class="card-title">New Grievances</div>
                <div class="card-meta">
                    <span class="card-author">Inbox</span>
                    <span style="font-weight:600;">5</span>
                </div>
            </a>

            <a href="#" class="card-item">
                <div class="card-preview" style="background:#e7f5ff; color:#228be6;">
                    <i class="fa-solid fa-list-check"></i>
                </div>
                <div class="card-title">In Progress</div>
                <div class="card-meta">
                    <span class="card-author">Tracking</span>
                    <span style="font-weight:600;">3</span>
                </div>
            </a>

            <a href="#" class="card-item">
                <div class="card-preview" style="background:#ebfbee; color:#40c057;">
                    <i class="fa-solid fa-check-double"></i>
                </div>
                <div class="card-title">Resolved</div>
                <div class="card-meta">
                    <span class="card-author">Archive</span>
                    <span style="font-weight:600;">42</span>
                </div>
            </a>

        </div>

    </div>

</body>
</html>
