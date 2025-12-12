<?php
$page_title = 'Absensi';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

$db = getDB();
$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

// Get today's attendance
$stmt = $db->prepare("SELECT * FROM attendance WHERE user_id = ? AND date = ?");
$stmt->execute([$user_id, $today]);
$today_attendance = $stmt->fetch();

$can_clock_in = !$today_attendance || !$today_attendance['clock_in'];
$can_clock_out = $today_attendance && $today_attendance['clock_in'] && !$today_attendance['clock_out'];

// Handle clock-in
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clock_in'])) {
    $photo = $_POST['photo_data'] ?? '';
    $location = sanitize($_POST['location'] ?? '');
    
    if (empty($photo)) {
        setFlashMessage('danger', 'Foto selfie wajib diambil');
    } elseif (empty($location)) {
        setFlashMessage('danger', 'Lokasi tidak terdeteksi');
    } else {
        // Save photo
        $photo_data = str_replace('data:image/jpeg;base64,', '', $photo);
        $photo_data = str_replace(' ', '+', $photo_data);
        $photo_decoded = base64_decode($photo_data);
        
        $photo_name = 'clock_in_' . $user_id . '_' . time() . '.jpg';
        $photo_path = __DIR__ . '/../uploads/selfies/' . $photo_name;
        
        if (!file_exists(__DIR__ . '/../uploads/selfies/')) {
            mkdir(__DIR__ . '/../uploads/selfies/', 0777, true);
        }
        
        file_put_contents($photo_path, $photo_decoded);
        
        // Calculate status
        $clock_in_time = date('H:i:s');
        $status = calculateAttendanceStatus($clock_in_time);
        
        // Insert or update attendance
        if (!$today_attendance) {
            $stmt = $db->prepare("INSERT INTO attendance (user_id, date, clock_in, clock_in_photo, clock_in_location, status) 
                                  VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $today, $clock_in_time, $photo_name, $location, $status]);
        } else {
            $stmt = $db->prepare("UPDATE attendance SET clock_in = ?, clock_in_photo = ?, clock_in_location = ?, status = ? 
                                  WHERE user_id = ? AND date = ?");
            $stmt->execute([$clock_in_time, $photo_name, $location, $status, $user_id, $today]);
        }
        
        setFlashMessage('success', 'Clock-in berhasil!');
        header('Location: /employee/attendance.php');
        exit();
    }
}

// Handle clock-out
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clock_out'])) {
    $photo = $_POST['photo_data'] ?? '';
    $location = sanitize($_POST['location'] ?? '');
    
    if (empty($photo)) {
        setFlashMessage('danger', 'Foto selfie wajib diambil');
    } elseif (empty($location)) {
        setFlashMessage('danger', 'Lokasi tidak terdeteksi');
    } else {
        // Save photo
        $photo_data = str_replace('data:image/jpeg;base64,', '', $photo);
        $photo_data = str_replace(' ', '+', $photo_data);
        $photo_decoded = base64_decode($photo_data);
        
        $photo_name = 'clock_out_' . $user_id . '_' . time() . '.jpg';
        $photo_path = __DIR__ . '/../uploads/selfies/' . $photo_name;
        
        file_put_contents($photo_path, $photo_decoded);
        
        // Update attendance
        $clock_out_time = date('H:i:s');
        $stmt = $db->prepare("UPDATE attendance SET clock_out = ?, clock_out_photo = ?, clock_out_location = ? 
                              WHERE user_id = ? AND date = ?");
        $stmt->execute([$clock_out_time, $photo_name, $location, $user_id, $today]);
        
        setFlashMessage('success', 'Clock-out berhasil!');
        header('Location: /employee/attendance.php');
        exit();
    }
}
?>

