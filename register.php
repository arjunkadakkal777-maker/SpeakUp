<?php
include "config.php";

$error = "";
$success = "";

if (isset($_SESSION['user'])) {
    if ($_SESSION['user']['role'] == 'student') header("Location: user/student.php");
    else header("Location: user/faculty.php"); // Generic fallback
    exit;
}

// Enable strict error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $branch = trim($_POST['branch']);
    // Checkbox is sent as 'on' if checked, or not sent if unchecked
    $is_anonymous = 0; // Default off, now per-grievance 

    // basic validation
    if (empty($username) || empty($email) || empty($password) || empty($branch)) {
        $error = "All fields are required.";
    } elseif (!preg_match("/^[a-zA-Z ]+$/", $username)) {
        $error = "Username must contain only alphabets.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        try {
            // Check if user exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
            $stmt->bind_param("ss", $email, $username);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $error = "Username or Email already exists.";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $role = 'student';
                $sec_q = NULL;
                $sec_a = NULL; 
                $phone = NULL;
                
                // Insert
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, branch, department, is_anonymous, security_question, security_answer, phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssisss", $username, $email, $hashed_password, $role, $branch, $branch, $is_anonymous, $sec_q, $sec_a, $phone);
                
                if ($stmt->execute()) {
                    $user_id = $conn->insert_id;
                    
                    // INSERT INTO SEPARATE REGISTRATION TABLE (As requested)
                    $reg_stmt = $conn->prepare("INSERT INTO student_registrations (user_id, username, email, branch) VALUES (?, ?, ?, ?)");
                    $reg_stmt->bind_param("isss", $user_id, $username, $email, $branch);
                    $reg_stmt->execute();

                    // SEND WELCOME EMAIL
                    $to = $email;
                    $subject = "Welcome to SpeakUp - Registration Successful";
                    
                    // HTML Email Headers
                    $headers  = "MIME-Version: 1.0" . "\r\n";
                    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
                    $headers .= "From: SpeakUp Admin <admin@speakup.local>" . "\r\n";
                    $headers .= "Reply-To: no-reply@speakup.local" . "\r\n";
                    
                    $body = "
                    <html>
                    <head>
                      <title>Welcome to SpeakUp</title>
                    </head>
                    <body>
                      <h3>Welcome, $username!</h3>
                      <p>Your registration with <strong>SpeakUp Grievance Portal</strong> was successful.</p>
                      <p><strong>Registration Details:</strong></p>
                      <ul>
                        <li>Username: $username</li>
                        <li>Branch: $branch</li>
                        <li>Email: $email</li>
                      </ul>
                      <p>You can now <a href='http://localhost/cgms/login.php'>login here</a> to report any grievances.</p>
                      <br>
                      <p>Best Regards,<br>SpeakUp Team</p>
                    </body>
                    </html>
                    ";
                    
                    // Attempt to send
                    @mail($to, $subject, $body, $headers);

                    // Redirect to login page
                    if (!headers_sent()) {
                        header("Location: login.php?created=1");
                    }
                    echo "<script>window.location.href='login.php?created=1';</script>";
                    exit;
                } else {
                    $error = "Registration failed: " . $conn->error;
                }
            }
        } catch (mysqli_sql_exception $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - SpeakUp</title>
    <!-- Use the existing CSS for consistency -->
    <link rel="stylesheet" href="css/catalog_style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Overrides for the centered auth layout */
        body {
            background-color: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .main-content {
            margin-left: 0;
            padding: 0;
            width: 100%;
            display: flex;
            justify-content: center;
        }
        .auth-card {
            background: white;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08); /* Premium shadow */
            width: 100%;
            max-width: 480px;
            border: 1px solid white;
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .auth-header h1 {
            font-size: 28px;
            margin: 0 0 10px 0;
            color: #1a1a1a;
        }
        .auth-header p {
            color: #666;
            margin: 0;
        }
        .logo-circle {
            width: 50px;
            height: 50px;
            background: #000;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        /* Checkbox styling */
        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 24px;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 12px;
        }
        .checkbox-group input {
            width: auto;
            margin: 0;
            margin-top: 3px;
        }
        .checkbox-group label {
            font-size: 14px;
            color: #333;
            line-height: 1.4;
            cursor: pointer;
        }
        .checkbox-info {
            font-size: 12px;
            color: #888;
            display: block;
            margin-top: 4px;
        }

        .alert-error {
            background: #ffe3e3;
            color: #c92a2a;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        .login-link a {
            color: #000;
            font-weight: 600;
            text-decoration: none;
        }
    </style>
</head>
<body>

    <div class="main-content">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo-circle" style="background: transparent;">
                    <img src="images/logo.png" alt="SpeakUp Logo" style="width: 100%; height: 100%; object-fit: contain; border-radius: 50%;">
                </div>
                <h1>Create Account</h1>
                <p>Join the student community</p>
            </div>

            <?php if($error): ?>
                <div class="alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div style="margin-bottom: 5px;">
                    <input type="text" name="username" placeholder="Username" required pattern="^[a-zA-Z ]+$" title="Only alphabets allowed." value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    <div style="font-size:11px; color:#888; margin: 4px 0 10px 4px;">Only alphabets allowed.</div>
                </div>
                
                <input type="email" name="email" placeholder="Student Email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                
                
                <input type="text" name="branch" placeholder="Branch (e.g., Computer Science)" required value="<?php echo isset($_POST['branch']) ? htmlspecialchars($_POST['branch']) : ''; ?>">

                <div style="margin-bottom: 5px; position: relative;">
                    <input type="password" id="password" name="password" placeholder="Password" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$!%*?&]).{8,}" title="Must contain at least one number, one uppercase, one lowercase, one special char, and at least 8 or more characters">
                    <i class="fa-solid fa-eye-slash" id="togglePassword" style="position: absolute; right: 15px; top: 15px; cursor: pointer; color: #666;"></i>
                    
                    <div style="font-size:11px; color:#666; margin: 4px 0 10px 4px; line-height: 1.4;">
                        Password must be at least 8 characters and contain:
                        <ul style="margin: 2px 0 0 15px; padding: 0;">
                            <li>1 uppercase letter (A–Z)</li>
                            <li>1 lowercase letter (a–z)</li>
                            <li>1 number (0–9)</li>
                            <li>1 special character (@#$!%*?&)</li>
                        </ul>
                    </div>
                </div>
                <div style="margin-bottom: 5px; position: relative;">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                    <i class="fa-solid fa-eye-slash" id="toggleConfirmPassword" style="position: absolute; right: 15px; top: 15px; cursor: pointer; color: #666;"></i>
                </div>
                
                <script>
                    const togglePassword = document.querySelector('#togglePassword');
                    const password = document.querySelector('#password');
                    
                    togglePassword.addEventListener('click', function (e) {
                        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                        password.setAttribute('type', type);
                        this.classList.toggle('fa-eye');
                        this.classList.toggle('fa-eye-slash');
                    });

                    const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
                    const confirmPassword = document.querySelector('#confirm_password');

                    toggleConfirmPassword.addEventListener('click', function (e) {
                         const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
                         confirmPassword.setAttribute('type', type);
                         this.classList.toggle('fa-eye');
                         this.classList.toggle('fa-eye-slash');
                    });
                </script>





                <button type="submit" class="btn-large" style="width: 100%;">Sign Up</button>
            </form>

            <div class="login-link">
                Already have an account? <a href="login.php">Log In</a>
            </div>
        </div>
    </div>

</body>
</html>
