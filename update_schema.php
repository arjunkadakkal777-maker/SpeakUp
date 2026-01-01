<?php
include 'config.php';

// Add new columns if they don't exist
$sql = "ALTER TABLE grievances 
        ADD COLUMN incident_date DATE AFTER semester,
        ADD COLUMN location VARCHAR(150) AFTER incident_date";

if ($conn->query($sql) === TRUE) {
    echo "Table grievances updated successfully with incident_date and location";
} else {
    // If error is duplicate column name, it's fine, otherwise print error
    echo "Query Result: " . $conn->error;
}
?>
