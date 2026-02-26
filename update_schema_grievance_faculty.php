<?php
include 'config.php';
// Add faculty_id to grievances table to link to specific faculty if needed
$sql = "ALTER TABLE grievances ADD COLUMN IF NOT EXISTS faculty_id INT DEFAULT NULL";
if ($conn->query($sql) === TRUE) {
    echo "Column 'faculty_id' added/checked in table 'grievances'.<br>";
} else {
    echo "Error adding column faculty_id: " . $conn->error . "<br>";
}
?>
