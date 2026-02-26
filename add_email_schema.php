<?php
include 'config.php';

// Add email column
$sql1 = "ALTER TABLE users ADD COLUMN email VARCHAR(100) AFTER username";
if ($conn->query($sql1) === TRUE) {
    echo "Added email column.<br>";
} else {
    echo "Email col error (may exist): " . $conn->error . "<br>";
}

// Add OTP column
$sql2 = "ALTER TABLE users ADD COLUMN otp VARCHAR(6)";
if ($conn->query($sql2) === TRUE) {
    echo "Added otp column.<br>";
}

// Add OTP Expiry column
$sql3 = "ALTER TABLE users ADD COLUMN otp_expiry DATETIME";
if ($conn->query($sql3) === TRUE) {
    echo "Added otp_expiry column.<br>";
}

// Seed emails for existing users for testing
$conn->query("UPDATE users SET email='admin@speakup.com' WHERE username='admin'");
$conn->query("UPDATE users SET email='student@speakup.com' WHERE username='student'");

echo "Done.";
?>
