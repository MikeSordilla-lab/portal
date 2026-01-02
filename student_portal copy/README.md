# College Student Portal

A comprehensive web-based student information system built with PHP, MySQL, and Bootstrap.

## Overview

This portal provides a complete solution for managing student information, grades, schedules, and academic records. It features separate interfaces for students and administrators with role-based access control.

## Features

### Student Portal

- **View Grades**: See all grades grouped by semester with GPA calculations
- **View Schedule**: Weekly timetable view with class details
- **Profile Management**: Update personal information and profile picture
- **Password Change**: Secure password update functionality

### Admin Portal

- **Student Management**: Add, edit, delete student records
- **Course Management**: Manage course catalog with department associations
- **Grade Management**: Assign and update student grades
- **Schedule Management**: Create and manage class schedules
- **Department Management**: Organize academic departments

## Requirements

- **PHP**: 7.4 or higher
- **MySQL**: 8.0 or higher
- **Web Server**: Apache with mod_rewrite (XAMPP recommended)
- **Browser**: Modern browser with JavaScript enabled

## Installation

### 1. Set Up XAMPP

1. Download and install [XAMPP](https://www.apachefriends.org/)
2. Start Apache and MySQL services from XAMPP Control Panel

### 2. Deploy Application

1. Copy the `student_portal` folder to `C:\xampp\htdocs\`
2. The application will be accessible at `http://localhost/student_portal/`

### 3. Set Up Database

1. Open phpMyAdmin at `http://localhost/phpmyadmin/`
2. Create a new database named `student_portal`
3. Import the `database.sql` file:
   - Click on the `student_portal` database
   - Go to the "Import" tab
   - Choose the `database.sql` file
   - Click "Go" to import

### 4. Configure Database Connection

Edit `config/database.php` if your MySQL settings differ from defaults:

```php
$host = 'localhost';
$dbname = 'student_portal';
$username = 'root';
$password = ''; // Default XAMPP password is empty
```

## Default Login Credentials

### Admin Account

- **Admin ID**: `ADMIN001`
- **Password**: `Admin@123`

### Student Account

- **Student ID**: `2021CS001`
- **Password**: `Student@123`

> ⚠️ **Important**: Change these passwords immediately after first login!

## Directory Structure

```
student_portal/
├── admin/                  # Admin module
│   ├── courses/           # Course management
│   ├── departments/       # Department management
│   ├── grades/            # Grade management
│   ├── schedule/          # Schedule management
│   └── students/          # Student management
├── ajax/                   # AJAX endpoints
├── assets/                 # Static assets
│   ├── css/               # Stylesheets
│   ├── images/            # Static images
│   └── js/                # JavaScript files
├── config/                 # Configuration files
├── includes/               # Shared PHP includes
├── student/                # Student module
├── uploads/                # User uploads
│   └── profile_pictures/  # Profile photos
├── admin-login.php         # Admin login page
├── database.sql            # Database schema
├── index.php               # Landing page
├── logout.php              # Logout handler
└── student-login.php       # Student login page
```

## Security Features

- **CSRF Protection**: All forms protected with tokens
- **Password Hashing**: Using PHP's `password_hash()` with bcrypt
- **SQL Injection Prevention**: PDO prepared statements throughout
- **XSS Prevention**: Output escaping with `htmlspecialchars()`
- **Session Security**: HttpOnly cookies, regeneration on login
- **Account Lockout**: After 5 failed login attempts
- **File Upload Validation**: 7-step validation process

## Usage

### For Students

1. Access the portal at `http://localhost/student_portal/`
2. Click "Student Login"
3. Enter your Student ID and password
4. Navigate using the sidebar menu

### For Administrators

1. Access the portal at `http://localhost/student_portal/`
2. Click "Admin Login"
3. Enter your Admin ID and password
4. Manage students, courses, grades, schedules, and departments

## Troubleshooting

### Database Connection Error

- Ensure MySQL is running in XAMPP
- Verify database name and credentials in `config/database.php`
- Check that the `student_portal` database exists

### Login Not Working

- Verify you're using the correct credentials
- Check if the account is locked (wait 30 minutes or clear `failed_login_attempts` in database)
- Ensure JavaScript is enabled in your browser

### File Upload Issues

- Check that `uploads/profile_pictures/` is writable
- Verify file is under 2MB
- Ensure file is JPG or PNG format

## License

This project is provided for educational purposes.

## Support

For issues or questions, please check the documentation or contact the system administrator.
