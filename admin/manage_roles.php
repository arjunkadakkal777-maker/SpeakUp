<?php
session_start();
include "../config.php";

/* 1. Add Role */
if (isset($_POST['add_role'])) {
    $role = trim($_POST['role_name']);
    if (!empty($role)) {
        $stmt = $conn->prepare("INSERT INTO roles (role_name) VALUES (?)");
        $stmt->bind_param("s", $role);
        $stmt->execute();
    }
}

/* 2. Delete Role */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM roles WHERE id=$id");
    header("Location: manage_roles.php"); // Refresh
    exit;
}

/* 3. Assign Role to User */
if (isset($_POST['assign_role'])) {
    $uid = intval($_POST['faculty_id']);
    $rid = intval($_POST['assign_role_id']);
    
    // Check duplication
    $check = $conn->query("SELECT id FROM user_roles WHERE user_id=$uid AND role_id=$rid");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO user_roles (user_id, role_id) VALUES ($uid, $rid)");
    }
}

/* 4. Remove Assignment */
if (isset($_GET['remove_assignment'])) {
    $mid = intval($_GET['remove_assignment']);
    $conn->query("DELETE FROM user_roles WHERE id=$mid");
    header("Location: manage_roles.php");
    exit;
}

/* 5. Fetch Active Roles for display */
$roles_q = $conn->query("SELECT * FROM roles ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Role Manager - Admin</title>
    <link rel="stylesheet" href="../css/catalog_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Modern Clean Layout */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .page-title h1 { margin: 0; font-size: 24px; }
        .page-title p { margin: 5px 0 0; color: #666; font-size: 14px; }
        
        .layout-grid {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 30px;
            align-items: start;
        }

        /* Card Styles */
        .section-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            border: 1px solid #f0f0f0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Role Item */
        .role-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 15px;
            border-bottom: 1px solid #f1f3f5;
            transition: background 0.2s;
        }
        .role-item:last-child { border-bottom: none; }
        .role-item:hover { background: #f8f9fa; }
        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            font-size: 14px;
        }
        .role-dot { width: 8px; height: 8px; border-radius: 50%; }

        /* Faculty Item */
        .faculty-row {
            padding: 15px;
            border-bottom: 1px solid #f1f3f5;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .faculty-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .faculty-avatar {
            width: 40px; height: 40px;
            background: #e7f5ff;
            color: #1c7ed6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        /* Form Elements */
        .simple-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .simple-btn {
            background: #228be6;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            width: 100%;
        }
        .tag {
            background: #f1f3f5;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            color: #495057;
            margin-right: 5px;
        }

        /* Modal Like overlay for adding/assigning (Simplified to inline for now) */
    </style>
    <script>
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "../logout.php";
            } else {
                history.pushState(null, null, location.href);
            }
        };
    </script>
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
        <a href="import_faculty.php" class="menu-item">
            <div class="menu-icon icon-blue"><i class="fa-solid fa-file-csv"></i></div>
            Import Faculty
        </a>
        <a href="../change_pass.php" class="menu-item">
            <div class="menu-icon icon-green"><i class="fa-solid fa-lock"></i></div>
            Password
        </a>
        
        <div class="menu-category">Session</div>
        <a href="../logout.php" class="menu-item" onclick="return confirm('Are you sure you want to logout?');">
            <div class="menu-icon" style="background:#eee; color:#333;"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
            Logout
        </a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        
        <div class="page-header">
            <div class="page-title">
                <h1>Role Manager</h1>
                <p>Configure system roles and assign faculty members.</p>
            </div>
            <!-- <button class="btn-large" style="width: auto; padding: 10px 20px;"><i class="fa-solid fa-plus"></i> Assign Role</button> -->
        </div>

        <div class="layout-grid">
            
            <!-- LEFT COLUMN: Roles Config -->
            <div>
                <!-- Add Role Card -->
                <div class="section-card">
                    <div class="section-title">
                        Create New Role
                    </div>
                    <form method="POST">
                        <input type="text" name="role_name" class="simple-input" placeholder="Role Name (e.g. Warden)" required>
                        <button type="submit" name="add_role" class="simple-btn">Create Role</button>
                    </form>
                </div>

                <!-- Roles List -->
                <div class="section-card">
                    <div class="section-title">
                        Active Roles
                        <span style="font-size: 12px; color: #999; font-weight: 400;"><?php echo mysqli_num_rows($roles_q); ?> roles</span>
                    </div>
                    <?php 
                    mysqli_data_seek($roles_q, 0); // Reset pointer
                    while($r = mysqli_fetch_assoc($roles_q)): 
                        $colors = ['#228be6', '#fa5252', '#40c057', '#e64980', '#7950f2'];
                        $color = $colors[$r['id'] % 5];
                    ?>
                    <div class="role-item">
                        <div class="role-badge">
                            <span class="role-dot" style="background: <?php echo $color; ?>;"></span>
                            <?php echo htmlspecialchars($r['role_name']); ?>
                        </div>
                        <a href="?delete=<?php echo $r['id']; ?>" onclick="return confirm('Remove this role?');" style="color: #adb5bd; font-size: 13px;">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- RIGHT COLUMN: Faculty Assignments -->
            <div class="section-card" style="min-height: 500px;">
                <div class="section-title">
                    Faculty Role Assignments
                </div>

                <!-- Assignment Form -->
                <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px dashed #dee2e6;">
                    <form method="POST" style="display: flex; gap: 10px; align-items: center;">
                        <select name="faculty_id" class="simple-input" style="margin:0; width: 50%;" required>
                            <option value="">Select Faculty User</option>
                            <?php 
                            // Fetch users who are likely staff (exclude students for cleaner list if possible, or show all but Student)
                            // Assuming 'student' role exists in users table.
                            $users = $conn->query("SELECT id, username, email FROM users WHERE role != 'student' ORDER BY username");
                            while($u = $users->fetch_assoc()) {
                                $display = $u['username'];
                                if (!empty($u['email'])) {
                                    $display .= " (" . $u['email'] . ")";
                                }
                                echo "<option value='".$u['id']."'>".$display."</option>";
                            }
                            ?>
                        </select>
                        <select name="assign_role_id" class="simple-input" style="margin:0; width: 35%;" required>
                            <option value="">Select Role</option>
                            <?php 
                            mysqli_data_seek($roles_q, 0);
                            while($r = mysqli_fetch_assoc($roles_q)) {
                                echo "<option value='".$r['id']."'>".$r['role_name']."</option>";
                            }
                            ?>
                        </select>
                        <button type="submit" name="assign_role" class="simple-btn" style="width: auto;">Assign</button>
                    </form>
                </div>

                <!-- Assignments List Table -->
                <div style="max-height: 400px; overflow-y: auto;">
                    <?php
                    // Fetch users with their assigned extra roles
                    // We need a table `user_roles` linking users to roles.
                    // IF WE DONT HAVE IT, WE NEED TO CREATE IT.
                    // Let's assume for this specific task, we might need to create a many-to-many table.
                    // OR, check if `roles` table is just definitions.
                    // The prompt implies "Assign faculties to one or multiple roles".
                    
                    // CHECK DB Structure: `user_roles` (user_id, role_id)
                    $chk = $conn->query("SHOW TABLES LIKE 'user_roles'");
                    if($chk->num_rows == 0) {
                        $conn->query("CREATE TABLE user_roles (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, role_id INT, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE)");
                    }
                    
                    $mappings = $conn->query("
                        SELECT ur.id as map_id, u.username, u.email, r.role_name 
                        FROM user_roles ur 
                        JOIN users u ON ur.user_id = u.id 
                        JOIN roles r ON ur.role_id = r.id
                        ORDER BY u.username
                    ");

                    if ($mappings->num_rows > 0):
                        while($m = $mappings->fetch_assoc()):
                    ?>
                    <div class="faculty-row">
                        <div class="faculty-info">
                            <div class="faculty-avatar">
                                <?php echo strtoupper(substr($m['username'], 0, 1)); ?>
                            </div>
                            <div>
                                <div style="font-weight: 600; color: #333;"><?php echo htmlspecialchars($m['username']); ?></div>
                                <div style="font-size: 12px; color: #888;"><?php echo htmlspecialchars($m['email']); ?></div>
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <span class="tag"><?php echo htmlspecialchars($m['role_name']); ?></span>
                            <a href="?remove_assignment=<?php echo $m['map_id']; ?>" style="color: #fa5252; font-size: 12px;" onclick="return confirm('Remove assignment?');"><i class="fa-solid fa-xmark"></i></a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                    <?php else: ?>
                        <div style="text-align:center; padding: 40px; color: #999;">
                            <i class="fa-solid fa-user-xmark" style="font-size: 30px; margin-bottom: 10px;"></i><br>
                            No roles assigned yet.
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>

    </div>

</body>
</html>
