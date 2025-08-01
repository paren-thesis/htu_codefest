<?php
/**
 * HTU COMPSSA CODEFEST 2025 - Data Window (Students)
 * Student data management and CSV import functionality
 * 
 * Features:
 * - Import data from CSV with auto/manual correction
 * - Add new students
 * - Edit existing student data
 * - Search functionality
 * - Display student data
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and has permission
if (!isLoggedIn() || !in_array($_SESSION['user_role'], ['administrator', 'supervisor', 'lecturer', 'student'])) {
    redirect('login.php');
}

$error_message = '';
$success_message = '';
$students = [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Only allow non-student roles to perform data management actions
    if (!in_array($_SESSION['user_role'], ['student'])) {
        switch ($action) {
            case 'import_csv':
                handleCSVImport();
                break;
            case 'add_student':
                handleAddStudent();
                break;
            case 'edit_student':
                handleEditStudent();
                break;
            case 'delete_student':
                handleDeleteStudent();
                break;
        }
    } else {
        $error_message = 'You do not have permission to perform this action.';
    }
}

function handleCSVImport() {
    global $error_message, $success_message;
    
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        $error_message = 'Please select a valid CSV file.';
        return;
    }
    
    $file = $_FILES['csv_file'];
    $filename = $file['name'];
    $tmp_name = $file['tmp_name'];
    
    // Validate file type
    $file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if ($file_extension !== 'csv') {
        $error_message = 'Please upload a CSV file.';
        return;
    }
    
    // Validate file size
    if ($file['size'] > MAX_FILE_SIZE) {
        $error_message = 'File size exceeds the maximum limit.';
        return;
    }
    
    try {
        $pdo = getDBConnection();
        
        // Read CSV file
        $handle = fopen($tmp_name, 'r');
        if (!$handle) {
            $error_message = 'Unable to read the CSV file.';
            return;
        }
        
        // Skip header row
        $header = fgetcsv($handle);
        $imported_count = 0;
        $errors = [];
        
        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) < 6) {
                $errors[] = "Row has insufficient data";
                continue;
            }
            
            // Parse CSV data (adjust based on your CSV structure)
            $name = sanitizeInput($data[0] ?? '');
            $index_no = sanitizeInput($data[1] ?? '');
            $email = sanitizeInput($data[2] ?? '');
            $phone = sanitizeInput($data[3] ?? '');
            $academic_year = sanitizeInput($data[4] ?? '');
            $programme = sanitizeInput($data[5] ?? '');
            
            // Validate required fields
            if (empty($index_no) || empty($email)) {
                $errors[] = "Row missing required data: Index No or Email";
                continue;
            }
            
            // Check if student already exists
            $stmt = $pdo->prepare("SELECT student_id FROM students WHERE index_no = ? OR email = ?");
            $stmt->execute([$index_no, $email]);
            if ($stmt->fetch()) {
                $errors[] = "Student with Index No $index_no or Email $email already exists";
                continue;
            }
            
            // Get or create programme
            $stmt = $pdo->prepare("SELECT programme_id FROM programmes WHERE programme_name = ?");
            $stmt->execute([$programme]);
            $programme_data = $stmt->fetch();
            
            if (!$programme_data) {
                // Create new programme
                $stmt = $pdo->prepare("INSERT INTO programmes (programme_name) VALUES (?)");
                $stmt->execute([$programme]);
                $programme_id = $pdo->lastInsertId();
            } else {
                $programme_id = $programme_data['programme_id'];
            }
            
            // Insert student
            $stmt = $pdo->prepare("INSERT INTO students (index_no, first_name, surname, email, phone, academic_year, programme_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$index_no, $name, '', $email, $phone, $academic_year, $programme_id]);
            
            $imported_count++;
        }
        
        fclose($handle);
        
        if ($imported_count > 0) {
            $success_message = "Successfully imported $imported_count students.";
            if (!empty($errors)) {
                $success_message .= " Errors: " . implode(', ', array_slice($errors, 0, 5));
            }
        } else {
            $error_message = "No students were imported. Please check your CSV format.";
        }
        
    } catch (PDOException $e) {
        $error_message = 'Import failed: ' . $e->getMessage();
    }
}

function handleAddStudent() {
    global $error_message, $success_message;
    
    $index_no = sanitizeInput($_POST['index_no'] ?? '');
    $first_name = sanitizeInput($_POST['first_name'] ?? '');
    $surname = sanitizeInput($_POST['surname'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $academic_year = sanitizeInput($_POST['academic_year'] ?? '');
    $programme_id = sanitizeInput($_POST['programme_id'] ?? '');
    
    if (empty($index_no) || empty($first_name) || empty($email)) {
        $error_message = 'Please fill in all required fields.';
        return;
    }
    
    try {
        $pdo = getDBConnection();
        
        // Check if student already exists
        $stmt = $pdo->prepare("SELECT student_id FROM students WHERE index_no = ? OR email = ?");
        $stmt->execute([$index_no, $email]);
        if ($stmt->fetch()) {
            $error_message = 'Student with this Index No or Email already exists.';
            return;
        }
        
        // Insert new student
        $stmt = $pdo->prepare("INSERT INTO students (index_no, first_name, surname, email, phone, academic_year, programme_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$index_no, $first_name, $surname, $email, $phone, $academic_year, $programme_id]);
        
        $success_message = 'Student added successfully!';
        
    } catch (PDOException $e) {
        $error_message = 'Failed to add student: ' . $e->getMessage();
    }
}

function handleEditStudent() {
    global $error_message, $success_message;
    
    $student_id = sanitizeInput($_POST['student_id'] ?? '');
    $index_no = sanitizeInput($_POST['index_no'] ?? '');
    $first_name = sanitizeInput($_POST['first_name'] ?? '');
    $surname = sanitizeInput($_POST['surname'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $academic_year = sanitizeInput($_POST['academic_year'] ?? '');
    $programme_id = sanitizeInput($_POST['programme_id'] ?? '');
    
    if (empty($student_id) || empty($index_no) || empty($first_name) || empty($email)) {
        $error_message = 'Please fill in all required fields.';
        return;
    }
    
    try {
        $pdo = getDBConnection();
        
        // Check if email/index_no already exists for other students
        $stmt = $pdo->prepare("SELECT student_id FROM students WHERE (index_no = ? OR email = ?) AND student_id != ?");
        $stmt->execute([$index_no, $email, $student_id]);
        if ($stmt->fetch()) {
            $error_message = 'Student with this Index No or Email already exists.';
            return;
        }
        
        // Update student
        $stmt = $pdo->prepare("UPDATE students SET index_no = ?, first_name = ?, surname = ?, email = ?, phone = ?, academic_year = ?, programme_id = ? WHERE student_id = ?");
        $stmt->execute([$index_no, $first_name, $surname, $email, $phone, $academic_year, $programme_id, $student_id]);
        
        $success_message = 'Student updated successfully!';
        
    } catch (PDOException $e) {
        $error_message = 'Failed to update student: ' . $e->getMessage();
    }
}

function handleDeleteStudent() {
    global $error_message, $success_message;
    
    $student_id = sanitizeInput($_POST['student_id'] ?? '');
    
    if (empty($student_id)) {
        $error_message = 'Invalid student ID.';
        return;
    }
    
    try {
        $pdo = getDBConnection();
        
        // Check if student has payments
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM payments WHERE student_id = ?");
        $stmt->execute([$student_id]);
        if ($stmt->fetchColumn() > 0) {
            $error_message = 'Cannot delete student with payment records.';
            return;
        }
        
        // Delete student
        $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
        $stmt->execute([$student_id]);
        
        $success_message = 'Student deleted successfully!';
        
    } catch (PDOException $e) {
        $error_message = 'Failed to delete student: ' . $e->getMessage();
    }
}

// Get search parameters
$search = sanitizeInput($_GET['search'] ?? '');
$programme_filter = sanitizeInput($_GET['programme'] ?? '');
$year_filter = sanitizeInput($_GET['year'] ?? '');

// Fetch students with search and filters
try {
    $pdo = getDBConnection();
    
    $where_conditions = [];
    $params = [];
    
    if (!empty($search)) {
        $where_conditions[] = "(s.index_no LIKE ? OR s.first_name LIKE ? OR s.surname LIKE ? OR s.email LIKE ?)";
        $search_param = "%$search%";
        $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    }
    
    if (!empty($programme_filter)) {
        $where_conditions[] = "s.programme_id = ?";
        $params[] = $programme_filter;
    }
    
    if (!empty($year_filter)) {
        $where_conditions[] = "s.academic_year = ?";
        $params[] = $year_filter;
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    $sql = "SELECT s.*, p.programme_name 
            FROM students s 
            LEFT JOIN programmes p ON s.programme_id = p.programme_id 
            $where_clause 
            ORDER BY s.first_name, s.surname";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error_message = 'Failed to fetch students: ' . $e->getMessage();
}

// Get programmes for filter and forms
try {
    $stmt = $pdo->prepare("SELECT programme_id, programme_name FROM programmes ORDER BY programme_name");
    $stmt->execute();
    $programmes = $stmt->fetchAll();
} catch (PDOException $e) {
    $programmes = [];
}

// Get academic years for filter
try {
    $stmt = $pdo->prepare("SELECT DISTINCT academic_year FROM students ORDER BY academic_year DESC");
    $stmt->execute();
    $academic_years = $stmt->fetchAll();
} catch (PDOException $e) {
    $academic_years = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Student Data</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Header with Logo and Title -->
    <header class="app-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-2">
                    <img src="../assets/Logo_Worldskills_Ghana.png" alt="HTU Logo" class="logo">
                </div>
                <div class="col-md-8 text-center">
                    <h1 class="app-title"><?php echo APP_NAME; ?></h1>
                </div>
                <div class="col-md-2 text-end">
                    <a href="control.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Back to Control
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="text-center mb-4" style="color: var(--blue); font-size: 28px; font-weight: bold;">
                        Student Data Management
                    </h2>
                    
                    <!-- Display Messages -->
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success_message): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <!-- Import CSV Section - Only for Administrators, Supervisors, and Lecturers -->
                    <?php if (!in_array($_SESSION['user_role'], ['student'])): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-upload me-2"></i>Import CSV Data</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="import_csv">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="csv_file" class="form-label">Select CSV File</label>
                                            <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                                            <small class="text-muted">Maximum file size: 5MB. File should contain: Name, Index No, Email, Phone, Academic Year, Programme</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-upload me-2"></i>Import CSV
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Student View Only Message -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-eye me-2"></i>Student Data (View Only)</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Student Access:</strong> You can view student data but cannot import, add, edit, or delete records. 
                                Only administrators, supervisors, and lecturers can manage student data.
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Search and Filter Section -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-search me-2"></i>Search & Filter</h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="search" class="form-label">Search</label>
                                        <input type="text" class="form-control" id="search" name="search" 
                                               value="<?php echo $search; ?>" placeholder="Search by name, index, email...">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="programme" class="form-label">Programme</label>
                                        <select class="form-control" id="programme" name="programme">
                                            <option value="">All Programmes</option>
                                            <?php foreach ($programmes as $prog): ?>
                                                <option value="<?php echo $prog['programme_id']; ?>" 
                                                        <?php echo $programme_filter == $prog['programme_id'] ? 'selected' : ''; ?>>
                                                    <?php echo $prog['programme_name']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="year" class="form-label">Academic Year</label>
                                        <select class="form-control" id="year" name="year">
                                            <option value="">All Years</option>
                                            <?php foreach ($academic_years as $year): ?>
                                                <option value="<?php echo $year['academic_year']; ?>" 
                                                        <?php echo $year_filter == $year['academic_year'] ? 'selected' : ''; ?>>
                                                    <?php echo $year['academic_year']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-secondary">
                                            <i class="fas fa-search me-2"></i>Search
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Add New Student Section - Only for Administrators, Supervisors, and Lecturers -->
                    <?php if (!in_array($_SESSION['user_role'], ['student'])): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Add New Student</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="add_student">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="index_no" class="form-label">Index No *</label>
                                            <input type="text" class="form-control" id="index_no" name="index_no" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="first_name" class="form-label">First Name *</label>
                                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="surname" class="form-label">Surname</label>
                                            <input type="text" class="form-control" id="surname" name="surname">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email *</label>
                                            <input type="email" class="form-control" id="email" name="email" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Phone</label>
                                            <input type="text" class="form-control" id="phone" name="phone">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="academic_year" class="form-label">Academic Year</label>
                                            <input type="text" class="form-control" id="academic_year" name="academic_year" 
                                                   value="<?php echo getCurrentAcademicYear(); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="programme_id" class="form-label">Programme</label>
                                            <select class="form-control" id="programme_id" name="programme_id">
                                                <option value="">Select Programme</option>
                                                <?php foreach ($programmes as $prog): ?>
                                                    <option value="<?php echo $prog['programme_id']; ?>">
                                                        <?php echo $prog['programme_name']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-plus me-2"></i>Add Student
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Students Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Student Records (<?php echo count($students); ?>)</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Index No</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Programme</th>
                                            <th>Academic Year</th>
                                            <?php if (!in_array($_SESSION['user_role'], ['student'])): ?>
                                            <th>Actions</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($students)): ?>
                                            <tr>
                                                <td colspan="<?php echo in_array($_SESSION['user_role'], ['student']) ? '6' : '7'; ?>" class="text-center">No students found.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($students as $student): ?>
                                                <tr>
                                                    <td><?php echo sanitizeInput($student['index_no']); ?></td>
                                                    <td><?php echo sanitizeInput($student['first_name'] . ' ' . $student['surname']); ?></td>
                                                    <td><?php echo sanitizeInput($student['email']); ?></td>
                                                    <td><?php echo sanitizeInput($student['phone']); ?></td>
                                                    <td><?php echo sanitizeInput($student['programme_name']); ?></td>
                                                    <td><?php echo sanitizeInput($student['academic_year']); ?></td>
                                                    <?php if (!in_array($_SESSION['user_role'], ['student'])): ?>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary" onclick="editStudent(<?php echo $student['student_id']; ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger" onclick="deleteStudent(<?php echo $student['student_id']; ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                    <?php endif; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function editStudent(studentId) {
            // Implement edit functionality
            alert('Edit functionality will be implemented here for student ID: ' + studentId);
        }
        
        function deleteStudent(studentId) {
            if (confirm('Are you sure you want to delete this student?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_student">
                    <input type="hidden" name="student_id" value="${studentId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html> 