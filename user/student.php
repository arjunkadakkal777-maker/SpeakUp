<?php

include "../config.php";
$name=$_SESSION['username']??'Student';

/* ---------- AUTH CHECK ---------- */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'student') {
    header("Location: ../login.php");
    exit;
}

/* ---------- PAGE HANDLING ---------- */
$page = $_GET['page'] ?? 'dashboard';
$message = "";

/* ---------- SIMPLE AI CATEGORIZATION ---------- */
function categorize($text) {
    $text = strtolower($text);

    if (preg_match("/hostel|room|water|food|bathroom/", $text)) {
        $category = "Hostel";
    } elseif (preg_match("/exam|marks|result|internal/", $text)) {
        $category = "Academic";
    } else {
        $category = "General";
    }

    if (preg_match("/urgent|emergency|harassment|ragging/", $text)) {
        $priority = "High";
    } elseif (preg_match("/delay|issue/", $text)) {
        $priority = "Medium";
    } else {
        $priority = "Low";
    }

    return [$category, $priority];
}

/* ---------- SUBMIT GRIEVANCE ---------- */
if (isset($_POST['submit_grievance'])) {

    $title = trim($_POST['title']);
    $desc  = trim($_POST['description']);
    $branch = $_POST['branch'];
    $semester = $_POST['semester'];
    $incident_date = $_POST['incident_date'];
    $location = trim($_POST['location']);
    $sid   = $_SESSION['user']['id'];

    list($category, $priority) = categorize($title . " " . $desc);

    /* FILE UPLOAD */
    $attachment = NULL;

    if (!empty($_FILES['attachment']['name'])) {

        $allowed = ['jpg','jpeg','png','pdf','doc','docx'];
        $fileName = $_FILES['attachment']['name'];
        $fileTmp  = $_FILES['attachment']['tmp_name'];
        $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileExt, $allowed)) {

            $newName = time() . "_" . uniqid() . "." . $fileExt;
            $uploadPath = "../uploads/grievances/" . $newName;

            if (move_uploaded_file($fileTmp, $uploadPath)) {
                $attachment = $newName;
            }
        }
    }

    $stmt = $conn->prepare(
        "INSERT INTO grievances 
        (student_id, title, description, category, priority, attachment, branch, semester, incident_date, location)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "isssssssss",
        $sid,
        $title,
        $desc,
        $category,
        $priority,
        $attachment,
        $branch,
        $semester,
        $incident_date,
        $location
    );

    $stmt->execute();

    $message = "Grievance submitted successfully (Category: $category | Priority: $priority)";
}


