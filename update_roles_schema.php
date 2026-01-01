<?php
include 'config.php';

$sql = "CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE
)";

if ($conn->query($sql) === TRUE) {
    echo "Table roles checked/created.";
} else {
    echo "Error: " . $conn->error;
}
?>
