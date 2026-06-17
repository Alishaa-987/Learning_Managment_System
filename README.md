# 📚 Learning Management System

A comprehensive Student Management System with multi-role support (Student, Admin, Faculty) for managing educational operations including student registration, bookings, news, and faculty management.

## 🚀 Quick Start

### Prerequisites
- PHP 7.4+
- MySQL 5.7+
- Apache with `.htaccess` support

### Setup
1. **Clone & Navigate**
   ```bash
   git clone https://github.com/Alishaa-987/Learning_Managment_System.git
   cd Learning_Managment_System
   ```

2. **Database Setup**
   - Import `database.sql` into MySQL
   - Update credentials in `lib/config.php`

3. **Access Points**
   - **Home:** `public/index.html`
   - **Login:** `public/login.php` (Students/Admin/Faculty)
   - **Register:** `public/signup.php` (Students)

## 📂 Project Structure

```
├── public/              # Web root (HTML, CSS, entry points)
├── admin/               # Admin dashboard & management
├── faculty/             # Faculty portal
├── user/                # Student portal
├── lib/                 # Database config & utilities
├── includes/            # Shared libraries (FPDF)
├── uploads/             # File storage
├── setup/               # Installation scripts
└── debug/               # Testing tools (remove before production)
```

**For detailed structure:** See [docs/STRUCTURE.md](docs/STRUCTURE.md)

## 👥 User Roles

| Role | Entry Point | Features |
|------|-------------|----------|
| **Student** | `public/login.php` | Dashboard, Bookings, Profile |
| **Admin** | `public/login.php` | Manage Students, Events, Faculty, News |
| **Faculty** | `public/login.php` | Dashboard, Profile, News |

## 🔧 Key Features

✅ Student registration & authentication  
✅ Admin dashboard with student management  
✅ Event booking system  
✅ News & notices management  
✅ Faculty management  
✅ Role-based access control  
✅ PDF generation (FPDF)  

## ⚠️ Production Checklist

- [ ] Delete `debug/` folder
- [ ] Secure `setup/` folder
- [ ] Update file paths if needed
- [ ] Set proper permissions on `uploads/` (755)
- [ ] Change default admin password
- [ ] Test all login flows

## 📖 Documentation

- [Full Project Structure](docs/STRUCTURE.md) - Detailed file organization and architecture

## 🔐 Security

- Password hashing with `password_hash()`
- PDO prepared statements (SQL injection protection)
- Role-based access control

## 📝 License

Educational Project

---

**Need help?** Check [docs/STRUCTURE.md](docs/STRUCTURE.md) for detailed setup and troubleshooting.