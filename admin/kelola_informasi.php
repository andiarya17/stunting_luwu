<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

include '../config/database.php';

$success = '';
$error = '';

// Handle tambah informasi baru
if (isset($_POST['tambah'])) {
    $judul = trim($_POST['judul']);
    $konten = trim($_POST['konten']);
    
    if (!empty($judul) && !empty($konten)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO informasi_stunting (judul, konten, tanggal_dibuat) VALUES (?, ?, NOW())");
            $stmt->execute([$judul, $konten]);
            $success = "Informasi baru berhasil ditambahkan!";
        } catch(PDOException $e) {
            $error = "Terjadi kesalahan saat menambah informasi!";
        }
    } else {
        $error = "Judul dan konten tidak boleh kosong!";
    }
}

// Handle update informasi
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $judul = trim($_POST['judul']);
    $konten = trim($_POST['konten']);
    
    if (!empty($judul) && !empty($konten)) {
        try {
            $stmt = $pdo->prepare("UPDATE informasi_stunting SET judul = ?, konten = ?, tanggal_diupdate = NOW() WHERE id = ?");
            $stmt->execute([$judul, $konten, $id]);
            $success = "Informasi berhasil diperbarui!";
        } catch(PDOException $e) {
            $error = "Terjadi kesalahan saat memperbarui informasi!";
        }
    } else {
        $error = "Judul dan konten tidak boleh kosong!";
    }
}

// Handle hapus informasi
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    try {
        $stmt = $pdo->prepare("DELETE FROM informasi_stunting WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Informasi berhasil dihapus!";
    } catch(PDOException $e) {
        $error = "Terjadi kesalahan saat menghapus informasi!";
    }
}

// Ambil semua informasi
try {
    $stmt = $pdo->query("SELECT * FROM informasi_stunting ORDER BY tanggal_dibuat DESC");
    $informasi_list = $stmt->fetchAll();
} catch(PDOException $e) {
    $informasi_list = [];
}

// Jika ada request edit, ambil data untuk edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM informasi_stunting WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_data = $stmt->fetch();
    } catch(PDOException $e) {
        $edit_data = null;
    }
}

$page_title = "Kelola Informasi Stunting - Admin";
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
        body {
            font-family: Arial, sans-serif;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .form-section {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .info-item {
            background: #f8f9fa;
            border-left: 4px solid var(--luwu-green);
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            position: relative;
        }
        .info-title {
            font-weight: bold;
            color: var(--luwu-green);
            margin-bottom: 10px;
            padding-right: 80px; /* Beri ruang untuk tombol */
        }
        .info-content {
            color: #666;
            line-height: 1.6;
        }
        .info-meta {
            font-size: 12px;
            color: #999;
            margin-top: 10px;
        }
        .action-buttons {
            position: absolute;
            top: 15px;
            right: 15px;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
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
                    <a class="nav-link active" href="kelola_informasi.php">
                        <i class="fas fa-info-circle me-2"></i>Informasi Stunting
                    </a>
                    <a class="nav-link" href="kelola_berita.php">
                        <i class="fas fa-newspaper me-2"></i>Berita Terkini
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
                    <h2 class="fw-bold" style="color: var(--luwu-green);">Kelola Informasi Stunting</h2>
                    <?php if (!$edit_data): ?>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#tambahModal">
                        <i class="fas fa-plus me-2"></i>Tambah Informasi Baru
                    </button>
                    <?php endif; ?>
                </div>

                <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Form Edit (jika ada) -->
                <?php if ($edit_data): ?>
                <div class="form-section">
                    <h4><i class="fas fa-edit me-2"></i>Edit Informasi</h4>
                    <form method="POST">
                        <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                        <div class="mb-3">
                            <label class="form-label">Judul Informasi</label>
                            <input type="text" class="form-control" name="judul" value="<?php echo htmlspecialchars($edit_data['judul']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Konten</label>
                            <textarea class="form-control" name="konten" rows="6" required><?php echo htmlspecialchars($edit_data['konten']); ?></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" name="update" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Perubahan
                            </button>
                            <a href="kelola_informasi.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <!-- Daftar Informasi -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-list me-2"></i>Daftar Informasi Stunting</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($informasi_list)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-info-circle me-2"></i>Belum ada informasi yang ditambahkan.
                        </div>
                        <?php else: ?>
                            <?php foreach ($informasi_list as $info): ?>
                            <div class="info-item">
                                <div class="action-buttons">
                                    <a href="kelola_informasi.php?edit=<?php echo $info['id']; ?>" class="btn btn-warning btn-sm me-1">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-danger btn-sm" onclick="hapusInformasi(<?php echo $info['id']; ?>, '<?php echo addslashes($info['judul']); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <div class="info-title"><?php echo htmlspecialchars($info['judul']); ?></div>
                                <div class="info-content"><?php echo nl2br(htmlspecialchars($info['konten'])); ?></div>
                                <div class="info-meta">
                                    Dibuat: <?php echo date('d/m/Y H:i', strtotime($info['tanggal_dibuat'])); ?>
                                    <?php if ($info['tanggal_diupdate']): ?>
                                    | Diupdate: <?php echo date('d/m/Y H:i', strtotime($info['tanggal_diupdate'])); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Informasi -->
    <div class="modal fade" id="tambahModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Tambah Informasi Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Judul Informasi</label>
                            <input type="text" class="form-control" name="judul" placeholder="Masukkan judul informasi..." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Konten</label>
                            <textarea class="form-control" name="konten" rows="6" placeholder="Masukkan konten informasi..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div class="modal fade" id="hapusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus informasi <strong id="judulHapus"></strong>?</p>
                    <p class="text-muted">Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a href="#" id="btnKonfirmHapus" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Ya, Hapus
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function hapusInformasi(id, judul) {
            document.getElementById('judulHapus').textContent = judul;
            document.getElementById('btnKonfirmHapus').href = 'kelola_informasi.php?hapus=' + id;
            new bootstrap.Modal(document.getElementById('hapusModal')).show();
        }

        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>