<?php
$page_title = "Cek Stunting - Sistem Informasi Pencegahan Stunting";
$css_path = "../assets/css/style.css";
$js_path = "../assets/js/script.js";
$admin_path = "../admin/";
$user_path = "";
$home_path = "../";
include '../includes/header.php';
include '../config/database.php';

// Fungsi untuk perhitungan stunting
function getExpectedHeight($usia, $jenis_kelamin) {
    $standards = [
        'Laki-laki' => [
            6 => 67.6, 12 => 75.7, 18 => 82.3, 24 => 87.1, 30 => 91.2,
            36 => 96.1, 42 => 99.9, 48 => 103.3, 54 => 106.7, 60 => 109.9
        ],
        'Perempuan' => [
            6 => 65.7, 12 => 74.0, 18 => 80.7, 24 => 85.7, 30 => 89.9,
            36 => 94.1, 42 => 97.9, 48 => 101.6, 54 => 105.0, 60 => 108.4
        ]
    ];
    
    $closest_age = 12;
    $min_diff = PHP_INT_MAX;
    
    foreach ($standards[$jenis_kelamin] as $age => $height) {
        $diff = abs($usia - $age);
        if ($diff < $min_diff) {
            $min_diff = $diff;
            $closest_age = $age;
        }
    }
    
    return $standards[$jenis_kelamin][$closest_age];
}

function getStandardDeviation($usia, $jenis_kelamin) {
    if ($usia <= 12) {
        return 2.8;
    } elseif ($usia <= 24) {
        return 3.2;
    } elseif ($usia <= 36) {
        return 3.6;
    } elseif ($usia <= 48) {
        return 3.9;
    } else {
        return 4.1;
    }
}

// Fungsi logging sederhana untuk menggantikan logActivity
function simpleLog($pdo, $message) {
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_log (activity, ip_address, created_at) VALUES (?, ?, NOW())");
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $stmt->execute([$message, $ip]);
    } catch(Exception $e) {
        error_log("Logging failed: " . $e->getMessage());
    }
}

$success = '';
$error = '';
$debug_info = '';

