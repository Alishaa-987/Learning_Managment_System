<img width="1881" height="919" alt="Screenshot 2026-02-03 085702" src="https://github.com/user-attachments/assets/f50c7230-6ebc-4555-9e54-687228b128e1" />
# 🎓 Learning Management System

![Learning Management System](assets/screenshots/admin-dashboard.png)

A comprehensive web-based **Learning Management & Student Management System** built using **PHP, MySQL, HTML, CSS, and JavaScript**. The platform provides dedicated portals for **Students, Faculty Members, and Administrators**, enabling efficient management of academic activities, student records, faculty information, event bookings, university announcements, and profile management.

---

## 📌 Overview

The Learning Management System (LMS) is designed to streamline educational institution operations through a centralized digital platform. Students can manage their profiles and bookings, faculty members can access institutional information, and administrators can efficiently manage students, departments, faculty, events, notices, and news.

---

## ✨ Features

### 🔐 Authentication & Authorization

- Secure Login System
- Student Registration
- Password Hashing
- Session Management
- Role-Based Access Control

### 👨‍🎓 Student Portal

- Student Registration & Login
- Personalized Dashboard
- Profile Management
- Event & Booking Requests
- View News & Notices
- Download Booking PDFs

### 👨‍🏫 Faculty Portal

- Faculty Dashboard
- Faculty Profile Management
- View University Announcements
- Access News & Notices

### 👨‍💼 Admin Portal

- Student Management
- Faculty Management
- Department Management
- Event Management
- News & Notice Management
- Booking Request Monitoring
- User Administration

### 📄 PDF Generation

- Automated PDF Generation using FPDF
- Booking Confirmation Documents
- Downloadable Records

---

## 🛠️ Technology Stack

### Frontend

- HTML5
- CSS3
- JavaScript

### Backend

- PHP

### Database

- MySQL

### Libraries & Tools

- PDO (Database Connectivity)
- FPDF (PDF Generation)

### Development Environment

- XAMPP / WAMP
- Apache Server

---

## 🚀 Quick Start

### Prerequisites

- PHP 7.4+
- MySQL 5.7+
- Apache Server
- XAMPP / WAMP

### Clone Repository

```bash
git clone https://github.com/Alishaa-987/Learning_Managment_System.git
cd Learning_Managment_System
```

### Database Setup

1. Open **phpMyAdmin**
2. Create a new database:

```sql
student_mgmt
```

3. Import the provided:

```text
database.sql
```

### Configure Database Connection

Update database credentials inside:

```text
lib/config.php
```

Example:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'student_mgmt');
```

### Run the Application

Place the project inside:

```text
xampp/htdocs/
```

Start:

- Apache
- MySQL

Access the application:

```text
http://localhost/Learning_Managment_System
```

---

## 📂 Project Structure

```text
Learning_Managment_System/
│
├── public/                  # Public entry points
│   ├── index.html
│   ├── login.php
│   └── signup.php
│
├── admin/                   # Admin dashboard & management
│   ├── dashboard.php
│   ├── students.php
│   ├── faculty.php
│   ├── departments.php
│   ├── events.php
│   ├── notices.php
│   └── news.php
│
├── faculty/                 # Faculty portal
│   ├── dashboard.php
│   ├── notices.php
│   ├── news.php
│   └── profile.php
│
├── user/                    # Student portal
│   ├── dashboard.php
│   ├── booking.php
│   ├── profile.php
│   └── process_booking.php
│
├── lib/                     # Configuration files
│   └── config.php
│
├── includes/                # Shared libraries
│   └── fpdf/
│
├── uploads/
│   ├── profile_images/
│   └── Bookings_Pdf/
│
├── setup/
├── database.sql
└── README.md
```

---

## 👥 User Roles

| Role | Access | Features |
|--------|---------|---------|
| **Student** | Student Portal | Dashboard, Bookings, Profile Management |
| **Faculty** | Faculty Portal | Profile Management, News & Notices |
| **Admin** | Admin Dashboard | Student, Faculty, Department & Event Management |

---



```text
assets/screenshots/
```

### Home Page

```md
![Home Page](screenshots/home-page.png)<img width="1881" height="919" alt="Screenshot 2026-02-03 085702" src="https://github.com/user-attachments/assets/d43e128d-3f79-4fea-8ee5-7f23e1a55340" />

```



## 🔒 Security Features

- Password Hashing
- PDO Prepared Statements
- SQL Injection Protection
- Session-Based Authentication
- Role-Based Authorization

---

## ⚠️ Production Checklist

- [ ] Remove Debug Files
- [ ] Secure Setup Directory
- [ ] Configure Proper File Permissions
- [ ] Update Database Credentials
- [ ] Change Default Admin Credentials
- [ ] Test All User Workflows
- [ ] Enable HTTPS in Production

---

## 🎯 Learning Outcomes

This project demonstrates:

- PHP Development
- CRUD Operations
- Database Design
- Authentication & Authorization
- Session Management
- Role-Based Access Control
- PDF Generation
- University Management Workflows

---

## 🔮 Future Enhancements

- Attendance Management
- Course Management
- Online Examination System
- Assignment Submission Portal
- Email Notifications
- Student Performance Analytics
- AI-Powered Academic Assistant

---

