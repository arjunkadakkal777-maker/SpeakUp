<?php
include '../config.php';
// Delete users where username contains non-printable characters or looks like binary garbage
// ASCII range 32-126 are printable. 
// We generally expect names to be alphanumeric.

$sql = "DELETE FROM users WHERE username REGEXP '[^a-zA-Z0-9 ._-]' AND role != 'admin'";
$conn->query($sql);

$deleted = $conn->affected_rows;
echo "Deleted $deleted users with corrupted/garbage usernames.\n";

// Also clean up user_roles if any orphans
$conn->query("DELETE FROM user_roles WHERE user_id NOT IN (SELECT id FROM users)");

echo "Cleanup complete. Please check the dropdown now.";
?>
