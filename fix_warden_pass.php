<?php
include "config.php";

$username = "DIVYA PRASAD";
$new_pass = "12345678"; // New, known password
$hash = password_hash($new_pass, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
$stmt->bind_param("ss", $hash, $username);

if ($stmt->execute()) {
    echo "Password for '$username' reset to '$new_pass'";
} else {
    echo "Error updating password: " . $conn->error;
}
?>
