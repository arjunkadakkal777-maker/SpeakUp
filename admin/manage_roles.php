<?php
session_start();
include "../config.php";

/* Add Role */
if (isset($_POST['add_role'])) {
    $role = trim($_POST['role_name']);
    if (!empty($role)) {
        // Prevent SQL injection basic
        $stmt = $conn->prepare("INSERT INTO roles (role_name) VALUES (?)");
        $stmt->bind_param("s", $role);
        $stmt->execute();
    }
}

/* Delete Role */
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM roles WHERE id=$id");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Roles - Admin</title>
    <link rel="stylesheet" href="../css/catalog_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .role-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            position: relative;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid #f0f0f0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 160px;
        }
        .role-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            border-color: transparent;
        }
        .role-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 15px;
        }
        .role-name {
            font-size: 18px;
            font-weight: 700;
            color: #333;
        }
        .role-id {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
        .delete-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            color: #adb5bd;
            transition: color 0.2s;
        }
        .delete-btn:hover {
            color: #fa5252;
        }

        /* Add Card Styles */
        .add-card {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            cursor: default; /* It contains a form */
        }
        .add-card:hover {
            background: #fff;
            border-color: #228be6;
            transform: none;
            box-shadow: none;
        }
        .add-input {
            width: 80%; 
            padding: 10px; 
            border: 1px solid #ddd; 
            border-radius: 6px; 
            margin-bottom: 10px;
            text-align: center;
        }
        .add-btn {
            background: #228be6; 
            color: white; 
            border: none; 
            padding: 8px 20px; 
            border-radius: 6px; 
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
        }
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
        <a href="manage_roles.php" class="menu-item active">
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
                <h1>Role Manager</h1>
                <p>Define and manage the different user roles available in the system.</p>
            </div>
            <!-- <div class="hero-card" style="background: linear-gradient(135deg, #e7f5ff 0%, #d0ebff 100%);">
                <div style="flex:1;">
                    <h3>Access Control</h3>
                    <p style="margin:0; color:#1971c2;">Role-based security</p>
                </div>
                 <i class="fa-solid fa-user-shield" style="font-size:60px; color: #a5d8ff;"></i>
            </div> -->
        </div>

        <div style="margin-bottom: 20px; color: #666; font-size: 14px;">
            <strong><?php echo mysqli_num_rows(mysqli_query($conn, "SELECT * FROM roles")); ?></strong> roles configured.
        </div>

        <!-- ROLES GRID -->
        <div class="grid-container" style="grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 25px;">
            
            <!-- 1. The "Add New" Card (Form) -->
            <div class="role-card add-card">
                <form method="POST" style="width: 100%; text-align: center;">
                    <div style="color: #adb5bd; font-size: 30px; margin-bottom: 10px;">
                        <i class="fa-solid fa-circle-plus"></i>
                    </div>
                    <input type="text" name="role_name" class="add-input" placeholder="New Role Name" required>
                    <button name="add_role" type="submit" class="add-btn">Create</button>
                </form>
            </div>

            <!-- 2. Existing Roles Loop -->
            <?php
            $roles = mysqli_query($conn, "SELECT * FROM roles ORDER BY id DESC");
            while ($r = mysqli_fetch_assoc($roles)) {
                
                // Color coding based on ID for variety
                $bg_colors = ['#e7f5ff', '#fff0f6', '#f3f0ff', '#e6fcf5', '#fff9db'];
                $txt_colors = ['#1971c2', '#c2255c', '#6741d9', '#099268', '#f08c00'];
                $idx = $r['id'] % count($bg_colors);
                $bg = $bg_colors[$idx];
                $txt = $txt_colors[$idx];

            ?>
                <div class="role-card">
                    <a href="?delete=<?php echo $r['id']; ?>" class="delete-btn" onclick="return confirm('Delete this role? Users assigned to this role might lose access.');">
                        <i class="fa-solid fa-trash-can"></i>
                    </a>
                    
                    <div class="role-icon" style="background: <?php echo $bg; ?>; color: <?php echo $txt; ?>;">
                        <i class="fa-solid fa-user-tag"></i>
                    </div>
                    
                    <div>
                        <div class="role-name"><?php echo htmlspecialchars($r['role_name']); ?></div>
                        <div class="role-id">System ID: <?php echo $r['id']; ?></div>
                    </div>
                </div>
            <?php } ?>

        </div>
    </div>

</body>
</html>
