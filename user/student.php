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

// /* ---------- SIMPLE AI CATEGORIZATION ---------- */
// function categorize($text) {
//     $text = strtolower($text);

//     if (preg_match("/hostel|room|water|food|bathroom/", $text)) {
//         $category = "Hostel";
//     } elseif (preg_match("/exam|marks|result|internal/", $text)) {
//         $category = "Academic";
//     } else {
//         $category = "General";
//     }

//     if (preg_match("/urgent|emergency|harassment|ragging/", $text)) {
//         $priority = "High";
//     } elseif (preg_match("/delay|issue/", $text)) {
//         $priority = "Medium";
//     } else {
//         $priority = "Low";
//     }

//     return [$category, $priority];
// }

    /* ---------- SUBMIT GRIEVANCE ---------- */
    if (isset($_POST['submit_grievance'])) {

        $title = trim($_POST['title']);
        $desc  = trim($_POST['description']);
        $branch = $_POST['branch'];
        $semester = $_POST['semester'];
        $incident_date = $_POST['incident_date'];
        $location = trim($_POST['location']);
        $faculty_id = !empty($_POST['faculty_id']) ? $_POST['faculty_id'] : NULL;
        $category = $_POST['category']; // Manual selection
        $sid   = $_SESSION['user']['id'];

        // Default priority, can be updated by admin later
        $priority = !empty($_POST['priority']) ? $_POST['priority'] : "Medium";
        $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;

        /* FILE UPLOAD (Multiple) */
        $attachments = [];

        if (!empty($_FILES['attachment']['name'][0])) {
            $allowed = ['jpg','jpeg','png','pdf','doc','docx'];
            
            // Loop through each file
            foreach($_FILES['attachment']['name'] as $key => $name) {
                if (empty($name)) continue;
                
                $fileName = $name;
                $fileTmp  = $_FILES['attachment']['tmp_name'][$key];
                $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if (in_array($fileExt, $allowed)) {
                    $newName = time() . "_" . uniqid() . "." . $fileExt;
                    $uploadPath = "../uploads/grievances/" . $newName;

                    // Ensure directory exists
                    if (!file_exists("../uploads/grievances/")) {
                        mkdir("../uploads/grievances/", 0777, true);
                    }

                    if (move_uploaded_file($fileTmp, $uploadPath)) {
                        $attachments[] = $newName;
                    }
                }
            }
        }
        
        // Store as JSON string, e.g. ["img1.jpg", "img2.pdf"]
        // If the DB column is NOT setup for JSON, you might need to change it or use comma separation.
        // Assuming current column allows text.
        $attachment = !empty($attachments) ? json_encode($attachments) : NULL;

        $stmt = $conn->prepare(
            "INSERT INTO grievances 
            (student_id, title, description, category, priority, attachment, branch, semester, incident_date, location, faculty_id, is_anonymous)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "isssssssssii",
            $sid,
            $title,
            $desc,
            $category,
            $priority,
            $attachment,
            $branch,
            $semester,
            $incident_date,
            $location,
            $faculty_id,
            $is_anonymous
        );

        if ($stmt->execute()) {
             $message = "Grievance has been submitted successfully";
        } else {
             $message = "Error submitting grievance: " . $stmt->error;
        }
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
        <a href="student.php?page=history" class="menu-item <?php echo $page=='history'?'active':''; ?>">
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
        <a href="#" class="menu-item" onclick="confirmLogout(event)">
            <div class="menu-icon" style="background:#eee; color:#333;"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
            Logout
        </a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        
        <?php include "notifications_partial.php"; ?>

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
                    <p>Describe your issue in detail.</p>
                </div>
            </div>

            <div class="form-container">
                <form method="post" enctype="multipart/form-data">
                    <label style="display:block; margin-bottom:8px; font-weight:500;">Title of Issue <span style="color:red">*</span></label>
                    <input type="text" name="title" placeholder="e.g. Water shortage in Hostel B" required>

                    <label style="display:block; margin-bottom:8px; font-weight:500;">Category <span style="color:red">*</span></label>
                    <select name="category" id="category" required>
                        <option value="">Select Category</option>
                        <option value="Hostel">Hostel</option>
                        <option value="Academic">Academic</option>
                        <option value="Infrastructure">Infrastructure</option>
                        <option value="Cafeteria">Cafeteria</option>
                        <option value="Other">Other</option>
                    </select>

                    <label style="display:block; margin-bottom:8px; font-weight:500;">Priority Level <span style="color:red">*</span></label>
                    <div style="font-size:12px; color:#666; margin-bottom:6px;">Select 'High' only for urgent/safety issues.</div>
                    <select name="priority" required>
                        <option value="Low">Low (General Query)</option>
                        <option value="Medium" selected>Medium (Standard Issue)</option>
                        <option value="High">High (Urgent/Safety)</option>
                    </select>

                    <label style="display:block; margin-bottom:8px; font-weight:500;">Branch <span style="color:red">*</span></label>
                    <select name="branch" required>
                        <option value="">Select Branch</option>
                        <option value="IT">IT</option>
                        <option value="CSE">CSE</option>
                        <option value="ME">ME</option>
                        <option value="ECE">ECE</option>
                        <option value="EEE">EEE</option>
                        <option value="Robotics">Robotics</option>
                    </select>

                    <div id="faculty_section">
                        <label style="display:block; margin-bottom:8px; font-weight:500;">Search Faculty <span style="color:red">*</span></label>
                        <div style="font-size:12px; color:#666; margin-bottom:6px;">All grievances must be assigned to a faculty mentor for initial review.</div>
                        <div style="position: relative;">
                            <input type="text" id="faculty_search" autocomplete="off" placeholder="Type first letter of faculty name..." onkeyup="searchFaculty()" required>
                            <input type="hidden" name="faculty_id" id="faculty_id" required>
                            <div id="faculty_list" style="position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; max-height: 200px; overflow-y: auto; z-index: 1000; display: none; border-radius: 0 0 8px 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></div>
                        </div>
                    </div>

                    <script>
                        // toggleFaculty removed as per request to make faculty mandatory for all categories
                        // function toggleFaculty() { ... }

                        function searchFaculty() {
                            let term = document.getElementById("faculty_search").value;
                            if (term.length < 1) {
                                document.getElementById("faculty_list").style.display = "none";
                                return;
                            }

                            fetch("search_faculty.php?term=" + term)
                            .then(response => response.json())
                            .then(data => {
                                let list = document.getElementById("faculty_list");
                                list.innerHTML = "";
                                if (data.length > 0) {
                                    list.style.display = "block";
                                    data.forEach(fac => {
                                        let item = document.createElement("div");
                                        item.style.padding = "10px";
                                        item.style.cursor = "pointer";
                                        item.style.borderBottom = "1px solid #eee";
                                        item.style.fontSize = "14px";
                                        item.innerText = fac.text;
                                        
                                        // Hover effect
                                        item.onmouseover = function() { this.style.backgroundColor = "#f0f0f0"; };
                                        item.onmouseout = function() { this.style.backgroundColor = "white"; };
                                        
                                        // Click handler
                                        item.onclick = function() {
                                            document.getElementById("faculty_search").value = fac.text;
                                            document.getElementById("faculty_id").value = fac.id;
                                            list.style.display = "none";
                                        };
                                        list.appendChild(item);
                                    });
                                } else {
                                    list.style.display = "none";
                                }
                            });
                        }
                        
                        // Close dropdown when clicking outside
                        document.addEventListener('click', function(e) {
                            if (e.target.id !== 'faculty_search') {
                                document.getElementById('faculty_list').style.display = 'none';
                            }
                        });
                    </script>

                    <label style="display:block; margin-bottom:8px; font-weight:500;">Semester <span style="color:red">*</span></label>
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

                    <label style="display:block; margin-bottom:8px; font-weight:500;">Date of Incident <span style="color:red">*</span></label>
                    <input type="date" name="incident_date" required value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>">

                    <label style="display:block; margin-bottom:8px; font-weight:500;">Location / Room No</label>
                    <input type="text" name="location" id="location" placeholder="e.g. Near Library, Hostel Block A, Room 101">

                    <label style="display:block; margin-bottom:8px; font-weight:500;">Detailed Description <span style="color:red">*</span></label>
                    <textarea name="description" placeholder="Provide as much detail as possible..." rows="6" required></textarea>

                    <label style="display:block; margin-bottom:8px; font-weight:500;">Proof / Attachment</label>
                    <input type="file" name="attachment[]" multiple>
                    
                    <div style="margin-top: 20px; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 8px;">
                        <input type="checkbox" id="is_anonymous" name="is_anonymous" style="width: auto; margin-top: 3px;">
                        <div>
                            <label for="is_anonymous" style="font-weight: 600; cursor: pointer;">Post Anonymously</label>
                            <div style="font-size: 12px; color: #666;">Your name will be hidden from faculty and wardens.</div>
                        </div>
                    </div>

                    <button type="submit" name="submit_grievance" class="btn-large" onclick="return validateFaculty()">Submit Report</button>
                    <p style="font-size:12px; color:#999; margin-top:10px;">* Supports PDF, DOC, JPG. Max 5MB.</p>
                    
                    <script>
                        function validateFaculty() {
                            // Validate Faculty
                            var facId = document.getElementById('faculty_id').value;
                            var facSearch = document.getElementById('faculty_search').value;
                            
                            // If user typed something but didn't select from dropdown resulting in empty ID
                            if (facSearch.trim() !== "" && facId === "") {
                                alert("Please select a faculty member from the appearing dropdown list.");
                                return false;
                            }
                            if (facId === "") {
                                alert("Please select a faculty member (Required for all grievances).");
                                return false;
                            }

                            // Validate Hostel Location / Room No
                            var cat = document.getElementById('category').value;
                            var isAnon = document.getElementById('is_anonymous').checked;
                            var loc = document.getElementById('location').value.trim();

                            if (cat === 'Hostel' && !isAnon && loc === "") {
                                alert("For Hostel complaints, providing the Room Number (in Location field) is mandatory unless submitting anonymously.");
                                return false;
                            }

                            return true;
                        }
                    </script>
                </form>
            </div>

        <?php elseif ($page == 'history'): ?>
            
            <div class="hero-section">
                <div class="hero-text">
                    <h1>My<br>Submission History</h1>
                    <p>Track the status and feedback of your reported issues.</p>
                </div>
            </div>

            <div style="background: white; border-radius: 16px; border: 1px solid #eee; overflow: hidden;">
                <?php
                $sid = $_SESSION['user']['id'];
                $hist_q = $conn->query("SELECT * FROM grievances WHERE student_id=$sid ORDER BY incident_date DESC");
                
                if ($hist_q->num_rows > 0):
                ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f9f9f9; border-bottom: 1px solid #eee;">
                            <th style="text-align: left; padding: 15px; font-size: 13px; color: #666;">ID</th>
                            <th style="text-align: left; padding: 15px; font-size: 13px; color: #666;">Title</th>
                            <th style="text-align: left; padding: 15px; font-size: 13px; color: #666;">Date</th>
                            <th style="text-align: left; padding: 15px; font-size: 13px; color: #666;">Status</th>
                            <th style="text-align: left; padding: 15px; font-size: 13px; color: #666;">Faculty Feedback</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $hist_q->fetch_assoc()): 
                             $status_badge = "background:#eee; color:#333;";
                             if($row['status']=='Open') $status_badge = "background:#ffe3e3; color:#c92a2a;";
                             if($row['status']=='In Progress') $status_badge = "background:#fff3bf; color:#f08c00;";
                             if($row['status']=='Resolved') $status_badge = "background:#d3f9d8; color:#2b8a3e;";
                             if($row['status']=='Escalated') $status_badge = "background:#ffc9c9; color:#e03131;";
                        ?>
                        <tr style="border-bottom: 1px solid #f0f0f0;">
                            <td style="padding: 15px; font-size: 14px;">
                                <a href="student.php?page=view_status&id=<?php echo $row['id']; ?>" style="color: #000; text-decoration: underline;">#<?php echo $row['id']; ?></a>
                            </td>
                            <td style="padding: 15px; font-size: 14px; font-weight: 500;">
                                <a href="student.php?page=view_status&id=<?php echo $row['id']; ?>" style="color: inherit; text-decoration: none;"><?php echo htmlspecialchars($row['title']); ?></a>
                            </td>
                            <td style="padding: 15px; font-size: 13px; color: #666;"><?php echo date("M j, Y", strtotime($row['incident_date'])); ?></td>
                            <td style="padding: 15px;">
                                <span style="padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; text-transform: uppercase; <?php echo $status_badge; ?>">
                                    <?php echo $row['status'] ? $row['status'] : 'Open'; ?>
                                </span>
                            </td>
                            <td style="padding: 15px; font-size: 13px;">
                                <?php 
                                if (!empty($row['feedback_text'])) {
                                    echo '<div style="background: #f8f9fa; padding: 8px; border-radius: 6px; border-left: 3px solid #000;">'.htmlspecialchars($row['feedback_text']).'</div>';
                                } else {
                                    echo '<span style="color: #bbb;">No feedback yet</span>';
                                }
                                ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div style="padding: 40px; text-align: center; color: #999;">
                        No grievances found.
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($page == 'view_status'): ?>

            <?php
            $gid = $_GET['id'] ?? 0;
            $sid = $_SESSION['user']['id'];
            $stmt = $conn->prepare("SELECT * FROM grievances WHERE id=? AND student_id=?");
            $stmt->bind_param("ii", $gid, $sid);
            $stmt->execute();
            $res = $stmt->get_result();
            
            if ($row = $res->fetch_assoc()):
                 // Determine status color/icon
                 $status_color = "#333";
                 $status_bg = "#eee";
                 $status_icon = "fa-circle";
                 
                 if($row['status']=='Open') { 
                     $status_color = "#c92a2a"; $status_bg = "#ffe3e3"; $status_icon = "fa-circle-exclamation";
                 }
                 if($row['status']=='In Progress') {
                     $status_color = "#f08c00"; $status_bg = "#fff3bf"; $status_icon = "fa-spinner fa-spin";
                 }
                 if($row['status']=='Resolved') {
                     $status_color = "#2b8a3e"; $status_bg = "#d3f9d8"; $status_icon = "fa-check-circle";
                 }
                 if($row['status']=='Escalated') {
                     $status_color = "#e03131"; $status_bg = "#ffc9c9"; $status_icon = "fa-arrow-trend-up";
                 }
            ?>
            
             <div class="hero-section">
                <div class="hero-text">
                    <a href="student.php?page=history" style="text-decoration: none; color: #666; font-size: 14px; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 15px;">
                        <i class="fa-solid fa-arrow-left"></i> Back to History
                    </a>
                    <h1>Grievance #<?php echo $row['id']; ?></h1>
                    
                    <div style="margin-top: 10px;">
                        <span style="display:inline-flex; align-items:center; gap:8px; padding:8px 16px; background:<?php echo $status_bg; ?>; color:<?php echo $status_color; ?>; border-radius:20px; font-weight:600; font-size:14px; border: 1px solid <?php echo $status_color; ?>20;">
                            <i class="fa-solid <?php echo $status_icon; ?>"></i> <?php echo $row['status'] ? $row['status'] : 'Open'; ?>
                        </span>
                    </div>
                </div>
            </div>

            <div style="background: white; border-radius: 16px; border: 1px solid #eee; padding: 30px; margin-bottom: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                
                <div style="border-bottom: 1px solid #f0f0f0; padding-bottom: 20px; margin-bottom: 20px;">
                    <h2 style="font-size: 24px; margin-bottom: 10px; color: #333;"><?php echo htmlspecialchars($row['title']); ?></h2>
                    <div style="color: #666; font-size: 14px;">
                        <i class="fa-regular fa-clock"></i> Submitted on <?php echo date("F j, Y, g:i a", strtotime($row['created_at'])); ?> 
                        &bull; <i class="fa-solid fa-tag"></i> <?php echo htmlspecialchars($row['category']); ?>
                        &bull; <i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($row['location'] ?? 'N/A'); ?>
                    </div>
                </div>
                
                <div style="margin-bottom: 30px;">
                    <label style="display: block; color: #333; font-weight: 600; font-size: 14px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px;">Detailed Description</label>
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; line-height: 1.7; color: #444; font-size: 15px; border: 1px solid #eee;">
                        <?php echo nl2br(htmlspecialchars($row['description'])); ?>
                    </div>
                </div>

                <?php if (!empty($row['feedback_text'])): ?>
                <div style="margin-bottom: 30px; border-left: 5px solid <?php echo $status_color; ?>; background: <?php echo $status_bg; ?>30; padding: 20px; border-radius: 0 8px 8px 0;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                        <i class="fa-solid fa-comment-dots" style="color: <?php echo $status_color; ?>;"></i>
                        <label style="color: <?php echo $status_color; ?>; font-size: 14px; font-weight: 700; text-transform: uppercase;">Authority Feedback</label>
                    </div>
                    <div style="color: #222; line-height: 1.6; font-size: 15px; font-weight: 500;">
                        <?php echo nl2br(htmlspecialchars($row['feedback_text'])); ?>
                    </div>
                </div>
                <?php endif; ?>

                 <?php if($row['attachment']): ?>
                 <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
                     <label style="display: block; color: #333; font-weight: 600; font-size: 14px; margin-bottom: 10px;">Supporting Documents</label>
                     
                     <?php 
                        // Handle multiple attachments if stored as JSON or singular
                        $att = $row['attachment'];
                        $files = [];
                        // Check if JSON
                        if (strpos($att, '[') === 0) {
                            $decoded = json_decode($att, true);
                            if (is_array($decoded)) $files = $decoded;
                        } else {
                            $files = [$att];
                        }
                        
                        foreach($files as $f):
                            if(empty($f)) continue;
                     ?>
                     <a href="../uploads/grievances/<?php echo $f; ?>" target="_blank" style="text-decoration: none; display: inline-flex; align-items: center; gap: 10px; background: white; border: 1px solid #ddd; padding: 10px 20px; border-radius: 8px; color: #333; font-weight: 500; margin-right: 10px; transition: all 0.2s;">
                        <i class="fa-solid fa-paperclip" style="color: #666;"></i> 
                        <?php echo (strlen($f) > 20) ? substr($f, 0, 15) . '...' . pathinfo($f, PATHINFO_EXTENSION) : $f; ?>
                        <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 12px; color: #999;"></i>
                     </a>
                     <?php endforeach; ?>
                 </div>
                 <?php endif; ?>

            </div>

            <?php else: ?>
                <div style="padding: 60px; text-align: center; background: white; border-radius: 16px; border: 1px solid #eee;">
                    <i class="fa-solid fa-magnifying-glass" style="font-size: 40px; color: #ddd; margin-bottom: 20px;"></i>
                    <h2 style="color: #666;">Grievance Not Found</h2>
                    <p style="color: #999; margin-bottom: 20px;">The grievance you are looking for does not exist or you don't have permission to view it.</p>
                    <a href="student.php?page=history" class="btn-large" style="display: inline-block; width: auto; text-decoration: none;">Back to History</a>
                </div>
            <?php endif; ?>

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

<?php include 'logout_modal.php'; ?>
<?php include 'chatbot_partial.php'; ?>
</body>
</html>
