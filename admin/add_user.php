<?php
include "../config.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['add_user'])) {

    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = $_POST['role'];

    if (empty($username) || empty($role)) {
        die("Empty fields!");
    }

    $stmt = $conn->prepare(
        "INSERT INTO users (username, password, role) VALUES (?, ?, ?)"
    );

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("sss", $username, $password, $role);

    if ($stmt->execute()) {
        $success = "User added successfully";
    } else {
        $error = "Insert error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - Admin</title>
    <link rel="stylesheet" href="../css/catalog_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f8f9fa;
            background-image: radial-gradient(#e9ecef 1px, transparent 1px);
            background-size: 24px 24px;
        }

        .card-container {
            width: 100%;
            max-width: 450px;
            background: white;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.04);
            text-align: center;
        }

        .header-section {
            margin-bottom: 30px;
        }
        .header-section h2 {
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 8px 0;
            color: #1a1a1a;
        }
        .header-section p {
            color: #666;
            font-size: 14px;
            margin: 0;
        }

        .form-group {
            text-align: left;
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 13px;
            color: #444;
        }
        
        /* Consistent Input Styling */
        input[type="text"], input[type="password"], select {
            width: 100%;
            padding: 12px;
            background: #fdfdfd;
            border: 1px solid #eee;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            box-sizing: border-box;
            transition: all 0.2s;
        }
        input:focus, select:focus {
            background: #fff;
            border-color: #000;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0,0,0,0.05);
        }

        .btn-large {
            width: 100%;
            margin-top: 10px;
            justify-content: center;
            padding: 14px;
            background: black;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-large:hover {
            background: #333;
        }

        .feedback-msg {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: left;
        }
        .success { background: #d3f9d8; color: #2b8a3e; }
        .error { background: #ffe3e3; color: #c92a2a; }
    </style>
</head>
<body>

<div class="card-container">
    <div class="header-section">
        <div style="width: 50px; height: 50px; background: #e7f5ff; color: #1c7ed6; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 20px;">
            <i class="fa-solid fa-user-plus"></i>
        </div>
        <h2>Create New User</h2>
        <p>Enter details to provision a new account.</p>
    </div>

    <?php if (isset($success)): ?>
        <div class="feedback-msg success"><i class="fa-solid fa-check-circle"></i> <?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="feedback-msg error"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error; ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <div class="form-group">
            <label>Username (ID)</label>
            <input type="text" name="username" placeholder="e.g. 2023CSB001" required>
        </div>

        <div class="form-group">
            <label>Default Password</label>
            <input type="password" name="password" placeholder="Set initial password" required>
        </div>

        <div class="form-group">
            <label>Role</label>
            <select name="role" required>
                <option value="" disabled selected>Select Role</option>
                <option value="student">Student</option>
                <option value="faculty">Faculty</option>
                <option value="warden">Warden</option>
                <option value="hod">HOD</option>
                <option value="committee">Committee</option>
                <!-- <option value="admin">Admin</option> -->
            </select>
        </div>

        <button type="submit" name="add_user" class="btn-large">Create Account</button>
    </form>
    
    <div style="margin-top: 24px; font-size: 13px;">
        <a href="dashboard.php" style="color: #666; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

</body>
</html>
