<?php
include "config.php";
$res = $conn->query("SELECT id, username, email FROM users LIMIT 10");
while($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . " | Name: " . $row['username'] . " | Email: " . $row['email'] . "\n";
    // Check for encoding
    echo "Encoding check: " . mb_detect_encoding($row['username']) . "\n";
}
?>
