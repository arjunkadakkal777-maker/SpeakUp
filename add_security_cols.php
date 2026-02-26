<?php
include 'config.php';
$conn->query("ALTER TABLE users ADD COLUMN security_question VARCHAR(255) NULL");
$conn->query("ALTER TABLE users ADD COLUMN security_answer VARCHAR(255) NULL");
echo "Security columns added.";
?>
