/**
 * Geolocation Handler
 * Handles getting user's current location
 */

let currentLocation = null;

// Get current location
function getCurrentLocation() {
    return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            reject(new Error('Geolocation tidak didukung oleh browser Anda'));
            return;
        }
        
        showLoading();
        
        navigator.geolocation.getCurrentPosition(
            (position) => {
                hideLoading();
                currentLocation = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy
                };
                
                displayLocation(currentLocation);
                resolve(currentLocation);
            },
            (error) => {
                hideLoading();
                let errorMessage = 'Tidak dapat mendapatkan lokasi';
                
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage = 'Anda menolak permintaan akses lokasi';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage = 'Informasi lokasi tidak tersedia';
                        break;
                    case error.TIMEOUT:
                        errorMessage = 'Waktu permintaan lokasi habis';
                        break;
                }
                
                reject(new Error(errorMessage));
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    });
}

// Display location on page
function displayLocation(location) {
    const locationDisplay = document.getElementById('location-display');
    if (locationDisplay && location) {
        locationDisplay.innerHTML = `
            <i class="fas fa-map-marker-alt"></i> 
            Lokasi: ${location.latitude.toFixed(6)}, ${location.longitude.toFixed(6)}
            <br>
            <small class="text-muted">Akurasi: ${location.accuracy.toFixed(0)} meter</small>
        `;
        locationDisplay.classList.add('location-display');
    }
}

// Format location for storage
function formatLocation(location) {
    if (!location) return '';
    return `${location.latitude},${location.longitude}`;
}

// Get location and set to hidden input
async function setLocationInput(inputId) {
    try {
        const location = await getCurrentLocation();
        const input = document.getElementById(inputId);
        if (input) {
            input.value = formatLocation(location);
        }
        return location;
    } catch (error) {
        console.error('Error getting location:', error);
        showError(error.message);
        return null;
    }
}

// Parse location string to object
function parseLocation(locationString) {
    if (!locationString) return null;
    
    const parts = locationString.split(',');
    if (parts.length !== 2) return null;
    
    return {
        latitude: parseFloat(parts[0]),
        longitude: parseFloat(parts[1])
    };
}

// Open location in Google Maps
function openInMaps(locationString) {
    const location = parseLocation(locationString);
    if (location) {
        const url = `https://www.google.com/maps?q=${location.latitude},${location.longitude}`;
        window.open(url, '_blank');
    }
}

// Event listeners
$(document).ready(function() {
    // Get location button
    $('#get-location-btn').on('click', async function() {
        const btn = $(this);
        btn.prop('disabled', true);
        
        try {
            await getCurrentLocation();
            btn.html('<i class="fas fa-check"></i> Lokasi Didapat');
            btn.removeClass('btn-primary').addClass('btn-success');
        } catch (error) {
            btn.prop('disabled', false);
        }
    });
    
    // Auto get location on page load if needed
    if ($('#auto-get-location').length) {
        getCurrentLocation().catch(error => {
            console.log('Auto location failed:', error);
        });
    }
});

// Export functions
window.geolocation = {
    getCurrentLocation,
    displayLocation,
    formatLocation,
    setLocationInput,
    parseLocation,
    openInMaps
};
