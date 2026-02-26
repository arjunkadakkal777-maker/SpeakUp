<?php
include "config.php";
$res = $conn->query("SELECT * FROM faculty_details");
while ($row = $res->fetch_assoc()) {
    echo "ID: " . $row['faculty_id'] . " | Name: " . $row['name'] . "\n";
}
?>
