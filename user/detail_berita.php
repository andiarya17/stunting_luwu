<?php
include '../config/database.php';

// Ambil ID berita
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id == 0) {
    echo "<script>alert('Berita tidak ditemukan!'); window.location.href='../index.php';</script>";
    exit;
}

// Ambil data berita
try {
    $stmt = $pdo->prepare("SELECT * FROM berita_terkini WHERE id = ?");
    $stmt->execute([$id]);
    $berita = $stmt->fetch();
    
    if (!$berita) {
        echo "<script>alert('Berita tidak ditemukan!'); window.location.href='../index.php';</script>";
        exit;
    }
} catch(PDOException $e) {
    echo "<script>alert('Terjadi kesalahan!'); window.location.href='../index.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($berita['judul']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .news-image {
            height: 400px; /* Adjust height as needed */
            overflow: hidden;
            background: #f8f9fa; /* Background color for loading */
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .news-img {
            width: 100%;
            height: 100%; /* Fill the height of the container */
            object-fit: cover; /* Maintain aspect ratio */
            object-position: center; /* Center the image */
            transition: transform 0.3s ease; /* Optional: add a transition effect */
        }

        .news-image:hover .news-img {
            transform: scale(1.05); /* Optional: zoom effect on hover */
        }
    </style>
</head>
<body class="bg-light">

<div class="container my-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            
            <!-- Tombol Kembali -->
            <div class="mb-3">
                <a href="../user/berita.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>
            
            <!-- Konten Berita -->
            <div class="card">
                <?php if (!empty($berita['gambar']) && file_exists('../assets/images/berita/' . $berita['gambar'])): ?>
                <div class="news-image">
                    <img src="../assets/images/berita/<?php echo $berita['gambar']; ?>" 
                         class="news-img" 
                         alt="<?php echo htmlspecialchars($berita['judul']); ?>">
                </div>
                <?php endif; ?>
                
                <div class="card-body">
                    <h1 class="h3 mb-3"><?php echo htmlspecialchars($berita['judul']); ?></h1>
                    
                    <p class="text-muted mb-4">
                        <i class="fas fa-calendar me-2"></i>
                        <?php echo date('d F Y', strtotime($berita['tanggal_buat'])); ?>
                    </p>
                    
                    <div style="line-height: 1.6;">
                        <?php echo nl2br(htmlspecialchars($berita['deskripsi'])); ?>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>