<?php
/**
 * Fungsi-fungsi helper untuk sistem monitoring stunting Kabupaten Luwu
 * Updated dengan fitur upload foto dan keamanan yang lebih baik
 */

// Fungsi untuk format tanggal Indonesia
function formatTanggalIndonesia($tanggal) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $split = explode('-', date('Y-m-d', strtotime($tanggal)));
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

// Fungsi untuk membersihkan input
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Fungsi untuk validasi email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Fungsi untuk generate random string
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Fungsi untuk menghitung status stunting berdasarkan WHO Growth Standards
function hitungStatusStunting($tinggi_badan, $usia, $jenis_kelamin) {
    // Tabel standar WHO untuk tinggi badan menurut umur (HAZ)
    // Diperluas untuk rentang usia yang lebih lengkap
    
    $standards = [
        'Laki-laki' => [
            6 => ['median' => 67.6, 'sd' => 2.5],
            12 => ['median' => 75.7, 'sd' => 2.9],
            18 => ['median' => 82.3, 'sd' => 3.2],
            24 => ['median' => 87.1, 'sd' => 3.4],
            30 => ['median' => 91.3, 'sd' => 3.6],
            36 => ['median' => 96.1, 'sd' => 3.7],
            42 => ['median' => 99.9, 'sd' => 3.8],
            48 => ['median' => 103.3, 'sd' => 4.0],
            54 => ['median' => 106.7, 'sd' => 4.1],
            60 => ['median' => 109.9, 'sd' => 4.2]
        ],
        'Perempuan' => [
            6 => ['median' => 65.7, 'sd' => 2.4],
            12 => ['median' => 74.0, 'sd' => 2.8],
            18 => ['median' => 80.7, 'sd' => 3.1],
            24 => ['median' => 85.7, 'sd' => 3.3],
            30 => ['median' => 90.0, 'sd' => 3.5],
            36 => ['median' => 94.1, 'sd' => 3.6],
            42 => ['median' => 98.0, 'sd' => 3.7],
            48 => ['median' => 101.6, 'sd' => 3.9],
            54 => ['median' => 105.0, 'sd' => 4.0],
            60 => ['median' => 108.4, 'sd' => 4.1]
        ]
    ];
    
    // Cari standar yang paling dekat dengan usia anak
    $closest_age = null;
    $min_diff = PHP_INT_MAX;
    
    foreach ($standards[$jenis_kelamin] as $age => $std) {
        $diff = abs($usia - $age);
        if ($diff < $min_diff) {
            $min_diff = $diff;
            $closest_age = $age;
        }
    }
    
    if ($closest_age === null) {
        return [
            'status' => 'Data tidak tersedia',
            'z_score' => null,
            'tinggi_expected' => null,
            'std_deviation' => null
        ];
    }
    
    $median = $standards[$jenis_kelamin][$closest_age]['median'];
    $sd = $standards[$jenis_kelamin][$closest_age]['sd'];
    
    // Hitung Z-Score
    $z_score = ($tinggi_badan - $median) / $sd;
    
    // Tentukan status berdasarkan Z-Score
    if ($z_score >= -2) {
        $status = 'Normal';
    } elseif ($z_score >= -3) {
        $status = 'Stunting';
    } else {
        $status = 'Severely Stunting';
    }
    
    return [
        'status' => $status,
        'z_score' => round($z_score, 2),
        'tinggi_expected' => $median,
        'std_deviation' => $sd
    ];
}

