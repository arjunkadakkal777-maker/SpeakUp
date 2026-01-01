<?php
include("../config.php");

// Make sure session is started
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

// Get the logged-in user's id from session
$user_id = $_SESSION['user']['id'];

// Fetch user data from database
$q = $conn->prepare("SELECT * FROM users WHERE id = ?");
$q->bind_param("i", $user_id);
$q->execute();
$result = $q->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
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
        <a href="student.php?page=dashboard" class="menu-item">
            <div class="menu-icon icon-pink"><i class="fa-solid fa-house"></i></div>
            Dashboard
        </a>
        <a href="student.php?page=grievance" class="menu-item">
            <div class="menu-icon icon-orange"><i class="fa-solid fa-plus"></i></div>
            New Grievance
        </a>
        <a href="#" class="menu-item">
            <div class="menu-icon icon-blue"><i class="fa-solid fa-list"></i></div>
            My History
        </a>

        <div class="menu-category">Settings</div>
        <a href="view_profile.php" class="menu-item active">
            <div class="menu-icon icon-purple"><i class="fa-solid fa-user"></i></div>
            Profile
        </a>
        <a href="student.php?page=password" class="menu-item">
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
                <h1>My Profile</h1>
                <p>View your registered account details and role status.</p>
            </div>
            <div class="hero-card" style="background: linear-gradient(135deg, #fdfbfb 0%, #f4f7f6 100%);">
                <div style="flex:1;">
                    <h3>Account Status</h3>
                    <p style="margin:0; color:#0ca678; font-weight:600;"><i class="fa-solid fa-check-circle"></i> Active</p>
                </div>
                 <i class="fa-solid fa-id-badge" style="font-size:60px; color: #ddd;"></i>
            </div>
        </div>

        <!-- PROFILE DETAILS GRID -->
        <div class="grid-container" style="grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));">
            
            <!-- User Info Card -->
            <div class="card-item" style="cursor: default;">
                <div class="card-preview" style="background:#f3f0ff; color:#7950f2; height: 120px;">
                    <i class="fa-solid fa-user-circle" style="font-size: 60px;"></i>
                </div>
                <div style="padding: 10px 0;">
                    <div style="margin-bottom: 20px;">
                        <label style="font-size:12px; color:#999; text-transform: uppercase; font-weight:bold; display:block; margin-bottom:4px;">Full Name</label>
                        <div style="font-size:16px; font-weight:600;"><?php echo htmlspecialchars($user['username']); ?></div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="font-size:12px; color:#999; text-transform: uppercase; font-weight:bold; display:block; margin-bottom:4px;">User ID</label>
                        <div style="font-size:16px; font-weight:600;">#<?php echo $user['id']; ?></div>
                    </div>

                    <div style="margin-bottom: 0;">
                        <label style="font-size:12px; color:#999; text-transform: uppercase; font-weight:bold; display:block; margin-bottom:4px;">Role</label>
                        <span style="background: #e7f5ff; color: #1971c2; padding: 4px 10px; border-radius: 12px; font-size: 13px; font-weight: 600;">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                    </div>
                </div>
            </div>



        </div>

    </div>

</body>
</html>