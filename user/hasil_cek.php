<?php
$page_title = "Hasil Cek Stunting - Sistem Informasi Pencegahan Stunting";
$css_path = "../assets/css/style.css";
$admin_path = "../admin/";
$user_path = "";
$home_path = "../";
include '../includes/header.php';
include '../config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: cek_stunting.php');
    exit;
}

// Ambil data hasil cek
try {
    $stmt = $pdo->prepare("SELECT * FROM cek_stunting WHERE id = ?");
    $stmt->execute([$id]);
    $hasil = $stmt->fetch();
    
    if (!$hasil) {
        header('Location: cek_stunting.php');
        exit;
    }
} catch(PDOException $e) {
    header('Location: cek_stunting.php');
    exit;
}

// Fungsi untuk mengkonversi usia bulan menjadi format tahun dan bulan
function formatUsia($usia_bulan) {
    $tahun = intval($usia_bulan / 12);
    $bulan = $usia_bulan % 12;
    
    $result = '';
    if ($tahun > 0) {
        $result .= $tahun . ' tahun';
    }
    if ($bulan > 0) {
        if ($tahun > 0) $result .= ' ';
        $result .= $bulan . ' bulan';
    }
    if ($tahun == 0 && $bulan == 0) {
        $result = '0 bulan';
    }
    
    return $result;
}

// Tentukan warna dan ikon berdasarkan hasil WHO
switch($hasil['hasil_cek']) {
    case 'Normal':
        $result_class = 'result-normal';
        $result_color = 'success';
        $result_icon = 'fas fa-smile';
        $result_message = 'Status pertumbuhan anak Anda normal menurut standar WHO. Pertahankan pola hidup sehat!';
        $who_status = 'Tinggi badan sesuai umur (z-score ≥ -2 SD)';
        break;
    case 'Stunting':
        $result_class = 'result-stunting';
        $result_color = 'warning';
        $result_icon = 'fas fa-exclamation-triangle';
        $result_message = 'Anak Anda mengalami stunting menurut WHO. Segera konsultasi dengan tenaga kesehatan.';
        $who_status = 'Stunting (z-score -3 SD hingga < -2 SD)';
        break;
    case 'Stunting Berat':
        $result_class = 'result-severely-stunting';
        $result_color = 'danger';
        $result_icon = 'fas fa-frown';
        $result_message = 'Anak Anda mengalami stunting berat menurut WHO. Perlu penanganan medis segera!';
        $who_status = 'Stunting berat (z-score < -3 SD)';
        break;
    default:
        // Fallback untuk compatibility dengan data lama
        if ($hasil['hasil_cek'] == 'Stunting') {
            $result_class = 'result-stunting';
            $result_color = 'warning';
            $result_icon = 'fas fa-exclamation-triangle';
            $result_message = 'Anak Anda mengalami stunting. Segera konsultasi dengan tenaga kesehatan.';
            $who_status = 'Stunting (berdasarkan pengukuran tinggi badan)';
        } elseif ($hasil['hasil_cek'] == 'Severely Stunting') {
            $result_class = 'result-severely-stunting';
            $result_color = 'danger';
            $result_icon = 'fas fa-frown';
            $result_message = 'Anak Anda mengalami stunting berat. Perlu penanganan medis segera!';
            $who_status = 'Stunting berat (berdasarkan pengukuran tinggi badan)';
        }
        break;
}
?>


