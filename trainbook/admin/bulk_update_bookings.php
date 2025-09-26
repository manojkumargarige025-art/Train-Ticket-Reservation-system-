<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    redirectWithMessage('../index.php', 'Please login as admin.', 'error');
}

$action = $_POST['bulk_action'] ?? '';
$ids = $_POST['selected_ids'] ?? [];

if (!in_array($action, ['approve', 'reject', 'processing']) || empty($ids) || !is_array($ids)) {
    redirectWithMessage('bookings.php', 'No bookings selected or invalid action.', 'warning');
}

// Sanitize IDs to integers
$bookingIds = array_values(array_unique(array_map(fn($v) => (int)$v, $ids)));

try {
    $db = getDB();
    $db->beginTransaction();

    // Fetch affected bookings and related trains
    $inPlaceholders = implode(',', array_fill(0, count($bookingIds), '?'));
    $stmt = $db->prepare("SELECT b.*, t.available_seats, t.id AS train_id FROM bookings b JOIN trains t ON b.train_id = t.id WHERE b.id IN ($inPlaceholders) FOR UPDATE");
    $stmt->execute($bookingIds);
    $rows = $stmt->fetchAll();

    if (!$rows) {
        throw new Exception('Selected bookings not found.');
    }

    $approvedCount = 0;
    $rejectedCount = 0;
    $processingCount = 0;

    foreach ($rows as $row) {
        $id = (int)$row['id'];

        if ($action === 'processing') {
            if ($row['booking_status'] !== 'processing') {
                $stmt = $db->prepare("UPDATE bookings SET booking_status = 'processing' WHERE id = ?");
                $stmt->execute([$id]);
                $stmt = $db->prepare("INSERT INTO booking_logs (booking_id, action, admin_id, created_at) VALUES (?, 'processing', ?, NOW())");
                $stmt->execute([$id, $_SESSION['admin_id']]);
                $processingCount++;
            }
            continue;
        }

        // From here: approve/reject should work for any non-finalised state.

        if ($action === 'approve') {
            if ((int)$row['available_seats'] <= 0) {
                continue; // skip if no seats
            }
            // Only decrement seats if moving into confirmed from a non-confirmed state
            $alreadyConfirmed = ($row['booking_status'] === 'confirmed');
            $stmt = $db->prepare("UPDATE bookings SET booking_status = 'confirmed', payment_status = 'paid' WHERE id = ?");
            $stmt->execute([$id]);
            if (!$alreadyConfirmed) {
                $stmt = $db->prepare("UPDATE trains SET available_seats = available_seats - 1 WHERE id = ?");
                $stmt->execute([(int)$row['train_id']]);
            }
            $stmt = $db->prepare("INSERT INTO booking_logs (booking_id, action, admin_id, created_at) VALUES (?, 'approved', ?, NOW())");
            $stmt->execute([$id, $_SESSION['admin_id']]);
            $approvedCount++;
        } elseif ($action === 'reject') {
            $stmt = $db->prepare("UPDATE bookings SET booking_status = 'rejected', payment_status = 'cancelled' WHERE id = ?");
            $stmt->execute([$id]);
            $stmt = $db->prepare("INSERT INTO booking_logs (booking_id, action, admin_id, created_at) VALUES (?, 'rejected', ?, NOW())");
            $stmt->execute([$id, $_SESSION['admin_id']]);
            $rejectedCount++;
        }
    }

    $db->commit();

    $parts = [];
    if ($processingCount) $parts[] = "$processingCount marked processing";
    if ($approvedCount) $parts[] = "$approvedCount approved";
    if ($rejectedCount) $parts[] = "$rejectedCount rejected";
    $msg = $parts ? ('Bulk update done: ' . implode(', ', $parts) . '.') : 'No changes applied (records may already be in the target state or seats unavailable).';

    redirectWithMessage('bookings.php', $msg, 'success');
} catch (Exception $e) {
    if ($db && $db->inTransaction()) {
        $db->rollback();
    }
    error_log('Bulk booking update error: ' . $e->getMessage());
    redirectWithMessage('bookings.php', 'Error: ' . $e->getMessage(), 'error');
}
?>


