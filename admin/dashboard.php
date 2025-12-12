<?php
$page_title = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

$db = getDB();

// Get statistics
$today = date('Y-m-d');
$current_month = date('Y-m');

// Total employees
$stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'employee'");
$total_employees = $stmt->fetch()['total'];

// Today's attendance count
$stmt = $db->prepare("SELECT COUNT(*) as total FROM attendance WHERE date = ?");
$stmt->execute([$today]);
$today_attendance_count = $stmt->fetch()['total'];

// This month's total attendance
$stmt = $db->prepare("SELECT COUNT(*) as total FROM attendance WHERE DATE_FORMAT(date, '%Y-%m') = ?");
$stmt->execute([$current_month]);
$month_attendance_count = $stmt->fetch()['total'];

// Late count today
$stmt = $db->prepare("SELECT COUNT(*) as total FROM attendance WHERE date = ? AND status = 'late'");
$stmt->execute([$today]);
$late_count_today = $stmt->fetch()['total'];

// Get today's attendance details
$stmt = $db->prepare("SELECT a.*, u.name, u.email, d.name as department_name 
                      FROM attendance a 
                      JOIN users u ON a.user_id = u.id 
                      LEFT JOIN departments d ON u.department_id = d.id 
                      WHERE a.date = ? 
                      ORDER BY a.clock_in DESC");
$stmt->execute([$today]);
$today_attendance = $stmt->fetchAll();

// Get recent employees
$stmt = $db->query("SELECT u.*, d.name as department_name 
                    FROM users u 
                    LEFT JOIN departments d ON u.department_id = d.id 
                    WHERE u.role = 'employee' 
                    ORDER BY u.created_at DESC 
                    LIMIT 5");
$recent_employees = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h2>
        <p class="text-muted">Selamat datang, <?php echo escape($current_user['name']); ?>!</p>
        <hr>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card bg-primary text-white">
            <i class="fas fa-users icon"></i>
            <h3><?php echo $total_employees; ?></h3>
            <p>Total Karyawan</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-success text-white">
            <i class="fas fa-user-check icon"></i>
            <h3><?php echo $today_attendance_count; ?></h3>
            <p>Hadir Hari Ini</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-warning text-white">
            <i class="fas fa-user-clock icon"></i>
            <h3><?php echo $late_count_today; ?></h3>
            <p>Terlambat Hari Ini</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-info text-white">
            <i class="fas fa-calendar-check icon"></i>
            <h3><?php echo $month_attendance_count; ?></h3>
            <p>Absensi Bulan Ini</p>
        </div>
    </div>
</div>

<!-- Today's Attendance -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-calendar-day"></i> Absensi Hari Ini - <?php echo formatDate($today, 'd F Y'); ?>
            </div>
            <div class="card-body">
                <?php if (count($today_attendance) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Departemen</th>
                                    <th>Clock-in</th>
                                    <th>Clock-out</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($today_attendance as $index => $att): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo escape($att['name']); ?></td>
                                    <td><?php echo escape($att['department_name'] ?? '-'); ?></td>
                                    <td><?php echo $att['clock_in'] ?? '-'; ?></td>
                                    <td><?php echo $att['clock_out'] ?? '-'; ?></td>
                                    <td>
                                        <span class="badge badge-status-<?php echo $att['status']; ?>">
                                            <?php echo strtoupper($att['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Belum ada absensi hari ini.
                    </div>
                <?php endif; ?>
                
                <div class="text-center mt-3">
                    <a href="/admin/attendance.php" class="btn btn-primary">
                        Lihat Semua Absensi <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Employees -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-users"></i> Karyawan Terbaru
            </div>
            <div class="card-body">
                <?php if (count($recent_employees) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Departemen</th>
                                    <th>Terdaftar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_employees as $index => $emp): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo escape($emp['name']); ?></td>
                                    <td><?php echo escape($emp['email']); ?></td>
                                    <td><?php echo escape($emp['department_name'] ?? '-'); ?></td>
                                    <td><?php echo formatDate($emp['created_at'], 'd M Y'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="/admin/employees.php" class="btn btn-primary">
                            Lihat Semua Karyawan <i class="fas fa-arrow-right"></i>
                        </a>
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
