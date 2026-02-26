<?php
include 'config.php';

// Add department column to users table
$sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS department VARCHAR(100) DEFAULT NULL";
if ($conn->query($sql) === TRUE) {
    echo "Column 'department' added/checked in table 'users'.<br>";
} else {
    echo "Error adding column department: " . $conn->error . "<br>";
}

echo "Schema update mapping completed.";
?>
