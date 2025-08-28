<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

include '../config/database.php';

$success = '';
$error = '';

// Hapus berita
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    try {
        $stmt = $pdo->prepare("DELETE FROM berita_terkini WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Berita berhasil dihapus!";
    } catch(PDOException $e) {
        $error = "Gagal menghapus berita!";
    }
}

// Ambil semua berita
try {
    $stmt = $pdo->query("SELECT * FROM berita_terkini ORDER BY tanggal_buat DESC");
    $berita = $stmt->fetchAll();
} catch(PDOException $e) {
    $berita = [];
}

$page_title = "Kelola Berita - Admin";

// Fungsi untuk format tanggal Indonesia
function formatTanggalIndonesia($tanggal) {
    $bulan_indo = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
        5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
        9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
    ];
    
    $timestamp = strtotime($tanggal);
    $hari = date('d', $timestamp);
    $bulan = $bulan_indo[date('n', $timestamp)];
    $tahun = date('Y', $timestamp);
    
    return $hari . ' ' . $bulan . ' ' . $tahun;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .action-buttons {
            min-width: 160px;
        }
        .action-buttons .btn {
            width: 70px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 admin-sidebar">
                <div class="text-center p-3">
                    <img src="https://pfst.cf2.poecdn.net/base/image/cb8d0f034a34f23a9824a116f1403faf0d13fbd49a4b0da7a1f539d61a6d3cbd?w=486&h=600" alt="Logo" width="50" height="50" class="mb-2">
                    <h6 class="text-white">Admin Panel</h6>
                    <small class="text-light">Selamat datang, <?php echo $_SESSION['admin_username']; ?></small>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                     </a>
                    <a class="nav-link" href="kelola_informasi.php">
                        <i class="fas fa-info-circle me-2"></i>informasi stunting
                    </a>
                    <a class="nav-link" href="kelola_berita.php">
                        <i class="fas fa-newspaper me-2"></i>berita terkini
                    </a>
                    <a class="nav-link" href="riwayat_cek.php">
                        <i class="fas fa-history me-2"></i>Riwayat Cek Stunting
                    </a>
                    <a class="nav-link" href="laporan.php">
                        <i class="fas fa-chart-bar me-2"></i>Laporan
                    </a>
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 content-wrapper">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold" style="color: var(--luwu-green);">Kelola Berita Terkini</h2>
                    <a href="tambah_berita.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Tambah Berita
                    </a>
                </div>

                <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                </div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <?php if (!empty($berita)): ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="35%">Judul Berita</th>
                                <th width="35%">Deskripsi</th>
                                <th width="15%">Tanggal</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($berita as $item): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($item['judul']); ?></strong></td>
                                <td><?php echo substr(htmlspecialchars($item['deskripsi']), 0, 100) . '...'; ?></td>
                                <td><?php echo formatTanggalIndonesia($item['tanggal_buat']); ?></td>
                                <td>
                                    <div class="d-flex gap-2 action-buttons">
                                        <a href="edit_berita.php?id=<?php echo $item['id']; ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="?hapus=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Yakin ingin menghapus berita ini?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="text-center p-5">
                        <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Belum ada berita</h5>
                        <p class="text-muted">Mulai tambahkan berita untuk masyarakat</p>
                        <a href="tambah_berita.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Tambah Berita Pertama
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>