<?php
ob_start();

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


$conn = new mysqli("localhost","root","","cgms");

if($conn->connect_error){
    die("database connection failed");
}

/* --- AUTO ESCALATION LOGIC --- */
// Grievances older than 3 days should be automatically escalated if still Open/In Progress
$escalation_limit_date = date('Y-m-d', strtotime('-3 days')); // 3 days ago

// Update Query
// Note: We use IFNULL to handle potentially NULL feedback_text
$auto_esc_sql = "UPDATE grievances 
                 SET status = 'Escalated', 
                     feedback_text = CONCAT(IFNULL(feedback_text, ''), '\n\n--- [System Auto-Escalation] ---\nAutomatically escalated due to inactivity (>3 days).')
                 WHERE status IN ('Open', 'In Progress') 
                 AND incident_date <= '$escalation_limit_date'";

$conn->query($auto_esc_sql);
?>