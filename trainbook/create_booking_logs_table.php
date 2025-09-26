<?php
require_once 'config/database.php';

echo "<h2>📋 Creating Booking Logs Table</h2>";

try {
    $db = getDB();
    
    // Create booking_logs table
    $sql = "
    CREATE TABLE IF NOT EXISTS booking_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        booking_id INT NOT NULL,
        action ENUM('approved', 'rejected', 'cancelled', 'modified') NOT NULL,
        admin_id INT NOT NULL,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
        FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
    )";
    
    $db->exec($sql);
    echo "✅ Booking logs table created successfully<br>";
    
    // Add approved_at column to bookings table if it doesn't exist
    try {
        $db->exec("ALTER TABLE bookings ADD COLUMN approved_at TIMESTAMP NULL");
        echo "✅ Added approved_at column to bookings table<br>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "✅ approved_at column already exists<br>";
        } else {
            echo "❌ Error adding approved_at column: " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<h3>🎯 Approval Workflow Ready!</h3>";
    echo "<p>Now the system works as follows:</p>";
    echo "<ul>";
    echo "<li>✅ Users submit bookings (status: pending)</li>";
    echo "<li>✅ Admin sees pending bookings in admin panel</li>";
    echo "<li>✅ Admin can approve or reject bookings</li>";
    echo "<li>✅ Only approved bookings reduce available seats</li>";
    echo "<li>✅ All actions are logged for audit trail</li>";
    echo "</ul>";
    
    echo "<p><a href='admin/bookings.php'>Go to Admin Bookings</a> | <a href='user/search_trains.php'>Go to User Search</a></p>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
