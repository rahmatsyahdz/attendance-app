<?php
$page_title = 'Kelola Karyawan';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

$db = getDB();

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    try {
        // Don't allow deleting admin users
        $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        if ($user && $user['role'] !== 'admin') {
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            setFlashMessage('success', 'Karyawan berhasil dihapus');
        } else {
            setFlashMessage('danger', 'Tidak dapat menghapus user admin');
        }
    } catch (PDOException $e) {
        setFlashMessage('danger', 'Gagal menghapus karyawan');
        error_log($e->getMessage());
    }
    
    header('Location: /admin/employees.php');
    exit();
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $department_id = sanitize($_POST['department_id'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($name) || empty($email)) {
        setFlashMessage('danger', 'Nama dan email wajib diisi');
    } elseif (!validateEmail($email)) {
        setFlashMessage('danger', 'Format email tidak valid');
    } else {
        try {
            if ($id) {
                // Edit existing employee
                $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $id]);
                if ($stmt->fetch()) {
                    setFlashMessage('danger', 'Email sudah digunakan');
                } else {
                    if (!empty($password)) {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, department_id = ?, phone = ?, password = ? WHERE id = ?");
                        $stmt->execute([$name, $email, $department_id, $phone, $hashed_password, $id]);
                    } else {
                        $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, department_id = ?, phone = ? WHERE id = ?");
                        $stmt->execute([$name, $email, $department_id, $phone, $id]);
                    }
                    setFlashMessage('success', 'Karyawan berhasil diperbarui');
                }
            } else {
                // Add new employee
                if (empty($password)) {
                    setFlashMessage('danger', 'Password wajib diisi untuk karyawan baru');
                } else {
                    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    if ($stmt->fetch()) {
                        setFlashMessage('danger', 'Email sudah terdaftar');
                    } else {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $db->prepare("INSERT INTO users (name, email, password, role, department_id, phone) VALUES (?, ?, ?, 'employee', ?, ?)");
                        $stmt->execute([$name, $email, $hashed_password, $department_id, $phone]);
                        setFlashMessage('success', 'Karyawan berhasil ditambahkan');
                    }
                }
            }
            
            header('Location: /admin/employees.php');
            exit();
        } catch (PDOException $e) {
            setFlashMessage('danger', 'Terjadi kesalahan sistem');
            error_log($e->getMessage());
        }
    }
}

// Get all employees
$stmt = $db->query("SELECT u.*, d.name as department_name 
                    FROM users u 
                    LEFT JOIN departments d ON u.department_id = d.id 
                    WHERE u.role = 'employee' 
                    ORDER BY u.name");
$employees = $stmt->fetchAll();

// Get departments
$stmt = $db->query("SELECT * FROM departments ORDER BY name");
$departments = $stmt->fetchAll();

// Get employee for edit
$edit_employee = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $edit_employee = $stmt->fetch();
}
?>

<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-users"></i> Kelola Karyawan</h2>
        <p class="text-muted">Tambah, edit, dan hapus data karyawan</p>
        <hr>
    </div>
</div>

<!-- Add/Edit Form -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-<?php echo $edit_employee ? 'edit' : 'plus'; ?>"></i>
                <?php echo $edit_employee ? 'Edit Karyawan' : 'Tambah Karyawan Baru'; ?>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <?php if ($edit_employee): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_employee['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="col-md-6">
                        <label for="name" class="form-label">Nama Lengkap *</label>
                        <input type="text" class="form-control" id="name" name="name" required
                               value="<?php echo $edit_employee ? escape($edit_employee['name']) : ''; ?>">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" required
                               value="<?php echo $edit_employee ? escape($edit_employee['email']) : ''; ?>">
                    </div>
                    
                    <div class="col-md-4">
                        <label for="department_id" class="form-label">Departemen</label>
                        <select class="form-select" id="department_id" name="department_id">
                            <option value="">Pilih Departemen</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>"
                                    <?php echo ($edit_employee && $edit_employee['department_id'] == $dept['id']) ? 'selected' : ''; ?>>
                                    <?php echo escape($dept['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="phone" class="form-label">No. Telepon</label>
                        <input type="tel" class="form-control" id="phone" name="phone"
                               value="<?php echo $edit_employee ? escape($edit_employee['phone'] ?? '') : ''; ?>">
                    </div>
                    
                    <div class="col-md-4">
                        <label for="password" class="form-label">
                            Password <?php echo !$edit_employee ? '*' : '(kosongkan jika tidak diubah)'; ?>
                        </label>
                        <input type="password" class="form-control" id="password" name="password"
                               <?php echo !$edit_employee ? 'required' : ''; ?>>
                    </div>
                    
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?php echo $edit_employee ? 'Update' : 'Tambah'; ?>
                        </button>
                        <?php if ($edit_employee): ?>
                            <a href="/admin/employees.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Employee List -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-list"></i> Daftar Karyawan
            </div>
            <div class="card-body">
                <?php if (count($employees) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover data-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Departemen</th>
                                    <th>Telepon</th>
                                    <th>Terdaftar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employees as $index => $emp): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo escape($emp['name']); ?></td>
                                    <td><?php echo escape($emp['email']); ?></td>
                                    <td><?php echo escape($emp['department_name'] ?? '-'); ?></td>
                                    <td><?php echo escape($emp['phone'] ?? '-'); ?></td>
                                    <td><?php echo formatDate($emp['created_at'], 'd M Y'); ?></td>
                                    <td>
                                        <a href="/admin/employees.php?action=edit&id=<?php echo $emp['id']; ?>" 
                                           class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" 
                                           onclick="return confirmDelete('/admin/employees.php?action=delete&id=<?php echo $emp['id']; ?>');" 
                                           class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Belum ada data karyawan.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
