<?php
include "config.php";
$username = "DIVYA PRASAD";
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    echo "Found User: " . $row['username'] . "\n";
    echo "Role: " . $row['role'] . "\n";
    echo "Password Hash: " . $row['password'] . "\n";
    echo "Department: " . $row['department'] . "\n";
} else {
    echo "User '$username' not found.\n";
    
    // Check similar names?
    $res = $conn->query("SELECT username FROM users WHERE username LIKE '%DIVYA%'");
    echo "Similar users:\n";
    while($row = $res->fetch_assoc()) {
        echo "- " . $row['username'] . "\n";
    }
}
?>
