// Sistem Informasi Pencegahan Stunting - JavaScript Functions
document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Initialize dark mode
    initializeDarkMode();

    // Form validation
    initializeFormValidation();

    // Smooth scrolling
    initializeSmoothScrolling();

    // Auto hide alerts
    autoHideAlerts();

    // Chart animations if Chart.js is loaded
    if (typeof Chart !== 'undefined') {
        Chart.defaults.animation.duration = 2000;
    }
});

// Dark mode support
function initializeDarkMode() {
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        document.documentElement.classList.add('dark');
    }
    
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
        if (event.matches) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    });
}

// Form validation
function initializeFormValidation() {
    // Custom form validation for cek stunting
    const formCekStunting = document.getElementById('formCekStunting');
    if (formCekStunting) {
        formCekStunting.addEventListener('submit', function(e) {
            if (!validateCekStuntingForm()) {
                e.preventDefault();
                e.stopPropagation();
            }
            this.classList.add('was-validated');
        });

        // Real-time validation
        const inputs = formCekStunting.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
        });
    }

    // Admin forms validation
    const adminForms = document.querySelectorAll('.admin-form');
    adminForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            this.classList.add('was-validated');
        });
    });
}

// Validate cek stunting form
function validateCekStuntingForm() {
    let isValid = true;
    const form = document.getElementById('formCekStunting');
    
    // Validate nama anak
    const namaAnak = form.querySelector('#nama_anak');
    if (!namaAnak.value.trim()) {
        showFieldError(namaAnak, 'Nama anak harus diisi');
        isValid = false;
    } else if (namaAnak.value.trim().length < 2) {
        showFieldError(namaAnak, 'Nama anak minimal 2 karakter');
        isValid = false;
    } else {
        hideFieldError(namaAnak);
    }

    // Validate alamat
    const alamat = form.querySelector('#alamat');
    if (!alamat.value.trim()) {
        showFieldError(alamat, 'Alamat harus diisi');
        isValid = false;
    } else {
        hideFieldError(alamat);
    }

    // Validate jenis kelamin
    const jenisKelamin = form.querySelector('#jenis_kelamin');
    if (!jenisKelamin.value) {
        showFieldError(jenisKelamin, 'Jenis kelamin harus dipilih');
        isValid = false;
    } else {
        hideFieldError(jenisKelamin);
    }

    // Validate usia
    const usia = form.querySelector('#usia');
    const usiaValue = parseInt(usia.value);
    if (!usiaValue || usiaValue < 1 || usiaValue > 60) {
        showFieldError(usia, 'Usia harus antara 1-60 bulan');
        isValid = false;
    } else {
        hideFieldError(usia);
    }

    // Validate berat badan
    const beratBadan = form.querySelector('#berat_badan');
    const beratValue = parseFloat(beratBadan.value);
    if (!beratValue || beratValue < 1 || beratValue > 50) {
        showFieldError(beratBadan, 'Berat badan tidak valid (1-50 kg)');
        isValid = false;
    } else {
        hideFieldError(beratBadan);
    }

    // Validate tinggi badan
    const tinggiBadan = form.querySelector('#tinggi_badan');
    const tinggiValue = parseFloat(tinggiBadan.value);
    if (!tinggiValue || tinggiValue < 30 || tinggiValue > 150) {
        showFieldError(tinggiBadan, 'Tinggi badan tidak valid (30-150 cm)');
        isValid = false;
    } else {
        hideFieldError(tinggiBadan);
    }

    return isValid;
}

// Validate individual field
function validateField(field) {
    const fieldName = field.name;
    const fieldValue = field.value.trim();

    switch(fieldName) {
        case 'nama_anak':
            if (!fieldValue) {
                showFieldError(field, 'Nama anak harus diisi');
                return false;
            } else if (fieldValue.length < 2) {
                showFieldError(field, 'Nama anak minimal 2 karakter');
                return false;
            }
            break;

        case 'alamat':
            if (!fieldValue) {
                showFieldError(field, 'Alamat harus diisi');
                return false;
            }
            break;

        case 'usia':
            const usia = parseInt(fieldValue);
            if (!usia || usia < 1 || usia > 60) {
                showFieldError(field, 'Usia harus antara 1-60 bulan');
                return false;
            }
            break;

        case 'berat_badan':
            const berat = parseFloat(fieldValue);
            if (!berat || berat < 1 || berat > 50) {
                showFieldError(field, 'Berat badan tidak valid');
                return false;
            }
            break;

        case 'tinggi_badan':
            const tinggi = parseFloat(fieldValue);
            if (!tinggi || tinggi < 30 || tinggi > 150) {
                showFieldError(field, 'Tinggi badan tidak valid');
                return false;
            }
            break;
    }

    hideFieldError(field);
    return true;
}

// Show field error
function showFieldError(field, message) {
    field.classList.add('is-invalid');
    field.classList.remove('is-valid');
    
    let errorDiv = field.parentNode.querySelector('.invalid-feedback');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        field.parentNode.appendChild(errorDiv);
    }
    errorDiv.textContent = message;
}

