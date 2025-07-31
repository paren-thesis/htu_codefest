<?php
/**
 * HTU COMPSSA CODEFEST 2025 - Student Data Import Script
 * Imports student data from CSV file into the database
 */

require_once 'includes/config.php';

// Function to import CSV data
function import_students_from_csv($csv_file) {
    $db = Database::getInstance();
    $imported = 0;
    $errors = [];
    
    // Check if file exists
    if (!file_exists($csv_file)) {
        throw new Exception("CSV file not found: $csv_file");
    }
    
    // Open CSV file
    $handle = fopen($csv_file, 'r');
    if (!$handle) {
        throw new Exception("Cannot open CSV file: $csv_file");
    }
    
    // Read header row
    $header = fgetcsv($handle);
    if (!$header) {
        throw new Exception("Cannot read CSV header");
    }
    
    // Expected columns
    $expected_columns = [
        'Name', 'Index No', 'Email', 'Phone', 'Academic Year', 
        'Dues Paid', 'Receipt No', 'Programme of Study', 
        'payment date', 'password', 'position'
    ];
    
    // Validate header
    if (count(array_intersect($header, $expected_columns)) < count($expected_columns)) {
        throw new Exception("CSV file does not contain expected columns");
    }
    
    // Begin transaction
    $db->getConnection()->beginTransaction();
    
    try {
        // Process each row
        $row_number = 1; // Start from 1 since we already read header
        while (($row = fgetcsv($handle)) !== false) {
            $row_number++;
            
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }
            
            // Create associative array from row
            $data = array_combine($header, $row);
            
            // Clean and validate data
            $name_parts = explode(',', trim($data['Name'], '"'));
            $first_name = trim($name_parts[1] ?? '');
            $surname = trim($name_parts[0] ?? '');
            
            $index_no = trim($data['Index No']);
            $email = trim($data['Email']);
            $phone = trim($data['Phone']);
            $academic_year = trim($data['Academic Year']);
            $dues_paid = floatval(str_replace([' ', 'GH₵', ','], '', $data['Dues Paid']));
            $receipt_no = trim($data['Receipt No']);
            $programme = trim($data['Programme of Study']);
            $payment_date = trim($data['payment date']);
            $password = trim($data['password']);
            $position = trim($data['position']);
            
            // Validate required fields
            if (empty($index_no) || empty($email) || empty($first_name) || empty($surname)) {
                $errors[] = "Row $row_number: Missing required fields (Index No, Email, Name)";
                continue;
            }
            
            // Validate email format
            if (!validate_email($email)) {
                $errors[] = "Row $row_number: Invalid email format: $email";
                continue;
            }
            
            // Get programme ID
            $programme_sql = "SELECT programme_id FROM programmes WHERE programme_name = ?";
            $programme_stmt = $db->query($programme_sql, [$programme]);
            $programme_data = $programme_stmt->fetch();
            
            if (!$programme_data) {
                $errors[] = "Row $row_number: Unknown programme: $programme";
                continue;
            }
            
            $programme_id = $programme_data['programme_id'];
            
            // Check if student already exists
            $check_sql = "SELECT student_id FROM students WHERE index_no = ? OR email = ?";
            $check_stmt = $db->query($check_sql, [$index_no, $email]);
            $existing = $check_stmt->fetch();
            
            if ($existing) {
                $errors[] = "Row $row_number: Student already exists (Index: $index_no, Email: $email)";
                continue;
            }
            
            // Insert student
            $student_sql = "INSERT INTO students (index_no, first_name, surname, email, phone, 
                           academic_year, programme_id, position, start_date) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $start_date = !empty($payment_date) ? date('Y-m-d', strtotime($payment_date)) : null;
            
            $db->query($student_sql, [
                $index_no, $first_name, $surname, $email, $phone,
                $academic_year, $programme_id, $position, $start_date
            ]);
            
            $student_id = $db->getConnection()->lastInsertId();
            
            // Insert payment if dues were paid
            if ($dues_paid > 0 && !empty($receipt_no)) {
                $payment_sql = "INSERT INTO payments (student_id, amount, receipt_no, payment_date, 
                               academic_year, created_by) VALUES (?, ?, ?, ?, ?, ?)";
                
                $payment_date_formatted = !empty($payment_date) ? date('Y-m-d', strtotime($payment_date)) : date('Y-m-d');
                
                $db->query($payment_sql, [
                    $student_id, $dues_paid, $receipt_no, $payment_date_formatted,
                    $academic_year, 1 // Default admin user
                ]);
            }
            
            $imported++;
        }
        
        // Commit transaction
        $db->getConnection()->commit();
        
        fclose($handle);
        
        return [
            'imported' => $imported,
            'errors' => $errors
        ];
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $db->getConnection()->rollBack();
        fclose($handle);
        throw $e;
    }
}

// Main execution
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $result = import_students_from_csv('students.csv');
        
        $message = "Import completed successfully!\n";
        $message .= "Imported: " . $result['imported'] . " students\n";
        
        if (!empty($result['errors'])) {
            $message .= "\nErrors:\n";
            foreach ($result['errors'] as $error) {
                $message .= "- " . $error . "\n";
            }
        }
        
        $success = true;
        
    } catch (Exception $e) {
        $message = "Import failed: " . $e->getMessage();
        $success = false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Data Import - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: <?php echo COLOR_WHITE; ?>;
        }
        .header {
            background-color: <?php echo COLOR_ORANGE_BROWN; ?>;
            color: white;
            padding: 20px 0;
        }
        .btn-primary {
            background-color: <?php echo COLOR_BLUE; ?>;
            border-color: <?php echo COLOR_BLUE; ?>;
        }
        .btn-primary:hover {
            background-color: #040470;
            border-color: #040470;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-2">
                    <img src="assets/Logo_Worldskills_Ghana.png" alt="Logo" height="60">
                </div>
                <div class="col-md-10">
                    <h1 class="mb-0"><?php echo APP_NAME; ?></h1>
                    <p class="mb-0">Student Data Import Tool</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Import Student Data</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($message)): ?>
                            <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?>">
                                <pre><?php echo htmlspecialchars($message); ?></pre>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">CSV File: students.csv</label>
                                <p class="text-muted">This will import student data from the students.csv file in the project directory.</p>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    Import Student Data
                                </button>
                            </div>
                        </form>
                        
                        <div class="mt-4">
                            <h5>Import Summary:</h5>
                            <ul>
                                <li>Students will be imported with their basic information</li>
                                <li>Payment records will be created for students who have paid dues</li>
                                <li>Duplicate entries will be skipped</li>
                                <li>Data validation will be performed</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <a href="index.php" class="btn btn-secondary">Back to Home</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 