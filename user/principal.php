<?php
include "../config.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_name = $_SESSION['user']['username'] ?? 'Principal';

// --- DATA FETCHING FOR ANALYTICS ---

// 1. KPI Stats
$kpi_query = $conn->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status='Resolved' THEN 1 ELSE 0 END) as resolved,
    SUM(CASE WHEN status='Open' OR status='In Progress' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status='Escalated' THEN 1 ELSE 0 END) as escalated
FROM grievances");
$kpi = $kpi_query->fetch_assoc();

// 2. Department Performance (Grievances by Branch)
$dept_labels = [];
$dept_counts = [];
$dept_query = $conn->query("SELECT branch, COUNT(*) as count FROM grievances WHERE branch IS NOT NULL AND branch != '' GROUP BY branch");
if ($dept_query) {
    while($row = $dept_query->fetch_assoc()) {
        $dept_labels[] = $row['branch'];
        $dept_counts[] = $row['count'];
    }
}

// 3. Category Distribution
$cat_labels = [];
$cat_counts = [];
$cat_query = $conn->query("SELECT category, COUNT(*) as count FROM grievances GROUP BY category");
if ($cat_query) {
    while($row = $cat_query->fetch_assoc()) {
        $cat_labels[] = $row['category'];
        $cat_counts[] = $row['count'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Principal Dashboard</title>
    <link rel="stylesheet" href="../css/catalog_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 16px;
            border: 1px solid #eee;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.05);
        }
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin: 10px 0 5px;
            color: #333;
        }
        .stat-label {
            color: #666;
            font-size: 14px;
            font-weight: 500;
        }
        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            font-size: 18px;
        }
        
        .charts-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .chart-box {
            background: white;
            padding: 24px;
            border-radius: 16px;
            border: 1px solid #eee;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        @media (max-width: 900px) {
            .charts-container {
                grid-template-columns: 1fr;
            }
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
        <a href="principal.php" class="menu-item active">
            <div class="menu-icon icon-pink"><i class="fa-solid fa-house"></i></div>
            Dashboard
        </a>
        <a href="#reports" class="menu-item">
            <div class="menu-icon icon-blue"><i class="fa-solid fa-chart-pie"></i></div>
            Analytics
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
        
        <!-- HERO SECTION -->
        <div class="hero-section" style="margin-bottom: 30px;">
            <div class="hero-text">
                <h1>Principal Dashboard</h1>
                <p>Overview of campus grievances, department performance, and escalated issues.</p>
            </div>
            <div style="text-align: right;">
                <button onclick="window.open('download_report.php', '_blank');" style="background: white; border: 1px solid #ddd; padding: 10px 20px; border-radius: 8px; font-weight: 500; cursor: pointer; color: #333; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-file-pdf"></i> View Formal Report
                </button>
            </div>
        </div>

        <!-- 1. KPI STATS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #e7f5ff; color: #1c7ed6;">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
                <div class="stat-value"><?php echo $kpi['total'] ?? 0; ?></div>
                <div class="stat-label">Total Grievances</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #fff9db; color: #f08c00;">
                    <i class="fa-regular fa-clock"></i>
                </div>
                <div class="stat-value"><?php echo $kpi['pending'] ?? 0; ?></div>
                <div class="stat-label">Pending / In Progress</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #ebfbee; color: #2b8a3e;">
                    <i class="fa-solid fa-check-double"></i>
                </div>
                <div class="stat-value"><?php echo $kpi['resolved'] ?? 0; ?></div>
                <div class="stat-label">Resolved</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #fff5f5; color: #e03131;">
                    <i class="fa-solid fa-fire"></i>
                </div>
                <div class="stat-value"><?php echo $kpi['escalated'] ?? 0; ?></div>
                <div class="stat-label">Escalated</div>
            </div>
        </div>

        <!-- 2. CHARTS SECTION -->
        <div class="charts-container" id="reports">
            <!-- Department Performance -->
            <div class="chart-box">
                <div class="section-header">
                    <h3 style="margin: 0; font-size: 18px;">Department Performance</h3>
                    <select style="padding: 6px; border-radius: 6px; border: 1px solid #ddd; font-size: 13px;">
                        <option>This Semester</option>
                        <option>All Time</option>
                    </select>
                </div>
                <!-- Canvas size wrapper -->
                <div style="height: 250px; position: relative;">
                    <canvas id="deptChart"></canvas>
                </div>
            </div>

            <!-- Category Distribution -->
            <div class="chart-box">
                <div class="section-header">
                    <h3 style="margin: 0; font-size: 18px;">Categories</h3>
                </div>
                <div style="height: 250px; position: relative;">
                    <canvas id="catChart"></canvas>
                </div>
            </div>
        </div>

        <!-- 3. SUMMARY REPORT TABLE -->
        <div style="margin-bottom: 40px;">
            <div class="section-header">
                <h2 style="margin: 0; font-size: 20px;">Summary Report</h2>
            </div>
            
            <div style="background: white; border-radius: 16px; border: 1px solid #eee; overflow: hidden;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th style="text-align: left; padding: 15px 20px; font-weight: 600; color: #666; font-size: 13px;">Department</th>
                            <th style="text-align: center; padding: 15px 20px; font-weight: 600; color: #666; font-size: 13px;">Total Issues</th>
                            <th style="text-align: center; padding: 15px 20px; font-weight: 600; color: #666; font-size: 13px;">Resolved</th>
                            <th style="text-align: center; padding: 15px 20px; font-weight: 600; color: #666; font-size: 13px;">Pending</th>
                            <th style="text-align: center; padding: 15px 20px; font-weight: 600; color: #666; font-size: 13px;">Performance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Aggregated query per department
                        $summary_q = $conn->query("SELECT 
                            branch, 
                            COUNT(*) as total,
                            SUM(CASE WHEN status='Resolved' THEN 1 ELSE 0 END) as resolved
                        FROM grievances 
                        WHERE branch IS NOT NULL AND branch != ''
                        GROUP BY branch
                        ORDER BY total DESC");
                        
                        if ($summary_q && $summary_q->num_rows > 0) {
                            while($srow = $summary_q->fetch_assoc()):
                                $pending = $srow['total'] - $srow['resolved'];
                                $rate = ($srow['total'] > 0) ? round(($srow['resolved'] / $srow['total']) * 100) : 0;
                                $color = ($rate >= 80) ? '#2b8a3e' : (($rate >= 50) ? '#f08c00' : '#e03131');
                            ?>
                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                <td style="padding: 15px 20px; font-weight: 500;"><?php echo htmlspecialchars($srow['branch']); ?></td>
                                <td style="padding: 15px 20px; text-align: center;"><?php echo $srow['total']; ?></td>
                                <td style="padding: 15px 20px; text-align: center; color: #2b8a3e;"><?php echo $srow['resolved']; ?></td>
                                <td style="padding: 15px 20px; text-align: center; color: #e67700;"><?php echo $pending; ?></td>
                                <td style="padding: 15px 20px; text-align: center;">
                                    <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                                        <div style="flex: 1; height: 6px; background: #eee; border-radius: 3px; max-width: 60px;">
                                            <div style="width: <?php echo $rate; ?>%; height: 100%; background: <?php echo $color; ?>; border-radius: 3px;"></div>
                                        </div>
                                        <span style="font-size: 12px; font-weight: 600; width: 30px;"><?php echo $rate; ?>%</span>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; 
                        } else {
                            echo '<tr><td colspan="5" style="padding: 20px; text-align: center; color: #999;">No department data available.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4. ESCALATED GRIEVANCES (Renamed & Refined) -->
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

    <!-- Chart Configuration -->
    <script>
        // Data passed from PHP
        const deptLabels = <?php echo json_encode($dept_labels); ?>;
        const deptData = <?php echo json_encode($dept_counts); ?>;
        
        const catLabels = <?php echo json_encode($cat_labels); ?>;
        const catData = <?php echo json_encode($cat_counts); ?>;

        document.addEventListener("DOMContentLoaded", function() {
            // 1. Department Chart (Bar)
            const ctxDept = document.getElementById('deptChart');
            if (ctxDept) {
                new Chart(ctxDept.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: deptLabels,
                        datasets: [{
                            label: 'Grievances',
                            data: deptData,
                            backgroundColor: '#1c7ed6',
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { beginAtZero: true },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // 2. Category Chart (Doughnut)
            const ctxCat = document.getElementById('catChart');
            if (ctxCat) {
                new Chart(ctxCat.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: catLabels,
                        datasets: [{
                            data: catData,
                            backgroundColor: [
                                '#fa5252', '#be4bdb', '#7950f2', '#4c6ef5', '#228be6', '#15aabf', '#12b886', '#40c057', '#82c91e', '#fab005'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'right', labels: { boxWidth: 12, font: {size: 11} } }
                        }
                    }
                });
            }
        });
    </script>
    <?php include 'logout_modal.php'; ?>
</body>
</html>
