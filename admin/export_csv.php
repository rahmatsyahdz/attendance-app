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

// Set headers for CSV download
$filename = 'attendance_report_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for Excel UTF-8 compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add CSV headers
fputcsv($output, [
    'No',
    'Tanggal',
    'Nama',
    'Email',
    'Departemen',
    'Clock-in',
    'Clock-out',
    'Status',
    'Durasi (menit)',
    'Lokasi Clock-in',
    'Lokasi Clock-out'
]);

// Add data rows
foreach ($records as $index => $record) {
    $duration = '';
    if ($record['clock_in'] && $record['clock_out']) {
        $in = new DateTime($record['clock_in']);
        $out = new DateTime($record['clock_out']);
        $diff = $in->diff($out);
        $duration = ($diff->h * 60) + $diff->i;
    }
    
    fputcsv($output, [
        $index + 1,
        date('d-m-Y', strtotime($record['date'])),
        $record['name'],
        $record['email'],
        $record['department_name'] ?? '-',
        $record['clock_in'] ?? '-',
        $record['clock_out'] ?? '-',
        strtoupper($record['status']),
        $duration,
        $record['clock_in_location'] ?? '-',
        $record['clock_out_location'] ?? '-'
    ]);
}

fclose($output);
exit();
?>
