<?php
include "config.php";

echo "<h2>Updating Schema...</h2>";

// 1. Add 'must_change_password' to users
$check = $conn->query("SHOW COLUMNS FROM users LIKE 'must_change_password'");
if ($check->num_rows == 0) {
    if ($conn->query("ALTER TABLE users ADD COLUMN must_change_password TINYINT(1) DEFAULT 0")) {
        echo "✅ Added 'must_change_password' column.<br>";
    } else {
        echo "❌ Error adding 'must_change_password': " . $conn->error . "<br>";
    }
} else {
    echo "ℹ️ 'must_change_password' already exists.<br>";
}

echo "<br>Done.";
?>
