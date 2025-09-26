# 🚂 TrainBook - Complete Train Booking System

## 📋 Project Overview
TrainBook is a comprehensive web-based train booking system built with PHP, MySQL, and Bootstrap. It features a complete admin approval workflow, real-time train tracking, and a modern user interface.

## 🎯 Key Features

### 👤 User Features
- **User Registration & Login**
- **Train Search & Filtering** (60 days of data)
- **Train Booking with Seat Selection**
- **Booking History & Management**
- **Profile Management**
- **Real-time Booking Status Updates**

### ⚙️ Admin Features
- **Admin Dashboard with Statistics**
- **Train Management** (Add, Edit, Delete)
- **Booking Approval System** (Approve/Reject)
- **User Management**
- **Real-time Train Status Tracking**
- **Audit Trail & Logging**

### 🔧 System Features
- **Admin Approval Workflow** (All bookings require approval)
- **Real-time Train Status** (Boarding, In Transit, Delayed, etc.)
- **Multiple Payment Status** (Pending, Paid, Refunded)
- **Comprehensive Logging System**
- **Responsive Design** (Mobile-friendly)
- **Security Features** (Password hashing, SQL injection prevention)

## 🗂️ Project Structure

```
trainbook/
├── admin/                          # Admin Panel
│   ├── dashboard.php              # Admin dashboard
│   ├── trains.php                 # Train management
│   ├── bookings.php               # Booking management
│   ├── users.php                  # User management
│   └── approve_booking.php        # Booking approval handler
├── user/                          # User Panel
│   ├── dashboard.php              # User dashboard
│   ├── search_trains.php          # Train search
│   ├── book_train.php             # Booking form
│   ├── my_bookings.php            # User bookings
│   └── profile.php                # User profile
├── auth/                          # Authentication
│   ├── login.php                  # Login system
│   ├── register.php               # Registration
│   └── logout.php                 # Logout
├── api/                           # API Endpoints
│   └── train_status.php           # Real-time train status
├── config/                        # Configuration
│   └── database.php               # Database connection
├── assets/                        # Static Files
│   ├── css/                       # Stylesheets
│   └── js/                        # JavaScript
├── database_setup.sql             # Database schema
└── setup_complete_project.php     # Project setup script
```

## 🚀 Installation & Setup

### Prerequisites
- XAMPP (Apache, MySQL, PHP)
- Web browser
- Text editor (optional)

### Installation Steps

1. **Download/Clone the project**
   ```bash
   # Place the trainbook folder in xampp/htdocs/
   ```

2. **Start XAMPP Services**
   - Start Apache
   - Start MySQL

3. **Setup Database**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create database: `trainbook`
   - Import: `database_setup.sql`

4. **Run Project Setup**
   - Go to: `http://localhost:8080/trainbook/setup_complete_project.php`
   - This will create all tables and add sample data

5. **Access the System**
   - Home: `http://localhost:8080/trainbook/`
   - Admin: `http://localhost:8080/trainbook/admin/dashboard.php`
   - User: `http://localhost:8080/trainbook/user/search_trains.php`

## 🔐 Default Login Credentials

### Admin Account
- **Email:** admin@trainbook.com
- **Password:** admin123

### Test User Account
- **Email:** user@test.com
- **Password:** user123

*Note: You can register new users through the registration form.*

## 📊 Database Schema

### Core Tables
- **users** - User accounts and profiles
- **admins** - Admin accounts
- **trains** - Train information and schedules
- **bookings** - Booking records
- **booking_logs** - Audit trail for booking actions

### Key Relationships
- Users can have multiple bookings
- Bookings belong to one train and one user
- Admins can approve/reject bookings
- All actions are logged in booking_logs

## 🔄 Booking Workflow

1. **User Registration/Login**
2. **Search Trains** (by route and date)
3. **Select Train** and click "Book Now"
4. **Fill Booking Form** (passenger details, seat preference)
5. **Submit Booking** (status: pending)
6. **Admin Review** (in admin panel)
7. **Admin Decision** (approve/reject)
8. **User Notification** (status update in "My Bookings")

## 🎨 User Interface

### Design Features
- **Modern Bootstrap 5** design
- **Responsive layout** (mobile-friendly)
- **Color-coded status** indicators
- **Interactive elements** (buttons, forms, alerts)
- **Real-time updates** (train status, booking status)

### Color Scheme
- **Primary:** #2c3e50 (Dark blue)
- **Secondary:** #3498db (Blue)
- **Success:** #27ae60 (Green)
- **Warning:** #f39c12 (Orange)
- **Danger:** #e74c3c (Red)

## 🔧 Technical Details

### Backend
- **PHP 7.4+** with PDO
- **MySQL 5.7+** database
- **Session management**
- **Password hashing** (PHP password_hash)
- **SQL injection prevention** (prepared statements)

### Frontend
- **HTML5** semantic markup
- **CSS3** with custom properties
- **Bootstrap 5** framework
- **Font Awesome** icons
- **JavaScript** for interactivity

### Security Features
- **Password hashing** (bcrypt)
- **SQL injection prevention**
- **XSS protection** (htmlspecialchars)
- **Session management**
- **Input validation**

## 📱 API Endpoints

### Train Status API
- **URL:** `/api/train_status.php`
- **Method:** GET
- **Response:** JSON with real-time train data
- **Usage:** Updates train status every 30 seconds

## 🧪 Testing the System

### User Testing
1. Register a new account
2. Search for trains (Chennai to Bangalore)
3. Book a train
4. Check booking status in "My Bookings"

### Admin Testing
1. Login as admin
2. Go to "Manage Bookings"
3. Approve/reject pending bookings
4. Check train management features

## 🐛 Troubleshooting

### Common Issues
1. **Database Connection Error**
   - Check XAMPP MySQL service
   - Verify database credentials in config/database.php

2. **Page Not Found (404)**
   - Ensure XAMPP Apache is running
   - Check file paths and permissions

3. **No Trains Showing**
   - Run setup_complete_project.php
   - Check database for train data

4. **Booking Approval Not Working**
   - Ensure booking_logs table exists
   - Check admin login status

## 📈 Future Enhancements

### Planned Features
- **Email notifications** for booking status
- **Payment gateway integration**
- **Mobile app** (React Native/Flutter)
- **Advanced reporting** and analytics
- **Multi-language support**
- **API for third-party integration**

## 👥 Development Team

- **Backend Development:** PHP, MySQL
- **Frontend Development:** HTML, CSS, JavaScript, Bootstrap
- **Database Design:** MySQL schema design
- **UI/UX Design:** Responsive design, user experience

## 📄 License

This project is developed for educational purposes. Feel free to use and modify as needed.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📞 Support

For technical support or questions:
- Check the troubleshooting section
- Review the code comments
- Test with the provided sample data

---

**🎉 Your TrainBook system is ready to use! Enjoy managing train bookings with a professional, modern interface.**
