<?php
$page_title = "Berita - Sistem Informasi Pencegahan Stunting";
$user_path = '';
$admin_path = '../admin/';
$home_path = '../';
$css_path = '../assets/css/style.css';
include '../includes/header.php';
include '../config/database.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 9; // 9 berita per halaman
$offset = ($page - 1) * $limit;

// Ambil total berita
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM berita_terkini WHERE status = 'aktif'");
    $total_result = $stmt->fetch();
    $total_berita = $total_result['total'];
    $total_pages = ceil($total_berita / $limit);
} catch(PDOException $e) {
    $total_berita = 0;
    $total_pages = 0;
}

// Ambil berita dengan pagination
try {
    $stmt = $pdo->prepare("SELECT * FROM berita_terkini WHERE status = 'aktif' ORDER BY tanggal_buat DESC LIMIT ? OFFSET ?");
    $stmt->execute([$limit, $offset]);
    $berita = $stmt->fetchAll();
} catch(PDOException $e) {
    $berita = [];
}
?>

<style>
    body {
        background-color: #f8f9fa;
    }

    .news-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s, box-shadow 0.3s;
        height: 100%;
        border: 1px solid #e9ecef;
    }

    .news-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
    }

    .news-image {
        height: 200px;
        overflow: hidden;
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

    .placeholder-image {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #6c757d;
    }

    .card-title a {
        text-decoration: none;
        color: #333;
        transition: color 0.3s ease;
    }

    .card-title a:hover {
        color: var(--luwu-green);
    }

    .alert-info {
        background: #d1ecf1;
        border: 1px solid #b6d4da;
        border-radius: 8px;
        color: #0c5460;
    }

    .pagination .page-link {
        color: var(--luwu-green);
        border: 1px solid #dee2e6;
        margin: 0 2px;
        border-radius: 5px;
    }

    .pagination .page-item.active .page-link {
        background-color: var(--luwu-green);
        border-color: var(--luwu-green);
        color: white;
    }

    .pagination .page-link:hover {
        color: var(--luwu-green);
        background-color: #f8f9fa;
        border-color: #dee2e6;
    }

    .empty-state {
        background: white;
        border-radius: 10px;
        padding: 40px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    @media (max-width: 768px) {
        .news-image {
            height: 180px;
        }
    }
</style>

<div class="container mt-4">
    
    <!-- News Cards -->
    <?php if (!empty($berita)): ?>
    <div class="row">
        <?php foreach ($berita as $item): ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card news-card h-100">
                <div class="news-image">
                    <a href="detail_berita.php?id=<?php echo $item['id']; ?>">
                        <?php if (!empty($item['gambar'])): ?>
                            <?php 
                            $image_path = '../assets/images/berita/' . $item['gambar'];
                            $thumbnail_path = '../assets/images/berita/thumb_' . $item['gambar'];
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
                        <a href="detail_berita.php?id=<?php echo $item['id']; ?>">
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
                        <a href="detail_berita.php?id=<?php echo $item['id']; ?>" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-arrow-right me-1"></i>Baca Selengkapnya
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <nav aria-label="Page navigation" class="mt-5">
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?php echo ($page - 1); ?>">
                    <i class="fas fa-chevron-left me-1"></i>Sebelumnya
                </a>
            </li>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>">
                    <?php echo $i; ?>
                </a>
            </li>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?php echo ($page + 1); ?>">
                    Selanjutnya<i class="fas fa-chevron-right ms-1"></i>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php endif; ?>

    <?php else: ?>
    <!-- No News Available -->
    <div class="text-center">
        <div class="empty-state">
            <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Belum ada berita tersedia</h5>
            <p class="text-muted">Berita dan informasi akan segera hadir</p>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>