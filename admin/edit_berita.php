<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

include '../config/database.php';
include '../functions.php';

$success = '';
$error = '';

// Ambil ID berita
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: kelola_berita.php');
    exit;
}

// Ambil data berita
try {
    $stmt = $pdo->prepare("SELECT * FROM berita_terkini WHERE id = ?");
    $stmt->execute([$id]);
    $berita = $stmt->fetch();
    
    if (!$berita) {
        header('Location: kelola_berita.php');
        exit;
    }
} catch(PDOException $e) {
    header('Location: kelola_berita.php');
    exit;
}

if (isset($_POST['update'])) {
    $judul = cleanInput($_POST['judul']);
    $deskripsi = cleanInput($_POST['deskripsi']);
    $status = cleanInput($_POST['status']);
    $gambar = $berita['gambar']; // Keep existing image by default
    
    if (!empty($judul) && !empty($deskripsi)) {
        // Handle upload gambar baru
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $upload_dir = '../assets/images/berita/';
            
            // Buat folder jika belum ada
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_extension = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            $max_size = 5 * 1024 * 1024; // 5MB

            // Validasi file
            $upload_errors = validateFileUpload($_FILES['gambar'], $allowed_types, $max_size);
            
            if (empty($upload_errors)) {
                // Generate nama file unik
                $filename = 'berita_' . time() . '_' . generateRandomString(5) . '.' . $file_extension;
                $upload_path = $upload_dir . $filename;

                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_path)) {
                    // Buat thumbnail
                    $thumbnail_path = $upload_dir . 'thumb_' . $filename;
                    createThumbnail($upload_path, $thumbnail_path, 400, 300);
                    
                    // Hapus gambar lama
                    if (!empty($berita['gambar']) && file_exists($upload_dir . $berita['gambar'])) {
                        unlink($upload_dir . $berita['gambar']);
                        // Hapus thumbnail lama juga
                        if (file_exists($upload_dir . 'thumb_' . $berita['gambar'])) {
                            unlink($upload_dir . 'thumb_' . $berita['gambar']);
                        }
                    }
                    
                    $gambar = $filename;
                } else {
                    $error = "Gagal mengupload gambar!";
                }
            } else {
                $error = implode('<br>', $upload_errors);
            }
        }

        if (empty($error)) {
            try {
                $stmt = $pdo->prepare("UPDATE berita_terkini SET judul = ?, deskripsi = ?, gambar = ?, status = ? WHERE id = ?");
                $stmt->execute([$judul, $deskripsi, $gambar, $status, $id]);
                
                // Log aktivitas
                logActivity($pdo, "Berita diperbarui: " . $judul, $_SESSION['admin_id']);
                
                $success = "Berita berhasil diperbarui!";
                
                // Refresh data
                $berita['judul'] = $judul;
                $berita['deskripsi'] = $deskripsi;
                $berita['gambar'] = $gambar;
                $berita['status'] = $status;
            } catch(PDOException $e) {
                $error = "Gagal memperbarui berita!";
            }
        }
    } else {
        $error = "Judul dan deskripsi harus diisi!";
    }
}

$page_title = "Edit Berita - Admin";
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
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 40px 20px;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .upload-area:hover {
            border-color: var(--luwu-green);
            background: #e8f5e8;
        }
        
        .current-image {
            max-width: 100%;
            max-height: 200px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        }
        
        .image-preview {
            max-width: 100%;
            max-height: 300px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        }
        
        .preview-container {
            position: relative;
            display: inline-block;
        }
        
        .remove-image {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .delete-current {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 5px 10px;
            margin-top: 10px;
            cursor: pointer;
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
                    <h2 class="fw-bold" style="color: var(--luwu-green);">Edit Berita</h2>
                    <a href="kelola_berita.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
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

                <div class="form-section">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="judul" class="form-label">
                                <i class="fas fa-heading me-2"></i>Judul Berita
                            </label>
                            <input type="text" class="form-control" id="judul" name="judul" 
                                   placeholder="Masukkan judul berita..." 
                                   value="<?php echo htmlspecialchars($berita['judul']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">
                                <i class="fas fa-align-left me-2"></i>Deskripsi
                            </label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="6" 
                                      placeholder="Masukkan deskripsi berita..." required><?php echo htmlspecialchars($berita['deskripsi']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">
                                <i class="fas fa-toggle-on me-2"></i>Status
                            </label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="aktif" <?php echo ($berita['status'] == 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                                <option value="nonaktif" <?php echo ($berita['status'] == 'nonaktif') ? 'selected' : ''; ?>>Non-aktif</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="gambar" class="form-label">
                                <i class="fas fa-image me-2"></i>Gambar Berita
                            </label>
                            
                            <?php if (!empty($berita['gambar'])): ?>
                            <div class="mb-3" id="currentImage">
                                <p class="text-muted mb-2">Gambar saat ini:</p>
                                <div class="preview-container">
                                    <img src="../assets/images/berita/<?php echo htmlspecialchars($berita['gambar']); ?>" 
                                         class="current-image" alt="Current Image">
                                    <button type="button" class="delete-current" onclick="deleteCurrentImage()">
                                        <i class="fas fa-trash"></i> Hapus Gambar
                                    </button>
                                </div>
                                <input type="hidden" id="deleteImage" name="delete_image" value="0">
                            </div>
                            <?php endif; ?>
                            
                            <div class="upload-area" id="uploadArea" onclick="document.getElementById('gambar').click()">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Klik untuk upload gambar baru</h5>
                                <p class="text-muted mb-0">Format: JPG, PNG, GIF (Maksimal 5MB)</p>
                            </div>
                            
                            <input type="file" class="form-control d-none" id="gambar" name="gambar" 
                                   accept="image/*" onchange="previewImage(this)">
                            
                            <div id="imagePreview" class="mt-3" style="display: none;">
                                <p class="text-muted mb-2">Preview gambar baru:</p>
                                <div class="preview-container">
                                    <img id="preview" class="image-preview" src="" alt="Preview">
                                    <button type="button" class="remove-image" onclick="removeImage()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <small class="text-muted">Upload gambar baru jika ingin mengganti yang lama.</small>
                        </div>

                        <div class="text-center">
                            <button type="submit" name="update" class="btn btn-primary btn-lg me-3">
                                <i class="fas fa-save me-2"></i>Simpan Perubahan
                            </button>
                            <a href="kelola_berita.php" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview gambar baru
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview').src = e.target.result;
                    document.getElementById('imagePreview').style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Hapus preview gambar baru
        function removeImage() {
            document.getElementById('gambar').value = '';
            document.getElementById('imagePreview').style.display = 'none';
        }

        // Hapus gambar saat ini
        function deleteCurrentImage() {
            if (confirm('Yakin ingin menghapus gambar saat ini?')) {
                document.getElementById('currentImage').style.display = 'none';
                document.getElementById('deleteImage').value = '1';
            }
        }

        // Drag & drop functionality
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('gambar');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        uploadArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            if (files.length > 0) {
                fileInput.files = files;
                previewImage(fileInput);
            }
        }
    </script>
</body>
</html>