<?php
include 'config.php';
// Create a new table for faculty details
$sql = "CREATE TABLE IF NOT EXISTS faculty_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    faculty_id VARCHAR(50) NOT NULL UNIQUE,
    department VARCHAR(100) NOT NULL,
    email VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'faculty_details' created/checked successfully.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}
?>
