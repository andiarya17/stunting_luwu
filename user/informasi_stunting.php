<?php
$page_title = "Informasi Stunting - Sistem Informasi Pencegahan Stunting";
$css_path = "../assets/css/style.css";
$admin_path = "../admin/";
$user_path = "";
$home_path = "../";
include '../includes/header.php';
include '../config/database.php';

// Ambil semua informasi stunting yang aktif
try {
    $stmt = $pdo->query("SELECT * FROM informasi_stunting WHERE status = 'aktif' ORDER BY urutan ASC, tanggal_dibuat ASC");
    $informasi_list = $stmt->fetchAll();
} catch(PDOException $e) {
    $informasi_list = [];
}

// Fungsi untuk menentukan icon berdasarkan judul
function getIconByTitle($judul) {
    $judul_lower = strtolower($judul);
    
    if (strpos($judul_lower, 'apa itu') !== false || strpos($judul_lower, 'pengertian') !== false) {
        return ['icon' => 'fas fa-question-circle', 'color' => 'primary'];
    } elseif (strpos($judul_lower, 'gejala') !== false || strpos($judul_lower, 'tanda') !== false) {
        return ['icon' => 'fas fa-exclamation-triangle', 'color' => 'warning'];
    } elseif (strpos($judul_lower, 'penyebab') !== false || strpos($judul_lower, 'faktor') !== false) {
        return ['icon' => 'fas fa-search', 'color' => 'danger'];
    } elseif (strpos($judul_lower, 'cara mencegah') !== false || strpos($judul_lower, 'pencegahan') !== false) {
        return ['icon' => 'fas fa-shield-alt', 'color' => 'success'];
    } elseif (strpos($judul_lower, 'dampak') !== false || strpos($judul_lower, 'akibat') !== false) {
        return ['icon' => 'fas fa-exclamation-circle', 'color' => 'danger'];
    } elseif (strpos($judul_lower, 'penanganan') !== false || strpos($judul_lower, 'intervensi') !== false) {
        return ['icon' => 'fas fa-medkit', 'color' => 'info'];
    } else {
        return ['icon' => 'fas fa-info-circle', 'color' => 'secondary'];
    }
}
?>

