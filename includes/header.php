<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/functions.php';
$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Attendance App</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo isAdmin() ? '/admin/dashboard.php' : '/employee/dashboard.php'; ?>">
                <i class="fas fa-clock"></i> Attendance App
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i> <?php echo escape($current_user['name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <?php if (isAdmin()): ?>
                                    <li><a class="dropdown-item" href="/admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                                    <li><a class="dropdown-item" href="/admin/employees.php"><i class="fas fa-users"></i> Karyawan</a></li>
                                    <li><a class="dropdown-item" href="/admin/attendance.php"><i class="fas fa-calendar-check"></i> Absensi</a></li>
                                    <li><a class="dropdown-item" href="/admin/reports.php"><i class="fas fa-chart-bar"></i> Laporan</a></li>
                                <?php else: ?>
                                    <li><a class="dropdown-item" href="/employee/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                                    <li><a class="dropdown-item" href="/employee/attendance.php"><i class="fas fa-clock"></i> Absensi</a></li>
                                    <li><a class="dropdown-item" href="/employee/history.php"><i class="fas fa-history"></i> Riwayat</a></li>
                                    <li><a class="dropdown-item" href="/employee/profile.php"><i class="fas fa-user"></i> Profil</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php 
    $flash = getFlashMessage();
    if ($flash): 
    ?>
    <div class="container mt-3">
        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
            <?php echo escape($flash['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="container-fluid mt-4">
