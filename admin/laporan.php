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

// Fungsi untuk format bulan Indonesia
function getNamaBulan($bulan_angka, $format = 'lengkap') {
    $bulan_lengkap = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    
    $bulan_pendek = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
        5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
        9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
    ];
    
    return $format == 'pendek' ? $bulan_pendek[$bulan_angka] : $bulan_lengkap[$bulan_angka];
}

function formatTanggalIndonesia($tanggal, $format = 'lengkap') {
    $timestamp = strtotime($tanggal);
    $hari = date('d', $timestamp);
    $bulan = date('n', $timestamp);
    $tahun = date('Y', $timestamp);
    $jam = date('H:i', $timestamp);
    
    if ($format == 'pendek') {
        return $hari . ' ' . getNamaBulan($bulan, 'pendek') . ' ' . $tahun;
    } elseif ($format == 'lengkap_dengan_jam') {
        return $hari . ' ' . getNamaBulan($bulan, 'lengkap') . ' ' . $tahun . ', ' . $jam;
    } else {
        return $hari . ' ' . getNamaBulan($bulan, 'lengkap') . ' ' . $tahun;
    }
}

// Filter parameters
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : '';
$filter_kelamin = isset($_GET['kelamin']) ? $_GET['kelamin'] : '';

// Build where clause for filters
$where_clause = "WHERE 1=1";
$params = [];

if ($filter_bulan) {
    $where_clause .= " AND MONTH(tanggal_cek) = ?";
    $params[] = $filter_bulan;
}
if ($filter_tahun) {
    $where_clause .= " AND YEAR(tanggal_cek) = ?";
    $params[] = $filter_tahun;
}
if ($filter_kelamin) {
    $where_clause .= " AND jenis_kelamin = ?";
    $params[] = $filter_kelamin;
}

