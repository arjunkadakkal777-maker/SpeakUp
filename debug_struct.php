<?php
include 'config.php';
$res = $conn->query("SHOW CREATE TABLE users");
$row = $res->fetch_assoc();
echo $row['Create Table'];
?>
