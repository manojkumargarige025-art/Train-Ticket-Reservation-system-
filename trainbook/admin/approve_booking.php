<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    redirectWithMessage('../index.php', 'Please login as admin.', 'error');
}

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if (!$booking_id || !in_array($action, ['approve', 'reject'])) {
    redirectWithMessage('bookings.php', 'Invalid request.', 'error');
}

try {
    $db = getDB();
    
    // Get booking details
    $stmt = $db->prepare("
        SELECT b.*, t.available_seats, t.total_seats 
        FROM bookings b 
        JOIN trains t ON b.train_id = t.id 
        WHERE b.id = ?
    ");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        redirectWithMessage('bookings.php', 'Booking not found.', 'error');
    }
    
    if ($booking['booking_status'] !== 'pending') {
        redirectWithMessage('bookings.php', 'This booking has already been processed.', 'warning');
    }
    
    // Start transaction
    $db->beginTransaction();
    
    if ($action === 'approve') {
        // Check if seats are still available
        if ($booking['available_seats'] <= 0) {
            throw new Exception('No seats available for this train.');
        }
        
        // Update booking status
        $stmt = $db->prepare("
            UPDATE bookings 
            SET booking_status = 'confirmed', payment_status = 'paid' 
            WHERE id = ?
        ");
        $stmt->execute([$booking_id]);
        
        // Reduce available seats
        $stmt = $db->prepare("
            UPDATE trains 
            SET available_seats = available_seats - 1 
            WHERE id = ?
        ");
        $stmt->execute([$booking['train_id']]);
        
        // Log the approval
        $stmt = $db->prepare("
            INSERT INTO booking_logs (booking_id, action, admin_id, created_at) 
            VALUES (?, 'approved', ?, NOW())
        ");
        $stmt->execute([$booking_id, $_SESSION['admin_id']]);
        
        $message = 'Booking approved successfully!';
        $message_type = 'success';
        
    } else { // reject
        // Update booking status
        $stmt = $db->prepare("
            UPDATE bookings 
            SET booking_status = 'rejected', payment_status = 'cancelled' 
            WHERE id = ?
        ");
        $stmt->execute([$booking_id]);
        
        // Log the rejection
        $stmt = $db->prepare("
            INSERT INTO booking_logs (booking_id, action, admin_id, created_at) 
            VALUES (?, 'rejected', ?, NOW())
        ");
        $stmt->execute([$booking_id, $_SESSION['admin_id']]);
        
        $message = 'Booking rejected successfully!';
        $message_type = 'info';
    }
    
    // Commit transaction
    $db->commit();
    
    redirectWithMessage('bookings.php', $message, $message_type);
    
} catch (Exception $e) {
    $db->rollback();
    error_log("Booking approval error: " . $e->getMessage());
    redirectWithMessage('bookings.php', 'Error processing booking: ' . $e->getMessage(), 'error');
}
?>
