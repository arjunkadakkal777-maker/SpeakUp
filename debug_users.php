<?php
include 'config.php';
$res = $conn->query("SELECT * FROM users ORDER BY id DESC LIMIT 5");
if ($res) {
    while($row = $res->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Query failed: " . $conn->error;
}
?>
