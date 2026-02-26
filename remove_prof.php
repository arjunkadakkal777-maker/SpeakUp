<?php
include 'config.php';

echo "<h2>Removing 'Prof.' prefix from Faculty Names...</h2>";

// 1. Update users table
$sql_users = "UPDATE users 
              SET username = TRIM(REPLACE(REPLACE(username, 'Prof. ', ''), 'Prof ', '')) 
              WHERE role = 'faculty' AND (username LIKE 'Prof.%' OR username LIKE 'Prof %')";

if ($conn->query($sql_users) === TRUE) {
    echo "<li>Updated 'users' table: " . $conn->affected_rows . " rows affected.</li>";
} else {
    echo "<li>Error updating users: " . $conn->error . "</li>";
}

// 2. Update faculty_details table
$sql_details = "UPDATE faculty_details 
                SET name = TRIM(REPLACE(REPLACE(name, 'Prof. ', ''), 'Prof ', '')) 
                WHERE name LIKE 'Prof.%' OR name LIKE 'Prof %'";

if ($conn->query($sql_details) === TRUE) {
    echo "<li>Updated 'faculty_details' table: " . $conn->affected_rows . " rows affected.</li>";
} else {
    echo "<li>Error updating faculty_details: " . $conn->error . "</li>";
}

echo "<br><strong>Operation Completed.</strong>";
?>
