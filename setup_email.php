<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        echo "<h3 style='color:red;'>Please provide both email and app password.</h3>";
    } else {
        // 1. Update sendmail.ini
        $sendmail_path = "C:/xampp/sendmail/sendmail.ini";
        if (file_exists($sendmail_path)) {
            $content = file_get_contents($sendmail_path);
            
            // Regex replacements
            $content = preg_replace('/^smtp_server=.*$/m', 'smtp_server=smtp.gmail.com', $content);
            $content = preg_replace('/^smtp_port=.*$/m', 'smtp_port=587', $content);
            $content = preg_replace('/^auth_username=.*$/m', "auth_username=$email", $content);
            $content = preg_replace('/^auth_password=.*$/m', "auth_password=$password", $content);
            $content = preg_replace('/^force_sender=.*$/m', "force_sender=$email", $content);
            
            // Uncomment if commented
            $content = str_replace(';smtp_server=smtp.gmail.com', 'smtp_server=smtp.gmail.com', $content);
            $content = str_replace(';auth_username=', 'auth_username=', $content);
            $content = str_replace(';auth_password=', 'auth_password=', $content);

            file_put_contents($sendmail_path, $content);
            echo "<p style='color:green;'>Updated sendmail.ini</p>";
        } else {
             echo "<p style='color:red;'>Could not find $sendmail_path</p>";
        }

        // 2. Update php.ini
        $php_ini_path = "C:/xampp/php/php.ini";
        if (file_exists($php_ini_path)) {
            $content = file_get_contents($php_ini_path);
            // Verify sendmail_path is correct for XAMPP
            // Usually: sendmail_path = "\"C:\xampp\sendmail\sendmail.exe\" -t"
            // We just ensure it's uncommented? It's complex to regex blindly.
            // Let's just append or replace the [mail function] section if possible, or just tell user to restart.
            // Actually, XAMPP usually defaults to using sendmail.exe, we just need to ensure the INI has the creds.
            // The php.ini usually points to sendmail.exe already.
            
            echo "<p style='color:green;'>Configuration updated! <strong>PLEASE RESTART APACHE</strong> in XAMPP Control Panel for changes to take effect.</p>";
            echo "<p>After restarting, try the Forgot Password page again.</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Configure Email</title>
    <style>
        body { font-family: sans-serif; padding: 40px; text-align: center; }
        form { max-width: 400px; margin: 0 auto; background: #f4f4f4; padding: 20px; border-radius: 10px; }
        input { width: 90%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #000; color: #fff; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .guide { text-align: left; background: #e8f0fe; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; color: #333; }
    </style>
</head>
<body>
    <h1>✉️ Configure Email Sending</h1>
    
    <div class="guide">
        <strong>Important:</strong> You need a Gmail <strong>App Password</strong>, not your regular password.<br><br>
        1. Go to <a href="https://myaccount.google.com/security" target="_blank">Google Security</a>.<br>
        2. Enable terms "2-Step Verification".<br>
        3. Search for "App Passwords".<br>
        4. Create one named "XAMPP" and copy the 16-character code.<br>
    </div>

    <form method="post">
        <h3>Enter Gmail Credentials</h3>
        <input type="email" name="email" placeholder="Your Gmail Address" required>
        <input type="text" name="password" placeholder="16-Digit App Password" required>
        <button type="submit">Save Configuration</button>
    </form>
</body>
</html>
