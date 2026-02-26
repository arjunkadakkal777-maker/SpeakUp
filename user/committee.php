<?php
include "../config.php";


if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'committee') {
    header("Location: ../login.php");
    exit;
}

$user = $_SESSION['user'];
$user_name = $user['username'];

// Check if email column exists
$check_email = $conn->query("SHOW COLUMNS FROM users LIKE 'email'");
$has_email = ($check_email->num_rows > 0);
$email_select = $has_email ? "u.email as student_email," : "NULL as student_email,";

// Fetch Stats (Global)
$stats = [
    'total' => 0,
    'pending' => 0,
    'resolved' => 0
];

$stat_q = $conn->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status != 'Resolved' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) as resolved
    FROM grievances");

if ($stat_q) {
    $stats = $stat_q->fetch_assoc();
}

// Fetch All Grievances for Review
$grievances = [];
$g_q = $conn->query("SELECT g.id, g.title, g.incident_date, g.created_at, g.status, g.category, g.location, $email_select u.username as student_name 
                     FROM grievances g 
                     LEFT JOIN users u ON g.student_id = u.id 
                     ORDER BY g.status ASC, g.incident_date DESC");

if ($g_q) {
    while ($row = $g_q->fetch_assoc()) {
        $grievances[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Committee Dashboard</title>
    <link rel="stylesheet" href="../css/catalog_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .stats-row {
            display: flex;
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            flex: 1;
            background: #fff;
            padding: 24px;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        }
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin: 0;
            line-height: 1;
        }
        .stat-label {
            color: var(--text-secondary);
            font-size: 13px;
            margin-top: 4px;
        }
        
        .grievance-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }
        .grievance-table th {
            text-align: left;
            padding: 16px 24px;
            background: #f9f9f9;
            font-size: 13px;
            font-weight: 600;
            color: #666;
            border-bottom: 1px solid var(--border-color);
        }
        .grievance-table td {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }
        .grievance-table tr:last-child td {
            border-bottom: none;
        }
        .grievance-table tr:hover {
            background-color: #fcfcfc;
        }
        
        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
        }
        .status-open { background: #ffe3e3; color: #c92a2a; }
        .status-progress { background: #fff3bf; color: #f08c00; }
        .status-resolved { background: #d3f9d8; color: #2b8a3e; }
        .status-escalated { background: #ffc9c9; color: #e03131; border: 1px solid #ffa8a8; }

        .action-btn {
            text-decoration: none;
            color: #000;
            background: #f0f0f0;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            transition: background 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .action-btn:hover {
            background: #e0e0e0;
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
        <a href="committee.php" class="menu-item active">
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
        <div class="hero-section" style="margin-bottom: 30px;">
            <div class="hero-text">
                <h1>Committee Dashboard</h1>
                <p>Welcome, <strong><?php echo htmlspecialchars($user_name); ?></strong>. Review and oversee all campus grievances.</p>
            </div>
            <div style="text-align: right; color: #999; font-size: 14px;">
                <?php echo date("l, F j, Y"); ?>
            </div>
        </div>

        <!-- STATS ROW -->
        <div class="stats-row">
            <div class="stat-card">
                <div>
                    <div class="stat-value"><?php echo $stats['total'] ?? 0; ?></div>
                    <div class="stat-label">Total Grievances</div>
                </div>
                <div class="menu-icon icon-blue" style="width: 48px; height: 48px; font-size: 20px;"><i class="fa-solid fa-list"></i></div>
            </div>
            <div class="stat-card">
                <div>
                    <div class="stat-value"><?php echo $stats['pending'] ?? 0; ?></div>
                    <div class="stat-label">Pending Reviews</div>
                </div>
                <div class="menu-icon icon-orange" style="width: 48px; height: 48px; font-size: 20px;"><i class="fa-solid fa-magnifying-glass"></i></div>
            </div>
            <div class="stat-card">
                <div>
                    <div class="stat-value"><?php echo $stats['resolved'] ?? 0; ?></div>
                    <div class="stat-label">Resolved</div>
                </div>
                <div class="menu-icon icon-green" style="width: 48px; height: 48px; font-size: 20px;"><i class="fa-solid fa-check-circle"></i></div>
            </div>
        </div>

        <!-- GRIEVANCE LIST -->
        <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <h2 style="font-size: 20px; font-weight: 600; margin: 0;">All Complaints</h2>
        </div>

        <?php if (count($grievances) > 0): ?>
            <table class="grievance-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student</th>
                        <th>Issue</th>
                        <th>Category</th>
                        <th>Date Reported</th>
                        <th>Status</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grievances as $g): 
                        $status_class = 'status-open';
                        if ($g['status'] == 'In Progress') $status_class = 'status-progress';
                        if ($g['status'] == 'Resolved') $status_class = 'status-resolved';
                        if ($g['status'] == 'Escalated') $status_class = 'status-escalated';

                        // Calculate Days Pending
                        $days_pending = 0;
                        $calc_date = $g['incident_date'];
                        
                        // Fallback to created_at if incident_date is empty or invalid
                        if (empty($calc_date) || strtotime($calc_date) < strtotime('2020-01-01')) {
                            $calc_date = $g['created_at'];
                        }

                        // Ensure we have a valid date to calculate
                        if ($g['status'] != 'Resolved' && strtotime($calc_date) > strtotime('2020-01-01')) {
                            $created = new DateTime($calc_date); 
                            $now = new DateTime();
                            $days_pending = $now->diff($created)->days;
                        }
                    ?>
                    <tr>
                        <td>#<?php echo $g['id']; ?></td>
                         <td>
                            <div style="font-weight: 500;"><?php echo htmlspecialchars($g['student_name'] ?? 'Unknown'); ?></div>
                        </td>
                        <td>
                            <div style="font-weight: 500;"><?php echo htmlspecialchars($g['title']); ?></div>
                            <?php if ($days_pending > 7): ?>
                                <span style="font-size: 11px; color: #e03131; font-weight: 600;">
                                    <i class="fa-solid fa-clock"></i> Overdue (<?php echo $days_pending; ?> days)
                                </span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($g['category']); ?></td>
                        <td>
                            <?php 
                                 echo ($days_pending >= 0 && strtotime($calc_date) > strtotime('2020-01-01')) 
                                     ? date("M j, Y", strtotime($calc_date)) 
                                     : '<span style="color:#ccc;">N/A</span>'; 
                            ?>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $status_class; ?>"><?php echo $g['status'] ? $g['status'] : 'Open'; ?></span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                <?php if (!empty($g['student_email'])): ?>
                                <a href="mailto:<?php echo htmlspecialchars($g['student_email']); ?>" class="action-btn" title="Contact Student">
                                    <i class="fa-solid fa-envelope"></i>
                                </a>
                                <?php endif; ?>
                                <a href="grievance_details.php?id=<?php echo $g['id']; ?>" class="action-btn">
                                    Review <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align: center; padding: 60px; background: #f8f8f8; border-radius: 16px; color: #999;">
                <i class="fa-solid fa-clipboard-check" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                <p>No grievances found for review.</p>
            </div>
        <?php endif; ?>

    </div>

<?php include 'logout_modal.php'; ?>
</body>
</html>
