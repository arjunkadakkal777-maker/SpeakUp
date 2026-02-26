<?php
include "config.php";
if ($_SESSION['user']['role'] == 'faculty') {
    $conn->query("ALTER TABLE grievances ADD COLUMN IF NOT EXISTS faculty_id INT DEFAULT NULL");
    header("Location: faculty.php");
} else {
    echo "Unauthorized";
}
?>
