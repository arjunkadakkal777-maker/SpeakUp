<?php
include "config.php";

$username = "Test Faculty"; // Replace with a username you know exists
$password = "EMP001"; // Replace with the password you expect

echo "<h2>Debug Login for: $username</h2>";

// 1. Check if user exists
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    echo "User found!<br>";
    echo "ID: " . $user['id'] . "<br>";
    echo "Username: " . $user['username'] . "<br>";
    echo "Stored Hash: " . $user['password'] . "<br>";
    echo "Role: " . $user['role'] . "<br><br>";

    // 2. Verify Password
    if (password_verify($password, $user['password'])) {
        echo "<strong style='color:green'>Password Verification SUCCESS!</strong>";
    } else {
        echo "<strong style='color:red'>Password Verification FAILED!</strong><br>";
        echo "Hash provided: " . $user['password'] . "<br>";
        echo "Password checked: " . $password . "<br>";
        echo "New Hash of '$password' would be: " . password_hash($password, PASSWORD_DEFAULT);
    }
} else {
    echo "<strong style='color:red'>User NOT found!</strong>";
}
?>