// Cek POST method dan ada data yang diperlukan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check required fields
    $required_fields = ['nama_anak', 'alamat', 'jenis_kelamin', 'usia_tahun', 'usia_bulan', 'berat_badan', 'tinggi_badan'];
    $all_fields_present = true;
    
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field])) {
            $all_fields_present = false;
        }
    }
    
    if ($all_fields_present) {
        $nama_anak = trim($_POST['nama_anak']);
        $alamat = trim($_POST['alamat']);
        $jenis_kelamin = trim($_POST['jenis_kelamin']);
        $usia_tahun = (int)$_POST['usia_tahun'];
        $usia_bulan = (int)$_POST['usia_bulan'];
        $berat_badan = (float)$_POST['berat_badan'];
        $tinggi_badan = (float)$_POST['tinggi_badan'];
        
        // Hitung total usia dalam bulan
        $usia = ($usia_tahun * 12) + $usia_bulan;
        
        // Validate input
        if (empty($nama_anak) || empty($alamat) || empty($jenis_kelamin) || 
            $usia_tahun < 0 || $usia_tahun > 5 || 
            $usia_bulan < 0 || $usia_bulan > 11 ||
            $usia <= 0 || $usia > 60 || 
            $berat_badan <= 0 || $berat_badan > 50 || 
            $tinggi_badan <= 0 || $tinggi_badan > 150) {
            $error = "Input tidak valid! Pastikan usia tidak melebihi 5 tahun dan bulan antara 0-11.";
        } else {
            // Calculate expected height and Z-Score
            $expected_height = getExpectedHeight($usia, $jenis_kelamin);
            $std_deviation = getStandardDeviation($usia, $jenis_kelamin);
            $z_score = ($tinggi_badan - $expected_height) / $std_deviation;

            // Determine stunting status
            if ($z_score >= -2) {
                $hasil_cek = 'Normal';
            } elseif ($z_score >= -3) {
                $hasil_cek = 'Stunting';
            } else {
                $hasil_cek = 'Severely Stunting';
            }
            
            // Save to database
            $query = "INSERT INTO cek_stunting (nama_anak, alamat, jenis_kelamin, usia, berat_badan, tinggi_badan, hasil_cek, z_score, tinggi_expected, std_deviation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($query);
            $params = [
                $nama_anak, 
                $alamat, 
                $jenis_kelamin, 
                $usia, 
                $berat_badan, 
                $tinggi_badan, 
                $hasil_cek,
                round($z_score, 2),
                $expected_height,
                $std_deviation
            ];
            $result = $stmt->execute($params);
            
            if ($result) {
                $last_id = $pdo->lastInsertId();
                simpleLog($pdo, "Cek stunting baru: {$nama_anak} - {$hasil_cek}");
                header("Location: hasil_cek.php?id=" . $last_id);
                exit;
            } else {
                $error = "Gagal menyimpan data ke database!";
            }
        }
    } else {
        $error = "Data form tidak lengkap!";
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
            </div>
            <?php endif; ?>

            <div class="form-section">
                <div class="text-center mb-4">
                    <i class="fas fa-child fa-4x" style="color: var(--luwu-green);"></i>
                    <h3 class="mt-3">Formulir Cek Stunting</h3>
                    <p class="text-muted">Silahkan isi data berikut ini untuk melakukan cek stunting.</p>
                </div>

                <form method="POST" action="cek_stunting.php" id="formCekStunting" autocomplete="off">
                    <input type="hidden" name="form_submitted" value="1">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nama_anak" class="form-label">
                                    <i class="fas fa-user me-2"></i>Nama Anak <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="nama_anak" name="nama_anak" 
                                       placeholder="Masukkan nama lengkap anak" required maxlength="100">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="alamat" class="form-label">
                                    <i class="fas fa-map-marker-alt me-2"></i>Alamat <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="alamat" name="alamat" 
                                       placeholder="Masukkan alamat lengkap" required maxlength="255">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="jenis_kelamin" class="form-label">
                                    <i class="fas fa-venus-mars me-2"></i>Jenis Kelamin <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" id="jenis_kelamin" name="jenis_kelamin" required>
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-calendar-alt me-2"></i>Usia <span class="text-danger">*</span>
                                </label>
                                <div class="row">
                                    <div class="col-6">
                                        <select class="form-control" id="usia_tahun" name="usia_tahun" required>
                                            <option value="">Tahun</option>
                                            <option value="0">0 Tahun</option>
                                            <option value="1">1 Tahun</option>
                                            <option value="2">2 Tahun</option>
                                            <option value="3">3 Tahun</option>
                                            <option value="4">4 Tahun</option>
                                            <option value="5">5 Tahun</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <select class="form-control" id="usia_bulan" name="usia_bulan" required>
                                            <option value="">Bulan</option>
                                            <option value="0">0 Bulan</option>
                                            <option value="1">1 Bulan</option>
                                            <option value="2">2 Bulan</option>
                                            <option value="3">3 Bulan</option>
                                            <option value="4">4 Bulan</option>
                                            <option value="5">5 Bulan</option>
                                            <option value="6">6 Bulan</option>
                                            <option value="7">7 Bulan</option>
                                            <option value="8">8 Bulan</option>
                                            <option value="9">9 Bulan</option>
                                            <option value="10">10 Bulan</option>
                                            <option value="11">11 Bulan</option>
                                        </select>
                                    </div>
                                </div>
                                <small class="text-muted">Contoh: Anak berusia 2 tahun 6 bulan</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="berat_badan" class="form-label">
                                    <i class="fas fa-weight me-2"></i>Berat Badan (Kg) <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" id="berat_badan" name="berat_badan" 
                                       placeholder="Contoh: 12.5" step="0.1" min="1" max="50" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tinggi_badan" class="form-label">
                                    <i class="fas fa-ruler-vertical me-2"></i>Tinggi Badan (Cm) <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" id="tinggi_badan" name="tinggi_badan" 
                                       placeholder="Contoh: 85.5" step="0.1" min="30" max="150" required>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit" name="cek_stunting" value="1" class="btn btn-primary btn-lg">
                            <i class="fas fa-search me-2"></i>Cek Hasil
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>