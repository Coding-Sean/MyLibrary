📚 MyLibrary - Library Management System
<img src="https://img.shields. io/badge/version-1.0.0-blue.svg" alt="Version">

<img src="https://img.shields. io/badge/PHP-8. 0+-purple.svg" alt="PHP">

<img src="https://img. shields.io/badge/MySQL-8.0+-orange.svg" alt="MySQL">

<img src="https://img. shields.io/badge/Bootstrap-5.3. 3-purple.svg" alt="Bootstrap">

<img src="https://img. shields.io/badge/license-MIT-green.svg" alt="License">

A comprehensive web-based library management system built with PHP, MySQL, and Bootstrap.  Designed for educational institutions to manage books, borrowers, reservations, and penalties efficiently.

__________________________________________________________________________________________________________________________________________________


📋 Table of Contents


Features


System Requirements


Installation


Database Setup


Project Structure


User Roles


Usage Guide


Security Features


Technologies Used


API Documentation


Troubleshooting


Contributing


License


Contact
__________________________________________________________________________________________________________________________________________________
✨ Features

**Core Functionality**

✅ User Authentication - Secure login/signup with password hashing

✅ Role-Based Access Control - Four distinct user roles with specific permissions

✅ Book Management - Complete CRUD operations for library inventory

✅ Borrowing System - Track book checkouts and returns

✅ Reservation System - Online book reservation with approval workflow

✅ Penalty Management - Automatic calculation of late fees

✅ Clearance Processing - Semester-end clearance verification

✅ Real-time Search - Instant book search by title, author, or category

**Technical Features**

🔒 Security First - SQL injection prevention, XSS protection, password hashing

🎨 Modern UI - Responsive design with custom styled components

📱 Mobile Friendly - Works seamlessly on all devices

🏗️ MVC Architecture - Clean code organization and separation of concerns

🔄 OOP Principles - Inheritance, polymorphism, and encapsulation

⚡ Optimized Performance - Efficient database queries and caching

__________________________________________________________________________________________________________________________________________________

💻 System Requirements

Minimum Requirements

Web Server: Apache 2.4+ (XAMPP recommended)

PHP: Version 8.0 or higher

MySQL: Version 8.0 or higher

Browser: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+

RAM: 2GB minimum (4GB recommended)

Storage: 500MB free space

Recommended Development Environment

XAMPP: Version 8. 0.x or higher

PHP Extensions: PDO, MySQLi, mbstring, openssl

Text Editor: VS Code, Sublime Text, or PHPStorm

Screen Resolution: 1920x1080 or higher

__________________________________________________________________________________________________________________________________________________

🚀 Installation

Step 1: Download and Extract

Clone the repository or download ZIP

```bash
git clone https://github. com/yourusername/MyLibrary.git

# OR extract downloaded ZIP to
C:\xampp\htdocs\MyLibrary
```

Step 2: Install XAMPP

Download XAMPP from https://www.apachefriends.org

Install with default settings

Start Apache and MySQL modules from XAMPP Control Panel

Step 3: Verify Installation

Open browser and navigate to:

http://localhost/MyLibrary

__________________________________________________________________________________________________________________________________________________

📁 Project Structure

```
MyLibrary/
│
├── assets/                      # CSS Stylesheets
│   ├── login.css               # Login page styles
│   ├── signup.css              # Signup page styles
│   ├── librarian.css           # Librarian dashboard styles
│   ├── staff.css               # Staff dashboard styles
│   └── stud_teacher.css        # Student/Teacher dashboard styles
│
├── config/                      # Configuration Files
│   └── database.php            # Database connection settings
│
├── controller/                  # Request Handlers (Controllers)
│   ├── BaseController.php      # Parent controller (inheritance)
│   ├── LoginController.php     # Login authentication
│   ├── SignupController.php    # User registration
│   ├── LogoutController.php    # Session termination
│   ├── LibrarianController.php # Book CRUD operations
│   ├── StaffController.php     # Borrowing/Return/Penalty
│   └── ReservationController.php # Reservation handling
│
├── database/                    # Database Scripts
│   └── db_schema.sql           # Database schema and structure
│
├── includes/                    # Reusable Components
│   ├── messages.php            # Custom alert messages
│   └── confirm_modal. php       # Custom confirmation dialogs
│
├── model/                       # Business Logic (Models)
│   ├── BaseModel.php           # Parent model (inheritance)
│   ├── User.php                # User authentication model
│   ├── LibrarianModel. php      # Book management model
│   ├── StaffModel. php          # Staff operations model
│   └── StudentTeacherModel.php # Student/Teacher operations model
│
├── view/                        # User Interfaces (Views)
│   ├── Log_In.php              # Login page
│   ├── Sign_Up.php             # Registration page
│   ├── Librarian_Dashboard.php # Librarian interface
│   ├── Staff_Dashboard.php     # Staff interface
│   ├── Teach_Stud_Dashboard.php # Student/Teacher interface
│   └── Librarian_Functions/
│       ├── Add_Book.php        # Add book form
│       └── Edit_Book. php       # Edit book form
│
├── index.php                    # Entry point (redirects to login)
└── README.md                    # This file
```

__________________________________________________________________________________________________________________________________________________
