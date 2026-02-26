<?php
include '../config.php';

// Delete users where username contains special characters often found in binary garbage
// targeting ?, <, >, |
$sql = "DELETE FROM users WHERE username LIKE '%?%' OR username LIKE '%<%' OR username LIKE '%|%'";
$conn->query($sql);
$rows = $conn->affected_rows;

echo "Deleted $rows users with garbage characters (?, <, |).";
?>
