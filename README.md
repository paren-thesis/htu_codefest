# HTU COMPSSA CODEFEST 2025 - Departmental Dues Management System

## Project Overview

This is a web-based departmental dues management system developed for HTU COMPSSA CODEFEST 2025. The system allows for managing student data, processing dues payments, and generating reports with different user roles and permissions.

## Features

- **Multi-role Authentication System**: Administrator, Supervisor, Cashier, Lecturer, and Student roles
- **Student Data Management**: Import, add, edit, and search student records
- **Payment Processing**: Track dues payments with receipt generation
- **Reporting System**: Generate various reports and analytics
- **CSV Import**: Bulk import student data from CSV files
- **Responsive Design**: Bootstrap-based UI following the project style guide

## Technology Stack

- **Backend**: PHP 8.0+
- **Database**: MySQL 8.0+
- **Frontend**: HTML5, CSS3, Bootstrap 5.3
- **Server**: Apache (XAMPP)
- **Security**: PDO with prepared statements, password hashing

## Database Design

### Tables Structure

1. **roles** - User role definitions
   - role_id (Primary Key)
   - role_name (administrator, supervisor, cashier, lecturer, student)
   - description
   - created_at

2. **programmes** - Academic programmes
   - programme_id (Primary Key)
   - programme_name
   - programme_code
   - created_at

3. **users** - Authentication and user management
   - user_id (Primary Key)
   - username (Unique)
   - password_hash (Encrypted)
   - email (Unique)
   - role_id (Foreign Key to roles)
   - is_active
   - last_login
   - created_at, updated_at

4. **students** - Student information
   - student_id (Primary Key)
   - index_no (Unique)
   - first_name, surname
   - email (Unique)
   - phone
   - academic_year
   - programme_id (Foreign Key to programmes)
   - position
   - start_date
   - user_id (Foreign Key to users, nullable)
   - created_at, updated_at

5. **payments** - Payment records
   - payment_id (Primary Key)
   - student_id (Foreign Key to students)
   - amount
   - receipt_no (Unique)
   - payment_date
   - academic_year
   - created_by (Foreign Key to users)
   - created_at

### Views

- **student_payment_summary** - Aggregated student payment information

## Installation & Setup

### Prerequisites

1. XAMPP installed and running
2. PHP 8.0 or higher
3. MySQL 8.0 or higher
4. Web browser

### Setup Instructions

1. **Clone/Download the Project**
   ```bash
   # Place the project in your XAMPP htdocs directory
   C:\xampp\htdocs\htu_codefest_25\
   ```

2. **Start XAMPP Services**
   - Start Apache and MySQL services from XAMPP Control Panel

3. **Create Database**
   ```sql
   # Open phpMyAdmin (http://localhost/phpmyadmin)
   # Import the database_setup.sql file
   # Or run the SQL commands manually
   ```

4. **Configure Database Connection**
   - Edit `includes/config.php` if needed
   - Default settings work with XAMPP default configuration

5. **Import Student Data**
   - Navigate to `http://localhost/htu_codefest_25/import_students.php`
   - Click "Import Student Data" to populate the database

6. **Access the Application**
   - Open `http://localhost/htu_codefest_25/` in your browser

## Default Login Credentials

- **Username**: admin
- **Password**: admin123
- **Role**: Administrator

## Project Structure

```
htu_codefest_25/
├── assets/                 # Images and static assets
├── css/                   # Custom CSS files
├── docs/                  # Documentation
├── includes/              # PHP includes and configuration
│   └── config.php        # Database and application configuration
├── js/                    # JavaScript files
├── pages/                 # Application pages
├── uploads/               # File upload directory
├── database_setup.sql     # Database schema
├── import_students.php    # CSV import script
├── students.csv           # Sample student data
└── README.md             # This file
```

## Style Guide Compliance

The application follows the HTU Codefest 2025 style guide:

### Colors
- **Primary Orange Brown**: #FF8B00 (RGB: 255, 139, 0)
- **Primary Blue**: #050589 (RGB: 5, 5, 137)
- **Background Yellow**: #F5D200 (RGB: 245, 210, 0)
- **White**: #FFFFFF (RGB: 255, 255, 255)

### Typography
- **Font**: Arial
- **Sizes**: 10-36pt
- **Variations**: Regular, Italic, Bold

### Layout Requirements
- Header with logo and title on all pages
- Consistent alignment and whitespace
- Logical grouping of elements
- At least one non-white background element per window

## Security Features

- **SQL Injection Prevention**: PDO prepared statements
- **XSS Protection**: Input sanitization and output escaping
- **Password Security**: bcrypt hashing
- **Session Management**: Secure session handling
- **Role-based Access Control**: Permission-based navigation

## User Roles & Permissions

1. **Administrator** (Level 5)
   - Full system access
   - User management
   - System configuration

2. **Supervisor** (Level 4)
   - View reports
   - Manage students
   - Payment oversight

3. **Cashier** (Level 3)
   - Process payments
   - View student data
   - Generate receipts

4. **Lecturer** (Level 2)
   - View student data
   - Access reports
   - Limited editing

5. **Student** (Level 1)
   - View own data
   - Payment history
   - Profile management

## API Endpoints

The system provides RESTful endpoints for:
- User authentication
- Student data management
- Payment processing
- Report generation

## Error Handling

- Comprehensive exception handling
- User-friendly error messages
- Detailed logging for debugging
- Graceful degradation

## Performance Optimization

- Database indexing on frequently queried fields
- Prepared statements for query optimization
- Efficient data pagination
- Caching strategies

## Testing

- Unit tests for core functions
- Integration tests for database operations
- User acceptance testing scenarios
- Security testing protocols

## Deployment

### Production Checklist
- [ ] Disable error reporting
- [ ] Configure secure database credentials
- [ ] Set up SSL certificate
- [ ] Configure backup procedures
- [ ] Set up monitoring and logging

## Support & Documentation

For technical support or questions:
- Review the inline code documentation
- Check the database schema documentation
- Refer to the style guide for UI/UX questions

## License

This project is developed for HTU COMPSSA CODEFEST 2025 educational purposes.

---

**Developed for HTU COMPSSA CODEFEST 2025**  
*IT Software Solutions for Business* 