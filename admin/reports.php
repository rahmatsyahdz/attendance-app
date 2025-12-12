<?php
$page_title = 'Laporan Absensi';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

$db = getDB();

// Filter parameters
$filter_date_from = $_GET['date_from'] ?? date('Y-m-01');
$filter_date_to = $_GET['date_to'] ?? date('Y-m-d');
$filter_employee = $_GET['employee'] ?? '';
$filter_department = $_GET['department'] ?? '';

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

if ($filter_department) {
    $query .= " AND u.department_id = ?";
    $params[] = $filter_department;
}

$query .= " ORDER BY a.date DESC, u.name";

$stmt = $db->prepare($query);
$stmt->execute($params);
$attendance_records = $stmt->fetchAll();

// Get statistics
$total_records = count($attendance_records);
$present_count = count(array_filter($attendance_records, fn($r) => $r['status'] === 'present'));
$late_count = count(array_filter($attendance_records, fn($r) => $r['status'] === 'late'));
$absent_count = count(array_filter($attendance_records, fn($r) => $r['status'] === 'absent'));

// Get all employees for filter
$stmt = $db->query("SELECT id, name FROM users WHERE role = 'employee' ORDER BY name");
$employees = $stmt->fetchAll();

// Get all departments
$stmt = $db->query("SELECT * FROM departments ORDER BY name");
$departments = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-chart-bar"></i> Laporan Absensi</h2>
        <p class="text-muted">Generate dan export laporan absensi</p>
        <hr>
    </div>
</div>

<!-- Filter -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-filter"></i> Filter Laporan
            </div>
            <div class="card-body">
                <form method="GET" id="filterForm" class="row g-3">
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
                        <label class="form-label">Departemen</label>
                        <select name="department" class="form-select">
                            <option value="">Semua Departemen</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>" 
                                    <?php echo $filter_department == $dept['id'] ? 'selected' : ''; ?>>
                                    <?php echo escape($dept['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Tampilkan Laporan
                        </button>
                        <a href="/admin/reports.php" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Statistics -->
<?php if ($total_records > 0): ?>
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card bg-primary text-white">
            <i class="fas fa-list icon"></i>
            <h3><?php echo $total_records; ?></h3>
            <p>Total Records</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-success text-white">
            <i class="fas fa-check-circle icon"></i>
            <h3><?php echo $present_count; ?></h3>
            <p>Hadir Tepat Waktu</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-warning text-white">
            <i class="fas fa-clock icon"></i>
            <h3><?php echo $late_count; ?></h3>
            <p>Terlambat</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-danger text-white">
            <i class="fas fa-times-circle icon"></i>
            <h3><?php echo $absent_count; ?></h3>
            <p>Tidak Hadir</p>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Export Buttons -->
<?php if ($total_records > 0): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-download"></i> Export Laporan
            </div>
            <div class="card-body">
                <div class="export-buttons">
                    <form method="POST" action="/admin/export_pdf.php" style="display: inline;">
                        <input type="hidden" name="date_from" value="<?php echo $filter_date_from; ?>">
                        <input type="hidden" name="date_to" value="<?php echo $filter_date_to; ?>">
                        <input type="hidden" name="employee" value="<?php echo $filter_employee; ?>">
                        <input type="hidden" name="department" value="<?php echo $filter_department; ?>">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </button>
                    </form>
                    
                    <form method="POST" action="/admin/export_excel.php" style="display: inline;">
                        <input type="hidden" name="date_from" value="<?php echo $filter_date_from; ?>">
                        <input type="hidden" name="date_to" value="<?php echo $filter_date_to; ?>">
                        <input type="hidden" name="employee" value="<?php echo $filter_employee; ?>">
                        <input type="hidden" name="department" value="<?php echo $filter_department; ?>">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </button>
                    </form>
                    
                    <form method="POST" action="/admin/export_csv.php" style="display: inline;">
                        <input type="hidden" name="date_from" value="<?php echo $filter_date_from; ?>">
                        <input type="hidden" name="date_to" value="<?php echo $filter_date_to; ?>">
                        <input type="hidden" name="employee" value="<?php echo $filter_employee; ?>">
                        <input type="hidden" name="department" value="<?php echo $filter_department; ?>">
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-file-csv"></i> Export CSV
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Report Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-table"></i> Data Laporan
            </div>
            <div class="card-body">
                <?php if ($total_records > 0): ?>
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
                                    <th>Status</th>
                                    <th>Durasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendance_records as $index => $record): ?>
                                <?php
                                    $duration = '-';
                                    if ($record['clock_in'] && $record['clock_out']) {
                                        $in = new DateTime($record['clock_in']);
                                        $out = new DateTime($record['clock_out']);
                                        $diff = $in->diff($out);
                                        $duration = $diff->format('%H jam %I menit');
                                    }
                                ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo formatDate($record['date'], 'd M Y'); ?></td>
                                    <td><?php echo escape($record['name']); ?></td>
                                    <td><?php echo escape($record['department_name'] ?? '-'); ?></td>
                                    <td><?php echo $record['clock_in'] ?? '-'; ?></td>
                                    <td><?php echo $record['clock_out'] ?? '-'; ?></td>
                                    <td>
                                        <span class="badge badge-status-<?php echo $record['status']; ?>">
                                            <?php echo strtoupper($record['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $duration; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Tidak ada data untuk periode yang dipilih. Silakan pilih filter dan klik "Tampilkan Laporan".
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
