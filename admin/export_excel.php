<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/reports.php');
    exit();
}

$db = getDB();

// Get filter parameters
$filter_date_from = $_POST['date_from'] ?? date('Y-m-01');
$filter_date_to = $_POST['date_to'] ?? date('Y-m-d');
$filter_employee = $_POST['employee'] ?? '';
$filter_department = $_POST['department'] ?? '';

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
$records = $stmt->fetchAll();

// Set headers for Excel download
$filename = 'attendance_report_' . date('Ymd_His') . '.xls';
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename=' . $filename);
header('Pragma: no-cache');
header('Expires: 0');

// Generate Excel content (HTML table format)
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; font-weight: bold; }
        .header { background-color: #2196F3; color: white; padding: 10px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN ABSENSI KARYAWAN</h2>
        <p>Periode: <?php echo date('d-m-Y', strtotime($filter_date_from)); ?> s/d <?php echo date('d-m-Y', strtotime($filter_date_to)); ?></p>
        <p>Dicetak: <?php echo date('d-m-Y H:i:s'); ?></p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Departemen</th>
                <th>Clock-in</th>
                <th>Clock-out</th>
                <th>Status</th>
                <th>Durasi (jam)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $index => $record): ?>
                <?php
                    $duration = '-';
                    if ($record['clock_in'] && $record['clock_out']) {
                        $in = new DateTime($record['clock_in']);
                        $out = new DateTime($record['clock_out']);
                        $diff = $in->diff($out);
                        $duration = $diff->format('%H:%I');
                    }
                ?>
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td><?php echo date('d-m-Y', strtotime($record['date'])); ?></td>
                    <td><?php echo htmlspecialchars($record['name']); ?></td>
                    <td><?php echo htmlspecialchars($record['email']); ?></td>
                    <td><?php echo htmlspecialchars($record['department_name'] ?? '-'); ?></td>
                    <td><?php echo $record['clock_in'] ?? '-'; ?></td>
                    <td><?php echo $record['clock_out'] ?? '-'; ?></td>
                    <td><?php echo strtoupper($record['status']); ?></td>
                    <td><?php echo $duration; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
<?php
exit();
?>
