<?php
include "config.php";
$check = $conn->query("SHOW COLUMNS FROM users LIKE 'email'");
if ($check->num_rows == 0) {
    echo "Email column missing. Adding...\n";
    if ($conn->query("ALTER TABLE users ADD COLUMN email VARCHAR(100) DEFAULT NULL")) {
        echo "Email column added successfully.\n";
    } else {
        echo "Error adding email column: " . $conn->error . "\n";
    }
} else {
    echo "Email column already exists.\n";
}
?>
