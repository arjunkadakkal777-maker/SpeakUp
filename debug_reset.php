<?php
include 'config.php';

// 1. Get a user
$res = $conn->query("SELECT id, username, email FROM users LIMIT 1");
if ($res->num_rows == 0) die("No users found");
$user = $res->fetch_assoc();
echo "User found: " . $user['username'] . " (ID: " . $user['id'] . ")\n";

// 2. Generate Token
$token = bin2hex(random_bytes(16));
echo "Generated Token: " . $token . "\n";

// 3. Update DB
$update = $conn->query("UPDATE users SET reset_token='$token' WHERE id=" . $user['id']);
if (!$update) {
    echo "Update FAILED: " . $conn->error . "\n";
    exit;
}
echo "Update SUCCESS\n";

// 4. Verify DB
$check = $conn->query("SELECT reset_token FROM users WHERE id=" . $user['id']);
$row = $check->fetch_assoc();
echo "Token in DB: " . $row['reset_token'] . "\n";

if ($row['reset_token'] === $token) {
    echo "Tokens MATCH.\n";
} else {
    echo "Tokens DO NOT MATCH.\n";
}

// 5. Test Select by Token (as in reset_password.php)
$stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$res2 = $stmt->get_result();
if ($res2->num_rows > 0) {
    echo "Select by Token SUCCESS. Found user ID: " . $res2->fetch_assoc()['id'] . "\n";
} else {
    echo "Select by Token FAILED.\n";
}
?>
