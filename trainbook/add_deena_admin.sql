-- Add Deena's Admin Account to existing database
USE trainbook;

-- Insert Deena's admin account
INSERT INTO admins (first_name, last_name, email, password, phone) VALUES
('Deena', 'Dhayalan', 'deena.dhayalan@cmr.edu.in', MD5('1505'), '9876543210');

-- Verify the account was created
SELECT * FROM admins WHERE email = 'deena.dhayalan@cmr.edu.in';