// Ambil statistik dengan filter
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM cek_stunting $where_clause");
    $stmt->execute($params);
    $total_cek = $stmt->fetch()['total'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM cek_stunting $where_clause AND hasil_cek = 'Normal'");
    $stmt->execute($params);
    $normal = $stmt->fetch()['total'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM cek_stunting $where_clause AND (hasil_cek = 'Stunting Sedang' OR hasil_cek = 'Stunting')");
    $stmt->execute($params);
    $stunting = $stmt->fetch()['total'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM cek_stunting $where_clause AND (hasil_cek = 'Stunting Berat' OR hasil_cek = 'Severely Stunting')");
    $stmt->execute($params);
    $stunting_berat = $stmt->fetch()['total'];
    
    // Data per bulan (6 bulan terakhir)
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(tanggal_cek, '%Y-%m') as bulan,
            COUNT(*) as total
        FROM cek_stunting 
        WHERE tanggal_cek >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(tanggal_cek, '%Y-%m')
        ORDER BY bulan ASC
    ");
    $data_bulanan = $stmt->fetchAll();
    
    // Ambil data detail untuk tabel riwayat
    $stmt = $pdo->prepare("
        SELECT * FROM cek_stunting $where_clause 
        ORDER BY tanggal_cek DESC 
        LIMIT 100
    ");
    $stmt->execute($params);
    $data_detail = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $total_cek = $normal = $stunting = $stunting_berat = 0;
    $data_bulanan = $data_detail = [];
}

$page_title = "Laporan - Admin";
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        @media print {
            .admin-sidebar, .btn, .no-print {
                display: none !important;
            }
            .content-wrapper {
                padding: 0 !important;
            }
            .container-fluid {
                margin: 0 !important;
                padding: 0 !important;
            }
            .col-md-9, .col-lg-10 {
                width: 100% !important;
                max-width: 100% !important;
            }
            .table {
                font-size: 11px;
            }
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .summary-card {
            border-radius: 10px;
            margin-bottom: 20px;
            color: white;
        }
        .summary-card i {
            font-size: 30px;
        }
        .table-responsive {
            max-height: 400px;
            overflow-y: auto;
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
                    <a class="nav-link active" href="laporan.php">
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
                    <h2 class="fw-bold" style="color: var(--luwu-green);">Laporan Hasil Cek Stunting</h2>
                    <div class="no-print">
                        <a href="cetak_laporan.php?<?php echo http_build_query($_GET); ?>" target="_blank" class="btn btn-success me-2">
                            <i class="fas fa-print me-2"></i>Cetak Laporan
                        </a>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="card mb-4 no-print">
                    <div class="card-body">
                        <h6><i class="fas fa-filter me-2"></i>Filter Laporan</h6>
                        <form method="GET" action="">
                            <div class="row">
                                <div class="col-md-3">
                                    <select name="bulan" class="form-select form-select-sm">
                                        <option value="">Semua Bulan</option>
                                        <?php for($i = 1; $i <= 12; $i++): ?>
                                            <option value="<?php echo $i; ?>" <?php echo $filter_bulan == $i ? 'selected' : ''; ?>>
                                                <?php echo getNamaBulan($i); ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="tahun" class="form-select form-select-sm">
                                        <option value="">Semua Tahun</option>
                                        <?php for($i = date('Y'); $i >= 2020; $i--): ?>
                                            <option value="<?php echo $i; ?>" <?php echo $filter_tahun == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="kelamin" class="form-select form-select-sm">
                                        <option value="">Semua Jenis Kelamin</option>
                                        <option value="Laki-laki" <?php echo $filter_kelamin == 'Laki-laki' ? 'selected' : ''; ?>>Laki-laki</option>
                                        <option value="Perempuan" <?php echo $filter_kelamin == 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-success btn-sm me-2">
                                        <i class="fas fa-search me-1"></i>Filter
                                    </button>
                                    <a href="laporan.php" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-refresh me-1"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card summary-card bg-primary text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-child mb-2"></i>
                                <h3><?php echo $total_cek; ?></h3>
                                <p>Total Pemeriksaan</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card summary-card bg-success text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-smile mb-2"></i>
                                <h3><?php echo $normal; ?></h3>
                                <p>Normal</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card summary-card bg-warning text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-exclamation-triangle mb-2"></i>
                                <h3><?php echo $stunting; ?></h3>
                                <p>Stunting</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card summary-card bg-danger text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-frown mb-2"></i>
                                <h3><?php echo $stunting_berat; ?></h3>
                                <p>Stunting Berat</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Riwayat Cek Stunting -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-history me-2"></i>Riwayat Cek Stunting (100 Data Terakhir)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Nama Anak</th>
                                        <th>JK</th>
                                        <th>Usia</th>
                                        <th>BB (kg)</th>
                                        <th>TB (cm)</th>
                                        <th>Hasil</th>
                                        <th>Alamat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($data_detail)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">
                                            <i class="fas fa-info-circle me-2"></i>Tidak ada data yang ditemukan
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                        <?php $no = 1; foreach($data_detail as $detail): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($detail['tanggal_cek'])); ?></td>
                                            <td><?php echo htmlspecialchars($detail['nama_anak']); ?></td>
                                            <td><?php echo $detail['jenis_kelamin'] == 'Laki-laki' ? 'L' : 'P'; ?></td>
                                            <td><?php echo formatUsia($detail['usia']); ?></td>
                                            <td><?php echo $detail['berat_badan']; ?></td>
                                            <td><?php echo $detail['tinggi_badan']; ?></td>
                                            <td>
                                                <?php 
                                                $hasil_formatted = formatHasilStunting($detail['hasil_cek']);
                                                $status_color = 'secondary';
                                                if ($hasil_formatted == 'Normal') $status_color = 'success';
                                                elseif ($hasil_formatted == 'Stunting') $status_color = 'warning';
                                                elseif ($hasil_formatted == 'Stunting Berat') $status_color = 'danger';
                                                ?>
                                                <span class="badge bg-<?php echo $status_color; ?>"><?php echo $hasil_formatted; ?></span>
                                            </td>
                                            <td style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                <?php echo htmlspecialchars($detail['alamat']); ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (!empty($data_detail) && count($data_detail) >= 100): ?>
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Menampilkan 100 data terbaru. Untuk melihat semua data, gunakan halaman 
                                <a href="riwayat_cek.php" class="text-decoration-none">Riwayat Cek Stunting</a>
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Percentage Analysis -->
                <?php if ($total_cek > 0): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-percentage me-2"></i>Analisis Persentase</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="fw-bold text-success" style="font-size: 2rem;">
                                    <?php echo round(($normal / $total_cek) * 100, 1); ?>%
                                </div>
                                <p>Status Gizi Normal</p>
                            </div>
                            <div class="col-md-4">
                                <div class="fw-bold text-warning" style="font-size: 2rem;">
                                    <?php echo round(($stunting / $total_cek) * 100, 1); ?>%
                                </div>
                                <p>Stunting</p>
                            </div>
                            <div class="col-md-4">
                                <div class="fw-bold text-danger" style="font-size: 2rem;">
                                    <?php echo round(($stunting_berat / $total_cek) * 100, 1); ?>%
                                </div>
                                <p>Stunting Berat</p>
                            </div>
                        </div>
                        
                        <hr>
                        <div class="text-center">
                            <div class="alert alert-info d-inline-block">
                                <h6><i class="fas fa-info-circle me-2"></i>Total Prevalensi Stunting</h6>
                                <div class="fw-bold" style="font-size: 1.5rem;">
                                    <?php echo round((($stunting + $stunting_berat) / $total_cek) * 100, 1); ?>%
                                </div>
                                <small>Target WHO 2030: < 20%</small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Charts Section - Paling Bawah -->
                <div class="row mb-4">
                    <!-- Pie Chart -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-chart-pie me-2"></i>Distribusi Hasil Cek</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="pieChart" width="400" height="400"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bar Chart -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-chart-bar me-2"></i>Tren 6 Bulan Terakhir</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="barChart" width="400" height="400"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Footer -->
                <div class="card mt-4">
                    <div class="card-body text-center">
                        <h6 class="fw-bold">Laporan Sistem Informasi Pencegahan Stunting</h6>
                        <p>Kabupaten Luwu - Sulawesi Selatan</p>
                        <p><small>Berdasarkan Standar WHO (World Health Organization)</small></p>
                        <small class="text-muted">Digenerate pada: <?php echo formatTanggalIndonesia(date('Y-m-d H:i:s'), 'lengkap_dengan_jam'); ?> WIB</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Pie Chart
        const pieCtx = document.getElementById('pieChart').getContext('2d');
        const pieChart = new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: ['Normal', 'Stunting', 'Stunting Berat'],
                datasets: [{
                    data: [<?php echo $normal; ?>, <?php echo $stunting; ?>, <?php echo $stunting_berat; ?>],
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                                return context.label + ': ' + context.raw + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });

        // Bar Chart
        const barCtx = document.getElementById('barChart').getContext('2d');
        const barChart = new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: [<?php 
                    $labels = [];
                    foreach ($data_bulanan as $data) {
                        $tahun_bulan = explode('-', $data['bulan']);
                        $tahun = $tahun_bulan[0];
                        $bulan = (int)$tahun_bulan[1];
                        $labels[] = "'" . getNamaBulan($bulan, 'pendek') . ' ' . $tahun . "'";
                    }
                    echo implode(',', $labels);
                ?>],
                datasets: [{
                    label: 'Jumlah Pemeriksaan',
                    data: [<?php 
                        $values = [];
                        foreach ($data_bulanan as $data) {
                            $values[] = $data['total'];
                        }
                        echo implode(',', $values);
                    ?>],
                    backgroundColor: '#20c997'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>