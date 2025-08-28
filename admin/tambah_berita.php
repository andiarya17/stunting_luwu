<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Cek dan include file yang diperlukan
$config_path = '../config/database.php';
$functions_path = '../functions.php';

// Alternatif path jika file tidak ditemukan
if (!file_exists($config_path)) {
    $config_path = dirname(__DIR__) . '/config/database.php';
}
if (!file_exists($functions_path)) {
    $functions_path = dirname(__DIR__) . '/functions.php';
}

// Include database config
if (file_exists($config_path)) {
    include $config_path;
} else {
    die('Error: File database.php tidak ditemukan!');
}

// Include functions
if (file_exists($functions_path)) {
    include $functions_path;
} else {
    die('Error: File functions.php tidak ditemukan!');
}

$success = '';
$error = '';

if (isset($_POST['tambah'])) {
    $judul = cleanInput($_POST['judul']);
    $deskripsi = cleanInput($_POST['deskripsi']);
    $status = cleanInput($_POST['status']);
    $gambar = '';

    if (!empty($judul) && !empty($deskripsi)) {
        // Handle upload gambar
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
                $stmt = $pdo->prepare("INSERT INTO berita_terkini (judul, deskripsi, gambar, status) VALUES (?, ?, ?, ?)");
                $stmt->execute([$judul, $deskripsi, $gambar, $status]);
                
                // Log aktivitas
                logActivity($pdo, "Berita baru ditambahkan: " . $judul, $_SESSION['admin_id']);
                
                $success = "Berita berhasil ditambahkan!";
                
                // Reset form
                $_POST = array();
            } catch(PDOException $e) {
                $error = "Gagal menambahkan berita!";
            }
        }
    } else {
        $error = "Judul dan deskripsi harus diisi!";
    }
}

$page_title = "Tambah Berita - Admin";
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
        
        .upload-area.dragover {
            border-color: #ffd700;
            background: #fff9e6;
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
                        <i class="fas fa-info-circle me-2"></i>Kelola Informasi
                    </a>
                    <a class="nav-link active" href="kelola_berita.php">
                        <i class="fas fa-newspaper me-2"></i>Kelola Berita
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
                    <h2 class="fw-bold" style="color: var(--luwu-green);">Tambah Berita Baru</h2>
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
                                   value="<?php echo isset($_POST['judul']) ? htmlspecialchars($_POST['judul']) : ''; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">
                                <i class="fas fa-align-left me-2"></i>Deskripsi
                            </label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="6" 
                                      placeholder="Masukkan deskripsi berita..." required><?php echo isset($_POST['deskripsi']) ? htmlspecialchars($_POST['deskripsi']) : ''; ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">
                                <i class="fas fa-toggle-on me-2"></i>Status
                            </label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="aktif" <?php echo (isset($_POST['status']) && $_POST['status'] == 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                                <option value="nonaktif" <?php echo (isset($_POST['status']) && $_POST['status'] == 'nonaktif') ? 'selected' : ''; ?>>Non-aktif</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="gambar" class="form-label">
                                <i class="fas fa-image me-2"></i>Upload Gambar
                            </label>
                            
                            <div class="upload-area" id="uploadArea" onclick="document.getElementById('gambar').click()">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Klik atau drag & drop gambar di sini</h5>
                                <p class="text-muted mb-0">Format: JPG, PNG, GIF (Maksimal 5MB)</p>
                            </div>
                            
                            <input type="file" class="form-control d-none" id="gambar" name="gambar" 
                                   accept="image/*" onchange="previewImage(this)">
                            
                            <div id="imagePreview" class="mt-3" style="display: none;">
                                <div class="preview-container">
                                    <img id="preview" class="image-preview" src="" alt="Preview">
                                    <button type="button" class="remove-image" onclick="removeImage()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <small class="text-muted">Gambar bersifat opsional. Kosongkan jika tidak ada gambar.</small>
                        </div>

                        <div class="text-center">
                            <button type="submit" name="tambah" class="btn btn-primary btn-lg me-3">
                                <i class="fas fa-plus me-2"></i>Tambah Berita
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
        // Preview gambar
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview').src = e.target.result;
                    document.getElementById('imagePreview').style.display = 'block';
                    document.getElementById('uploadArea').style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Hapus preview gambar
        function removeImage() {
            document.getElementById('gambar').value = '';
            document.getElementById('imagePreview').style.display = 'none';
            document.getElementById('uploadArea').style.display = 'block';
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

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            uploadArea.classList.add('dragover');
        }

        function unhighlight(e) {
            uploadArea.classList.remove('dragover');
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