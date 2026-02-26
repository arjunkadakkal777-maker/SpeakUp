<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "../config.php";

// Simulate Session for CLI Debug
if(php_sapi_name() === 'cli' && !isset($_SESSION['user'])) {
    $_SESSION['user'] = ['role' => 'faculty', 'id' => 9]; // Adjust ID based on debug_struct output if needed
    $_GET['id'] = 1; // Assume ID 1 exists
}

echo "Starting Debug...\n";

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] != 'faculty' && $_SESSION['user']['role'] != 'warden' && $_SESSION['user']['role'] != 'hod')) {
    die("Redirect to login");
}

$id = $_GET['id'] ?? 0;
echo "Fetching ID: $id\n";

$stmt = $conn->prepare("SELECT * FROM grievances WHERE id=?");
if(!$stmt) die("Prepare failed: " . $conn->error);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$grievance = $result->fetch_assoc();

if (!$grievance) {
    die("Grievance not found.");
}
print_r($grievance);

echo "Fetching Student...\n";
$student_q = $conn->prepare("SELECT username, email, branch FROM users WHERE id=?");
if(!$student_q) die("Student Query Prepare Failed: " . $conn->error);
$student_q->bind_param("i", $grievance['student_id']);
$student_q->execute();
$student_res = $student_q->get_result();
$student = $student_res->fetch_assoc();
print_r($student);

echo "Render Complete.\n";
?>