// Fungsi untuk logging aktivitas
function logActivity($pdo, $activity, $user_id = null, $ip_address = null, $user_agent = null) {
    try {
        if ($ip_address === null) {
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }
        if ($user_agent === null) {
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        }
        
        $stmt = $pdo->prepare("INSERT INTO activity_log (activity, user_id, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$activity, $user_id, $ip_address, $user_agent]);
    } catch(PDOException $e) {
        // Silent fail untuk logging
        error_log("Log activity error: " . $e->getMessage());
    }
}

// Fungsi untuk format file size
function formatFileSize($size) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    $size = max($size, 0);
    $pow = floor(($size ? log($size) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $size /= pow(1024, $pow);
    
    return round($size, 2) . ' ' . $units[$pow];
}

// Fungsi untuk validasi file upload yang lebih ketat
function validateFileUpload($file, $allowed_types = ['jpg', 'jpeg', 'png', 'gif'], $max_size = 5242880) {
    $errors = [];
    
    // Cek error upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errors[] = 'File terlalu besar';
                break;
            case UPLOAD_ERR_PARTIAL:
                $errors[] = 'File tidak terupload sempurna';
                break;
            case UPLOAD_ERR_NO_FILE:
                $errors[] = 'Tidak ada file yang dipilih';
                break;
            default:
                $errors[] = 'Error dalam upload file';
        }
        return $errors;
    }
    
    // Validasi ekstensi file
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_types)) {
        $errors[] = 'Tipe file tidak diizinkan. Hanya ' . implode(', ', array_map('strtoupper', $allowed_types)) . ' yang diperbolehkan.';
    }
    
    // Validasi ukuran file
    if ($file['size'] > $max_size) {
        $errors[] = 'Ukuran file terlalu besar. Maksimal ' . formatFileSize($max_size);
    }
    
    // Validasi MIME type untuk keamanan ekstra (hanya jika ekstensi tersedia)
    if (function_exists('finfo_open')) {
        $allowed_mimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg', 
            'png' => 'image/png',
            'gif' => 'image/gif'
        ];
        
        if (isset($allowed_mimes[$file_extension])) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime_type = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                
                if ($mime_type !== $allowed_mimes[$file_extension]) {
                    $errors[] = 'File tidak valid atau tipe MIME tidak sesuai';
                }
            }
        }
    }
    
    return $errors;
}

// Fungsi untuk membuat thumbnail yang lebih advanced
function createThumbnail($source, $destination, $width = 400, $height = 300, $quality = 90) {
    // Cek apakah ekstensi GD tersedia
    if (!extension_loaded('gd')) {
        error_log('GD extension is not loaded. Thumbnail creation skipped.');
        return false;
    }
    
    $info = getimagesize($source);
    if (!$info) return false;
    
    list($orig_width, $orig_height, $type) = $info;
    
    // Hitung dimensi thumbnail dengan mempertahankan aspect ratio
    $ratio = min($width / $orig_width, $height / $orig_height);
    $new_width = $orig_width * $ratio;
    $new_height = $orig_height * $ratio;
    
    // Buat image resource berdasarkan tipe
    $source_image = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            if (function_exists('imagecreatefromjpeg')) {
                $source_image = imagecreatefromjpeg($source);
            }
            break;
        case IMAGETYPE_PNG:
            if (function_exists('imagecreatefrompng')) {
                $source_image = imagecreatefrompng($source);
            }
            break;
        case IMAGETYPE_GIF:
            if (function_exists('imagecreatefromgif')) {
                $source_image = imagecreatefromgif($source);
            }
            break;
        default:
            return false;
    }
    
    if (!$source_image) return false;
    
    // Buat thumbnail dengan background putih
    $thumbnail = imagecreatetruecolor($new_width, $new_height);
    if (!$thumbnail) {
        imagedestroy($source_image);
        return false;
    }
    
    $white = imagecolorallocate($thumbnail, 255, 255, 255);
    imagefill($thumbnail, 0, 0, $white);
    
    // Untuk PNG, preserve transparency
    if ($type == IMAGETYPE_PNG) {
        imagecolortransparent($thumbnail, $white);
        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);
    }
    
    // Resize image
    imagecopyresampled($thumbnail, $source_image, 0, 0, 0, 0, 
                      $new_width, $new_height, $orig_width, $orig_height);
    
    // Simpan thumbnail
    $result = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            if (function_exists('imagejpeg')) {
                $result = imagejpeg($thumbnail, $destination, $quality);
            }
            break;
        case IMAGETYPE_PNG:
            if (function_exists('imagepng')) {
                $result = imagepng($thumbnail, $destination);
            }
            break;
        case IMAGETYPE_GIF:
            if (function_exists('imagegif')) {
                $result = imagegif($thumbnail, $destination);
            }
            break;
    }
    
    // Cleanup
    imagedestroy($source_image);
    imagedestroy($thumbnail);
    
    return $result;
}

