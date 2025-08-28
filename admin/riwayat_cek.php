<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

include '../config/database.php';

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

// Fungsi untuk menampilkan nama hasil stunting yang konsisten
function formatHasilStunting($hasil) {
    switch($hasil) {
        case 'Severely Stunting':
            return 'Stunting Berat';
        case 'Stunting Sedang':
            return 'Stunting';
        default:
            return $hasil;
    }
}

$success = '';
$error = '';

// Hapus data cek
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    try {
        $stmt = $pdo->prepare("DELETE FROM cek_stunting WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Data berhasil dihapus!";
    } catch(PDOException $e) {
        $error = "Gagal menghapus data!";
    }
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filter
$where = "";
$params = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%';
    $where = "WHERE nama_anak LIKE ? OR alamat LIKE ?";
    $params = [$search, $search];
}

try {
    // Count total records
    $count_query = "SELECT COUNT(*) as total FROM cek_stunting $where";
    $stmt = $pdo->prepare($count_query);
    $stmt->execute($params);
    $total_records = $stmt->fetch()['total'];
    $total_pages = ceil($total_records / $limit);
    
    // Get data
    $query = "SELECT * FROM cek_stunting $where ORDER BY tanggal_cek DESC LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $data_cek = $stmt->fetchAll();
} catch(PDOException $e) {
    $data_cek = [];
    $total_pages = 0;
}

$page_title = "Riwayat Cek Stunting - Admin";
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
        .table thead th {
            vertical-align: middle;
            text-align: center;
            white-space: nowrap;
        }
        
        @media print {
            body {
                margin: 0;
                font-size: 12px; /* Adjusted for print */
            }
            .admin-sidebar, .btn, .no-print {
                display: none !important; /* Hide sidebar and buttons */
            }
            .content-wrapper {
                padding: 0 !important;
            }
            h2 {
                text-align: center;
                margin-bottom: 20px;
            }
            .table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            th, td {
                border: 1px solid #000;
                padding: 8px;
                text-align: center;
                font-size: 12px; /* Consistent font size */
            }
            th {
                background-color: #f8f9fa;
            }
            /* Add additional styles for print footer if needed */
            .footer {
                text-align: center;
                margin-top: 20px;
                font-size: 12px;
            }
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
                    <a class="nav-link active" href="riwayat_cek.php">
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
                    <h2 class="fw-bold" style="color: var(--luwu-green);">Riwayat Cek Stunting</h2>
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

                <!-- Search -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="d-flex">
                            <input type="text" name="search" class="form-control me-2" placeholder="Cari nama anak atau alamat..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                            <?php if (isset($_GET['search'])): ?>
                            <a href="riwayat_cek.php" class="btn btn-secondary ms-2">
                                <i class="fas fa-times"></i>
                            </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <?php if (!empty($data_cek)): ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Anak</th>
                                <th>Alamat</th>
                                <th>JK</th>
                                <th>Usia</th>
                                <th>BB (kg)</th>
                                <th>TB (cm)</th>
                                <th>Hasil</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data_cek as $index => $cek): ?>
                            <tr>
                                <td><?php echo $offset + $index + 1; ?></td>
                                <td><strong><?php echo htmlspecialchars($cek['nama_anak']); ?></strong></td>
                                <td><?php echo htmlspecialchars($cek['alamat']); ?></td>
                                <td><?php echo $cek['jenis_kelamin'] == 'Laki-laki' ? 'L' : 'P'; ?></td>
                                <td><?php echo formatUsia($cek['usia']); ?></td>
                                <td><?php echo $cek['berat_badan']; ?></td>
                                <td><?php echo $cek['tinggi_badan']; ?></td>
                                <td>
                                    <span class="badge <?php 
                                        $hasil_formatted = formatHasilStunting($cek['hasil_cek']);
                                        echo ($hasil_formatted == 'Normal') ? 'bg-success' : 
                                             (($hasil_formatted == 'Stunting') ? 'bg-warning' : 'bg-danger'); 
                                    ?>">
                                        <?php echo $hasil_formatted; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($cek['tanggal_cek'])); ?></td>
                                <td>
                                    <a href="?hapus=<?php echo $cek['id']; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Yakin ingin menghapus data ini?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>">Previous</a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>">Next</a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>

                    <?php else: ?>
                    <div class="text-center p-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Tidak ada data ditemukan</h5>
                        <p class="text-muted">Belum ada pemeriksaan stunting yang tercatat</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>