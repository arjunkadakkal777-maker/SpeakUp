<?php
include "config.php";

// 1. Create dummy grievance
$conn->query("INSERT INTO grievances (title, student_id, status, feedback_text) VALUES ('Test Feedback', 1, 'Open', 'Initial')");
$id = $conn->insert_id;
echo "Created Grievance ID: $id\n";

// 2. Simulate Update
$new_status = "In Progress";
$feedback = "This is a test feedback from faculty.";
$stmt = $conn->prepare("UPDATE grievances SET status=?, feedback_text=? WHERE id=?");
$stmt->bind_param("ssi", $new_status, $feedback, $id);
if ($stmt->execute()) {
    echo "Update executed.\n";
} else {
    echo "Update failed: " . $stmt->error . "\n";
}

// 3. Verify
$res = $conn->query("SELECT status, feedback_text FROM grievances WHERE id=$id");
$row = $res->fetch_assoc();
print_r($row);

// 4. Cleanup
$conn->query("DELETE FROM grievances WHERE id=$id");
?>
