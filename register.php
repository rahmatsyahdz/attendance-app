<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: /admin/dashboard.php');
    } else {
        header('Location: /employee/dashboard.php');
    }
    exit();
}

$error = '';
$success = '';

// Get departments for dropdown
$db = getDB();
$stmt = $db->query("SELECT * FROM departments ORDER BY name");
$departments = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $department_id = sanitize($_POST['department_id'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Semua field wajib diisi';
    } elseif (!validateEmail($email)) {
        $error = 'Format email tidak valid';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak sama';
    } else {
        try {
            // Check if email already exists
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email sudah terdaftar';
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $stmt = $db->prepare("INSERT INTO users (name, email, password, role, department_id, phone) 
                                      VALUES (?, ?, ?, 'employee', ?, ?)");
                $stmt->execute([$name, $email, $hashed_password, $department_id, $phone]);
                
                $success = 'Registrasi berhasil! Silakan login.';
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan sistem';
            error_log($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Attendance App</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="login-container" style="max-width: 600px;">
            <div class="card login-card">
                <div class="login-header">
                    <i class="fas fa-user-plus"></i>
                    <h2>Registrasi Karyawan</h2>
                    <p>Buat akun baru</p>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> <?php echo escape($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> <?php echo escape($success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="registerForm">
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                <i class="fas fa-user"></i> Nama Lengkap
                            </label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   placeholder="Masukkan nama lengkap" required
                                   value="<?php echo isset($_POST['name']) ? escape($_POST['name']) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i> Email
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="Masukkan email" required
                                   value="<?php echo isset($_POST['email']) ? escape($_POST['email']) : ''; ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i> Password
                                </label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Minimal 6 karakter" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">
                                    <i class="fas fa-lock"></i> Konfirmasi Password
                                </label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       placeholder="Ulangi password" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="department_id" class="form-label">
                                <i class="fas fa-building"></i> Departemen
                            </label>
                            <select class="form-select" id="department_id" name="department_id" required>
                                <option value="">Pilih Departemen</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>"
                                        <?php echo (isset($_POST['department_id']) && $_POST['department_id'] == $dept['id']) ? 'selected' : ''; ?>>
                                        <?php echo escape($dept['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">
                                <i class="fas fa-phone"></i> No. Telepon
                            </label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   placeholder="08xxxxxxxxxx"
                                   value="<?php echo isset($_POST['phone']) ? escape($_POST['phone']) : ''; ?>">
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-user-plus"></i> Daftar
                        </button>
                        
                        <div class="text-center">
                            <p>Sudah punya akun? <a href="/index.php">Login di sini</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
