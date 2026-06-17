# Student Management System - Setup Guide

## 📋 Overview
Complete Student Management System with two roles:
- **Student (User)**: Can signup, login, and view their profile
- **Admin**: Can login, view all students, add/edit/delete students, search and filter

## 🗄️ Database Setup

### Step 1: Create Database
1. Open phpMyAdmin or MySQL command line
2. Import the `database.sql` file OR run the following:

```sql
-- Create database
CREATE DATABASE student_mgmt;

-- Use the database
USE student_mgmt;

-- Then run the SQL commands from database.sql file
```

### Step 2: Configure Database Connection
Edit `config.php` and update these constants if needed:
```php
define('DB_HOST', 'localhost');  // Your MySQL host
define('DB_USER', 'root');       // Your MySQL username
define('DB_PASS', '');           // Your MySQL password
define('DB_NAME', 'student_mgmt'); // Database name
```

## 📁 File Structure

```
Web_page_uni/
├── config.php                 # Database configuration
├── database.sql               # Database schema
├── login.php                  # Login page (Student/Admin)
├── signup.php                 # Student registration
├── user/
│   ├── dashboard.php          # Student dashboard
│   └── logout.php             # Student logout
├── admin/
│   ├── login.php              # Admin login redirect
│   ├── dashboard.php          # Admin dashboard
│   ├── add_student.php        # Add new student
│   ├── edit_student.php       # Edit student
│   └── logout.php             # Admin logout
└── uploads/                   # Profile pictures directory (create this)
```

## 🔧 Setup Instructions

### 1. Create Uploads Directory
Create a folder named `uploads` in the root directory:
```bash
mkdir uploads
chmod 777 uploads  # Linux/Mac - allow write permissions
```

### 2. Set File Permissions (Linux/Mac)
```bash
chmod 755 uploads/
```

### 3. Default Admin Credentials
After importing `database.sql`, default admin credentials are:
- **Email**: admin@university.edu
- **Password**: admin123

⚠️ **Important**: Change the admin password after first login!

## 🚀 Usage

### For Students:
1. Go to `signup.php` to register
2. Fill all required fields
3. Upload profile picture (optional)
4. After registration, login at `login.php`
5. Select "Student" role and enter credentials
6. View profile at `user/dashboard.php`

### For Admin:
1. Go to `login.php`
2. Select "Admin" role
3. Enter admin credentials
4. Access admin dashboard at `admin/dashboard.php`
5. Features available:
   - View all students
   - Add new student
   - Edit student information
   - Delete student
   - Search by name, email, or AG No
   - Filter by department

## 🔐 Security Features

- ✅ Password hashing using `password_hash()`
- ✅ PDO prepared statements (SQL injection protection)
- ✅ Input sanitization
- ✅ Session management
- ✅ Role-based access control
- ✅ File upload validation
- ✅ Email, CNIC, and phone number validation

## 📝 Database Fields

### Students Table:
- `stu_id` - Primary key (auto increment)
- `name` - Student full name
- `father_name` - Father's name
- `dob` - Date of birth
- `cnic` - CNIC (unique)
- `gender` - Male/Female/Other
- `email` - Email (unique)
- `adm_date` - Admission date
- `ag_no` - AG/Registration number (unique)
- `department` - Department name
- `class` - Class (optional)
- `section` - Section (optional)
- `degree` - Degree program
- `phone_no` - Phone number
- `address` - Address
- `stu_status` - Active/Inactive/Graduated/Suspended
- `password` - Hashed password
- `profile_picture` - Profile picture filename
- `role` - student/admin
- `created_at` - Timestamp
- `updated_at` - Timestamp

## 🎨 Design Notes

- All pages use your existing `style.css`
- HTML/CSS layout is preserved
- Only PHP backend code added
- Responsive design maintained
- Custom cursor works on all pages

## ⚠️ Important Notes

1. **Database**: Make sure MySQL is running
2. **PHP Version**: Requires PHP 7.0+ (for password_hash)
3. **File Uploads**: Ensure `uploads/` directory has write permissions
4. **Session**: Sessions are stored server-side
5. **Security**: Change default admin password immediately

## 🐛 Troubleshooting

### Database Connection Error:
- Check MySQL is running
- Verify credentials in `config.php`
- Ensure database `student_mgmt` exists

### File Upload Not Working:
- Check `uploads/` directory exists
- Verify write permissions (chmod 777)
- Check `MAX_FILE_SIZE` in `config.php`

### Login Not Working:
- Verify user exists in database
- Check password is correct
- Ensure role matches (student/admin)

### Session Issues:
- Check PHP session is enabled
- Verify `session_start()` is called
- Clear browser cookies if needed

## 📞 Support

For issues or questions, check:
1. PHP error logs
2. MySQL error logs
3. Browser console for JavaScript errors
4. Network tab for HTTP errors

---

**Created with**: PHP, MySQL, PDO, HTML, CSS
**Version**: 1.0
**Last Updated**: 2025

