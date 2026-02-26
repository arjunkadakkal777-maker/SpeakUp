<?php
include "../config.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = "";
$msg_type = "";

if (isset($_POST['import_faculty'])) {
    
    // Auto-fix schema constraints
    $conn->query("ALTER TABLE users MODIFY COLUMN role VARCHAR(50) NOT NULL DEFAULT 'student'");
    $conn->query("ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NOT NULL");
    $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS department VARCHAR(100)");
    
    // Ensure faculty_details table exists (just in case)
    $conn->query("CREATE TABLE IF NOT EXISTS faculty_details (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        faculty_id VARCHAR(50) NOT NULL UNIQUE,
        department VARCHAR(100) NOT NULL,
        email VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
        $file = $_FILES['csv_file']['tmp_name'];
                // Read entire file content to check encoding
                $content = file_get_contents($file);
                
                // Detect encoding (UTF-8, UTF-16, ISO-8859-1, Windows-1252)
                $encoding = mb_detect_encoding($content, "UTF-8, UTF-16LE, UTF-16BE, ISO-8859-1, Windows-1252", true);
                
                if ($encoding !== 'UTF-8') {
                    $content = mb_convert_encoding($content, 'UTF-8', $encoding);
                }
                
                // Save converted content to a temp file
                $temp_file = tempnam(sys_get_temp_dir(), 'utf8_import_');
                file_put_contents($temp_file, $content);
                
                // Open the new UTF-8 file
                $handle = fopen($temp_file, "r");

                if ($handle !== FALSE) {
                    $row = 0;
                    $updated_count = 0;
                    $inserted_count = 0;
                    $errors = [];
                    
                    $imported_names = [];
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        // Skip header row if it exists
                        if ($row == 0) {
                            $first_cell = strtolower($data[0] ?? '');
                            // Check for common header strings including the ones seen in the image
                            if (strpos($first_cell, 'serial') !== false || strpos($first_cell, 'no.') !== false || strpos($first_cell, 'ktu') !== false || strpos($first_cell, 'name') !== false) {
                                $row++;
                                continue;
                            }
                        }

                // Clean invisible characters/BOM from all data
                $data = array_map(function($text) {
                    return trim(preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $text));
                }, $data);

                // BASED ON USER IMAGE of bad data:
                // Col 0 seems to be Serial No
                // Col 1 seems to be Faculty ID
                // Col 2 seems to be Name
                // Col 3 seems to be Department
                
                // Set default values
                $serial = isset($data[0]) ? $data[0] : '';
                $raw_id = isset($data[1]) ? $data[1] : '';
                $raw_name = isset($data[2]) ? $data[2] : '';
                $raw_dept = isset($data[3]) ? $data[3] : '';

                // Improved Heuristic for Column Mapping
                
                // 1. Identify valid columns
                $candidates = [$serial, $raw_id, $raw_name, $raw_dept];
                
                // Find potential ID (alphanumeric, short)
                // Find potential Name (letters, spaces, longer)
                
                $mapped_name = '';
                $mapped_id = '';
                $mapped_dept = '';
                
                // Case A: 4 columns (Serial, ID, Name, Dept)
                // If col 0 is numeric and col 2 is text
                if (preg_match('/^[0-9]+$/', $serial) && !is_numeric($raw_name)) {
                     $mapped_id = $raw_id;
                     $mapped_name = $raw_name;
                     $mapped_dept = $raw_dept;
                }
                // Case B: 3 columns (Name, ID, Dept) or (ID, Name, Dept)
                else {
                    // Try to guess based on content
                    foreach($candidates as $c) {
                        if (empty($c)) continue;
                        
                        // If it matches patterns
                        if (empty($mapped_name) && preg_match('/[a-zA-Z]{3,}[ ]+[a-zA-Z]/', $c)) {
                            // Contains letters and space -> likely Name
                            $mapped_name = $c;
                        } elseif (empty($mapped_id) && preg_match('/^[A-Z0-9]+$/', $c) && strlen($c) > 3 && strlen($c) < 15) {
                            // Alphanumeric, short -> likely ID (e.g. KTE123)
                            $mapped_id = $c;
                        } elseif (empty($mapped_dept) && (stripos($c, 'Dept') !== false || stripos($c, 'Engineering') !== false)) {
                            $mapped_dept = $c;
                        }
                    }
                    
                    // Fallback if regex failed
                    if (empty($mapped_name)) {
                        // If col 0 is NOT numeric, assume it is name
                        if (!is_numeric($serial)) $mapped_name = $serial;
                        else if (!is_numeric($raw_id)) $mapped_name = $raw_id; // Col 1 is name?
                        else $mapped_name = $raw_name;
                    }
                    if (empty($mapped_id)) {
                        $mapped_id = $raw_id; // Default to col 1
                    }
                }
                
                $name = $mapped_name;
                $faculty_id = $mapped_id;
                $department = !empty($mapped_dept) ? $mapped_dept : 'General Dept';

                // Strip titles (Prof., Dr., etc.)
                $name = trim(str_replace(['Prof.', 'Prof ', 'Dr.', 'Dr '], '', $name));

                // Clean up data
                if ($name == "Name of the Faculty" || $faculty_id == "KTU Faculty ID") continue;

                if (!empty($name) && !empty($faculty_id)) {
                    
                    // 1. Insert/Update into faculty_details table
                    $stmt_fac = $conn->prepare("INSERT INTO faculty_details (name, faculty_id, department) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name), department = VALUES(department)");
                    $stmt_fac->bind_param("sss", $name, $faculty_id, $department);
                    $stmt_fac->execute();
                    $stmt_fac->close();

                    // 2. Sync to users table for Login (Username=Name, Password=ID)
                    // Hashed password must match exactly what user types
                    $hashed_password = password_hash($faculty_id, PASSWORD_DEFAULT);

                    // Check if user exists
                    $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
                    $check->bind_param("s", $name); // Username is Name
                    $check->execute();
                    $check->store_result();

                    if ($check->num_rows > 0) {
                        // Update existing user credentials
                        $stmt = $conn->prepare("UPDATE users SET password = ?, department = ?, role = 'faculty' WHERE username = ?");
                        $stmt->bind_param("sss", $hashed_password, $department, $name);
                        $stmt->execute();
                        $updated_count++;
                        $stmt->close();
                    } else {
                        // Create new login user
                        $stmt = $conn->prepare("INSERT INTO users (username, password, department, role) VALUES (?, ?, ?, 'faculty')");
                        $stmt->bind_param("sss", $name, $hashed_password, $department);
                        $stmt->execute();
                        $inserted_count++;
                        $stmt->close();
                    }
                    $check->close();
                    
                    if (count($imported_names) < 5) {
                        $imported_names[] = "User: <b>$name</b> | Pass: <b>$faculty_id</b>";
                    }
                } 
                $row++;
            }
            fclose($handle);
            
            $message = "Import successful! Faculty details updated.<br>Updated logins: $updated_count, New logins: $inserted_count.<br>";
            if (!empty($imported_names)) {
                $message .= "<strong>Sample Imported Users:</strong> " . implode(", ", $imported_names) . "...";
            }
            $msg_type = "success";
           
        } else {
            $message = "Error opening file.";
            $msg_type = "error";
        }
    } else {
        $message = "Please upload a valid CSV file.";
        $msg_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Faculty Department - Admin</title>
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
            max-width: 500px;
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
            margin-bottom: 20px;
            border: 2px dashed #ddd;
            padding: 30px;
            border-radius: 12px;
            background: #fafafa;
            transition: all 0.2s;
        }
        .form-group:hover {
            border-color: #aaa;
            background: #fdfdfd;
        }
        .form-group label {
            display: block;
            margin-bottom: 12px;
            font-weight: 600;
            font-size: 14px;
            color: #444;
            text-align: center;
        }
        
        input[type="file"] {
            width: 100%;
            padding: 10px;
            background: white;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
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
            font-size: 15px;
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

        .template-info {
            text-align: left;
            margin-top: 20px;
            padding: 15px;
            background: #e7f5ff;
            border-radius: 8px;
            font-size: 13px;
            color: #1971c2;
        }
    </style>
</head>
<body>

<div class="card-container">
    <div class="header-section">
        <div style="width: 50px; height: 50px; background: #e9ecef; color: #444; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 20px;">
            <i class="fa-solid fa-file-csv"></i>
        </div>
        <h2>Import Faculty Departments</h2>
        <p>Upload a CSV file to map faculty to their departments.</p>
        
    </div>

    <?php if (!empty($message)): ?>
        <div class="feedback-msg <?php echo $msg_type; ?>">
            <i class="fa-solid <?php echo $msg_type == 'success' ? 'fa-check-circle' : 'fa-triangle-exclamation'; ?>"></i> 
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label><i class="fa-solid fa-cloud-arrow-up" style="font-size: 24px; display: block; margin-bottom: 10px; color: #888;"></i> Choose CSV File</label>
            <input type="file" name="csv_file" accept=".csv" required>
        </div>

        <button type="submit" name="import_faculty" class="btn-large">Upload & Import</button>
    </form>

    
    <div style="margin-top: 24px; font-size: 13px;">
        <a href="dashboard.php" style="color: #666; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

</body>
</html>