/* ---------- CHANGE PASSWORD ---------- */
if (isset($_POST['change_password'])) {
    $new = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $id  = $_SESSION['user']['id'];

    $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
    $stmt->bind_param("si", $new, $id);
    $stmt->execute();

    $message = "Password changed successfully";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../css/catalog_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="logo">
            <img src="../images/logo.png" alt="Logo" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;">
            SpeakUp
        </div>

        <div class="menu-category">Menu</div>
        <a href="student.php?page=dashboard" class="menu-item <?php echo $page=='dashboard'?'active':''; ?>">
            <div class="menu-icon icon-pink"><i class="fa-solid fa-house"></i></div>
            Dashboard
        </a>
        <a href="student.php?page=grievance" class="menu-item <?php echo $page=='grievance'?'active':''; ?>">
            <div class="menu-icon icon-orange"><i class="fa-solid fa-plus"></i></div>
            New Grievance
        </a>
        <a href="#" class="menu-item">
            <div class="menu-icon icon-blue"><i class="fa-solid fa-list"></i></div>
            My History
        </a>

        <div class="menu-category">Settings</div>
        <a href="view_profile.php" class="menu-item">
            <div class="menu-icon icon-purple"><i class="fa-solid fa-user"></i></div>
            Profile
        </a>
        <a href="student.php?page=password" class="menu-item <?php echo $page=='password'?'active':''; ?>">
            <div class="menu-icon icon-green"><i class="fa-solid fa-lock"></i></div>
            Password
        </a>
        
        <div class="menu-category">Session</div>
        <a href="../logout.php" class="menu-item">
            <div class="menu-icon" style="background:#eee; color:#333;"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
            Logout
        </a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        
        <?php if ($message): ?>
            <div style="padding: 15px; margin-bottom: 30px; background: #e0ffe0; color: #006600; border-radius: 8px;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($page == 'dashboard'): ?>
        
            <!-- HERO SECTION -->
            <div class="hero-section">
                <div class="hero-text">
                    <h1>Student Portal<br>Dashboard</h1>
                    <p>Welcome back, <strong><?php echo htmlspecialchars($name); ?></strong>. Track your campus activities, manage grievances, and stay updated.</p>
                    <button class="subscribe-btn" onclick="location.href='student.php?page=grievance'">Submit Issue</button>
                </div>
                <!-- Hero Advertisement / Feature Card Style -->
                <div class="hero-card">
                    <div style="flex:1;">
                        <h3>Quick Stats</h3>
                        <p style="margin:0; color:#666;">You have 0 active issues.</p>
                    </div>
                     <i class="fa-solid fa-chart-pie" style="font-size:60px; color: #ddd;"></i>
                </div>
            </div>

            <!-- FILTERS -->
            <div class="filter-bar">
                <div class="filter-chip">Status <i class="fa-solid fa-chevron-down" style="font-size:10px;"></i></div>
                <div class="filter-chip">Category <i class="fa-solid fa-chevron-down" style="font-size:10px;"></i></div>
                <div class="filter-chip">Recent</div>
            </div>

            <!-- GRID OF CARDS -->
            <div class="grid-container">
                
                <!-- Card 1 -->
                <a href="student.php?page=grievance" class="card-item">
                    <div class="card-preview" style="background:#ffecec; color:#ff6b6b;">
                        <i class="fa-solid fa-circle-exclamation"></i>
                    </div>
                    <div class="card-title">Report Issue</div>
                    <div class="card-meta">
                        <span class="card-author">Grievance</span>
                        <i class="fa-solid fa-arrow-right" style="font-size:12px;"></i>
                    </div>
                </a>

                <!-- Card 2 -->
                <a href="view_profile.php" class="card-item">
                    <div class="card-preview" style="background:#ecf0ff; color:#5c7cfa;">
                        <i class="fa-solid fa-id-card"></i>
                    </div>
                    <div class="card-title">My Profile</div>
                    <div class="card-meta">
                        <span class="card-author">Account</span>
                        <i class="fa-solid fa-arrow-right" style="font-size:12px;"></i>
                    </div>
                </a>

                <!-- Card 3 -->
               <!--<a href="#" class="card-item">
                    <div class="card-preview" style="background:#f3ffe3; color:#51cf66;">
                        <i class="fa-solid fa-bullhorn"></i>
                    </div>
                    <div class="card-title">Notices</div>
                    <div class="card-meta">
                        <span class="card-author">Campus</span>
                        <i class="fa-solid fa-arrow-right" style="font-size:12px;"></i>
                    </div>
                </a>-->

            </div>

        <?php elseif ($page == 'grievance'): ?>

            <div class="hero-section">
                <div class="hero-text">
                    <h1>Register<br>Complaint</h1>
                    <p>Describe your issue in detail. Our automated system will categorize and prioritize it for you.</p>
                </div>
            </div>

            <div class="form-container">
                <form method="post" enctype="multipart/form-data">
                    <label style="display:block; margin-bottom:8px; font-weight:500;">Title of Issue</label>
                    <input type="text" name="title" placeholder="e.g. Water shortage in Hostel B" required>

                    <label style="display:block; margin-bottom:8px; font-weight:500;">Branch</label>
                    <select name="branch" required>
                        <option value="">Select Branch</option>
                        <option value="IT">IT</option>
                        <option value="CSE">CSE</option>
                        <option value="ME">ME</option>
                        <option value="ECE">ECE</option>
                        <option value="EEE">EEE</option>
                        <option value="Robotics">Robotics</option>
                    </select>

                    <label style="display:block; margin-bottom:8px; font-weight:500;">Semester</label>
                    <select name="semester" required>
                        <option value="">Select Semester</option>
                        <option value="S1">S1</option>
                        <option value="S2">S2</option>
                        <option value="S3">S3</option>
                        <option value="S4">S4</option>
                        <option value="S5">S5</option>
                        <option value="S6">S6</option>
                        <option value="S7">S7</option>
                        <option value="S8">S8</option>
                    </select>

                    <label style="display:block; margin-bottom:8px; font-weight:500;">Date of Incident</label>
                    <input type="date" name="incident_date" required>

                    <label style="display:block; margin-bottom:8px; font-weight:500;">Location</label>
                    <input type="text" name="location" placeholder="e.g. Near Library, Hostel Block A" required>

                    <label style="display:block; margin-bottom:8px; font-weight:500;">Detailed Description</label>
                    <textarea name="description" placeholder="Provide as much detail as possible..." rows="6" required></textarea>

                    <label style="display:block; margin-bottom:8px; font-weight:500;">Proof / Attachment</label>
                    <input type="file" name="attachment">

                    <button type="submit" name="submit_grievance" class="btn-large">Submit Report</button>
                    <p style="font-size:12px; color:#999; margin-top:10px;">* Supports PDF, DOC, JPG. Max 5MB.</p>
                </form>
            </div>

        <?php elseif ($page == 'password'): ?>

            <div class="hero-section">
                <div class="hero-text">
                    <h1>Security<br>Settings</h1>
                    <p>Ensure your account remains safe by updating your password regularly.</p>
                </div>
            </div>

            <div class="form-container">
                <form method="post">
                    <label style="display:block; margin-bottom:8px; font-weight:500;">New Password</label>
                    <input type="password" name="new_password" placeholder="Enter strong password" required>
                    <button type="submit" name="change_password" class="btn-large">Update</button>
                </form>
            </div>

        <?php endif; ?>

    </div>

</body>
</html>
