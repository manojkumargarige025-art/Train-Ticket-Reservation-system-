# 🚂 Train Ticket Reservation System - Capstone Project

## 📋 **Complete Setup Guide**

This is a comprehensive Train Ticket Reservation System built as a capstone project using **XAMPP** (PHP, MySQL, HTML5, CSS3, JavaScript).

---

## 🎯 **Project Overview**

### **Features:**
- ✅ **User Registration & Authentication**
- ✅ **Admin Panel with Full Control**
- ✅ **Train Management System**
- ✅ **Online Ticket Booking**
- ✅ **Payment Processing**
- ✅ **Booking History & Management**
- ✅ **Real-time Search & Filtering**
- ✅ **Responsive Design**
- ✅ **Modern UI/UX**

### **Technologies Used:**
- **Backend:** PHP 8.0+
- **Database:** MySQL 8.0+
- **Frontend:** HTML5, CSS3, JavaScript
- **Framework:** Bootstrap 5.3
- **Icons:** Font Awesome 6.0
- **Server:** Apache (XAMPP)

---

## 🚀 **Quick Setup (5 Minutes)**

### **Step 1: Install XAMPP**
1. Download XAMPP from: https://www.apachefriends.org/download.html
2. Install with **Apache** and **MySQL** components
3. Start both services in XAMPP Control Panel

### **Step 2: Setup Database**
1. Open: http://localhost/phpmyadmin
2. Click **"Import"** tab
3. Choose file: `database_setup.sql`
4. Click **"Go"** to import

### **Step 3: Access Application**
- **Main URL:** http://localhost/trainbook/
- **Admin Login:** admin@trainbook.com / admin123
- **User Login:** john@example.com / user123

---

## 📊 **Database Structure**

### **Tables Created:**
- `users` - User accounts and profiles
- `admins` - Admin accounts
- `trains` - Train information and schedules
- `bookings` - Ticket bookings and reservations

### **Sample Data Included:**
- **2 Admin accounts** with full access
- **4 User accounts** for testing
- **10 Sample trains** with different routes
- **3 Sample bookings** for demonstration

---

## 🔑 **Default Login Credentials**

### **Admin Accounts:**
- **Email:** admin@trainbook.com
- **Password:** admin123

- **Email:** system@trainbook.com
- **Password:** system123

### **User Accounts:**
- **Email:** john@example.com
- **Password:** user123

- **Email:** jane@example.com
- **Password:** user123

- **Email:** mike@example.com
- **Password:** user123

- **Email:** sarah@example.com
- **Password:** user123

---

## 📁 **Project Structure**

```
trainbook/
├── index.php                 # Homepage
├── register.php              # User registration
├── database_setup.sql        # Database setup script
├── config/
│   └── database.php          # Database configuration
├── auth/
│   ├── login.php            # Authentication handler
│   └── logout.php           # Logout handler
├── user/
│   └── dashboard.php        # User dashboard
├── admin/
│   └── dashboard.php        # Admin dashboard
└── SETUP_GUIDE.md          # This file
```

---

## 🎨 **Key Features Implemented**

### **User Features:**
- **Registration & Login** - Secure user authentication
- **Dashboard** - Personal dashboard with statistics
- **Search Trains** - Find trains between stations
- **Book Tickets** - Online ticket booking system
- **Booking History** - View past and upcoming bookings
- **Profile Management** - Update personal information

### **Admin Features:**
- **Admin Dashboard** - Comprehensive admin panel
- **Train Management** - Add, edit, delete trains
- **Booking Management** - View and manage all bookings
- **User Management** - Manage user accounts
- **Reports** - Generate system reports
- **Statistics** - Real-time system statistics

---

## 🔧 **Technical Features**

### **Security:**
- Password hashing using PHP's `password_hash()`
- SQL injection prevention with prepared statements
- Input validation and sanitization
- Session management
- CSRF protection

### **Database:**
- Normalized database design
- Foreign key constraints
- Indexes for performance
- Views for complex queries
- Stored procedures for data integrity

### **Frontend:**
- Responsive Bootstrap design
- Modern UI/UX with animations
- Mobile-friendly interface
- Font Awesome icons
- Custom CSS styling

---

## 🚀 **Advanced Features**

### **Real-time Updates:**
- Live search functionality
- Dynamic content loading
- Real-time availability checking

### **User Experience:**
- Intuitive navigation
- Clear visual feedback
- Error handling and validation
- Success/error messages
- Loading states

### **Admin Tools:**
- Bulk operations
- Advanced filtering
- Export functionality
- System monitoring
- User activity logs

---

## 📱 **Responsive Design**

The application is fully responsive and works on:
- **Desktop** (1200px+)
- **Tablet** (768px - 1199px)
- **Mobile** (320px - 767px)

---

## 🔍 **Testing the System**

### **User Journey:**
1. **Register** a new account
2. **Login** with credentials
3. **Search** for trains
4. **Book** a ticket
5. **View** booking history
6. **Update** profile

### **Admin Journey:**
1. **Login** as admin
2. **View** dashboard statistics
3. **Add** new trains
4. **Manage** bookings
5. **View** user reports
6. **Monitor** system

---

## 🛠️ **Customization**

### **Adding New Features:**
1. Create new PHP files in appropriate directories
2. Add database tables if needed
3. Update navigation menus
4. Test thoroughly

### **Styling Changes:**
1. Modify CSS in the `<style>` sections
2. Update Bootstrap classes
3. Customize color scheme in `:root` variables
4. Add new animations

### **Database Modifications:**
1. Update `database_setup.sql`
2. Modify `config/database.php` if needed
3. Update related PHP files
4. Test all functionality

---

## 🐛 **Troubleshooting**

### **Common Issues:**

**1. Database Connection Error:**
- Check MySQL service is running
- Verify database credentials in `config/database.php`
- Ensure database exists

**2. Page Not Found (404):**
- Check file paths are correct
- Verify Apache is running
- Check file permissions

**3. Login Issues:**
- Verify database is imported correctly
- Check password hashing
- Clear browser cache

**4. Styling Issues:**
- Check Bootstrap CDN links
- Verify Font Awesome CDN
- Check custom CSS syntax

---

## 📈 **Performance Optimization**

### **Database:**
- Use indexes on frequently queried columns
- Optimize queries with EXPLAIN
- Regular database maintenance

### **Frontend:**
- Minify CSS and JavaScript
- Optimize images
- Use CDN for libraries
- Enable browser caching

---

## 🔒 **Security Considerations**

### **Production Deployment:**
- Change default passwords
- Use HTTPS
- Regular security updates
- Database backup
- Input validation
- Error logging

---

## 📞 **Support**

### **For Issues:**
1. Check this setup guide
2. Verify XAMPP services are running
3. Check database connection
4. Review error logs

### **Contact:**
- **Email:** support@trainbook.com
- **Documentation:** This file
- **Version:** 1.0.0

---

## 🎉 **Congratulations!**

Your Train Ticket Reservation System is now ready to use! This capstone project demonstrates:

- **Full-stack development** with PHP and MySQL
- **Modern web design** with Bootstrap
- **Database design** and management
- **User authentication** and authorization
- **Responsive design** principles
- **Security best practices**

**Happy Coding! 🚂✨**

---

*Built with ❤️ for educational purposes*
