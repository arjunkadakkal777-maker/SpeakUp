<?php
include "config.php";

$username = $_POST['username'];
$password = $_POST['password'];

$q = $conn->prepare("SELECT * FROM users WHERE username=?");
$q->bind_param("s", $username);
$q->execute();
$result = $q->get_result();

if ($user = $result->fetch_assoc()) {
    if (password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;

        if ($user['role'] == 'admin') {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: user/".$user['role'].".php");
        }
        exit;
    }
}
header("Location: login.php?error=invalid");
exit;
?>