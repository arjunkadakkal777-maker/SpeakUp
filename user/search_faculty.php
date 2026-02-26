<?php
include '../config.php';

if (isset($_GET['term'])) {
    $term = $_GET['term'] . '%'; // Start with wildcard
    // Search in users table for faculties
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE role = 'faculty' AND username LIKE ? LIMIT 10");
    $stmt->bind_param("s", $term);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $faculties = [];
    while ($row = $result->fetch_assoc()) {
        $faculties[] = ['id' => $row['id'], 'text' => $row['username']];
    }
    
    echo json_encode($faculties);
}
?>
