/**
 * Camera/Webcam Handler
 * Handles webcam access and photo capture
 */

let cameraStream = null;
let capturedPhoto = null;

// Initialize camera
async function initCamera() {
    try {
        const constraints = {
            video: {
                width: { ideal: 640 },
                height: { ideal: 480 },
                facingMode: 'user'
            }
        };
        
        cameraStream = await navigator.mediaDevices.getUserMedia(constraints);
        const video = document.getElementById('camera-preview');
        
        if (video) {
            video.srcObject = cameraStream;
            video.play();
        }
        
        return true;
    } catch (error) {
        console.error('Error accessing camera:', error);
        showError('Tidak dapat mengakses kamera. Pastikan Anda memberikan izin akses kamera.');
        return false;
    }
}

// Capture photo from camera
function capturePhoto() {
    const video = document.getElementById('camera-preview');
    const canvas = document.getElementById('camera-canvas');
    
    if (!video || !canvas) {
        showError('Elemen kamera tidak ditemukan');
        return null;
    }
    
    const context = canvas.getContext('2d');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    
    // Draw current video frame to canvas
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    // Get base64 image data
    capturedPhoto = canvas.toDataURL('image/jpeg', 0.8);
    
    // Show preview
    const preview = document.getElementById('photo-preview');
    if (preview) {
        preview.src = capturedPhoto;
        preview.style.display = 'block';
    }
    
    // Show success message
    showSuccess('Foto berhasil diambil!');
    
    return capturedPhoto;
}

// Stop camera stream
function stopCamera() {
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
    }
}

// Get captured photo data
function getCapturedPhoto() {
    return capturedPhoto;
}

// Clear captured photo
function clearCapturedPhoto() {
    capturedPhoto = null;
    const preview = document.getElementById('photo-preview');
    if (preview) {
        preview.src = '';
        preview.style.display = 'none';
    }
}

// Check if camera is supported
function isCameraSupported() {
    return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
}

// Event listeners
$(document).ready(function() {
    // Initialize camera button
    $('#start-camera-btn').on('click', async function() {
        if (!isCameraSupported()) {
            showError('Browser Anda tidak mendukung akses kamera');
            return;
        }
        
        const success = await initCamera();
        if (success) {
            $('#camera-container').show();
            $('#start-camera-btn').hide();
            $('#capture-btn').show();
        }
    });
    
    // Capture photo button
    $('#capture-btn').on('click', function() {
        capturePhoto();
        $(this).prop('disabled', true);
        $('#retake-btn').show();
    });
    
    // Retake photo button
    $('#retake-btn').on('click', function() {
        clearCapturedPhoto();
        $('#capture-btn').prop('disabled', false);
        $(this).hide();
    });
    
    // Stop camera when leaving page
    $(window).on('beforeunload', function() {
        stopCamera();
    });
});
