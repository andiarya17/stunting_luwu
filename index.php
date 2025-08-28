<?php
$page_title = "Beranda - Sistem Informasi Pencegahan Stunting";
include 'includes/header.php';
include 'config/database.php';

// Ambil berita terkini
try {
    $stmt = $pdo->query("SELECT * FROM berita_terkini WHERE status = 'aktif' ORDER BY tanggal_buat DESC LIMIT 3");
    $berita = $stmt->fetchAll();
} catch(PDOException $e) {
    $berita = [];
}

// Hitung statistik
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total_cek FROM cek_stunting");
    $stats = $stmt->fetch();
    $total_cek = $stats['total_cek'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total_berita FROM berita_terkini WHERE status = 'aktif'");
    $stats = $stmt->fetch();
    $total_berita = $stats['total_berita'];
} catch(PDOException $e) {
    $total_cek = 0;
    $total_berita = 0;
}
?>

<style>
    body {
        background-color: #f4f7fa;
    }
    .hero-section {
        background: linear-gradient(135deg, #d0f0c0, #ffffff);
        padding: 60px 0;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        margin-bottom: 40px;
    }

    .hero-title {
        font-size: 2.5rem;
        color: var(--luwu-green);
        line-height: 1.2;
        margin-bottom: 8px !important;
        font-weight: 700;
    }
    
    .hero-subtitle {
        font-size: 2rem !important;
        color: var(--luwu-green) !important;
        font-weight: 600 !important;
        line-height: 1.2 !important;
        margin-top: 8px !important;
        margin-bottom: 24px !important;
    }

    .hero-description {
        margin-top: 20px !important;
        line-height: 1.6;
        font-size: 1.1rem;
    }

    .animation-placeholder {
        transition: all 0.3s ease;
    }

    .animation-placeholder:hover {
        transform: scale(1.02);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .stats-card {
        background: white;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
    }

    .news-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .news-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
    }

    .news-image {
        height: 200px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }

    .news-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .news-card:hover .news-img {
        transform: scale(1.05);
    }

    .card-title a {
        text-decoration: none;
        color: #333;
    }

    .card-title a:hover {
        color: var(--luwu-green);
    }

    .call-to-action {
        background: linear-gradient(135deg, var(--light-green), #ffffff);
        border-radius: 10px;
        padding: 40px;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .news-section {
        margin-top: 50px;
        padding-top: 20px;
    }
</style>

<div class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="hero-title">Sistem Informasi Pencegahan Stunting Kabupaten Luwu</h1>
                <p class="lead hero-description mb-4">Kabupaten Luwu berkomitmen untuk mencegah stunting dan meningkatkan kesehatan anak-anak melalui sistem informasi terintegrasi yang mudah diakses oleh masyarakat.</p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="user/informasi_stunting.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-info-circle me-2"></i>Klik untuk Info Stunting
                    </a>
                    <a href="user/cek_stunting.php" class="btn btn-success btn-lg">
                        <i class="fas fa-search me-2"></i>Klik untuk Cek Stunting
                    </a>
                </div>
            </div>
            <div class="col-lg-5 text-center">
                <div class="hero-animation">
                    <img src="assets/images/foto.png" alt="Ilustrasi Pencegahan Stunting" class="img-fluid" style="max-width: 100%; height: 400px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Berita Terkini Section -->
<div class="container news-section">
    <div class="text-center mb-5">
        <h2 class="display-5 fw-bold" style="color: var(--luwu-green);">Berita Terkini</h2>
    </div>
    
    <?php if (!empty($berita)): ?>
    <div class="row">
        <?php foreach ($berita as $item): ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card news-card h-100">
                <div class="news-image">
                    <a href="user/detail_berita.php?id=<?php echo $item['id']; ?>">
                        <?php if (!empty($item['gambar'])): ?>
                            <?php 
                            $image_path = 'assets/images/berita/' . $item['gambar'];
                            $thumbnail_path = 'assets/images/berita/thumb_' . $item['gambar'];
                            ?>
                            <?php if (file_exists($thumbnail_path)): ?>
                                <img src="<?php echo $thumbnail_path; ?>" 
                                     alt="<?php echo htmlspecialchars($item['judul']); ?>" 
                                     class="news-img">
                            <?php elseif (file_exists($image_path)): ?>
                                <img src="<?php echo $image_path; ?>" 
                                     alt="<?php echo htmlspecialchars($item['judul']); ?>" 
                                     class="news-img">
                            <?php else: ?>
                                <div class="placeholder-image">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                    <p class="text-muted mt-2 mb-0">Gambar tidak ditemukan</p>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="placeholder-image">
                                <i class="fas fa-newspaper fa-3x text-muted"></i>
                                <p class="text-muted mt-2 mb-0">Tidak ada gambar</p>
                            </div>
                        <?php endif; ?>
                    </a>
                </div>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">
                        <a href="user/detail_berita.php?id=<?php echo $item['id']; ?>" class="text-decoration-none text-dark">
                            <?php echo htmlspecialchars($item['judul']); ?>
                        </a>
                    </h5>
                    <p class="card-text flex-grow-1">
                        <?php 
                        $deskripsi = strip_tags($item['deskripsi']);
                        echo htmlspecialchars(substr($deskripsi, 0, 120)) . (strlen($deskripsi) > 120 ? '...' : ''); 
                        ?>
                    </p>
                    <div class="mt-auto">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <small class="text-muted">
                                <i class="fas fa-calendar-alt me-1"></i>
                                <?php echo date('d M Y', strtotime($item['tanggal_buat'])); ?>
                            </small>
                            <?php if (!empty($item['views'])): ?>
                            <small class="text-muted">
                                <i class="fas fa-eye me-1"></i>
                                <?php echo $item['views']; ?> views
                            </small>
                            <?php endif; ?>
                        </div>
                        <a href="user/detail_berita.php?id=<?php echo $item['id']; ?>" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-arrow-right me-1"></i>Baca Selengkapnya
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    </div>
    <?php else: ?>
    <div class="text-center">
        <div class="card p-5">
            <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Belum ada berita tersedia</h5>
            <p class="text-muted">Berita dan informasi akan segera hadir</p>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>