<?php
$page_title = 'Profil Saya';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

$db = getDB();
$user_id = $_SESSION['user_id'];

// Get departments for dropdown
$stmt = $db->query("SELECT * FROM departments ORDER BY name");
$departments = $stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $department_id = sanitize($_POST['department_id'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($name) || empty($email)) {
        setFlashMessage('danger', 'Nama dan email wajib diisi');
    } elseif (!validateEmail($email)) {
        setFlashMessage('danger', 'Format email tidak valid');
    } else {
        try {
            // Check if email already exists for other users
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user_id]);
            if ($stmt->fetch()) {
                setFlashMessage('danger', 'Email sudah digunakan oleh pengguna lain');
            } else {
                // Update basic info
                $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, phone = ?, department_id = ? WHERE id = ?");
                $stmt->execute([$name, $email, $phone, $department_id, $user_id]);
                
                // Update password if provided
                if (!empty($current_password) && !empty($new_password)) {
                    // Verify current password
                    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user = $stmt->fetch();
                    
                    if (password_verify($current_password, $user['password'])) {
                        if (strlen($new_password) < 6) {
                            setFlashMessage('danger', 'Password baru minimal 6 karakter');
                        } elseif ($new_password !== $confirm_password) {
                            setFlashMessage('danger', 'Password baru dan konfirmasi tidak sama');
                        } else {
                            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                            $stmt->execute([$hashed_password, $user_id]);
                            setFlashMessage('success', 'Profil dan password berhasil diperbarui');
                        }
                    } else {
                        setFlashMessage('danger', 'Password saat ini salah');
                    }
                } else {
                    setFlashMessage('success', 'Profil berhasil diperbarui');
                }
                
                // Update session data
                $_SESSION['name'] = $name;
                $_SESSION['email'] = $email;
                
                header('Location: /employee/profile.php');
                exit();
            }
        } catch (PDOException $e) {
            setFlashMessage('danger', 'Terjadi kesalahan sistem');
            error_log($e->getMessage());
        }
    }
}

// Get current user data
$user = getCurrentUser();
?>

<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-user"></i> Profil Saya</h2>
        <p class="text-muted">Kelola informasi profil Anda</p>
        <hr>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-edit"></i> Edit Profil
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo escape($user['name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo escape($user['email']); ?>" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="department_id" class="form-label">Departemen</label>
                            <select class="form-select" id="department_id" name="department_id">
                                <option value="">Pilih Departemen</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>"
                                        <?php echo $user['department_id'] == $dept['id'] ? 'selected' : ''; ?>>
                                        <?php echo escape($dept['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">No. Telepon</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo escape($user['phone'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h5><i class="fas fa-key"></i> Ubah Password</h5>
                    <p class="text-muted small">Kosongkan jika tidak ingin mengubah password</p>
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Password Saat Ini</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="new_password" class="form-label">Password Baru</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>
                    </div>
                    
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-info-circle"></i> Informasi Akun
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th>Role:</th>
                        <td><span class="badge bg-primary"><?php echo strtoupper($user['role']); ?></span></td>
                    </tr>
                    <tr>
                        <th>Terdaftar:</th>
                        <td><?php echo formatDate($user['created_at'], 'd M Y'); ?></td>
                    </tr>
                    <tr>
                        <th>Update Terakhir:</th>
                        <td><?php echo formatDate($user['updated_at'], 'd M Y H:i'); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
