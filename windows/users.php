<?php
/**
 * HTU COMPSSA CODEFEST 2025 - User Management Window
 * Admin-only user management system
 * 
 * Features:
 * - View all users
 * - Add new users
 * - Edit existing users
 * - Delete users
 * - Role management
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is administrator
if (!isLoggedIn() || $_SESSION['user_role'] !== 'administrator') {
    redirect('login.php');
}

$error_message = '';
$success_message = '';
$users = [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_user':
                handleAddUser();
                break;
            case 'edit_user':
                handleEditUser();
                break;
            case 'delete_user':
                handleDeleteUser();
                break;
        }
    }
}

function handleAddUser() {
    global $error_message, $success_message;
    
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $role = sanitizeInput($_POST['role']);
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        $error_message = 'All fields are required.';
        return;
    }
    
    if (!isValidEmail($email)) {
        $error_message = 'Please enter a valid email address.';
        return;
    }
    
    if (strlen($password) < 6) {
        $error_message = 'Password must be at least 6 characters long.';
        return;
    }
    
    try {
        $pdo = getDBConnection();
        
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error_message = 'Username or email already exists.';
            return;
        }
        
        // Get role_id
        $stmt = $pdo->prepare("SELECT role_id FROM roles WHERE role_name = ?");
        $stmt->execute([$role]);
        $roleData = $stmt->fetch();
        if (!$roleData) {
            $error_message = 'Invalid role selected.';
            return;
        }
        
        // Insert new user
        $hashedPassword = hashPassword($password);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role_id, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$username, $email, $hashedPassword, $roleData['role_id']]);
        
        $success_message = 'User added successfully.';
    } catch (PDOException $e) {
        $error_message = 'Failed to add user: ' . $e->getMessage();
    }
}

function handleEditUser() {
    global $error_message, $success_message;
    
    $user_id = (int)$_POST['user_id'];
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $role = sanitizeInput($_POST['role']);
    $password = $_POST['password'];
    
    // Validation
    if (empty($username) || empty($email) || empty($role)) {
        $error_message = 'Username, email, and role are required.';
        return;
    }
    
    if (!isValidEmail($email)) {
        $error_message = 'Please enter a valid email address.';
        return;
    }
    
    try {
        $pdo = getDBConnection();
        
        // Check if username or email already exists (excluding current user)
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
        $stmt->execute([$username, $email, $user_id]);
        if ($stmt->fetch()) {
            $error_message = 'Username or email already exists.';
            return;
        }
        
        // Get role_id
        $stmt = $pdo->prepare("SELECT role_id FROM roles WHERE role_name = ?");
        $stmt->execute([$role]);
        $roleData = $stmt->fetch();
        if (!$roleData) {
            $error_message = 'Invalid role selected.';
            return;
        }
        
        // Update user
        if (!empty($password)) {
            if (strlen($password) < 6) {
                $error_message = 'Password must be at least 6 characters long.';
                return;
            }
            $hashedPassword = hashPassword($password);
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ?, role_id = ? WHERE user_id = ?");
            $stmt->execute([$username, $email, $hashedPassword, $roleData['role_id'], $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role_id = ? WHERE user_id = ?");
            $stmt->execute([$username, $email, $roleData['role_id'], $user_id]);
        }
        
        $success_message = 'User updated successfully.';
    } catch (PDOException $e) {
        $error_message = 'Failed to update user: ' . $e->getMessage();
    }
}

function handleDeleteUser() {
    global $error_message, $success_message;
    
    $user_id = (int)$_POST['user_id'];
    
    // Prevent admin from deleting themselves
    if ($user_id == $_SESSION['user_id']) {
        $error_message = 'You cannot delete your own account.';
        return;
    }
    
    try {
        $pdo = getDBConnection();
        
        // Check if user exists
        $stmt = $pdo->prepare("SELECT username FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        if (!$stmt->fetch()) {
            $error_message = 'User not found.';
            return;
        }
        
        // Delete user
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        $success_message = 'User deleted successfully.';
    } catch (PDOException $e) {
        $error_message = 'Failed to delete user: ' . $e->getMessage();
    }
}

// Fetch all users
try {
    $pdo = getDBConnection();
    $sql = "SELECT u.user_id, u.username, u.email, r.role_name, u.created_at FROM users u JOIN roles r ON u.role_id = r.role_id ORDER BY u.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = 'Failed to fetch users: ' . $e->getMessage();
}

// Fetch roles for dropdown
try {
    $stmt = $pdo->prepare("SELECT role_name FROM roles ORDER BY role_name");
    $stmt->execute();
    $roles = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = 'Failed to fetch roles: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - User Management</title>
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
                        User Management
                    </h2>
                    
                    <!-- Display Messages -->
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success_message): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <!-- Add New User Form -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Add New User</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="row g-3">
                                <input type="hidden" name="action" value="add_user">
                                
                                <div class="col-md-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                
                                <div class="col-md-2">
                                    <label for="role" class="form-label">Role</label>
                                    <select class="form-control" id="role" name="role" required>
                                        <option value="">Select Role</option>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?php echo $role['role_name']; ?>">
                                                <?php echo ucfirst($role['role_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-2">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-plus me-1"></i>Add User
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Users Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-users me-2"></i>All Users</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Created At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($users)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No users found.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($users as $user): ?>
                                                <tr>
                                                    <td><?php echo sanitizeInput($user['username']); ?></td>
                                                    <td><?php echo sanitizeInput($user['email']); ?></td>
                                                    <td>
                                                        <span class="badge bg-primary">
                                                            <?php echo ucfirst(sanitizeInput($user['role_name'])); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo sanitizeInput($user['created_at']); ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary" onclick="editUser(<?php echo $user['user_id']; ?>, '<?php echo $user['username']; ?>', '<?php echo $user['email']; ?>', '<?php echo $user['role_name']; ?>')">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(<?php echo $user['user_id']; ?>, '<?php echo $user['username']; ?>')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
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

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_user">
                        <input type="hidden" name="user_id" id="edit_user_id">
                        
                        <div class="mb-3">
                            <label for="edit_username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="edit_username" name="username" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_role" class="form-label">Role</label>
                            <select class="form-control" id="edit_role" name="role" required>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?php echo $role['role_name']; ?>">
                                        <?php echo ucfirst($role['role_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_password" class="form-label">New Password (leave blank to keep current)</label>
                            <input type="password" class="form-control" id="edit_password" name="password">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete User Modal -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete user "<span id="delete_username"></span>"?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <form method="POST">
                    <div class="modal-footer">
                        <input type="hidden" name="action" value="delete_user">
                        <input type="hidden" name="user_id" id="delete_user_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function editUser(userId, username, email, role) {
            document.getElementById('edit_user_id').value = userId;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_role').value = role;
            document.getElementById('edit_password').value = '';
            
            new bootstrap.Modal(document.getElementById('editUserModal')).show();
        }
        
        function deleteUser(userId, username) {
            document.getElementById('delete_user_id').value = userId;
            document.getElementById('delete_username').textContent = username;
            
            new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
        }
    </script>
</body>
</html> 