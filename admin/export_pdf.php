<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/reports.php');
    exit();
}

// Simple PDF generation without external library
// Using HTML to PDF conversion via browser print

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

// Calculate statistics
$total_records = count($records);
$present_count = count(array_filter($records, fn($r) => $r['status'] === 'present'));
$late_count = count(array_filter($records, fn($r) => $r['status'] === 'late'));
$absent_count = count(array_filter($records, fn($r) => $r['status'] === 'absent'));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Absensi</title>
    <style>
        @page { size: A4 landscape; margin: 15mm; }
        @media print {
            .no-print { display: none; }
        }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 11px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h2 { margin: 5px 0; color: #333; }
        .header p { margin: 3px 0; color: #666; }
        
        .stats {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
            padding: 10px;
            background: #f5f5f5;
        }
        .stat-item {
            text-align: center;
            padding: 10px;
        }
        .stat-item .number {
            font-size: 24px;
            font-weight: bold;
            color: #2196F3;
        }
        .stat-item .label {
            font-size: 10px;
            color: #666;
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px;
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 6px; 
            text-align: left;
            font-size: 10px;
        }
        th { 
            background-color: #2196F3; 
            color: white; 
            font-weight: bold;
        }
        tr:nth-child(even) { background-color: #f9f9f9; }
        
        .status-present { 
            background-color: #4CAF50; 
            color: white; 
            padding: 3px 8px; 
            border-radius: 3px;
            font-size: 9px;
        }
        .status-late { 
            background-color: #FF9800; 
            color: white; 
            padding: 3px 8px; 
            border-radius: 3px;
            font-size: 9px;
        }
        .status-absent { 
            background-color: #F44336; 
            color: white; 
            padding: 3px 8px; 
            border-radius: 3px;
            font-size: 9px;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: right;
            font-size: 9px;
            color: #666;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #2196F3;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .print-button:hover {
            background: #1976D2;
        }
    </style>
    <script>
        function printPDF() {
            window.print();
        }
    </script>
</head>
<body>
    <button class="print-button no-print" onclick="printPDF()">
        <i class="fas fa-print"></i> Print / Save as PDF
    </button>
    
    <div class="header">
        <h2>LAPORAN ABSENSI KARYAWAN</h2>
        <p>Periode: <?php echo date('d F Y', strtotime($filter_date_from)); ?> s/d <?php echo date('d F Y', strtotime($filter_date_to)); ?></p>
        <p>Dicetak: <?php echo date('d F Y H:i:s'); ?></p>
    </div>
    
    <div class="stats">
        <div class="stat-item">
            <div class="number"><?php echo $total_records; ?></div>
            <div class="label">Total Records</div>
        </div>
        <div class="stat-item">
            <div class="number" style="color: #4CAF50;"><?php echo $present_count; ?></div>
            <div class="label">Hadir Tepat Waktu</div>
        </div>
        <div class="stat-item">
            <div class="number" style="color: #FF9800;"><?php echo $late_count; ?></div>
            <div class="label">Terlambat</div>
        </div>
        <div class="stat-item">
            <div class="number" style="color: #F44336;"><?php echo $absent_count; ?></div>
            <div class="label">Tidak Hadir</div>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="10%">Tanggal</th>
                <th width="20%">Nama</th>
                <th width="15%">Departemen</th>
                <th width="10%">Clock-in</th>
                <th width="10%">Clock-out</th>
                <th width="10%">Status</th>
                <th width="10%">Durasi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($records) > 0): ?>
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
                        <td><?php echo date('d/m/Y', strtotime($record['date'])); ?></td>
                        <td><?php echo htmlspecialchars($record['name']); ?></td>
                        <td><?php echo htmlspecialchars($record['department_name'] ?? '-'); ?></td>
                        <td><?php echo $record['clock_in'] ?? '-'; ?></td>
                        <td><?php echo $record['clock_out'] ?? '-'; ?></td>
                        <td>
                            <span class="status-<?php echo $record['status']; ?>">
                                <?php echo strtoupper($record['status']); ?>
                            </span>
                        </td>
                        <td><?php echo $duration; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align: center;">Tidak ada data</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div class="footer">
        Generated by Attendance App - <?php echo date('d F Y H:i:s'); ?>
    </div>
</body>
</html>
