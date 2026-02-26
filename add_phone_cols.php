<?php
include 'config.php';
$conn->query("ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL");
$conn->query("ALTER TABLE users ADD COLUMN otp_code VARCHAR(10) NULL");
$conn->query("ALTER TABLE users ADD COLUMN otp_expiry DATETIME NULL");
echo "Phone and OTP columns added.";
?>
