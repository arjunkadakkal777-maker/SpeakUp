<?php
include 'config.php';
$res = $conn->query("SELECT id, username, reset_token FROM users WHERE reset_token IS NOT NULL");
if ($res->num_rows > 0) {
    while($row = $res->fetch_assoc()) {
        echo "User: " . $row['username'] . " | Token: " . $row['reset_token'] . "\n";
    }
} else {
    echo "No active reset tokens found.\n";
}
?>
