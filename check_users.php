<?php
include "config.php";

$output = "Users (Role=Faculty)\n";
$output .= sprintf("%-5s %-30s %-10s %-30s\n", "ID", "Username", "Role", "Department");
$output .= str_repeat("-", 80) . "\n";

$res = $conn->query("SELECT * FROM users WHERE role='faculty' LIMIT 20");
while ($row = $res->fetch_assoc()) {
    $output .= sprintf("%-5s %-30s %-10s %-30s\n", 
        $row['id'], 
        substr($row['username'], 0, 30), 
        $row['role'], 
        substr($row['department'], 0, 30)
    );
}

file_put_contents("user_dump.txt", $output);
?>