<div class="container info-section">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <?php if (empty($informasi_list)): ?>
            <!-- Jika tidak ada informasi -->
            <div class="text-center py-5">
                <i class="fas fa-info-circle fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">Informasi Stunting Belum Tersedia</h4>
                <p class="text-muted">Maaf, informasi stunting sedang dalam proses pembaruan.</p>
            </div>
            <?php else: ?>
            
            <?php foreach ($informasi_list as $index => $info): ?>
            <?php $iconData = getIconByTitle($info['judul']); ?>
            
            <!-- Informasi Stunting Card -->
            <div class="info-card">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-<?php echo $iconData['color']; ?> rounded-circle p-3 me-3">
                        <i class="<?php echo $iconData['icon']; ?> text-white fa-2x"></i>
                    </div>
                    <h4 class="mb-0"><?php echo htmlspecialchars($info['judul']); ?></h4>
                </div>
                <div class="content">
                    <?php echo nl2br(htmlspecialchars($info['konten'])); ?>
                </div>
                
                <?php 
                // Tambahan konten berdasarkan judul tertentu
                $judul_lower = strtolower($info['judul']);
                ?>
                
                <?php if (strpos($judul_lower, 'gejala') !== false): ?>
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-ruler-vertical me-2"></i>Fisik</h6>
                            <ul class="mb-0">
                                <li>Tinggi badan di bawah standar usia</li>
                                <li>Berat badan rendah</li>
                                <li>Tubuh lebih kecil dari anak seusianya</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-brain me-2"></i>Perkembangan</h6>
                            <ul class="mb-0">
                                <li>Perkembangan kognitif terlambat</li>
                                <li>Mudah sakit</li>
                                <li>Daya tahan tubuh lemah</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (strpos($judul_lower, 'penyebab') !== false): ?>
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="text-center p-3 border rounded">
                            <i class="fas fa-utensils fa-3x text-danger mb-2"></i>
                            <h6>Kurang Gizi</h6>
                            <small class="text-muted">Asupan nutrisi tidak memadai</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 border rounded">
                            <i class="fas fa-virus fa-3x text-warning mb-2"></i>
                            <h6>Infeksi Berulang</h6>
                            <small class="text-muted">Penyakit yang berulang</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 border rounded">
                            <i class="fas fa-home fa-3x text-info mb-2"></i>
                            <h6>Sanitasi Buruk</h6>
                            <small class="text-muted">Lingkungan tidak sehat</small>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (strpos($judul_lower, 'cara mencegah') !== false || strpos($judul_lower, 'pencegahan') !== false): ?>
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-baby fa-3x text-primary mb-3"></i>
                                <h6>1000 Hari Pertama Kehidupan</h6>
                                <p class="small text-muted">Masa kritis dari kehamilan hingga anak berusia 2 tahun</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-heartbeat fa-3x text-danger mb-3"></i>
                                <h6>Pemeriksaan Rutin</h6>
                                <p class="small text-muted">Pantau tumbuh kembang anak secara berkala</p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (strpos($judul_lower, 'dampak') !== false): ?>
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-clock me-2"></i>Dampak Jangka Pendek</h6>
                            <ul class="mb-0">
                                <li>Meningkatnya risiko kesakitan</li>
                                <li>Perkembangan terhambat</li>
                                <li>Daya tahan tubuh lemah</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-calendar-alt me-2"></i>Dampak Jangka Panjang</h6>
                            <ul class="mb-0">
                                <li>Produktivitas menurun</li>
                                <li>Risiko penyakit tidak menular</li>
                                <li>Kualitas hidup rendah</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (strpos($judul_lower, 'penanganan') !== false || strpos($judul_lower, 'intervensi') !== false): ?>
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card border-success h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-apple-alt fa-3x text-success mb-3"></i>
                                <h6>Intervensi Gizi Spesifik</h6>
                                <p class="small text-muted">Makanan tambahan, suplementasi, promosi ASI</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-info h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-3x text-info mb-3"></i>
                                <h6>Intervensi Gizi Sensitif</h6>
                                <p class="small text-muted">Air bersih, sanitasi, pendidikan, ekonomi</p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Meta informasi (tanggal update) -->
                <?php if ($info['tanggal_diupdate']): ?>
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i>
                        Terakhir diperbarui: <?php echo date('d F Y', strtotime($info['tanggal_diupdate'])); ?>
                    </small>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.info-card {
    background: #fff;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border-left: 5px solid var(--luwu-green);
}

.info-card .content {
    font-size: 16px;
    line-height: 1.8;
    color: #555;
}

.info-card h4 {
    color: var(--luwu-green);
    font-weight: 600;
}

.alert {
    border: none;
    border-radius: 10px;
}

.card {
    border-radius: 10px;
    transition: transform 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
}

/* Smooth scroll untuk navigasi */
html {
    scroll-behavior: smooth;
}

/* Add scroll target untuk navigasi */
<?php foreach ($informasi_list as $info): ?>
.info-card:nth-of-type(<?php echo $info['urutan'] ?: $info['id']; ?>) {
    scroll-margin-top: 80px;
}
<?php endforeach; ?>
</style>

<script>
// Tambahkan ID anchor untuk setiap info card
document.addEventListener('DOMContentLoaded', function() {
    const infoCards = document.querySelectorAll('.info-card');
    const informasiIds = <?php echo json_encode(array_column($informasi_list, 'id')); ?>;
    
    infoCards.forEach((card, index) => {
        if (informasiIds[index]) {
            card.id = 'info-' + informasiIds[index];
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>