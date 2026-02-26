<?php
include "config.php";
$result = $conn->query("SHOW COLUMNS FROM users");
while($row = $result->fetch_assoc()){
    echo $row['Field'] . "\n";
}
echo "---GRIEVANCES---\n";
$result = $conn->query("SHOW COLUMNS FROM grievances");
while($row = $result->fetch_assoc()){
    echo $row['Field'] . "\n";
}
?>
