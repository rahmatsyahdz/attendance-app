<?php
$page_title = 'Kelola Absensi';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

$db = getDB();

// Filter parameters
$filter_date_from = $_GET['date_from'] ?? date('Y-m-01');
$filter_date_to = $_GET['date_to'] ?? date('Y-m-d');
$filter_employee = $_GET['employee'] ?? '';
$filter_status = $_GET['status'] ?? '';

// Build query
$query = "SELECT a.*, u.name, u.email, d.name as department_name 
          FROM attendance a 
          JOIN users u ON a.user_id = u.id 
          LEFT JOIN departments d ON u.department_id = d.id 
          WHERE a.date BETWEEN ? AND ?";
$params = [$filter_date_from, $filter_date_to];

if ($filter_employee) {
    $query .= " AND a.user_id = ?";
    $params[] = $filter_employee;
}

if ($filter_status) {
    $query .= " AND a.status = ?";
    $params[] = $filter_status;
}

$query .= " ORDER BY a.date DESC, a.clock_in DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$attendance_records = $stmt->fetchAll();

// Get all employees for filter
$stmt = $db->query("SELECT id, name FROM users WHERE role = 'employee' ORDER BY name");
$employees = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-calendar-check"></i> Kelola Absensi</h2>
        <p class="text-muted">Lihat dan kelola data absensi karyawan</p>
        <hr>
    </div>
</div>

<!-- Filter -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-filter"></i> Filter Absensi
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" name="date_from" class="form-control" value="<?php echo $filter_date_from; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date" name="date_to" class="form-control" value="<?php echo $filter_date_to; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Karyawan</label>
                        <select name="employee" class="form-select">
                            <option value="">Semua Karyawan</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?php echo $emp['id']; ?>" 
                                    <?php echo $filter_employee == $emp['id'] ? 'selected' : ''; ?>>
                                    <?php echo escape($emp['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="present" <?php echo $filter_status === 'present' ? 'selected' : ''; ?>>Present</option>
                            <option value="late" <?php echo $filter_status === 'late' ? 'selected' : ''; ?>>Late</option>
                            <option value="absent" <?php echo $filter_status === 'absent' ? 'selected' : ''; ?>>Absent</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="/admin/attendance.php" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-table"></i> Data Absensi
                <span class="badge bg-info float-end"><?php echo count($attendance_records); ?> records</span>
            </div>
            <div class="card-body">
                <?php if (count($attendance_records) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover data-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Nama</th>
                                    <th>Departemen</th>
                                    <th>Clock-in</th>
                                    <th>Clock-out</th>
                                    <th>Foto In</th>
                                    <th>Foto Out</th>
                                    <th>Status</th>
                                    <th>Lokasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendance_records as $index => $record): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo formatDate($record['date'], 'd M Y'); ?></td>
                                    <td><?php echo escape($record['name']); ?></td>
                                    <td><?php echo escape($record['department_name'] ?? '-'); ?></td>
                                    <td>
                                        <?php if ($record['clock_in']): ?>
                                            <span class="badge bg-success"><?php echo $record['clock_in']; ?></span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($record['clock_out']): ?>
                                            <span class="badge bg-danger"><?php echo $record['clock_out']; ?></span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($record['clock_in_photo']): ?>
                                            <img src="/uploads/selfies/<?php echo $record['clock_in_photo']; ?>" 
                                                 class="photo-preview" width="50" alt="Clock-in photo">
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($record['clock_out_photo']): ?>
                                            <img src="/uploads/selfies/<?php echo $record['clock_out_photo']; ?>" 
                                                 class="photo-preview" width="50" alt="Clock-out photo">
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-status-<?php echo $record['status']; ?>">
                                            <?php echo strtoupper($record['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($record['clock_in_location']): ?>
                                            <a href="#" onclick="openInMaps('<?php echo $record['clock_in_location']; ?>'); return false;" 
                                               class="btn btn-sm btn-outline-primary" title="Lihat Lokasi">
                                                <i class="fas fa-map-marker-alt"></i>
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Tidak ada data absensi untuk filter yang dipilih.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/geolocation.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
