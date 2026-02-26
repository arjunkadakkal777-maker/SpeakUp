<?php
include '../config.php';

// Keep the LOWEST ID (first created) for each unique username name
// Delete the rest.
$sql = "DELETE u1 FROM users u1
        INNER JOIN users u2 
        WHERE u1.id > u2.id AND u1.username = u2.username AND u1.role != 'admin'";

$conn->query($sql);
$rows = $conn->affected_rows;

echo "Deleted $rows duplicate users (kept the first instance of each name).";
?>
