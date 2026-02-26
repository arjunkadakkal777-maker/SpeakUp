<?php
include 'config.php';

// Drop branch column from users table
$sql1 = "ALTER TABLE users DROP COLUMN branch";
if ($conn->query($sql1) === TRUE) {
    echo "Column 'branch' dropped from table 'users'.<br>";
} else {
    echo "Error dropping column branch: " . $conn->error . "<br>";
}

// Drop feedback_text and status from grievances table
$sql2 = "ALTER TABLE grievances DROP COLUMN feedback_text";
if ($conn->query($sql2) === TRUE) {
    echo "Column 'feedback_text' dropped from table 'grievances'.<br>";
} else {
    echo "Error dropping column feedback_text: " . $conn->error . "<br>";
}

$sql3 = "ALTER TABLE grievances DROP COLUMN status";
if ($conn->query($sql3) === TRUE) {
    echo "Column 'status' dropped from table 'grievances'.<br>";
} else {
    echo "Error dropping column status: " . $conn->error . "<br>";
}

echo "Schema revert completed.";
?>
