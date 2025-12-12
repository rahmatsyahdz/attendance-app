<?php
$page_title = 'Riwayat Absensi';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

$db = getDB();
$user_id = $_SESSION['user_id'];

// Filter parameters
$filter_month = $_GET['month'] ?? date('Y-m');
$filter_status = $_GET['status'] ?? '';

// Build query
$query = "SELECT * FROM attendance WHERE user_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?";
$params = [$user_id, $filter_month];

if ($filter_status) {
    $query .= " AND status = ?";
    $params[] = $filter_status;
}

$query .= " ORDER BY date DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$attendance_records = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-history"></i> Riwayat Absensi</h2>
        <p class="text-muted">Lihat riwayat kehadiran Anda</p>
        <hr>
    </div>
</div>

<!-- Filter -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Bulan</label>
                        <input type="month" name="month" class="form-control" value="<?php echo $filter_month; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="present" <?php echo $filter_status === 'present' ? 'selected' : ''; ?>>Present</option>
                            <option value="late" <?php echo $filter_status === 'late' ? 'selected' : ''; ?>>Late</option>
                            <option value="absent" <?php echo $filter_status === 'absent' ? 'selected' : ''; ?>>Absent</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="/employee/history.php" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
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
            </div>
            <div class="card-body">
                <?php if (count($attendance_records) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover data-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Clock-in</th>
                                    <th>Clock-out</th>
                                    <th>Foto Clock-in</th>
                                    <th>Foto Clock-out</th>
                                    <th>Status</th>
                                    <th>Lokasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendance_records as $index => $record): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo formatDate($record['date'], 'd M Y'); ?></td>
                                    <td><?php echo $record['clock_in'] ?? '-'; ?></td>
                                    <td><?php echo $record['clock_out'] ?? '-'; ?></td>
                                    <td>
                                        <?php if ($record['clock_in_photo']): ?>
                                            <img src="/uploads/selfies/<?php echo $record['clock_in_photo']; ?>" 
                                                 class="photo-preview" width="50">
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($record['clock_out_photo']): ?>
                                            <img src="/uploads/selfies/<?php echo $record['clock_out_photo']; ?>" 
                                                 class="photo-preview" width="50">
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
                                            <a href="#" onclick="openInMaps('<?php echo $record['clock_in_location']; ?>'); return false;" class="btn btn-sm btn-outline-primary">
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
                        <i class="fas fa-info-circle"></i> Tidak ada data absensi untuk periode yang dipilih.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/geolocation.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
