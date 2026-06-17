# Web Page University - File Structure Documentation

## 📂 New Organized Structure

This document describes the organized file structure of the Web Page University Student Management System.

```
Web_page_uni/
│
├── 📂 public/                          # Public-facing files (web root)
│   ├── index.html                     # Home page
│   ├── about.html                     # About page
│   ├── contact.html                   # Contact page
│   ├── program.html                   # Programs page
│   ├── login.php                      # Login entry point
│   ├── signup.php                     # Registration entry point
│   ├── events.php                     # Public events listing
│   ├── list_faculty.php               # Public faculty listing
│   │
│   ├── 📂 assets/                     # Stylesheets and static assets
│   │   └── style.css                  # Main stylesheet
│   │
│   └── 📂 images/                     # Images and media
│       ├── logo.png
│       ├── logo.webp
│       ├── University images.webp
│       ├── vice chancellor.webp
│       └── ChatGPT Image Oct 1, 2025, 10_03_37 AM.png
│
├── 📂 admin/                           # Admin section
│   ├── login.php                      # Admin login
│   ├── dashboard.php                  # Admin main dashboard
│   ├── logout.php                     # Admin logout
│   │
│   ├── 📂 students/                   # Student management
│   │   ├── add_student.php            # Add new student
│   │   └── edit_student.php           # Edit student info
│   │
│   ├── 📂 bookings/                   # Booking management
│   │   ├── bookings.php               # View all bookings
│   │   └── events.php                 # Manage events
│   │
│   ├── 📂 notifications/              # News & notices management
│   │   ├── news.php                   # Manage news
│   │   └── notices.php                # Manage notices
│   │
│   ├── 📂 faculty_mgmt/               # Faculty management
│   │   ├── faculty.php                # Manage faculty
│   │   └── departments.php            # Manage departments
│   │
│   └── 📂 utils/                      # Admin utilities
│       └── debug_log.txt              # Debug logs
│
├── 📂 faculty/                         # Faculty section
│   ├── login.php                      # Faculty login
│   ├── dashboard.php                  # Faculty dashboard
│   ├── logout.php                     # Faculty logout
│   │
│   ├── 📂 profile/                    # Faculty profile management
│   │   └── edit_faculty.php           # Edit faculty profile
│   │
│   └── 📂 notifications/              # News & notices viewing
│       ├── news.php                   # View news
│       └── notices.php                # View notices
│
├── 📂 user/                            # Student/User section
│   ├── login.php                      # Student login (redirects to public/login.php)
│   ├── dashboard.php                  # Student dashboard
│   ├── logout.php                     # Student logout
│   │
│   └── 📂 bookings_module/            # Booking functionality
│       ├── booking.php                # Make booking
│       ├── booking_success.php        # Booking confirmation
│       ├── process_booking.php        # Process booking form
│       └── get_slots.php              # Get available slots
│
├── 📂 includes/                        # Shared PHP includes
│   └── 📂 fpdf/                       # PDF generation library
│       ├── fpdf.php                   # FPDF main class
│       └── 📂 font/                   # FPDF fonts
│           └── helvetica.php          # Helvetica font
│
├── 📂 uploads/                         # File uploads storage
│   ├── 📂 profiles/                   # Student profile pictures
│   │   └── profile_69817052bc6b39.31254056.png
│   │
│   └── 📂 bookings/                   # Booking-related uploads
│
├── 📂 lib/                             # Library & utility files
│   ├── config.php                     # Database configuration
│   └── update_database.php            # Database update utilities
│
├── 📂 setup/                           # Setup scripts (run once, then secure)
│   ├── setup_admin.php                # Setup admin user
│   ├── setup_booking_db.php           # Setup booking database
│   └── setup_database.php             # Initial database setup
│
├── 📂 debug/                           # Debug & testing utilities (REMOVE BEFORE PRODUCTION)
│   ├── debug_helper.php               # Debug helper functions
│   ├── debug_users.php                # User debugging tool
│   ├── check_hash.php                 # Hash verification tool
│   ├── check_table_structure.php      # Table structure checker
│   ├── fix_faculty.php                # Faculty fixing tool
│   └── admin_hash.txt                 # Admin hash notes
│
├── database.sql                        # Database schema & initial data
├── config.php                          # DEPRECATED: Use lib/config.php instead
├── .htaccess                           # Apache rewrite rules
├── README_STUDENT_MGMT.md              # Setup guide
└── STRUCTURE.md                        # This file
```

## 🔑 Key Organization Principles

### 1. **Public Directory** (`public/`)
- Entry point files for the web application
- All publicly accessible content (HTML, CSS, images)
- Static files served directly to users

### 2. **Role-Based Sections** (`admin/`, `faculty/`, `user/`)
- Separated by user role for better security and organization
- Each has login, dashboard, and logout handlers
- Subfolders group related functionality

### 3. **Shared Resources** (`includes/`, `lib/`)
- **includes/** - PHP includes like FPDF library
- **lib/** - Configuration and utility functions
- Shared across all sections

### 4. **File Storage** (`uploads/`)
- **profiles/** - Student profile pictures
- **bookings/** - Booking-related files
- Easy to manage and backup

### 5. **Setup & Debug** (`setup/`, `debug/`)
- **setup/** - Run once during installation
- **debug/** - Testing/debugging utilities (remove before production)
- Keep separate for security

## 🚀 Configuration Notes

After reorganization, update file paths in:

1. **HTML files in `public/`** - Update references:
   ```html
   <!-- Old -->
   <link rel="stylesheet" href="style.css">
   
   <!-- New -->
   <link rel="stylesheet" href="assets/style.css">
   ```

2. **PHP includes** - Update require/include paths:
   ```php
   // Old
   require 'config.php';
   
   // New
   require '../lib/config.php';
   ```

3. **.htaccess** - Ensure rewrites match new structure

## ⚠️ Production Checklist

Before deploying to production:

- [ ] Remove `debug/` folder entirely
- [ ] Secure `setup/` folder (delete or restrict access)
- [ ] Update all file path references
- [ ] Test all login flows (student, admin, faculty)
- [ ] Test booking system
- [ ] Verify file uploads work correctly
- [ ] Check permissions on `uploads/` folder (755)

## 📝 File Migration Notes

Files moved on **2026-06-17**:

- Public files: `about.html`, `contact.html`, `program.html`, `index.html` → `public/`
- Styles: `style.css` → `public/assets/`
- Images: `logo.png`, `logo.webp`, etc. → `public/images/`
- Admin files: Organized into subfolders (`students/`, `bookings/`, etc.)
- Faculty files: Organized into subfolders (`profile/`, `notifications/`)
- User files: Booking functionality → `bookings_module/`
- Setup scripts: → `setup/` folder
- Debug tools: → `debug/` folder

---

**Last Updated:** 2026-06-17  
**Organizer:** System Reorganization Tool
