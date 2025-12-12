<?php
$page_title = 'Dashboard Karyawan';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

$db = getDB();
$user_id = $_SESSION['user_id'];

// Get today's attendance
$today = date('Y-m-d');
$stmt = $db->prepare("SELECT * FROM attendance WHERE user_id = ? AND date = ?");
$stmt->execute([$user_id, $today]);
$today_attendance = $stmt->fetch();

// Get this month's attendance statistics
$current_month = date('Y-m');
$stmt = $db->prepare("SELECT 
    COUNT(*) as total_days,
    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
    SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days,
    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days
    FROM attendance 
    WHERE user_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?");
$stmt->execute([$user_id, $current_month]);
$stats = $stmt->fetch();

// Get recent attendance
$stmt = $db->prepare("SELECT * FROM attendance WHERE user_id = ? ORDER BY date DESC LIMIT 5");
$stmt->execute([$user_id]);
$recent_attendance = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-tachometer-alt"></i> Dashboard Karyawan</h2>
        <p class="text-muted">Selamat datang, <?php echo escape($current_user['name']); ?>!</p>
        <hr>
    </div>
</div>

<!-- Current Date and Time -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body text-center">
                <h4><?php echo formatDate(date('Y-m-d'), 'l, d F Y'); ?></h4>
                <h2 id="current-time" class="text-primary"></h2>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card bg-primary text-white">
            <i class="fas fa-calendar-check icon"></i>
            <h3><?php echo $stats['total_days'] ?? 0; ?></h3>
            <p>Total Hari Kerja</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-success text-white">
            <i class="fas fa-check-circle icon"></i>
            <h3><?php echo $stats['present_days'] ?? 0; ?></h3>
            <p>Hadir Tepat Waktu</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-warning text-white">
            <i class="fas fa-clock icon"></i>
            <h3><?php echo $stats['late_days'] ?? 0; ?></h3>
            <p>Terlambat</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-danger text-white">
            <i class="fas fa-times-circle icon"></i>
            <h3><?php echo $stats['absent_days'] ?? 0; ?></h3>
            <p>Tidak Hadir</p>
        </div>
    </div>
</div>

<!-- Today's Status -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-calendar-day"></i> Status Hari Ini
            </div>
            <div class="card-body">
                <?php if ($today_attendance): ?>
                    <table class="table table-borderless">
                        <tr>
                            <th>Tanggal:</th>
                            <td><?php echo formatDate($today_attendance['date'], 'd F Y'); ?></td>
                        </tr>
                        <tr>
                            <th>Clock-in:</th>
                            <td>
                                <?php if ($today_attendance['clock_in']): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-clock"></i> <?php echo $today_attendance['clock_in']; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">Belum absen</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Clock-out:</th>
                            <td>
                                <?php if ($today_attendance['clock_out']): ?>
                                    <span class="badge bg-danger">
                                        <i class="fas fa-clock"></i> <?php echo $today_attendance['clock_out']; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">Belum clock-out</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge badge-status-<?php echo $today_attendance['status']; ?>">
                                    <?php echo strtoupper($today_attendance['status']); ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Anda belum melakukan absensi hari ini.
                    </div>
                <?php endif; ?>
                
                <div class="text-center mt-3">
                    <a href="/employee/attendance.php" class="btn btn-primary">
                        <i class="fas fa-fingerprint"></i> Absensi Sekarang
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user"></i> Profil Saya
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th>Nama:</th>
                        <td><?php echo escape($current_user['name']); ?></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><?php echo escape($current_user['email']); ?></td>
                    </tr>
                    <tr>
                        <th>Departemen:</th>
                        <td><?php echo escape($current_user['department_name'] ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <th>Telepon:</th>
                        <td><?php echo escape($current_user['phone'] ?? '-'); ?></td>
                    </tr>
                </table>
                
                <div class="text-center mt-3">
                    <a href="/employee/profile.php" class="btn btn-secondary">
                        <i class="fas fa-edit"></i> Edit Profil
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Attendance -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-history"></i> Riwayat Absensi Terbaru
            </div>
            <div class="card-body">
                <?php if (count($recent_attendance) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Clock-in</th>
                                    <th>Clock-out</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_attendance as $att): ?>
                                <tr>
                                    <td><?php echo formatDate($att['date'], 'd F Y'); ?></td>
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
                    <div class="text-center mt-3">
                        <a href="/employee/history.php" class="btn btn-outline-primary">
                            Lihat Semua Riwayat <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Belum ada riwayat absensi.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