// Fungsi untuk menghapus file dan thumbnail
function deleteImageFiles($filename, $upload_dir) {
    $success = true;
    
    // Hapus file utama
    if (!empty($filename) && file_exists($upload_dir . $filename)) {
        if (!unlink($upload_dir . $filename)) {
            $success = false;
        }
    }
    
    // Hapus thumbnail
    if (file_exists($upload_dir . 'thumb_' . $filename)) {
        if (!unlink($upload_dir . 'thumb_' . $filename)) {
            $success = false;
        }
    }
    
    return $success;
}

// Fungsi untuk pagination
function createPagination($current_page, $total_pages, $base_url, $params = []) {
    $pagination = '';
    
    if ($total_pages <= 1) return $pagination;
    
    // Build query string
    $query_string = '';
    if (!empty($params)) {
        $query_string = '&' . http_build_query($params);
    }
    
    $pagination .= '<nav aria-label="Page navigation">';
    $pagination .= '<ul class="pagination justify-content-center">';
    
    // Previous button
    if ($current_page > 1) {
        $pagination .= '<li class="page-item">';
        $pagination .= '<a class="page-link" href="' . $base_url . '?page=' . ($current_page - 1) . $query_string . '">Previous</a>';
        $pagination .= '</li>';
    }
    
    // Page numbers
    $start = max(1, $current_page - 2);
    $end = min($total_pages, $current_page + 2);
    
    // Show first page if not in range
    if ($start > 1) {
        $pagination .= '<li class="page-item">';
        $pagination .= '<a class="page-link" href="' . $base_url . '?page=1' . $query_string . '">1</a>';
        $pagination .= '</li>';
        if ($start > 2) {
            $pagination .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    // Page range
    for ($i = $start; $i <= $end; $i++) {
        $active = ($i == $current_page) ? ' active' : '';
        $pagination .= '<li class="page-item' . $active . '">';
        $pagination .= '<a class="page-link" href="' . $base_url . '?page=' . $i . $query_string . '">' . $i . '</a>';
        $pagination .= '</li>';
    }
    
    // Show last page if not in range
    if ($end < $total_pages) {
        if ($end < $total_pages - 1) {
            $pagination .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $pagination .= '<li class="page-item">';
        $pagination .= '<a class="page-link" href="' . $base_url . '?page=' . $total_pages . $query_string . '">' . $total_pages . '</a>';
        $pagination .= '</li>';
    }
    
    // Next button
    if ($current_page < $total_pages) {
        $pagination .= '<li class="page-item">';
        $pagination .= '<a class="page-link" href="' . $base_url . '?page=' . ($current_page + 1) . $query_string . '">Next</a>';
        $pagination .= '</li>';
    }
    
    $pagination .= '</ul>';
    $pagination .= '</nav>';
    
    return $pagination;
}

// Fungsi untuk escape output
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Fungsi untuk check admin login
function requireAdmin() {
    if (!isset($_SESSION['admin_logged_in'])) {
        header('Location: login.php');
        exit;
    }
}

// Fungsi untuk redirect dengan message
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header("Location: $url");
    exit;
}

// Fungsi untuk menampilkan flash message
function showFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">';
        echo e($message);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
        
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}

// Fungsi untuk upload file dengan handling yang lebih baik
function uploadFile($file, $upload_dir, $allowed_types = ['jpg', 'jpeg', 'png', 'gif'], $max_size = 5242880, $create_thumbnail = true) {
    $result = [
        'success' => false,
        'filename' => '',
        'error' => '',
        'thumbnail' => ''
    ];
    
    // Validasi file
    $errors = validateFileUpload($file, $allowed_types, $max_size);
    if (!empty($errors)) {
        $result['error'] = implode(', ', $errors);
        return $result;
    }
    
    // Buat folder jika belum ada
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            $result['error'] = 'Gagal membuat folder upload';
            return $result;
        }
    }
    
    // Generate nama file unik
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = 'img_' . time() . '_' . generateRandomString(8) . '.' . $file_extension;
    $upload_path = $upload_dir . $filename;
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        $result['success'] = true;
        $result['filename'] = $filename;
        
        // Buat thumbnail jika diminta dan GD tersedia
        if ($create_thumbnail && extension_loaded('gd')) {
            $thumbnail_path = $upload_dir . 'thumb_' . $filename;
            if (createThumbnail($upload_path, $thumbnail_path, 400, 300)) {
                $result['thumbnail'] = 'thumb_' . $filename;
            }
        }
    } else {
        $result['error'] = 'Gagal mengupload file';
    }
    
    return $result;
}