<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <!-- Hasil Utama -->
            <div class="result-card <?php echo $result_class; ?>">
                <div class="text-center">
                    <i class="<?php echo $result_icon; ?> fa-4x text-<?php echo $result_color; ?> mb-4"></i>
                    <h2 class="fw-bold text-<?php echo $result_color; ?> mb-3">
                        Status: <?php echo $hasil['hasil_cek']; ?>
                    </h2>
                    <div class="badge bg-<?php echo $result_color; ?> mb-3 p-2">
                        <i class="fas fa-chart-line me-1"></i>WHO: <?php echo $who_status; ?>
                    </div>
                    <p class="lead"><?php echo $result_message; ?></p>
                </div>
            </div>

            <!-- Detail Data -->
            <div class="form-section">
                <h3><i class="fas fa-clipboard-list me-2"></i>Detail Pemeriksaan</h3>
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Nama Anak:</strong></td>
                                <td><?php echo htmlspecialchars($hasil['nama_anak']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Alamat:</strong></td>
                                <td><?php echo htmlspecialchars($hasil['alamat']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Jenis Kelamin:</strong></td>
                                <td><?php echo $hasil['jenis_kelamin']; ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Usia:</strong></td>
                                <td><?php echo formatUsia($hasil['usia']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Berat Badan:</strong></td>
                                <td><?php echo $hasil['berat_badan']; ?> kg</td>
                            </tr>
                            <tr>
                                <td><strong>Tinggi Badan:</strong></td>
                                <td><?php echo $hasil['tinggi_badan']; ?> cm</td>
                            </tr>
                            <?php if (isset($hasil['z_score'])): ?>
                            <tr>
                                <td><strong>Z-Score (TB/U):</strong></td>
                                <td class="fw-bold text-<?php echo $result_color; ?>"><?php echo number_format($hasil['z_score'], 2); ?> SD</td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-calendar-alt me-1"></i>
                        Pemeriksaan dilakukan pada: <?php echo date('d F Y, H:i', strtotime($hasil['tanggal_cek'])); ?> WIB
                    </small>
                </div>
            </div>

            <!-- Standar WHO Info -->
            <div class="card border-info mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Standar WHO untuk Stunting</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-success me-2">Normal</span>
                                <small>Z-score ≥ -2 SD</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-warning me-2">Stunting</span>
                                <small>Z-score -3 SD hingga < -2 SD</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-danger me-2">Stunting Berat</span>
                                <small>Z-score < -3 SD</small>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <small class="text-muted">
                        <strong>Z-score</strong> adalah ukuran standar yang menunjukkan seberapa jauh nilai tinggi badan anak dari rata-rata anak seusianya menurut WHO.
                    </small>
                </div>
            </div>

            <!-- Rekomendasi -->
            <div class="form-section">
                <h3><i class="fas fa-lightbulb me-2"></i>Rekomendasi Berdasarkan WHO</h3>
                <?php if ($hasil['hasil_cek'] == 'Normal'): ?>
                <div class="alert alert-success">
                    <h6><i class="fas fa-check-circle me-2"></i>Pertahankan Status Pertumbuhan Normal</h6>
                    <ul class="mb-0">
                        <li>Lanjutkan pemberian makanan bergizi seimbang dengan 4 bintang (karbohidrat, protein, lemak, vitamin & mineral)</li>
                        <li>Pastikan anak mendapat ASI eksklusif hingga 6 bulan</li>
                        <li>Berikan MPASI yang tepat waktu, berkualitas, dan beragam mulai usia 6 bulan</li>
                        <li>Lakukan pemantauan pertumbuhan rutin setiap bulan di posyandu setempat</li>
                        <li>Jaga kebersihan diri, makanan, dan lingkungan (sanitasi)</li>
                        <li>Pastikan imunisasi lengkap sesuai jadwal</li>
                    </ul>
                </div>
                <?php elseif ($hasil['hasil_cek'] == 'Stunting'): ?>
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Penanganan untuk Stunting</h6>
                    <ul class="mb-0">
                        <li><strong>Segera konsultasi dengan dokter atau ahli gizi terlatih</strong></li>
                        <li>Ikuti program pemulihan gizi di puskesmas atau posyandu setempat</li>
                        <li>Tingkatkan asupan protein hewani (telur, ikan, daging, susu)</li>
                        <li>Berikan makanan tinggi kalori dan protein secara bertahap</li>
                        <li>Pantau pertumbuhan setiap 2 minggu</li>
                        <li>Perbaiki pola makan dengan makanan beragam dan bergizi</li>
                        <li>Pastikan kebersihan makanan dan sanitasi lingkungan</li>
                        <li>Berikan suplemen vitamin dan mineral sesuai anjuran tenaga kesehatan</li>
                    </ul>
                </div>
                <?php else: ?>
                <div class="alert alert-danger">
                    <h6><i class="fas fa-ambulance me-2"></i>Penanganan Stunting Berat - Perlu Tindakan Segera</h6>
                    <ul class="mb-0">
                        <li><strong>SEGERA bawa anak ke dokter spesialis anak atau rumah sakit terdekat</strong></li>
                        <li>Ikuti program terapi gizi intensif dan pemulihan gizi akut</li>
                        <li>Pantau kondisi kesehatan anak setiap minggu</li>
                        <li>Berikan makanan khusus dengan formula tinggi kalori dan protein</li>
                        <li>Lakukan pemeriksaan laboratorium lengkap untuk evaluasi status gizi</li>
                        <li>Pastikan pengobatan penyakit penyerta (infeksi, diare, dll)</li>
                        <li>Konsultasi dengan tim multidisiplin (dokter, ahli gizi, psikolog)</li>
                        <li>Pantau perkembangan kognitif dan motorik anak</li>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .navbar, .page-header nav, .btn, .footer-custom {
        display: none !important;
    }
    .page-header {
        padding: 1rem 0 !important;
        background: white !important;
        color: black !important;
    }
    .container {
        margin: 0 !important;
        padding: 1rem !important;
    }
    .card, .alert {
        box-shadow: none !important;
        border: 1px solid #ccc !important;
    }
}

.result-card {
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.result-normal {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    border: 2px solid #28a745;
}

.result-stunting {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    border: 2px solid #ffc107;
}

.result-severely-stunting {
    background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
    border: 2px solid #dc3545;
}

.form-section {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin-bottom: 1.5rem;
}
</style>

<?php include '../includes/footer.php'; ?>