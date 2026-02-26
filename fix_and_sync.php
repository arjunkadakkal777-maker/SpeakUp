<?php
include 'config.php';

echo "<h2>Fixing Faculty Data & Syncing Logins...</h2>";

// 1. Fix corrupted data (Column Shifting Issue)
// Problem: Name contains "1" (Serial), Dept contains "Prof. Name" (Name)
$fix_sql = "UPDATE faculty_details 
            SET name = department, department = 'General/Unknown' 
            WHERE name REGEXP '^[0-9]+$'";

if ($conn->query($fix_sql) === TRUE) {
    echo "<li>Fixed column shifting in faculty_details (Rows affected: " . $conn->affected_rows . ")</li>";
} else {
    echo "<li>Error fixing data: " . $conn->error . "</li>";
}

// 2. Clean up "Header" rows that got imported as data
$conn->query("DELETE FROM faculty_details WHERE name LIKE '%Name%' OR faculty_id LIKE '%ID%'");
echo "<li>Removed header rows from data.</li>";

// 3. Sync to Users Table (Create Logins)
echo "<h3>Syncing to Users Table...</h3>";
$result = $conn->query("SELECT * FROM faculty_details");
$count = 0;

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $name = trim($row['name']);
        $fac_id = trim($row['faculty_id']);
        $dept = trim($row['department']);
        
        if (empty($name) || empty($fac_id)) continue;

        // Password is ID
        $hashed_pass = password_hash($fac_id, PASSWORD_DEFAULT);

        // Check if user exists
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $name);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            // Update
            $u = $conn->prepare("UPDATE users SET password = ?, department = ?, role = 'faculty' WHERE username = ?");
            $u->bind_param("sss", $hashed_pass, $dept, $name);
            $u->execute();
        } else {
            // Insert
            $i = $conn->prepare("INSERT INTO users (username, password, department, role) VALUES (?, ?, ?, 'faculty')");
            $i->bind_param("sss", $name, $hashed_pass, $dept);
            $i->execute();
        }
        $count++;
    }
}

echo "<li><strong>Success!</strong> valid logins synced for $count faculty members.</li>";
echo "<br><a href='admin/import_faculty.php'>Return to Import Page</a>";
?>
