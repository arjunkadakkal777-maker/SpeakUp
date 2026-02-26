<?php
include 'config.php';
$res = $conn->query("DESCRIBE grievances");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
