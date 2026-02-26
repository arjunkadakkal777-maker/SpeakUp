<?php
include "../config.php";
// Ensure session is started if not already (config usually does, but good practice if standalone)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_name = $_SESSION['user']['username'] ?? 'Principal';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Principal Dashboard</title>
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
            <div class="menu-icon icon-blue"><i class="fa-solid fa-chart-pie"></i></div>
            Analytics
        </a>
        <a href="#" class="menu-item">
            <div class="menu-icon icon-purple"><i class="fa-solid fa-file-contract"></i></div>
            All Reports
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
        <div class="hero-section" style="margin-bottom: 30px;">
            <div class="hero-text">
                <h1>Principal Dashboard</h1>
                <p>Welcome, <strong><?php echo htmlspecialchars($user_name); ?></strong>. Overview of critical issues requiring attention.</p>
            </div>
            <div style="text-align: right; color: #999; font-size: 14px;">
                <?php echo date("l, F j, Y"); ?>
            </div>
        </div>

        <!-- ESCALATED GRIEVANCES SECTION -->
        <div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                <h2 style="font-size: 20px; font-weight: 600; margin: 0; color: #e03131;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Escalated Grievances
                </h2>
            </div>
            
            <div style="background: white; border-radius: 16px; border: 1px solid #eee; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.02);">
                <?php
                // Fetch escalated grievances
                $esc_q = $conn->query("SELECT g.id, g.title, g.category, g.incident_date, g.status, u.username as student_name, u.email as student_email 
                                       FROM grievances g 
                                       LEFT JOIN users u ON g.student_id = u.id 
                                       WHERE g.status = 'Escalated' 
                                       ORDER BY g.incident_date DESC");
                
                if ($esc_q && $esc_q->num_rows > 0):
                ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #fff5f5; border-bottom: 1px solid #ffc9c9;">
                            <th style="text-align: left; padding: 16px 24px; font-size: 13px; font-weight: 600; color: #e03131;">ID</th>
                            <th style="text-align: left; padding: 16px 24px; font-size: 13px; font-weight: 600; color: #e03131;">Student</th>
                            <th style="text-align: left; padding: 16px 24px; font-size: 13px; font-weight: 600; color: #e03131;">Issue</th>
                            <th style="text-align: left; padding: 16px 24px; font-size: 13px; font-weight: 600; color: #e03131;">Date</th>
                            <th style="text-align: right; padding: 16px 24px; font-size: 13px; font-weight: 600; color: #e03131;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $esc_q->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #f0f0f0;">
                            <td style="padding: 20px 24px; font-weight: 600;">#<?php echo $row['id']; ?></td>
                            <td style="padding: 20px 24px;">
                                <div style="font-weight: 500;"><?php echo htmlspecialchars($row['student_name'] ?? 'Unknown'); ?></div>
                                <div style="font-size: 12px; color: #888;"><?php echo htmlspecialchars($row['student_email'] ?? ''); ?></div>
                            </td>
                            <td style="padding: 20px 24px;">
                                <div style="font-weight: 500; margin-bottom: 4px;"><?php echo htmlspecialchars($row['title']); ?></div>
                                <span style="font-size: 11px; background: #eee; padding: 2px 8px; border-radius: 4px; color: #555;">
                                    <?php echo htmlspecialchars($row['category']); ?>
                                </span>
                            </td>
                            <td style="padding: 20px 24px; color: #666; font-size: 14px;"><?php echo date("M j, Y", strtotime($row['incident_date'])); ?></td>
                            <td style="padding: 20px 24px; text-align: right;">
                                <a href="grievance_details.php?id=<?php echo $row['id']; ?>" style="text-decoration: none; background: #fff; border: 1px solid #ddd; padding: 8px 16px; border-radius: 8px; color: #333; font-size: 13px; font-weight: 500; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s;">
                                    View Details <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 60px;">
                        <div style="width: 60px; height: 60px; background: #f8f9fa; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: #adb5bd; font-size: 24px;">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <h3 style="font-size: 16px; margin: 0 0 8px 0; color: #333;">No Escalated Issues</h3>
                        <p style="margin: 0; color: #999; font-size: 14px;">There are currently no grievances escalated to your level.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php include 'logout_modal.php'; ?>
</body>
</html>
