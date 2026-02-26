<?php
include "config.php";

// 1. Add is_anonymous to grievances if not exists
$conn->query("ALTER TABLE grievances ADD COLUMN IF NOT EXISTS is_anonymous TINYINT(1) DEFAULT 0");

// 2. Check columns in grievances
$res = $conn->query("SHOW COLUMNS FROM grievances");
echo "Columns in grievances table:\n";
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}
?>
