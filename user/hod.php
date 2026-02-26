<?php include "../config.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session Refresh: If department is missing, re-fetch user data
if (empty($_SESSION['user']['department'])) {
    $uid = $_SESSION['user']['id'];
    $u_q = $conn->prepare("SELECT * FROM users WHERE id=?");
    $u_q->bind_param("i", $uid);
    $u_q->execute();
    $u_res = $u_q->get_result();
    if ($u_row = $u_res->fetch_assoc()) {
        $_SESSION['user'] = $u_row;
    }
}

$user_name = $_SESSION['user']['username'] ?? 'Head of Department';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOD Dashboard</title>
    <link rel="stylesheet" href="../css/catalog_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .hero-stats {
            display: flex;
            gap: 20px;
        }
        .stat-item {
            background: rgba(255,255,255,0.2);
            padding: 15px 25px;
            border-radius: 12px;
            text-align: center;
            backdrop-filter: blur(5px);
        }
        .stat-item span {
            display: block;
            font-size: 24px;
            font-weight: 700;
        }
        .stat-item small {
            font-size: 12px;
            opacity: 0.9;
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
        <a href="#" class="menu-item active">
            <div class="menu-icon icon-pink"><i class="fa-solid fa-house"></i></div>
            Dashboard
        </a>


        <div class="menu-category">Settings</div>
        <a href="../change_pass.php" class="menu-item">
            <div class="menu-icon icon-green"><i class="fa-solid fa-lock"></i></div>
            Password
        </a>
        
        <div class="menu-category">Session</div>
        <a href="#" class="menu-item" onclick="confirmLogout(event)">
            <div class="menu-icon" style="background:#eee; color:#333;"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
            Logout
        </a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        
        <?php include "notifications_partial.php"; ?>

        <!-- HERO SECTION -->
        <div class="hero-section">
            <div class="hero-text">
                <h1>HOD<br>Dashboard</h1>
                <p>Welcome, <strong><?php echo htmlspecialchars($user_name); ?></strong>. Managing Department: <strong><?php echo htmlspecialchars($_SESSION['user']['department'] ?? 'General'); ?></strong></p>
                <div style="margin-top: 10px; font-size: 13px; opacity: 0.8;">
                    <i class="fa-solid fa-circle-info"></i> Viewing grievances filed by students of your department.
                </div>
            </div>
            <div class="hero-stats">
                <?php
                // Fetch Stats for this Department
                $dept = $_SESSION['user']['department'] ?? '';
                
                // Total
                $t_q = $conn->prepare("SELECT COUNT(*) FROM grievances WHERE branch = ?");
                $t_q->bind_param("s", $dept);
                $t_q->execute();
                $total = $t_q->get_result()->fetch_row()[0] ?? 0;

                // Resolved
                $r_q = $conn->prepare("SELECT COUNT(*) FROM grievances WHERE branch = ? AND status='Resolved'");
                $r_q->bind_param("s", $dept);
                $r_q->execute();
                $resolved = $r_q->get_result()->fetch_row()[0] ?? 0;

                // Escalated (Attention needed)
                $e_q = $conn->prepare("SELECT COUNT(*) FROM grievances WHERE branch = ? AND status='Escalated'");
                $e_q->bind_param("s", $dept);
                $e_q->execute();
                $escalated = $e_q->get_result()->fetch_row()[0] ?? 0;
                ?>
                <div class="stat-item">
                    <span><?php echo $total; ?></span>
                    <small>Total</small>
                </div>
                <div class="stat-item">
                    <span><?php echo $resolved; ?></span>
                    <small>Resolved</small>
                </div>
                <div class="stat-item" style="background: rgba(255, 200, 200, 0.3); color: #fff;">
                    <span><?php echo $escalated; ?></span>
                    <small>Escalated</small>
                </div>
            </div>
        </div>

        <!-- GRIEVANCE LIST -->
        <h2 style="margin-top: 40px; margin-bottom: 20px; font-size: 18px; color: #333;">Department Grievances</h2>
        
        <div style="background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; text-align: left; border-bottom: 2px solid #eee;">
                        <th style="padding: 15px; color: #666; font-weight: 600; font-size: 13px;">ID & SUBJECT</th>
                        <th style="padding: 15px; color: #666; font-weight: 600; font-size: 13px;">STUDENT</th>
                        <th style="padding: 15px; color: #666; font-weight: 600; font-size: 13px;">CATEGORY</th>
                        <th style="padding: 15px; color: #666; font-weight: 600; font-size: 13px;">DATE</th>
                        <th style="padding: 15px; color: #666; font-weight: 600; font-size: 13px;">STATUS</th>
                        <th style="padding: 15px; color: #666; font-weight: 600; font-size: 13px; text-align: right;">QUICK OPTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch Grievances (Assuming anonymous column is 'is_anonymous' and student table users)
                    // We need to JOIN to get student details
                    $sql = "SELECT g.*, u.username as student_name, u.email as student_email, f.username as faculty_name, f.email as faculty_email 
                              FROM grievances g 
                              LEFT JOIN users u ON g.student_id = u.id 
                              LEFT JOIN users f ON g.faculty_id = f.id
                              WHERE g.branch = ? 
                              ORDER BY g.created_at DESC";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $dept);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                             // Status Styling
                            $s_color = '#444'; $s_bg = '#eee';
                            if ($row['status'] == 'Open') { $s_color = '#c92a2a'; $s_bg = '#ffe3e3'; }
                            if ($row['status'] == 'In Progress') { $s_color = '#f08c00'; $s_bg = '#fff3bf'; }
                            if ($row['status'] == 'Resolved') { $s_color = '#2b8a3e'; $s_bg = '#d3f9d8'; }
                            if ($row['status'] == 'Escalated') { $s_color = '#e03131'; $s_bg = '#ffc9c9'; }
                            
                             // Anonymity Check
                            $display_name = htmlspecialchars($row['student_name']);
                            if (isset($row['is_anonymous']) && $row['is_anonymous'] == 1) {
                                $display_name = "Anonymous Student";
                            }

                            echo "<tr style='border-bottom: 1px solid #eee;'>";
                            
                            // ID & Title
                            echo "<td style='padding: 15px;'>
                                    <div style='font-weight: 600; color: #333;'>#" . $row['id'] . "</div>
                                    <div style='font-size: 13px; color: #666;'>" . htmlspecialchars(substr($row['title'], 0, 30)) . "</div>
                                  </td>";
                            
                            // Student
                            echo "<td style='padding: 15px; font-size: 14px;'>" . $display_name . "</td>";
                            
                            // Category
                            echo "<td style='padding: 15px; font-size: 14px;'>" . htmlspecialchars($row['category']) . "</td>";
                            
                            // Date
                            echo "<td style='padding: 15px; font-size: 13px; color: #666;'>" . date("M d, Y", strtotime($row['created_at'])) . "</td>";
                            
                            // Status
                            echo "<td style='padding: 15px;'>
                                    <span style='background: $s_bg; color: $s_color; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;'>
                                        " . $row['status'] . "
                                    </span>
                                  </td>";
                            
                            // Options
                            echo "<td style='padding: 15px; text-align: right;'>
                                    <div style='display: flex; gap: 8px; justify-content: flex-end;'>";
                                    
                            // View Details
                            echo "<a href='grievance_details.php?id=" . $row['id'] . "' title='View Details / Send Feedback' style='background: #228be6; color: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 5px;'>
                                    <i class='fa-solid fa-eye'></i> Details
                                  </a>";
                            
                             // Contact Faculty (if assigned)
                            if (!empty($row['faculty_email'])) {
                                echo "<a href='mailto:" . $row['faculty_email'] . "?subject=Referencing Grievance #" . $row['id'] . "' title='Contact Assigned Faculty' style='background: white; border: 1px solid #ddd; color: #444; padding: 6px 10px; border-radius: 6px; font-size: 12px; text-decoration: none;'>
                                        <i class='fa-solid fa-envelope'></i>
                                      </a>";
                            }
                            
                            echo "  </div>
                                  </td>";
                            
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='padding: 40px; text-align: center; color: #999; font-style: italic;'>No grievances found for the department: " . htmlspecialchars($dept) . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>

<?php include 'logout_modal.php'; ?>
</body>
</html>
