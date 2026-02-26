<?php
include "../config.php";


if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] != 'faculty' && $_SESSION['user']['role'] != 'warden' && $_SESSION['user']['role'] != 'hod' && $_SESSION['user']['role'] != 'committee' && $_SESSION['user']['role'] != 'principal')) {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'] ?? 0;
$message = "";

// 1. Fetch Grievance Details FIRST
$stmt = $conn->prepare("SELECT * FROM grievances WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$grievance = $result->fetch_assoc();

if (!$grievance) {
    die("Grievance not found.");
}

// 2. Fetch Student Details (for email)
$check_email = $conn->query("SHOW COLUMNS FROM users LIKE 'email'");
$has_email = ($check_email->num_rows > 0);

if ($has_email) {
    $student_q = $conn->prepare("SELECT username, email, branch FROM users WHERE id=?");
} else {
    $student_q = $conn->prepare("SELECT username, NULL as email, branch FROM users WHERE id=?");
}

$student_q->bind_param("i", $grievance['student_id']);
$student_q->execute();
$student_res = $student_q->get_result();
$student = $student_res->fetch_assoc();

// 2.5 Fetch Assigned Officer (Faculty/Warden) Name if exists
// 2.5 Fetch Assigned Officer (Faculty/Warden) Name if exists
$assigned_officer_name = "Not Assigned";

// If Hostel category, generally assigned to Warden
if ($grievance['category'] == 'Hostel') {
    $assigned_officer_name = "Hostel Warden";
} 
// If Academic/Other, fetch assigned faculty
elseif (!empty($grievance['faculty_id'])) {
    $f_q = $conn->prepare("SELECT username FROM users WHERE id=?");
    $f_q->bind_param("i", $grievance['faculty_id']);
    $f_q->execute();
    $f_res = $f_q->get_result();
    if ($f_row = $f_res->fetch_assoc()) {
        $assigned_officer_name = $f_row['username'];
    }
}

// 3. Handle Form Submission
// 3. Handle Form Submission
// 3. Handle Form Submission
if (isset($_POST['update_status'])) {
    // Prevent updating if already resolved
    if ($grievance['status'] == 'Resolved') {
         $message = "This grievance is already resolved and cannot be updated.";
    } else {
        $new_status = $_POST['status'];
        $feedback = trim($_POST['feedback']);
    $escalation_level = $_POST['escalation_level'] ?? NULL;

    // Check if escalating
    $is_escalating = ($new_status == 'Escalated');
    
    // Check for mandatory feedback
    if (empty($feedback)) {
        $message = "Feedback/Action Taken is compulsory for status update.";
    } else {
        // Update Query
        $stmt = $conn->prepare("UPDATE grievances SET status=?, feedback_text=? WHERE id=?");
        $stmt->bind_param("ssi", $new_status, $feedback, $id);
        
        if ($stmt->execute()) {
            $message = "Grievance updated successfully.";
            
            // Refresh grievance data for display
            $grievance['status'] = $new_status;
            $grievance['feedback_text'] = $feedback;
    
            // NOTIFICATIONS
            $notified = [];
    
            // 1. Notify Student (Always)
            if (!empty($student['email'])) {
                $to = $student['email'];
                $subject = "Update on Grievance: " . $grievance['title'];
                
                $body = "Dear " . $student['username'] . ",\n\n";
                $body .= "Status: $new_status\n";
                
                if ($is_escalating) {
                    $escalated_to_name = ($escalation_level == 'HOD') ? "Head of Department" : (($escalation_level == 'Principal') ? "Principal" : "Committee");
                    $body .= "Important: Your grievance has been escalated to the $escalated_to_name for further review.\n";
                }
                
                $body .= "Feedback: $feedback\n\n- SpeakUp Team";
                
                $headers = "From: SpeakUp Admin <notify@speakup.local>";
                if (@mail($to, $subject, $body, $headers)) $notified[] = "Student";
            }
    
            // 2. Notify Higher Authority (If Escalated)
            if ($is_escalating) {
                // Determine recipient based on level (Mock logic for demonstration)
                $auth_email = ""; 
                $auth_role = "Committee";
    
                if ($escalation_level == 'Principal') {
                    $auth_email = "principal@speakup.local"; 
                    $auth_role = "Principal";
                } elseif ($escalation_level == 'HOD') {
                    $auth_email = "hod@speakup.local";
                    $auth_role = "HOD";
                } else {
                    $auth_email = "committee@speakup.local";
                }
    
                // Send Email to Authority
                $subject = "Escalated Grievance Alert: #" . $id;
                $body = "Grievance #$id has been escalated to $auth_role.\n\nTitle: " . $grievance['title'] . "\nFeedback: $feedback\n\nPlease review on dashboard.";
                $headers = "From: SpeakUp Admin <system@speakup.local>";
                if (@mail($auth_email, $subject, $body, $headers)) $notified[] = $auth_role;
                
                $message .= " (Escalated to $auth_role)";
                
                // Append System Log to Feedback for History
                $escalated_by = $_SESSION['user']['username'] ?? 'Staff';
                $timestamp = date("D, d M Y H:i");
                $feedback .= "\n\n--- [System Log] ---\nEscalated to: $auth_role\nBy: $escalated_by\naccess Time: $timestamp";
    
                // --- DATABASE NOTIFICATIONS ---
                
                // 1. Notify Student
                $stud_msg = "Your grievance #$id has been escalated to $auth_role.";
                $stmt_n = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                if ($stmt_n) {
                    $stmt_n->bind_param("is", $grievance['student_id'], $stud_msg);
                    $stmt_n->execute();
                }
    
                // 2. Notify Authority (Find users with the target role)
                // Note: For HOD, we should ideally filter by branch. For now, we notify any user with that role.
                $auth_role_db = strtolower($auth_role); // roles in DB are likely lowercase: hod, principal, committee
                if ($auth_role == 'HOD') $auth_role_db = 'hod';
                
                // If HOD, try to match branch if possible, otherwise fetch all HODs
                $sql_auth = "SELECT id FROM users WHERE role = ?";
                $params_type = "s";
                $params_val = [$auth_role_db];
    
                if ($auth_role == 'HOD' && !empty($student['branch'])) {
                    // Assuming HODs might have a 'branch' or 'department' column matching the student
                    // Checking if department column exists first to be safe
                    $check_dept = $conn->query("SHOW COLUMNS FROM users LIKE 'department'");
                    if ($check_dept->num_rows > 0) {
                       $sql_auth .= " AND department = ?";
                       $params_type .= "s";
                       $params_val[] = $student['branch'];
                    }
                }
    
                $stmt_auth = $conn->prepare($sql_auth);
                if ($stmt_auth) {
                    $stmt_auth->bind_param($params_type, ...$params_val);
                    $stmt_auth->execute();
                    $res_auth = $stmt_auth->get_result();
                    while ($u = $res_auth->fetch_assoc()) {
                        $auth_msg = "New Escalated Grievance #$id requires your attention.";
                        $ins = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                        $ins->bind_param("is", $u['id'], $auth_msg);
                        $ins->execute();
                    }
                }
            }
    
            if (!empty($notified)) {
                $message .= " Notifications sent to: " . implode(", ", $notified);
            }
    
        } else {
            $message = "Error updating grievance.";
        }
    }
    }
}

// 4. Handle Revert Action
if (isset($_POST['revert_grievance'])) {
    $revert_reason = trim($_POST['revert_reason']);
    $revert_to = $_POST['revert_to'] ?? 'committee'; // 'committee', 'hod', 'faculty', 'warden'
    
    if (!empty($revert_reason)) {
        // Update status to 'In Progress' (de-escalate)
        $revert_status = 'In Progress';
        
        // Define target name for logic and display
        $target_display = "Committee";
        if ($revert_to == 'hod') $target_display = "Head of Department (HOD)";
        if ($revert_to == 'faculty') $target_display = "Assigned Faculty";
        if ($revert_to == 'warden') $target_display = "Warden";

        // Append revert note to feedback
        $timestamp = date("D, d M Y H:i");
        $reverter = $_SESSION['user']['username'] ?? 'Authority';
        $new_feedback = $grievance['feedback_text'] . "\n\n--- [REVERTED] ---\nReverted to $target_display by $reverter at $timestamp.\nReason: $revert_reason";
        
        $stmt_r = $conn->prepare("UPDATE grievances SET status=?, feedback_text=? WHERE id=?");
        $stmt_r->bind_param("ssi", $revert_status, $new_feedback, $id);
        
        if ($stmt_r->execute()) {
            $message = "Grievance reverted to $target_display successfully.";
            $grievance['status'] = $revert_status;
            $grievance['feedback_text'] = $new_feedback;
            
            // Notification Logic
            $sender_role_title = ($_SESSION['user']['role'] == 'principal') ? "Principal" : "HOD";
            $notif_msg = "Grievance #$id Reverted: $sender_role_title to you. Reason: " . substr($revert_reason, 0, 50) . "...";
            $users_to_notify = [];

            if ($revert_to == 'hod') {
                // Determine HOD based on branch (assuming mapped or just notify all HODs for now)
                $h_stmt = $conn->prepare("SELECT id FROM users WHERE role='hod'"); // Ideally filter by branch
                 if (!empty($student['branch'])) { // Filter if possible
                    $check_dept = $conn->query("SHOW COLUMNS FROM users LIKE 'department'");
                    if ($check_dept && $check_dept->num_rows > 0) {
                        $h_stmt = $conn->prepare("SELECT id FROM users WHERE role='hod' AND department=?");
                        $h_stmt->bind_param("s", $student['branch']);
                    }
                 }
                $h_stmt->execute();
                $res_h = $h_stmt->get_result();
                while($u = $res_h->fetch_assoc()) $users_to_notify[] = $u;

            } elseif ($revert_to == 'faculty') {
                if (!empty($grievance['faculty_id'])) {
                    $users_to_notify[] = ['id' => $grievance['faculty_id']];
                }
            } elseif ($revert_to == 'warden') {
                // Notify all wardens
                $w_stmt = $conn->prepare("SELECT id FROM users WHERE role='warden'");
                $w_stmt->execute();
                $res_w = $w_stmt->get_result();
                while($u = $res_w->fetch_assoc()) $users_to_notify[] = $u;
            } else {
                // Determine Committee (default)
                $cmt_role = 'committee';
                $stmt_c = $conn->prepare("SELECT id FROM users WHERE role=?");
                $stmt_c->bind_param("s", $cmt_role);
                $stmt_c->execute();
                $res_c = $stmt_c->get_result();
                while($u = $res_c->fetch_assoc()) $users_to_notify[] = $u;
            }

            foreach ($users_to_notify as $u) {
                $ins = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                $ins->bind_param("is", $u['id'], $notif_msg);
                $ins->execute();
            }

        } else {
            $message = "Error reverting grievance.";
        }
    } else {
        $message = "Please provide a reason for reverting.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grievance Details</title>
    <link rel="stylesheet" href="../css/catalog_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .detail-card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            max-width: 800px;
            margin: 40px auto;
        }
        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .info-item label {
            display: block;
            color: #666;
            font-size: 13px;
            margin-bottom: 4px;
        }
        .info-item div {
            font-weight: 500;
            font-size: 15px;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-open { background: #ffe3e3; color: #c92a2a; }
        .status-progress { background: #fff3bf; color: #f08c00; }
        .status-resolved { background: #d3f9d8; color: #2b8a3e; }
        .status-escalated { background: #ffc9c9; color: #e03131; }
    </style>
</head>
<body style="background: #f8f9fa;">

    <div class="main-content" style="margin-left: 0; padding: 20px;">
        
        <?php if ($message): ?>
            <div style="background: #d3f9d8; color: #2b8a3e; padding: 15px; border-radius: 8px; max-width: 800px; margin: 0 auto 20px; text-align: center;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php
        // Security Check for Faculty: Only allow viewing if assigned
        if ($_SESSION['user']['role'] == 'faculty' && $grievance['faculty_id'] != $_SESSION['user']['id']) {
            echo '<div style="max-width:800px; margin:40px auto; text-align:center; padding:40px; background:white; border-radius:16px;">
                    <i class="fa-solid fa-lock" style="font-size:48px; color:#ccc; margin-bottom:20px;"></i>
                    <h2>Access Denied</h2>
                    <p style="color:#666;">You are not authorized to view this grievance.</p>
                    <a href="faculty.php" style="display:inline-block; margin-top:20px; padding:10px 20px; background:#000; color:#fff; text-decoration:none; border-radius:8px;">Back to Dashboard</a>
                  </div>';
            exit;
        }

        // Security Check for Warden: REMOVED as per request to allow viewing all grievances (Hostel & Faculty)
        /* 
        if ($_SESSION['user']['role'] == 'warden' && $grievance['category'] != 'Hostel') {
             echo '<div style="max-width:800px; margin:40px auto; text-align:center; padding:40px; background:white; border-radius:16px;">
                    <i class="fa-solid fa-lock" style="font-size:48px; color:#ccc; margin-bottom:20px;"></i>
                    <h2>Access Denied</h2>
                    <p style="color:#666;">Wardens can only view Hostel-related grievances.</p>
                    <a href="warden.php" style="display:inline-block; margin-top:20px; padding:10px 20px; background:#000; color:#fff; text-decoration:none; border-radius:8px;">Back to Dashboard</a>
                  </div>';
            exit;
        } 
        */
        
        $back_link = '#';
        if ($_SESSION['user']['role'] == 'faculty') $back_link = 'faculty.php';
        if ($_SESSION['user']['role'] == 'warden') $back_link = 'warden.php';
        if ($_SESSION['user']['role'] == 'hod') $back_link = 'hod.php';
        if ($_SESSION['user']['role'] == 'committee') $back_link = 'committee.php';
        if ($_SESSION['user']['role'] == 'principal') $back_link = 'principal.php';
        ?>

        <div class="detail-card">
            <div class="header-row">
                <div>
                    <a href="<?php echo $back_link; ?>" style="text-decoration: none; color: #666; font-size: 14px; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 10px;">
                        <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <h1 style="margin: 0; font-size: 24px;">Grievance #<?php echo $grievance['id']; ?></h1>
                    
                    <?php
                    // Overdue Calculation
                    if ($grievance['status'] != 'Resolved' && $grievance['status'] != 'Escalated') {
                        $min_valid_ts = strtotime('2020-01-01');
                        $calc_date = $grievance['incident_date'];
                        
                        // Fallback
                        if (empty($calc_date) || strtotime($calc_date) < $min_valid_ts) {
                             $calc_date = $grievance['created_at'];
                        }

                        if (strtotime($calc_date) > $min_valid_ts) {
                            $c_date = new DateTime($calc_date);
                            $now = new DateTime();
                            $days = $now->diff($c_date)->days;
                            
                            if ($days > 7) {
                                echo '<div style="margin-top: 10px; color: #c92a2a; font-weight: 500; font-size: 14px; display: flex; align-items: center; gap: 6px;">
                                        <i class="fa-solid fa-triangle-exclamation"></i> 
                                        Overdue by ' . $days . ' days. Escalation recommended.
                                      </div>';
                            }
                        }
                    }
                    ?>
                </div>
                <?php
                $s_class = 'status-open';
                if ($grievance['status'] == 'In Progress') $s_class = 'status-progress';
                if ($grievance['status'] == 'Resolved') $s_class = 'status-resolved';
                if ($grievance['status'] == 'Escalated') $s_class = 'status-escalated';
                ?>
                <span class="status-badge <?php echo $s_class; ?>"><?php echo $grievance['status'] ?? 'Open'; ?></span>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <label>Assigned Officer</label>
                    <div style="margin-bottom: 5px;">
                        <i class="fa-solid fa-user-shield" style="color: #666; margin-right: 5px;"></i>
                        <?php echo htmlspecialchars($assigned_officer_name); ?>
                    </div>
                </div>

                <div class="info-item">
                     <label>Last Action Taken</label>
                    <div style="font-size: 13px; color: #444; background: #f1f3f5; padding: 8px; border-radius: 6px;">
                         <?php 
                            $latest_feedback = $grievance['feedback_text'];
                            if (empty($latest_feedback)) {
                                echo '<span style="color: #999; font-style: italic;">No actions recorded yet.</span>';
                            } else {
                                echo nl2br(htmlspecialchars(substr($latest_feedback, 0, 100) . (strlen($latest_feedback) > 100 ? '...' : '')));
                            }
                         ?>
                    </div>
                </div>

                <div class="info-item">
                    <label>Student Details</label>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <?php 
                        $display_name = htmlspecialchars($student['username'] ?? 'Unknown');
                        $display_branch = htmlspecialchars($student['branch']);
                        
                        // Check for Anonymity (assuming 'is_anonymous' column in users table)
                        // Ideally checking if the grievance itself was filed anonymously or if the user is anonymous
                        // Based on register.php 'is_anonymous' is stored in users table.
                        
                        // We need to fetch 'is_anonymous' from student result (not currently fetched in lines 29-37)
                        // Correction: Let's fetch it now or use a separate check.
                        
                        // Check for Anonymity (Per Grievance Setting)
                        if (isset($grievance['is_anonymous']) && $grievance['is_anonymous'] == 1) {
                            $display_name = "Anonymous Student";
                        }
                        ?>
                        <span style="font-weight: 600;"><?php echo $display_name; ?></span>
                        
                        <?php if (!empty($display_branch)): ?>
                            <span style="font-size: 13px; color: #666;">
                                <i class="fa-solid fa-code-branch"></i> <?php echo $display_branch; ?>
                            </span>
                        <?php endif; ?>

                    </div>
                </div>
                <div class="info-item">
                    <label>Date Reported</label>
                    <div><?php echo date("F j, Y", strtotime($grievance['incident_date'])); ?></div>
                </div>
                <div class="info-item">
                    <label>Category</label>
                    <div><?php echo htmlspecialchars($grievance['category']); ?></div>
                </div>
                <div class="info-item">
                    <label>Location</label>
                    <div><?php echo htmlspecialchars($grievance['location']); ?></div>
                </div>
            </div>

            <div style="margin-bottom: 30px;">
                <label style="display: block; color: #666; font-size: 13px; margin-bottom: 8px;">Subject</label>
                <div style="font-size: 18px; font-weight: 600; margin-bottom: 10px;"><?php echo htmlspecialchars($grievance['title']); ?></div>
                <label style="display: block; color: #666; font-size: 13px; margin-bottom: 8px;">Description</label>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; line-height: 1.6;">
                    <?php echo nl2br(htmlspecialchars($grievance['description'])); ?>
                </div>
            </div>

            <?php if ($grievance['attachment']): ?>
                <div style="margin-bottom: 30px;">
                     <label style="display: block; color: #666; font-size: 13px; margin-bottom: 8px;">Attachment</label>
                     <a href="../uploads/grievances/<?php echo $grievance['attachment']; ?>" target="_blank" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px; background: #eee; padding: 8px 16px; border-radius: 6px; color: #333; font-weight: 500;">
                        <i class="fa-solid fa-paperclip"></i> View Attachment
                     </a>
                </div>
            <?php endif; ?>

            <hr style="border: 0; border-top: 1px solid #eee; margin: 30px 0;">

            <h3>Update Status & Feedback</h3>
            <?php if ($grievance['status'] == 'Resolved'): ?>
                <div style="background: #e6fcf5; color: #0ca678; padding: 20px; border-radius: 8px; text-align: center; border: 1px solid #20c997;">
                    <i class="fa-solid fa-check-circle" style="font-size: 24px; margin-bottom: 10px;"></i>
                    <h3 style="margin: 0;">Grievance Resolved</h3>
                    <p>This grievance has been marked as resolved and cannot be reopened.</p>
                </div>
            <?php else: ?>
            <form method="post">
                <div style="margin-bottom: 20px;">
            <label style="display: block; font-weight: 500; margin-bottom: 8px;">Status</label>
                    
                    <select id="statusSelect" name="status" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;" onchange="toggleEscalation()">
                        <option value="Open" <?php echo ($grievance['status'] == 'Open') ? 'selected' : ''; ?>>Open</option>
                        <option value="In Progress" <?php echo ($grievance['status'] == 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                        
                        <?php if ($_SESSION['user']['role'] != 'principal'): ?>
                        <option value="Escalated" <?php echo ($grievance['status'] == 'Escalated') ? 'selected' : ''; ?>>Escalated</option>
                        <?php else: ?>
                        <option value="Reverted" <?php echo ($grievance['status'] == 'Reverted') ? 'selected' : ''; ?>>Reverted</option>
                        <?php endif; ?>
                        
                        <option value="Resolved" <?php echo ($grievance['status'] == 'Resolved') ? 'selected' : ''; ?>>Resolved</option>
                    </select>
                </div>

                <div id="escalationDiv" style="margin-bottom: 20px; display: none; background: #fff0f6; padding: 15px; border-radius: 8px;">
                    <label style="display: block; font-weight: 500; margin-bottom: 8px; color: #d6336c;">Escalate To:</label>
                    <select name="escalation_level" style="width: 100%; padding: 10px; border: 1px solid #fab005; border-radius: 6px;">
                        <option value="HOD">Head of Department (HOD)</option>
                        <?php if ($_SESSION['user']['role'] != 'principal'): ?>
                        <option value="Principal">Principal</option>
                        <?php endif; ?>
                        <?php if ($_SESSION['user']['role'] != 'committee'): ?>
                        <option value="Committee">Grievance Committee</option>
                        <?php endif; ?>
                    </select>
                    <p style="font-size: 12px; color: #666; margin-top: 5px;">* This will notify the selected authority immediately.</p>
                </div>

                <script>
                function toggleEscalation() {
                    var status = document.getElementById("statusSelect").value;
                    var div = document.getElementById("escalationDiv");
                    if (status === "Escalated") {
                        div.style.display = "block";
                    } else {
                        div.style.display = "none";
                    }
                }

                // Run on load
                toggleEscalation();
                </script>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 500; margin-bottom: 8px;">Feedback to Student <span style="color:red">*</span></label>
                    <textarea name="feedback" rows="4" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;" placeholder="Write your response or action taken (Required)..." required><?php echo htmlspecialchars($grievance['feedback_text'] ?? ''); ?></textarea>
                </div>

                <button type="submit" name="update_status" style="background: black; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Update Grievance
                </button>
            </form>
            <?php endif; ?>

            <!-- Revert Section (Only for Escalated Grievances & Authority) -->
            <?php if ($grievance['status'] == 'Escalated' && ($_SESSION['user']['role'] == 'principal' || $_SESSION['user']['role'] == 'hod')): ?>
                <hr style="border: 0; border-top: 1px solid #eee; margin: 30px 0;">
                
                <h3 style="color: #e03131;">Revert (Send Back)</h3>
                <form method="post" onsubmit="return confirm('Are you sure you want to revert this grievance?');">
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 500; margin-bottom: 8px;">Revert To</label>
                        <select name="revert_to" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                            <?php
                            // Logic to determine source of escalation
                            $source_role = 'unknown';
                            $logs = $grievance['feedback_text'];
                            
                            // Regex to find "Escalated to: Principal" log entries
                            // Log format: "Escalated to: Principal\nBy: Username\naccess Time: ..."
                            // We use 'access Time' as a reliable anchor.
                            if (preg_match_all('/Escalated to: Principal.*?By: (.*?)\s+access Time:/s', $logs, $matches, PREG_SET_ORDER)) {
                                // Get the very last match
                                $last_match = end($matches);
                                $escalated_by_user = trim($last_match[1]);
                                
                                // Identify sender role
                                $stmt_u = $conn->prepare("SELECT role FROM users WHERE username = ?");
                                $stmt_u->bind_param("s", $escalated_by_user);
                                $stmt_u->execute();
                                $res_u = $stmt_u->get_result();
                                if ($row_u = $res_u->fetch_assoc()) {
                                    $source_role = strtolower($row_u['role']);
                                }
                            }
                            
                            // STRICT REVERSAL LOGIC
                            if ($source_role == 'hod') {
                                echo '<option value="hod">Head of Department (HOD)</option>';
                            } elseif ($source_role == 'committee') {
                                echo '<option value="committee">Grievance Committee</option>';
                            } else {
                                // Default / Fallback if source unknown:
                                // If we can't determine, we default to Committee as the most common path.
                                // We do NOT show HOD here to avoid incorrect options.
                                echo '<option value="committee">Grievance Committee</option>';
                            }
                            ?>
                        </select>
                        <?php if ($source_role != 'unknown'): ?>
                            <p style="font-size: 11px; color: #666; margin-top: 4px;">* Detected source: <?php echo ucfirst($source_role); ?></p>
                        <?php endif; ?>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: 500; margin-bottom: 8px;">Reason for Reverting</label>
                        <textarea name="revert_reason" rows="3" style="width: 100%; padding: 10px; border: 1px solid #ffc9c9; border-radius: 6px; background: #fff5f5;" placeholder="Explain why this is being sent back..." required></textarea>
                    </div>
                    <button type="submit" name="revert_grievance" style="background: white; color: #e03131; border: 1px solid #e03131; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-rotate-left"></i> Revert
                    </button>
                </form>
            <?php endif; ?>

        </div>
    </div>

</body>
</html>