// Fungsi untuk mendapatkan statistik stunting
function getStuntingStats($pdo) {
    try {
        $stats = [
            'total_pemeriksaan' => 0,
            'normal' => 0,
            'stunting' => 0,
            'severely_stunting' => 0,
            'persentase_stunting' => 0
        ];
        
        // Total pemeriksaan
        $stmt = $pdo->query("SELECT COUNT(*) FROM cek_stunting");
        $stats['total_pemeriksaan'] = $stmt->fetchColumn();
        
        // Statistik berdasarkan hasil
        $stmt = $pdo->query("
            SELECT hasil_cek, COUNT(*) as jumlah 
            FROM cek_stunting 
            GROUP BY hasil_cek
        ");
        
        while ($row = $stmt->fetch()) {
            switch ($row['hasil_cek']) {
                case 'Normal':
                    $stats['normal'] = $row['jumlah'];
                    break;
                case 'Stunting':
                    $stats['stunting'] = $row['jumlah'];
                    break;
                case 'Severely Stunting':
                    $stats['severely_stunting'] = $row['jumlah'];
                    break;
            }
        }
        
        // Hitung persentase stunting
        if ($stats['total_pemeriksaan'] > 0) {
            $total_stunting = $stats['stunting'] + $stats['severely_stunting'];
            $stats['persentase_stunting'] = round(($total_stunting / $stats['total_pemeriksaan']) * 100, 1);
        }
        
        return $stats;
    } catch (PDOException $e) {
        return [
            'total_pemeriksaan' => 0,
            'normal' => 0,
            'stunting' => 0,
            'severely_stunting' => 0,
            'persentase_stunting' => 0
        ];
    }
}

// Fungsi untuk validasi input yang lebih ketat
function validateInput($data, $type = 'string', $options = []) {
    $data = cleanInput($data);
    
    switch ($type) {
        case 'email':
            return filter_var($data, FILTER_VALIDATE_EMAIL) ? $data : false;
            
        case 'int':
            $min = $options['min'] ?? null;
            $max = $options['max'] ?? null;
            $int_data = filter_var($data, FILTER_VALIDATE_INT);
            if ($int_data === false) return false;
            if ($min !== null && $int_data < $min) return false;
            if ($max !== null && $int_data > $max) return false;
            return $int_data;
            
        case 'float':
            $min = $options['min'] ?? null;
            $max = $options['max'] ?? null;
            $float_data = filter_var($data, FILTER_VALIDATE_FLOAT);
            if ($float_data === false) return false;
            if ($min !== null && $float_data < $min) return false;
            if ($max !== null && $float_data > $max) return false;
            return $float_data;
            
        case 'string':
            $min_length = $options['min_length'] ?? 0;
            $max_length = $options['max_length'] ?? null;
            if (strlen($data) < $min_length) return false;
            if ($max_length !== null && strlen($data) > $max_length) return false;
            return $data;
            
        default:
            return $data;
    }
}

// Fungsi untuk membuat slug URL yang aman
function createSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    $string = trim($string, '-');
    return $string;
}
?>