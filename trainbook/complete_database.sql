-- =====================================================
-- TRAIN TICKET RESERVATION SYSTEM - COMPLETE DATABASE
-- =====================================================

-- Create database
CREATE DATABASE IF NOT EXISTS trainbook;
USE trainbook;

-- Drop existing tables and views if they exist
DROP VIEW IF EXISTS train_availability;
DROP VIEW IF EXISTS booking_summary;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS trains;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS admins;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create admins table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create trains table
CREATE TABLE trains (
    id INT AUTO_INCREMENT PRIMARY KEY,
    train_number VARCHAR(10) UNIQUE NOT NULL,
    train_name VARCHAR(100) NOT NULL,
    source_station VARCHAR(50) NOT NULL,
    destination_station VARCHAR(50) NOT NULL,
    departure_time TIME NOT NULL,
    arrival_time TIME NOT NULL,
    total_seats INT NOT NULL,
    available_seats INT NOT NULL,
    fare_per_seat DECIMAL(10,2) NOT NULL,
    journey_date DATE NOT NULL,
    status ENUM('active', 'cancelled', 'completed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create bookings table
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id VARCHAR(20) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    train_id INT NOT NULL,
    passenger_name VARCHAR(100) NOT NULL,
    passenger_age INT NOT NULL,
    passenger_gender ENUM('Male', 'Female', 'Other') NOT NULL,
    seat_number VARCHAR(10),
    journey_date DATE NOT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_fare DECIMAL(10,2) NOT NULL,
    payment_status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
    booking_status ENUM('confirmed', 'cancelled', 'completed') DEFAULT 'confirmed',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (train_id) REFERENCES trains(id) ON DELETE CASCADE
);

-- Insert sample admin data
INSERT INTO admins (first_name, last_name, email, password, phone) VALUES
('Deena', 'Dhayalan', 'deena.dhayalan@cmr.edu.in', MD5('1505'), '9876543210'),
('Admin', 'User', 'admin@trainbook.com', MD5('admin123'), '9876543211'),
('System', 'Administrator', 'system@trainbook.com', MD5('system123'), '9876543212');

-- Insert sample user data
INSERT INTO users (first_name, last_name, email, password, phone, address) VALUES
('John', 'Doe', 'john@example.com', MD5('user123'), '9876543212', '123 Main St, City'),
('Jane', 'Smith', 'jane@example.com', MD5('user123'), '9876543213', '456 Oak Ave, Town'),
('Mike', 'Johnson', 'mike@example.com', MD5('user123'), '9876543214', '789 Pine Rd, Village'),
('Sarah', 'Wilson', 'sarah@example.com', MD5('user123'), '9876543215', '321 Elm St, City');

-- Insert sample train data
INSERT INTO trains (train_number, train_name, source_station, destination_station, departure_time, arrival_time, total_seats, available_seats, fare_per_seat, journey_date) VALUES
('TR001', 'Express Superfast', 'Mumbai', 'Delhi', '08:00:00', '20:00:00', 100, 100, 1500.00, '2024-12-25'),
('TR002', 'Rajdhani Express', 'Delhi', 'Mumbai', '10:30:00', '22:30:00', 80, 80, 2000.00, '2024-12-25'),
('TR003', 'Shatabdi Express', 'Delhi', 'Agra', '06:00:00', '08:30:00', 60, 60, 800.00, '2024-12-25'),
('TR004', 'Duronto Express', 'Kolkata', 'Chennai', '14:00:00', '06:00:00', 120, 120, 1800.00, '2024-12-25'),
('TR005', 'Garib Rath', 'Mumbai', 'Goa', '22:00:00', '08:00:00', 150, 150, 500.00, '2024-12-25'),
('TR006', 'Vande Bharat', 'Delhi', 'Varanasi', '06:00:00', '14:00:00', 70, 70, 1200.00, '2024-12-26'),
('TR007', 'Tejas Express', 'Mumbai', 'Pune', '07:00:00', '10:00:00', 50, 50, 400.00, '2024-12-26'),
('TR008', 'Gatimaan Express', 'Delhi', 'Agra', '08:10:00', '09:50:00', 40, 40, 900.00, '2024-12-26'),
('TR009', 'Sampark Kranti', 'Delhi', 'Bangalore', '11:00:00', '14:00:00', 200, 200, 2200.00, '2024-12-26'),
('TR010', 'Jan Shatabdi', 'Mumbai', 'Ahmedabad', '15:30:00', '23:30:00', 90, 90, 600.00, '2024-12-26');

-- Insert sample booking data
INSERT INTO bookings (booking_id, user_id, train_id, passenger_name, passenger_age, passenger_gender, seat_number, journey_date, total_fare, payment_status, booking_status) VALUES
('BK001', 1, 1, 'John Doe', 25, 'Male', 'A1', '2024-12-25', 1500.00, 'paid', 'confirmed'),
('BK002', 2, 2, 'Jane Smith', 30, 'Female', 'B2', '2024-12-25', 2000.00, 'paid', 'confirmed'),
('BK003', 3, 3, 'Mike Johnson', 28, 'Male', 'C3', '2024-12-25', 800.00, 'paid', 'confirmed');

-- Create indexes for better performance
CREATE INDEX idx_user_email ON users(email);
CREATE INDEX idx_train_number ON trains(train_number);
CREATE INDEX idx_booking_id ON bookings(booking_id);
CREATE INDEX idx_booking_user ON bookings(user_id);
CREATE INDEX idx_booking_train ON bookings(train_id);
CREATE INDEX idx_train_route ON trains(source_station, destination_station);
CREATE INDEX idx_train_date ON trains(journey_date);

-- Create a view for train availability
CREATE VIEW train_availability AS
SELECT 
    t.id,
    t.train_number,
    t.train_name,
    t.source_station,
    t.destination_station,
    t.departure_time,
    t.arrival_time,
    t.total_seats,
    t.available_seats,
    t.fare_per_seat,
    t.journey_date,
    t.status,
    CASE 
        WHEN t.available_seats > 0 THEN 'Available'
        ELSE 'Fully Booked'
    END as availability_status
FROM trains t
WHERE t.status = 'active' AND t.journey_date >= CURDATE();

-- Create a view for booking summary
CREATE VIEW booking_summary AS
SELECT 
    b.booking_id,
    b.passenger_name,
    t.train_name,
    t.train_number,
    t.source_station,
    t.destination_station,
    b.journey_date,
    b.seat_number,
    b.total_fare,
    b.payment_status,
    b.booking_status,
    b.booking_date,
    u.first_name,
    u.last_name,
    u.email
FROM bookings b
JOIN trains t ON b.train_id = t.id
JOIN users u ON b.user_id = u.id;

-- Display success message
SELECT 'Database setup completed successfully!' as message;
SELECT 'Tables created: users, admins, trains, bookings' as tables;
SELECT 'Sample data inserted successfully!' as data;
SELECT 'Views created: train_availability, booking_summary' as views;
