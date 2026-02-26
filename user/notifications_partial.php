<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user']['id'] ?? 0;

// Handle Mark as Read
if (isset($_POST['mark_notification_read'])) {
    $nid = $_POST['notification_id'];
    $upd = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $upd->bind_param("ii", $nid, $user_id);
    $upd->execute();
}

// Fetch Unread Notifications
$notif_check = $conn->query("SHOW TABLES LIKE 'notifications'");
$notifications = [];

if ($notif_check->num_rows > 0 && $user_id > 0) {
    $n_stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
    $n_stmt->bind_param("i", $user_id);
    $n_stmt->execute();
    $n_res = $n_stmt->get_result();
    while ($row = $n_res->fetch_assoc()) {
        $notifications[] = $row;
    }
}
?>

<?php if (count($notifications) > 0): ?>
    <div style="margin-bottom: 20px;">
        <?php foreach ($notifications as $note): ?>
            <div style="background: #e3f2fd; border-left: 4px solid #2196f3; color: #0d47a1; padding: 12px 16px; margin-bottom: 10px; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <div style="font-size: 14px; font-weight: 500;">
                    <i class="fa-solid fa-bell" style="margin-right: 8px;"></i>
                    <?php echo htmlspecialchars($note['message']); ?>
                    <div style="font-size: 11px; color: #5472d3; margin-top: 2px;">
                        <?php echo date("M j, H:i", strtotime($note['created_at'])); ?>
                    </div>
                </div>
                <form method="post" style="margin: 0;">
                    <input type="hidden" name="notification_id" value="<?php echo $note['id']; ?>">
                    <button type="submit" name="mark_notification_read" style="background: none; border: none; color: #0d47a1; cursor: pointer; font-size: 18px; padding: 0 4px;" title="Dismiss">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
