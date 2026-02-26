<?php
include "../config.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'student') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing ID']);
    exit;
}

$gid = intval($_GET['id']);
$sid = $_SESSION['user']['id'];

$stmt = $conn->prepare("SELECT id, title, status, feedback_text FROM grievances WHERE id = ? AND student_id = ?");
$stmt->bind_param("ii", $gid, $sid);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        'status' => 'success',
        'data' => [
            'id' => $row['id'],
            'title' => $row['title'],
            'status' => $row['status'],
            'feedback' => $row['feedback_text']
        ]
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Grievance not found']);
}
?>
