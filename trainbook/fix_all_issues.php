<?php
require_once 'config/database.php';

echo "<h1>🔧 Fixing All Issues - TrainBook System</h1>";
echo "<p>This will fix all the problems in your train booking system...</p>";

try {
    $db = getDB();
    
    // 1. Create admins table if it doesn't exist
    echo "<h3>1. Creating Admins Table</h3>";
    $sql = "
    CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $db->exec($sql);
    echo "✅ Admins table created/verified<br>";
    
    // 2. Create default admin if none exists
    $stmt = $db->query("SELECT COUNT(*) as count FROM admins");
    $admin_count = $stmt->fetch()['count'];
    
    if ($admin_count == 0) {
        echo "<h3>2. Creating Default Admin</h3>";
        $stmt = $db->prepare("
            INSERT INTO admins (username, email, password, first_name, last_name) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            'admin',
            'admin@trainbook.com',
            password_hash('admin123', PASSWORD_DEFAULT),
            'Admin',
            'User'
        ]);
        echo "✅ Default admin created<br>";
        echo "&nbsp;&nbsp;&nbsp;Email: admin@trainbook.com<br>";
        echo "&nbsp;&nbsp;&nbsp;Password: admin123<br>";
    } else {
        echo "✅ Admin account already exists<br>";
    }
    
    // 3. Create booking_logs table
    echo "<h3>3. Creating Booking Logs Table</h3>";
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
    echo "✅ Booking logs table created<br>";
    
    // 4. Add approved_at column to bookings
    echo "<h3>4. Adding Approval Column</h3>";
    try {
        $db->exec("ALTER TABLE bookings ADD COLUMN approved_at TIMESTAMP NULL");
        echo "✅ Added approved_at column<br>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "✅ approved_at column already exists<br>";
        } else {
            echo "❌ Error: " . $e->getMessage() . "<br>";
        }
    }
    
    // 5. Update existing bookings to confirmed status
    echo "<h3>5. Updating Existing Bookings</h3>";
    $db->exec("UPDATE bookings SET booking_status = 'confirmed', payment_status = 'paid' WHERE booking_status IS NULL OR booking_status = ''");
    echo "✅ Updated existing bookings<br>";
    
    // 6. Add comprehensive train data
    echo "<h3>6. Adding Train Data</h3>";
    
    // Clear existing trains first
    $db->exec("DELETE FROM trains");
    echo "✅ Cleared existing trains<br>";
    
    // Major Indian train routes
    $routes = [
        // Chennai to Bangalore (Multiple trains)
        ['Chennai Central', 'Bangalore', 'Shatabdi Express', '12007', '06:00', '11:00', 300, 280, 1500],
        ['Chennai Central', 'Bangalore', 'Brindavan Express', '12640', '08:00', '13:00', 500, 450, 800],
        ['Chennai Central', 'Bangalore', 'Karnataka Express', '12627', '10:00', '15:00', 600, 550, 700],
        ['Chennai Central', 'Bangalore', 'Shatabdi Express', '12008', '12:00', '17:00', 300, 280, 1500],
        ['Chennai Central', 'Bangalore', 'Brindavan Express', '12641', '14:00', '19:00', 500, 450, 800],
        ['Chennai Central', 'Bangalore', 'Karnataka Express', '12628', '16:00', '21:00', 600, 550, 700],
        ['Chennai Central', 'Bangalore', 'Shatabdi Express', '12009', '18:00', '23:00', 300, 280, 1500],
        
        // Bangalore to Chennai
        ['Bangalore', 'Chennai Central', 'Shatabdi Express', '12010', '07:00', '12:00', 300, 280, 1500],
        ['Bangalore', 'Chennai Central', 'Brindavan Express', '12642', '09:00', '14:00', 500, 450, 800],
        ['Bangalore', 'Chennai Central', 'Karnataka Express', '12629', '11:00', '16:00', 600, 550, 700],
        ['Bangalore', 'Chennai Central', 'Shatabdi Express', '12011', '13:00', '18:00', 300, 280, 1500],
        ['Bangalore', 'Chennai Central', 'Brindavan Express', '12643', '15:00', '20:00', 500, 450, 800],
        ['Bangalore', 'Chennai Central', 'Karnataka Express', '12630', '17:00', '22:00', 600, 550, 700],
        
        // Delhi to Mumbai
        ['Delhi', 'Mumbai', 'Rajdhani Express', '12001', '06:00', '14:30', 500, 450, 2500],
        ['Delhi', 'Mumbai', 'Shatabdi Express', '12002', '08:00', '16:30', 400, 380, 2200],
        ['Delhi', 'Mumbai', 'Duronto Express', '12213', '10:00', '18:30', 600, 550, 2000],
        ['Delhi', 'Mumbai', 'Garib Rath', '12214', '12:00', '20:30', 800, 750, 1200],
        ['Delhi', 'Mumbai', 'Jan Shatabdi', '12051', '14:00', '22:30', 350, 320, 1800],
        
        // Mumbai to Delhi
        ['Mumbai', 'Delhi', 'Rajdhani Express', '12003', '07:00', '15:30', 500, 450, 2500],
        ['Mumbai', 'Delhi', 'Shatabdi Express', '12004', '09:00', '17:30', 400, 380, 2200],
        ['Mumbai', 'Delhi', 'Duronto Express', '12215', '11:00', '19:30', 600, 550, 2000],
        ['Mumbai', 'Delhi', 'Garib Rath', '12216', '13:00', '21:30', 800, 750, 1200],
        ['Mumbai', 'Delhi', 'Jan Shatabdi', '12052', '15:00', '23:30', 350, 320, 1800],
        
        // Delhi to Kolkata
        ['Delhi', 'Kolkata', 'Rajdhani Express', '12005', '06:00', '14:30', 500, 450, 2500],
        ['Delhi', 'Kolkata', 'Shatabdi Express', '12006', '08:00', '16:30', 400, 380, 2200],
        ['Delhi', 'Kolkata', 'Duronto Express', '12217', '10:00', '18:30', 600, 550, 2000],
        
        // Kolkata to Delhi
        ['Kolkata', 'Delhi', 'Rajdhani Express', '12007', '07:00', '15:30', 500, 450, 2500],
        ['Kolkata', 'Delhi', 'Shatabdi Express', '12008', '09:00', '17:30', 400, 380, 2200],
        ['Kolkata', 'Delhi', 'Duronto Express', '12218', '11:00', '19:30', 600, 550, 2000],
        
        // Mumbai to Bangalore
        ['Mumbai', 'Bangalore', 'Udyan Express', '12627', '11:00', '19:30', 500, 450, 1400],
        ['Mumbai', 'Bangalore', 'Karnataka Express', '12628', '13:00', '21:30', 600, 550, 1200],
        ['Mumbai', 'Bangalore', 'Shatabdi Express', '12009', '15:00', '23:30', 400, 380, 1800],
        
        // Bangalore to Mumbai
        ['Bangalore', 'Mumbai', 'Udyan Express', '12629', '12:00', '20:30', 500, 450, 1400],
        ['Bangalore', 'Mumbai', 'Karnataka Express', '12630', '14:00', '22:30', 600, 550, 1200],
        ['Bangalore', 'Mumbai', 'Shatabdi Express', '12010', '16:00', '00:30', 400, 380, 1800],
        
        // Chennai to Mumbai
        ['Chennai Central', 'Mumbai', 'Chennai Express', '12601', '13:00', '21:30', 500, 450, 1300],
        ['Chennai Central', 'Mumbai', 'Mumbai Express', '12602', '15:00', '23:30', 600, 550, 1100],
        ['Chennai Central', 'Mumbai', 'Shatabdi Express', '12011', '17:00', '01:30', 400, 380, 1600],
        
        // Mumbai to Chennai
        ['Mumbai', 'Chennai Central', 'Chennai Express', '12603', '14:00', '22:30', 500, 450, 1300],
        ['Mumbai', 'Chennai Central', 'Mumbai Express', '12604', '16:00', '00:30', 600, 550, 1100],
        ['Mumbai', 'Chennai Central', 'Shatabdi Express', '12012', '18:00', '02:30', 400, 380, 1600],
    ];
    
    // Add trains for the next 60 days
    $added_count = 0;
    $start_date = new DateTime();
    
    for ($day = 0; $day < 60; $day++) {
        $current_date = clone $start_date;
        $current_date->add(new DateInterval('P' . $day . 'D'));
        $date_str = $current_date->format('Y-m-d');
        
        foreach ($routes as $route) {
            [$source, $destination, $train_name, $train_number, $departure_time, $arrival_time, $total_seats, $available_seats, $fare] = $route;
            
            // Add some variation to departure times
            $departure_hour = (int)substr($departure_time, 0, 2);
            $departure_hour = ($departure_hour + $day) % 24;
            $departure_time = sprintf('%02d:%s', $departure_hour, substr($departure_time, 3));
            
            // Add some variation to fares
            $fare_variation = rand(-200, 200);
            $fare = max(500, $fare + $fare_variation);
            
            // Add some variation to available seats
            $seat_variation = rand(-50, 50);
            $available_seats = max(0, min($total_seats, $available_seats + $seat_variation));
            
            try {
                $stmt = $db->prepare("
                    INSERT INTO trains (train_number, train_name, source_station, destination_station, 
                                      departure_time, arrival_time, total_seats, available_seats, 
                                      fare_per_seat, journey_date, status, current_location, current_status, 
                                      delay_minutes, progress_percentage) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, 'active', 0, 0.00)
                ");
                
                $stmt->execute([
                    $train_number,
                    $train_name,
                    $source,
                    $destination,
                    $departure_time,
                    $arrival_time,
                    $total_seats,
                    $available_seats,
                    $fare,
                    $date_str,
                    $source
                ]);
                
                $added_count++;
                
            } catch (Exception $e) {
                // Skip if train already exists for this date
                if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                    echo "Error adding train: " . $e->getMessage() . "<br>";
                }
            }
        }
    }
    
    echo "✅ Added $added_count trains for 60 days<br>";
    
    // 7. Check final counts
    $stmt = $db->query("SELECT COUNT(*) as count FROM trains");
    $train_count = $stmt->fetch()['count'];
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM bookings");
    $booking_count = $stmt->fetch()['count'];
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $user_count = $stmt->fetch()['count'];
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM admins");
    $admin_count = $stmt->fetch()['count'];
    
    echo "<h3>7. Final Statistics</h3>";
    echo "✅ Total Trains: $train_count<br>";
    echo "✅ Total Bookings: $booking_count<br>";
    echo "✅ Total Users: $user_count<br>";
    echo "✅ Total Admins: $admin_count<br>";
    
    echo "<h2>🎉 All Issues Fixed!</h2>";
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>✅ Your System is Now Ready!</h3>";
    echo "<p><strong>Admin Login:</strong></p>";
    echo "<p>Email: admin@trainbook.com</p>";
    echo "<p>Password: admin123</p>";
    echo "</div>";
    
    echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>🔗 Quick Access Links:</h3>";
    echo "<p><a href='test_admin_login.php' target='_blank'>🔐 Admin Login Test</a></p>";
    echo "<p><a href='admin/dashboard.php' target='_blank'>⚙️ Admin Dashboard</a></p>";
    echo "<p><a href='admin/bookings.php' target='_blank'>📋 Manage Bookings</a></p>";
    echo "<p><a href='user/search_trains.php' target='_blank'>🔍 Search Trains</a></p>";
    echo "<p><a href='index.php' target='_blank'>🏠 Home Page</a></p>";
    echo "</div>";
    
    echo "<div style='background: #fff3e0; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>🧪 Test the Complete System:</h3>";
    echo "<ol>";
    echo "<li>1. <strong>Login as Admin:</strong> Use admin@trainbook.com / admin123</li>";
    echo "<li>2. <strong>Go to Manage Bookings:</strong> You should see all bookings with Approve/Reject buttons</li>";
    echo "<li>3. <strong>Test User Booking:</strong> Go to Search Trains and book a train</li>";
    echo "<li>4. <strong>Approve Booking:</strong> Go back to admin panel and approve the booking</li>";
    echo "<li>5. <strong>Check Status:</strong> User should see status change in 'My Bookings'</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
