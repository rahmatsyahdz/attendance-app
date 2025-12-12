/**
 * Main Application JavaScript
 */

// Initialize DataTables
$(document).ready(function() {
    if ($('.data-table').length) {
        $('.data-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json"
            },
            "pageLength": 10,
            "ordering": true,
            "searching": true,
            "responsive": true
        });
    }
});

// Show loading spinner
function showLoading() {
    if (!$('.loading').length) {
        $('body').append('<div class="loading"><div class="spinner"></div></div>');
    }
    $('.loading').addClass('show');
}

// Hide loading spinner
function hideLoading() {
    $('.loading').removeClass('show');
}

// Show success message
function showSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: message,
        timer: 3000,
        showConfirmButton: false
    });
}

// Show error message
function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: message
    });
}

// Show confirmation dialog
function showConfirm(title, text, confirmCallback) {
    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed && typeof confirmCallback === 'function') {
            confirmCallback();
        }
    });
}

// Delete confirmation
function confirmDelete(url, message = 'Data ini akan dihapus permanen!') {
    showConfirm('Apakah Anda yakin?', message, function() {
        window.location.href = url;
    });
    return false;
}

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const inputs = form.querySelectorAll('[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// Email validation
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Preview image before upload
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            $('#' + previewId).attr('src', e.target.result);
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Auto dismiss alerts
setTimeout(function() {
    $('.alert').fadeOut('slow');
}, 5000);

// Prevent double submission
$('form').on('submit', function() {
    $(this).find('button[type="submit"]').prop('disabled', true);
    setTimeout(function() {
        $('button[type="submit"]').prop('disabled', false);
    }, 3000);
});

// Real-time clock
function updateClock() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    
    const timeString = `${hours}:${minutes}:${seconds}`;
    
    if ($('#current-time').length) {
        $('#current-time').text(timeString);
    }
}

// Update clock every second
if ($('#current-time').length) {
    setInterval(updateClock, 1000);
    updateClock(); // Initial call
}

// Photo modal preview
$(document).on('click', '.photo-preview', function() {
    const src = $(this).attr('src');
    Swal.fire({
        imageUrl: src,
        imageAlt: 'Preview',
        showConfirmButton: false,
        showCloseButton: true,
        width: 'auto'
    });
});
