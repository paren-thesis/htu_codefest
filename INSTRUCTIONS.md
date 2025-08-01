# HTU Departmental Dues Management System  
**Comprehensive Setup & Usage Guide**

---

## 1. Prerequisites

- **XAMPP** (PHP 8.0+ and MySQL 8.0+)
- A modern web browser (Chrome, Firefox, Edge, etc.)
- The project folder (e.g., `htu_codefest`) located on your Desktop

---

## 2. Project Structure

```
htu_codefest/
├── assets/
│   └── Logo_Worldskills_Ghana.png
├── css/
│   └── style.css
├── includes/
│   ├── functions.php
│   └── header.php
├── config/
│   ├── config.php
│   └── database.php
├── windows/
│   ├── login.php
│   ├── register.php
│   ├── change_password.php
│   ├── control.php
│   ├── data.php
│   ├── payment.php
│   ├── report.php
│   └── users.php
├── students.csv
├── database_setup.sql
├── index.php
├── README.md
└── Project.txt
```

---

## 3. Installation & Setup

### **A. Move Project to XAMPP Directory**

1. Copy the entire `htu_codefest` folder from your Desktop to your XAMPP `htdocs` directory:
   - Example:  
     ```
     C:\xampp\htdocs\htu_codefest
     ```

### **B. Start XAMPP Services**

1. Open the XAMPP Control Panel.
2. Start **Apache** and **MySQL**.

### **C. Create the Database**

1. Open your browser and go to [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
2. Click the **Import** tab.
3. Click **Choose File** and select `database_setup.sql` from your project folder.
4. Click **Go** to import the database and tables.

   - This will create a database named `htu_codefest_25` with all required tables and a default admin user.

### **D. Configure Database Connection (if needed)**

- By default, the project uses:
  - Host: `localhost`
  - Database: `htu_codefest_25`
  - User: `root`
  - Password: *(empty)*
- If your MySQL settings are different, edit `config/database.php` accordingly.

### **E. Access the Application**

1. In your browser, go to:  
   [http://localhost/htu_codefest/](http://localhost/htu_codefest/)
2. You will be redirected to the login page.

---

## 4. Default Admin Login

- **Username:** `admin`
- **Password:** `admin123`  
  *(You should change this password after first login.)*

---

## 5. User Roles & Permissions

| Role           | Data Management | Payment Processing | Reports | User Management |
|----------------|----------------|-------------------|---------|-----------------|
| Administrator  | Full           | Full              | Full    | Full            |
| Supervisor     | Full           | View Only         | Full    | No              |
| Cashier        | No             | Full              | No      | No              |
| Lecturer       | Full           | No                | Full    | No              |
| Student        | View Own Only  | No                | No      | No              |

---

## 6. Features Overview

- **Authentication:** Secure login, registration, and password change.
- **Role-Based Access:** Each user sees only what they are permitted to.
- **Student Data:** Admins, supervisors, and lecturers can manage all students. Students can only view their own record.
- **Payments:** Admins and cashiers can process payments. Supervisors can view payment history. Students cannot access payment features.
- **Reports:** Admins, supervisors, and lecturers can view and export payment reports.
- **User Management:** Admins can add, edit, and delete users.
- **CSV Import:** Admins, supervisors, and lecturers can import student data from a CSV file.
- **Modern UI:** Responsive, professional design with Bootstrap 5 and custom styles.

---

## 7. How to Use

### **A. Logging In**

- Go to [http://localhost/htu_codefest/](http://localhost/htu_codefest/)
- Use your credentials to log in.

### **B. Admin Tasks**

- **Add Users:** Go to User Management and add new users with appropriate roles.
- **Import Students:** Use the Data window to import students from `students.csv`.
- **Manage Students:** Add, edit, or delete student records as needed.
- **Process Payments:** Use the Payment window to record dues payments.
- **View Reports:** Use the Report window to analyze and export payment data.

### **C. Student Experience**

- Students can log in and only see their own data (no access to other students or management features).

---

## 8. Security Notes

- All passwords are securely hashed.
- All user input is sanitized to prevent XSS and SQL injection.
- Role checks are enforced both in the UI and on the server.

---

## 9. Troubleshooting

- **Blank Page/Error:** Check XAMPP is running, and review the PHP error log (`xampp/php/logs/php_error_log`).
- **Database Connection Error:** Double-check your `config/database.php` settings and that the database was imported correctly.
- **Permission Issues:** Make sure you are logged in with the correct role for the action you want to perform.

---

## 10. Customization

- **Style:** Edit `css/style.css` to change the look and feel.
- **Logo:** Replace `assets/Logo_Worldskills_Ghana.png` with your own logo if desired.
- **Roles:** You can add or modify roles in the `roles` table and update permissions in the code as needed.

---

## 11. Support

For further help, consult the `README.md` or contact the project maintainer.

---

**Enjoy using the HTU Departmental Dues Management System!**