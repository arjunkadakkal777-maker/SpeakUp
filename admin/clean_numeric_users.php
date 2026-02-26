<?php
include '../config.php';

// Delete users where username is purely digits (mistaken ID for Name)
// But safeguard 'admin' and other textual names.
$conn->query("DELETE FROM users WHERE username REGEXP '^[0-9]+$'");
$rows = $conn->affected_rows;
echo "Deleted $rows users with numeric usernames.\n";
echo "Now please re-import.";
?>
