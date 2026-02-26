<?php
include 'config.php';

// Create student_registrations table
$sql = "CREATE TABLE IF NOT EXISTS student_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    branch VARCHAR(50) NOT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'student_registrations' created successfully.";
} else {
    echo "Error creating table: " . $conn->error;
}
?>
