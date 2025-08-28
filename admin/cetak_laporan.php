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

function formatTanggalIndonesia($tanggal = null) {
    if ($tanggal === null) {
        $tanggal = date('Y-m-d');
    }
    
    $timestamp = strtotime($tanggal);
    $hari = date('d', $timestamp);
    $bulan = date('n', $timestamp);
    $tahun = date('Y', $timestamp);
    
    return $hari . ' ' . getNamaBulan($bulan, 'lengkap') . ' ' . $tahun;
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
    $stunting_sedang = $stmt->fetch()['total'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM cek_stunting $where_clause AND (hasil_cek = 'Stunting Berat' OR hasil_cek = 'Severely Stunting')");
    $stmt->execute($params);
    $stunting_berat = $stmt->fetch()['total'];
    
    // Ambil data detail berdasarkan kategori
    // Data Normal
    $stmt = $pdo->prepare("SELECT * FROM cek_stunting $where_clause AND hasil_cek = 'Normal' ORDER BY tanggal_cek DESC");
    $stmt->execute($params);
    $data_normal = $stmt->fetchAll();
    
    // Data Stunting Sedang
    $stmt = $pdo->prepare("SELECT * FROM cek_stunting $where_clause AND (hasil_cek = 'Stunting Sedang' OR hasil_cek = 'Stunting') ORDER BY tanggal_cek DESC");
    $stmt->execute($params);
    $data_stunting = $stmt->fetchAll();
    
    // Data Stunting Berat
    $stmt = $pdo->prepare("SELECT * FROM cek_stunting $where_clause AND (hasil_cek = 'Stunting Berat' OR hasil_cek = 'Severely Stunting') ORDER BY tanggal_cek DESC");
    $stmt->execute($params);
    $data_stunting_berat = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $total_cek = $normal = $stunting_sedang = $stunting_berat = 0;
    $data_normal = $data_stunting = $data_stunting_berat = [];
}

// Tentukan periode laporan
$periode_laporan = "Seluruh Periode";
if ($filter_bulan && $filter_tahun) {
    $periode_laporan = getNamaBulan($filter_bulan) . ' ' . $filter_tahun;
} elseif ($filter_tahun) {
    $periode_laporan = "Tahun " . $filter_tahun;
} elseif ($filter_bulan) {
    $periode_laporan = "Bulan " . getNamaBulan($filter_bulan);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Stunting</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #000;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
            position: relative;
        }
        
        .header img {
            float: left;
            margin-right: 20px;
            margin-top: 5px;
        }
        
        .header-text {
            text-align: center;
            padding-left: 100px;
        }
        
        .header-text h1 {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
            letter-spacing: 1px;
        }
        
        .header-text h2 {
            font-size: 16px;
            font-weight: bold;
            margin: 2px 0;
            letter-spacing: 0.5px;
        }
        
        .header-text h3 {
            font-size: 24px;
            font-weight: bold;
            margin: 5px 0;
            letter-spacing: 8px;
        }
        
        .header-text p {
            font-size: 10px;
            margin: 2px 0;
            line-height: 1.2;
        }
        
        .header-text .email {
            font-size: 10px;
            margin-top: 3px;
        }
        
        .clear {
            clear: both;
        }
        
        .title {
            text-align: center;
            margin: 20px 0;
        }
        
        .stats {
            margin: 20px 0;
        }
        
        .stats table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 10px;
        }
        
        .stats th, .stats td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }
        
        .stats th {
            background: #f0f0f0;
            font-weight: bold;
        }
        
        .detail-table {
            margin: 20px 0;
            page-break-inside: avoid;
        }
        
        .detail-table h4 {
            margin-bottom: 10px;
            color: #000;
        }
        
        .detail-table table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        
        .detail-table th, .detail-table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
        }
        
        .detail-table th {
            background: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .detail-table td:nth-child(1), 
        .detail-table td:nth-child(2),
        .detail-table td:nth-child(4),
        .detail-table td:nth-child(6),
        .detail-table td:nth-child(7) {
            text-align: center;
        }
        
        .signature {
            margin-top: 40px;
            text-align: right;
        }
        
        .signature-box {
            display: inline-block;
            text-align: left;
            width: 300px;
            line-height: 1.4;
        }
        
        .btn-print {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        
        @media print {
            .btn-print { display: none; }
            .detail-table { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <button class="btn-print" onclick="window.print()">Cetak</button>

    <!-- Header -->
    <div class="header">
        <img src="https://pfst.cf2.poecdn.net/base/image/cb8d0f034a34f23a9824a116f1403faf0d13fbd49a4b0da7a1f539d61a6d3cbd?w=486&h=600" alt="Logo" width="80" height="80">
        <div class="header-text">
            <h1>PEMERINTAH KABUPATEN LUWU</h1>
            <h2>DINAS PEMBERDAYAAN MASYARAKAT DAN DESA</h2>
            <h3>DPMD</h3>
            <p>Jl. A. Djemma No. 1 (Kompleks Perkantoran Pemkab. Luwu) Telp/Faks. (0471) 3314526 Kode Pos 91994 Belopa</p>
            <p class="email">Email: dpmdluwukab@gmail.com</p>
        </div>
        <div class="clear"></div>
    </div>

    <!-- Judul -->
    <div class="title">
        <h3><u>LAPORAN HASIL PEMERIKSAAN STUNTING</u></h3>
        <p>Periode: <?php echo $periode_laporan; ?></p>
    </div>

    <!-- Statistik -->
    <div class="stats">
        <h4>Hasil Pemeriksaan:</h4>
        <table>
            <tr>
                <th>Keterangan</th>
                <th>Jumlah</th>
                <th>Persentase</th>
            </tr>
            <tr>
                <td>Total Pemeriksaan</td>
                <td><?php echo $total_cek; ?></td>
                <td>100%</td>
            </tr>
            <tr>
                <td>Gizi Normal</td>
                <td><?php echo $normal; ?></td>
                <td><?php echo $total_cek > 0 ? round(($normal / $total_cek) * 100, 1) : 0; ?>%</td>
            </tr>
            <tr>
                <td>Stunting</td>
                <td><?php echo $stunting_sedang; ?></td>
                <td><?php echo $total_cek > 0 ? round(($stunting_sedang / $total_cek) * 100, 1) : 0; ?>%</td>
            </tr>
            <tr>
                <td>Stunting Berat</td>
                <td><?php echo $stunting_berat; ?></td>
                <td><?php echo $total_cek > 0 ? round(($stunting_berat / $total_cek) * 100, 1) : 0; ?>%</td>
            </tr>
            <tr style="font-weight: bold; background: #f5f5f5;">
                <td>Total Stunting</td>
                <td><?php echo $stunting_sedang + $stunting_berat; ?></td>
                <td><?php echo $total_cek > 0 ? round((($stunting_sedang + $stunting_berat) / $total_cek) * 100, 1) : 0; ?>%</td>
            </tr>
        </table>
    </div>

    <!-- Detail Data Normal -->
    <?php if (!empty($data_normal)): ?>
    <div class="detail-table">
        <h4>Detail Anak dengan Gizi Normal (<?php echo count($data_normal); ?> anak)</h4>
        <table>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama Anak</th>
                <th>JK</th>
                <th>Usia</th>
                <th>BB (kg)</th>
                <th>TB (cm)</th>
                <th>Alamat</th>
            </tr>
            <?php $no = 1; foreach($data_normal as $data): ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo date('d/m/Y', strtotime($data['tanggal_cek'])); ?></td>
                <td><?php echo htmlspecialchars($data['nama_anak']); ?></td>
                <td><?php echo $data['jenis_kelamin'] == 'Laki-laki' ? 'L' : 'P'; ?></td>
                <td><?php echo formatUsia($data['usia']); ?></td>
                <td><?php echo $data['berat_badan']; ?></td>
                <td><?php echo $data['tinggi_badan']; ?></td>
                <td><?php echo htmlspecialchars($data['alamat']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>

    <!-- Detail Data Stunting -->
    <?php if (!empty($data_stunting)): ?>
    <div class="detail-table">
        <h4>Detail Anak dengan Status Stunting (<?php echo count($data_stunting); ?> anak)</h4>
        <table>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama Anak</th>
                <th>JK</th>
                <th>Usia</th>
                <th>BB (kg)</th>
                <th>TB (cm)</th>
                <th>Alamat</th>
            </tr>
            <?php $no = 1; foreach($data_stunting as $data): ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo date('d/m/Y', strtotime($data['tanggal_cek'])); ?></td>
                <td><?php echo htmlspecialchars($data['nama_anak']); ?></td>
                <td><?php echo $data['jenis_kelamin'] == 'Laki-laki' ? 'L' : 'P'; ?></td>
                <td><?php echo formatUsia($data['usia']); ?></td>
                <td><?php echo $data['berat_badan']; ?></td>
                <td><?php echo $data['tinggi_badan']; ?></td>
                <td><?php echo htmlspecialchars($data['alamat']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>

    <!-- Detail Data Stunting Berat -->
    <?php if (!empty($data_stunting_berat)): ?>
    <div class="detail-table">
        <h4>Detail Anak dengan Status Stunting Berat (<?php echo count($data_stunting_berat); ?> anak)</h4>
        <table>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama Anak</th>
                <th>JK</th>
                <th>Usia</th>
                <th>BB (kg)</th>
                <th>TB (cm)</th>
                <th>Alamat</th>
            </tr>
            <?php $no = 1; foreach($data_stunting_berat as $data): ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo date('d/m/Y', strtotime($data['tanggal_cek'])); ?></td>
                <td><?php echo htmlspecialchars($data['nama_anak']); ?></td>
                <td><?php echo $data['jenis_kelamin'] == 'Laki-laki' ? 'L' : 'P'; ?></td>
                <td><?php echo formatUsia($data['usia']); ?></td>
                <td><?php echo $data['berat_badan']; ?></td>
                <td><?php echo $data['tinggi_badan']; ?></td>
                <td><?php echo htmlspecialchars($data['alamat']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>

    <!-- Kesimpulan -->
    <div style="margin: 20px 0;">
        <h4>Kesimpulan:</h4>
        <p>
            Berdasarkan hasil pemeriksaan pada periode <?php echo strtolower($periode_laporan); ?>, 
            dari total <?php echo $total_cek; ?> anak yang diperiksa, terdapat 
            <?php echo $stunting_sedang + $stunting_berat; ?> anak 
            (<?php echo $total_cek > 0 ? round((($stunting_sedang + $stunting_berat) / $total_cek) * 100, 1) : 0; ?>%) 
            yang mengalami stunting.
        </p>
    </div>

    <!-- Tanda Tangan -->
    <div class="signature">
        <div class="signature-box">
            <p style="margin-bottom: 15px;">Belopa, <?php echo formatTanggalIndonesia(); ?></p>
            <p style="margin-bottom: 5px;">Mengetahui,</p>
            <p style="margin-bottom: 3px; font-weight: bold;">Kepala Bidang</p>
            <p style="margin-top: 0; margin-bottom: 15px; font-weight: bold;">Kelembagaan dan Sosial Budaya Masyarakat</p>
            <div style="height: 60px;"></div>
            <p style="margin-bottom: 2px; font-weight: bold; text-decoration: underline;">Indah Kumalasari, SE., MM</p>
            <p style="margin-top: 0; font-size: 11px;">NIP. 1234567890</p>
        </div>
    </div>

</body>
</html>