<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-fingerprint"></i> Absensi</h2>
        <p class="text-muted">Lakukan clock-in dan clock-out</p>
        <hr>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-calendar-day"></i> Absensi Hari Ini - <?php echo formatDate($today, 'd F Y'); ?>
            </div>
            <div class="card-body">
                <!-- Current Status -->
                <?php if ($today_attendance): ?>
                    <div class="alert alert-info mb-4">
                        <h5><i class="fas fa-info-circle"></i> Status Absensi</h5>
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th width="150">Clock-in:</th>
                                <td><?php echo $today_attendance['clock_in'] ? '<span class="badge bg-success">' . $today_attendance['clock_in'] . '</span>' : '<span class="text-muted">Belum clock-in</span>'; ?></td>
                            </tr>
                            <tr>
                                <th>Clock-out:</th>
                                <td><?php echo $today_attendance['clock_out'] ? '<span class="badge bg-danger">' . $today_attendance['clock_out'] . '</span>' : '<span class="text-muted">Belum clock-out</span>'; ?></td>
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
                    </div>
                <?php endif; ?>
                
                <!-- Clock-in Form -->
                <?php if ($can_clock_in): ?>
                    <form method="POST" id="clockInForm">
                        <h4 class="mb-3"><i class="fas fa-sign-in-alt"></i> Clock-in</h4>
                        
                        <!-- Camera Section -->
                        <div class="text-center mb-3">
                            <button type="button" id="start-camera-btn" class="btn btn-primary">
                                <i class="fas fa-camera"></i> Aktifkan Kamera
                            </button>
                        </div>
                        
                        <div id="camera-container" style="display: none;">
                            <div class="text-center">
                                <video id="camera-preview" autoplay playsinline></video>
                                <canvas id="camera-canvas"></canvas>
                                <br>
                                <img id="photo-preview" style="display: none; max-width: 100%; border-radius: 10px;" class="mt-3">
                            </div>
                            
                            <div class="camera-controls text-center mt-3">
                                <button type="button" id="capture-btn" class="btn btn-success" style="display: none;">
                                    <i class="fas fa-camera"></i> Ambil Foto
                                </button>
                                <button type="button" id="retake-btn" class="btn btn-warning" style="display: none;">
                                    <i class="fas fa-redo"></i> Foto Ulang
                                </button>
                            </div>
                        </div>
                        
                        <!-- Location Section -->
                        <div class="mt-4">
                            <h5><i class="fas fa-map-marker-alt"></i> Lokasi</h5>
                            <button type="button" id="get-location-btn" class="btn btn-primary mb-2">
                                <i class="fas fa-crosshairs"></i> Dapatkan Lokasi
                            </button>
                            <div id="location-display"></div>
                            <input type="hidden" name="location" id="location-input">
                        </div>
                        
                        <input type="hidden" name="photo_data" id="photo-data-input">
                        
                        <div class="text-center mt-4">
                            <button type="submit" name="clock_in" class="btn btn-success btn-lg clock-btn">
                                <i class="fas fa-sign-in-alt"></i> Clock-in Sekarang
                            </button>
                        </div>
                    </form>
                
                <!-- Clock-out Form -->
                <?php elseif ($can_clock_out): ?>
                    <form method="POST" id="clockOutForm">
                        <h4 class="mb-3"><i class="fas fa-sign-out-alt"></i> Clock-out</h4>
                        
                        <!-- Camera Section -->
                        <div class="text-center mb-3">
                            <button type="button" id="start-camera-btn" class="btn btn-primary">
                                <i class="fas fa-camera"></i> Aktifkan Kamera
                            </button>
                        </div>
                        
                        <div id="camera-container" style="display: none;">
                            <div class="text-center">
                                <video id="camera-preview" autoplay playsinline></video>
                                <canvas id="camera-canvas"></canvas>
                                <br>
                                <img id="photo-preview" style="display: none; max-width: 100%; border-radius: 10px;" class="mt-3">
                            </div>
                            
                            <div class="camera-controls text-center mt-3">
                                <button type="button" id="capture-btn" class="btn btn-success" style="display: none;">
                                    <i class="fas fa-camera"></i> Ambil Foto
                                </button>
                                <button type="button" id="retake-btn" class="btn btn-warning" style="display: none;">
                                    <i class="fas fa-redo"></i> Foto Ulang
                                </button>
                            </div>
                        </div>
                        
                        <!-- Location Section -->
                        <div class="mt-4">
                            <h5><i class="fas fa-map-marker-alt"></i> Lokasi</h5>
                            <button type="button" id="get-location-btn" class="btn btn-primary mb-2">
                                <i class="fas fa-crosshairs"></i> Dapatkan Lokasi
                            </button>
                            <div id="location-display"></div>
                            <input type="hidden" name="location" id="location-input">
                        </div>
                        
                        <input type="hidden" name="photo_data" id="photo-data-input">
                        
                        <div class="text-center mt-4">
                            <button type="submit" name="clock_out" class="btn btn-danger btn-lg clock-btn">
                                <i class="fas fa-sign-out-alt"></i> Clock-out Sekarang
                            </button>
                        </div>
                    </form>
                
                <?php else: ?>
                    <div class="alert alert-success text-center">
                        <i class="fas fa-check-circle fa-3x mb-3"></i>
                        <h4>Absensi Hari Ini Selesai!</h4>
                        <p>Anda sudah melakukan clock-in dan clock-out untuk hari ini.</p>
                        <a href="/employee/dashboard.php" class="btn btn-primary mt-2">
                            <i class="fas fa-home"></i> Kembali ke Dashboard
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/camera.js"></script>
<script src="/assets/js/geolocation.js"></script>
<script>
// Handle form submission
$('#clockInForm, #clockOutForm').on('submit', function(e) {
    const photo = getCapturedPhoto();
    const location = $('#location-input').val();
    
    if (!photo) {
        e.preventDefault();
        showError('Silakan ambil foto selfie terlebih dahulu');
        return false;
    }
    
    if (!location) {
        e.preventDefault();
        showError('Silakan dapatkan lokasi terlebih dahulu');
        return false;
    }
    
    $('#photo-data-input').val(photo);
    showLoading();
});

// Get location on button click
$('#get-location-btn').on('click', async function() {
    try {
        const location = await getCurrentLocation();
        $('#location-input').val(formatLocation(location));
        $(this).html('<i class="fas fa-check"></i> Lokasi Didapat');
        $(this).removeClass('btn-primary').addClass('btn-success');
    } catch (error) {
        console.error('Location error:', error);
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
