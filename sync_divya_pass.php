<?php
include "config.php";
$username = "DIVYA PRASAD";

// 1. Get faculty ID from faculty_details
$stmt = $conn->prepare("SELECT faculty_id FROM faculty_details WHERE name = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    $fid = $row['faculty_id'];
    echo "Found Faculty ID: " . $fid . "\n";
    
    // 2. Update password in users table
    $hash = password_hash($fid, PASSWORD_DEFAULT);
    $upd = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
    $upd->bind_param("ss", $hash, $username);
    if ($upd->execute()) {
        echo "Password updated to match Faculty ID ('$fid').";
    } else {
        echo "Update failed: " . $conn->error;
    }
} else {
    echo "Faculty details not found for '$username'. Cannot retrieve ID from DB.\n";
    // Fallback: Check if we can infer from user table or just report failure
}
?>
