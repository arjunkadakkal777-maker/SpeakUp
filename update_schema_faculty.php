<?php
include 'config.php';

// Add branch column to users table
$sql1 = "ALTER TABLE users ADD COLUMN IF NOT EXISTS branch VARCHAR(50) DEFAULT NULL";
if ($conn->query($sql1) === TRUE) {
    echo "Column 'branch' added/checked in table 'users'.<br>";
} else {
    echo "Error adding column branch: " . $conn->error . "<br>";
}

// Add feedback_text and status to grievances table
$sql2 = "ALTER TABLE grievances ADD COLUMN IF NOT EXISTS feedback_text TEXT DEFAULT NULL";
if ($conn->query($sql2) === TRUE) {
    echo "Column 'feedback_text' added/checked in table 'grievances'.<br>";
} else {
    echo "Error adding column feedback_text: " . $conn->error . "<br>";
}

$sql3 = "ALTER TABLE grievances ADD COLUMN IF NOT EXISTS status VARCHAR(50) DEFAULT 'Open'";
if ($conn->query($sql3) === TRUE) {
    echo "Column 'status' added/checked in table 'grievances'.<br>";
} else {
    echo "Error adding column status: " . $conn->error . "<br>";
}

echo "Schema update completed.";
?>