// Hide field error
function hideFieldError(field) {
    field.classList.remove('is-invalid');
    field.classList.add('is-valid');
    
    const errorDiv = field.parentNode.querySelector('.invalid-feedback');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// Smooth scrolling for anchor links
function initializeSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Auto hide alerts after 5 seconds
function autoHideAlerts() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        if (!alert.querySelector('.btn-close')) return;
        
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
}

// Loading state management
function showLoading(element, text = 'Memproses...') {
    if (element.tagName === 'BUTTON') {
        element.setAttribute('data-original-text', element.innerHTML);
        element.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status"></span>${text}`;
        element.disabled = true;
    }
}

function hideLoading(element) {
    if (element.tagName === 'BUTTON') {
        const originalText = element.getAttribute('data-original-text');
        if (originalText) {
            element.innerHTML = originalText;
            element.removeAttribute('data-original-text');
        }
        element.disabled = false;
    }
}

// Confirm delete action
function confirmDelete(message = 'Apakah Anda yakin ingin menghapus data ini?') {
    return confirm(message);
}

// Format number input
function formatNumber(input, decimals = 1) {
    input.addEventListener('input', function() {
        let value = this.value.replace(/[^0-9.]/g, '');
        
        // Allow only one decimal point
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts[1];
        }
        
        // Limit decimal places
        if (parts[1] && parts[1].length > decimals) {
            value = parts[0] + '.' + parts[1].substring(0, decimals);
        }
        
        this.value = value;
    });
}

// Initialize number formatting for specific inputs
document.addEventListener('DOMContentLoaded', function() {
    const beratInput = document.getElementById('berat_badan');
    const tinggiInput = document.getElementById('tinggi_badan');
    
    if (beratInput) formatNumber(beratInput, 1);
    if (tinggiInput) formatNumber(tinggiInput, 1);
});

// Table search functionality
function initializeTableSearch() {
    const searchInput = document.querySelector('.table-search');
    if (!searchInput) return;

    const table = document.querySelector('.searchable-table');
    if (!table) return;

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = table.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
}

// Print functionality
function printPage() {
    window.print();
}

// Export functionality (basic CSV export)
function exportToCSV(tableId, filename = 'data.csv') {
    const table = document.getElementById(tableId);
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll('tr');

    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = [];
        cols.forEach(col => {
            rowData.push('"' + col.textContent.replace(/"/g, '""') + '"');
        });
        csv.push(rowData.join(','));
    });

    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    
    if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

// Toast notification system
function showToast(message, type = 'info') {
    const toastContainer = document.querySelector('.toast-container') || createToastContainer();
    
    const toastElement = document.createElement('div');
    toastElement.className = `toast align-items-center text-white bg-${type} border-0`;
    toastElement.setAttribute('role', 'alert');
    toastElement.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toastElement);
    
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    
    // Remove element after hide
    toastElement.addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}

function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    container.style.zIndex = '11';
    document.body.appendChild(container);
    return container;
}

// Local storage helpers
function saveToLocalStorage(key, data) {
    try {
        localStorage.setItem(key, JSON.stringify(data));
        return true;
    } catch (e) {
        console.warn('LocalStorage not available');
        return false;
    }
}

function getFromLocalStorage(key, defaultValue = null) {
    try {
        const item = localStorage.getItem(key);
        return item ? JSON.parse(item) : defaultValue;
    } catch (e) {
        console.warn('LocalStorage not available');
        return defaultValue;
    }
}

// Form auto-save (if localStorage is available)
function initializeAutoSave(formId) {
    const form = document.getElementById(formId);
    if (!form) return;

    const storageKey = `autosave_${formId}`;
    
    // Load saved data
    const savedData = getFromLocalStorage(storageKey);
    if (savedData) {
        Object.keys(savedData).forEach(key => {
            const field = form.querySelector(`[name="${key}"]`);
            if (field && field.type !== 'password') {
                field.value = savedData[key];
            }
        });
    }
    
    // Save on input
    form.addEventListener('input', function() {
        const formData = new FormData(this);
        const data = {};
        for (let [key, value] of formData.entries()) {
            if (key !== 'password') {
                data[key] = value;
            }
        }
        saveToLocalStorage(storageKey, data);
    });
    
    // Clear on successful submit
    form.addEventListener('submit', function() {
        localStorage.removeItem(storageKey);
    });
}

// Initialize common functionality
document.addEventListener('DOMContentLoaded', function() {
    initializeTableSearch();
    
    // Initialize auto-save for forms
    initializeAutoSave('formCekStunting');
    
    // Add loading states to form submissions
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                showLoading(submitBtn);
            }
        });
    });
});

// Utility functions
const Utils = {
    // Debounce function
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    // Throttle function
    throttle: function(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        }
    },
    
    // Format currency
    formatCurrency: function(number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR'
        }).format(number);
    },
    
    // Format date
    formatDate: function(date) {
        return new Intl.DateTimeFormat('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }).format(new Date(date));
    }
};

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        showToast,
        showLoading,
        hideLoading,
        confirmDelete,
        formatNumber,
        Utils
    };
}