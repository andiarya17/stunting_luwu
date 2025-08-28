<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

include '../config/database.php';

$page_title = "Dashboard Admin - Sistem Informasi Pencegahan Stunting";
$css_path = "../assets/css/style.css";
$admin_path = "";
$user_path = "../user/";
$home_path = "../";

// Ambil statistik
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM cek_stunting");
    $total_cek = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM berita_terkini");
    $total_berita = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM cek_stunting WHERE DATE(tanggal_cek) = CURDATE()");
    $cek_hari_ini = $stmt->fetch()['total'];
    
    // Statistik hasil cek stunting
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM cek_stunting WHERE hasil_cek = 'Normal'");
    $normal = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM cek_stunting WHERE hasil_cek IN ('Stunting', 'Stunting Sedang')");
    $stunting_sedang = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM cek_stunting WHERE hasil_cek IN ('Severely Stunting', 'Stunting Berat')");
    $stunting_berat = $stmt->fetch()['total'];
    
    // Ambil data cek stunting terbaru
    $stmt = $pdo->query("SELECT * FROM cek_stunting ORDER BY tanggal_cek DESC LIMIT 5");
    $cek_terbaru = $stmt->fetchAll();
    
    // Ambil berita terbaru
    $stmt = $pdo->query("SELECT * FROM berita_terkini ORDER BY tanggal_publikasi DESC LIMIT 3");
    $berita_terbaru = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $total_cek = $total_berita = $cek_hari_ini = $normal = $stunting_sedang = $stunting_berat = 0;
    $cek_terbaru = $berita_terbaru = [];
}

// Function untuk format tanggal Indonesia
function tanggal_indonesia($tanggal) {
    $hari_indonesia = array(
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
    );
    $bulan_indonesia = array(
        'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
        'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
        'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
        'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
    );
    
    $tanggal_english = date('l, d F Y', strtotime($tanggal));
    $tanggal_indo = str_replace(array_keys($hari_indonesia), array_values($hari_indonesia), $tanggal_english);
    $tanggal_indo = str_replace(array_keys($bulan_indonesia), array_values($bulan_indonesia), $tanggal_indo);
    
    return $tanggal_indo;
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
    <link href="<?php echo $css_path; ?>" rel="stylesheet">
    <style>
        .welcome-section {
            background: linear-gradient(135deg, #2d8f47 0%, #2d8f47 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            text-decoration: none;
            color: inherit;
        }
        .stats-card i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        .stats-card.primary i { color: #007bff; }
        .stats-card.success i { color: #28a745; }
        .stats-card.warning i { color: #ffc107; }
        .stats-card.danger i { color: #dc3545; }
        .stats-card.info i { color: #17a2b8; }
        
        .quick-action-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            border-left: 4px solid #007bff;
            height: 160px; /* Tinggi tetap untuk semua card */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .quick-action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            text-decoration: none;
            color: inherit;
        }
        .quick-action-card h6 {
            margin: 0.5rem 0;
        }
        .quick-action-card p {
            margin: 0;
            flex-grow: 1;
            display: flex;
            align-items: center;
        }
        .recent-activity {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
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
                    <a class="nav-link active" href="dashboard.php">
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
                <!-- Welcome Section -->
                <div class="welcome-section">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="fw-bold mb-2">
                                <i class="fas fa-user-shield me-2"></i>
                                Selamat Datang di Dashboard Admin
                            </h2>
                            <h4 class="mb-3">Sistem Informasi Pencegahan Stunting</h4>
                            <p class="mb-0">
                                <i class="fas fa-map-marker-alt me-2"></i>Kabupaten Luwu, Sulawesi Selatan
                            </p>
                            <p class="mb-0">
                                <i class="fas fa-user me-2"></i>Login sebagai: <strong><?php echo $_SESSION['admin_username']; ?></strong>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="text-white-50">
                                <i class="fas fa-calendar-alt me-1"></i>
                                <?php echo tanggal_indonesia(date('Y-m-d')); ?>
                            </div>
                            <div class="text-white-50">
                                <i class="fas fa-clock me-1"></i>
                                <span id="current-time"><?php echo date('H:i:s'); ?> WIB</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h5 class="fw-bold mb-3">
                            <i class="fas fa-bolt me-2" style="color: var(--luwu-green);"></i>
                            Aksi Cepat
                        </h5>
                    </div>
                    <div class="col-md-3">
                        <a href="kelola_informasi.php" class="quick-action-card d-block">
                            <i class="fas fa-info-circle text-primary mb-2" style="font-size: 2rem;"></i>
                            <h6>informasi stunting</h6>
                            <p class="small text-muted">Tambah & edit informasi stunting</p>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="kelola_berita.php" class="quick-action-card d-block" style="border-left-color: #28a745;">
                            <i class="fas fa-newspaper text-success mb-2" style="font-size: 2rem;"></i>
                            <h6>berita terkini</h6>
                            <p class="small text-muted">Tambah & edit berita terkini</p>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="riwayat_cek.php" class="quick-action-card d-block" style="border-left-color: #ffc107;">
                            <i class="fas fa-history text-warning mb-2" style="font-size: 2rem;"></i>
                            <h6>Riwayat Cek</h6>
                            <p class="small text-muted">Lihat semua data pemeriksaan</p>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="laporan.php" class="quick-action-card d-block" style="border-left-color: #dc3545;">
                            <i class="fas fa-chart-bar text-danger mb-2" style="font-size: 2rem;"></i>
                            <h6>Laporan</h6>
                            <p class="small text-muted">Lihat laporan & statistik</p>
                        </a>
                    </div>
                </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update jam real-time
        function updateTime() {
            const now = new Date();
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const seconds = now.getSeconds().toString().padStart(2, '0');
            const timeString = `${hours}:${minutes}:${seconds} WIB`;
            document.getElementById('current-time').textContent = timeString;
        }
        
        // Update setiap detik
        setInterval(updateTime, 1000);
    </script>
</body>
</html>