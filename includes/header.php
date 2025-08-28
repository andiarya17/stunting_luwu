<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Sistem Informasi Pencegahan Stunting - Kabupaten Luwu'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo isset($css_path) ? $css_path : 'assets/css/style.css'; ?>" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo isset($home_path) ? $home_path : 'index.php'; ?>">
                <img src="https://pfst.cf2.poecdn.net/base/image/cb8d0f034a34f23a9824a116f1403faf0d13fbd49a4b0da7a1f539d61a6d3cbd?w=486&h=600" alt="Logo Kabupaten Luwu" class="me-2">
                <span>KABUPATEN LUWU</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" style="border: 1px solid white;">
                <i class="fas fa-bars text-white"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (!isset($_SESSION['admin_logged_in'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo isset($home_path) ? $home_path : ''; ?>index.php">
                            <i class="fas fa-home me-1"></i>Beranda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo isset($user_path) ? $user_path : 'user/'; ?>informasi_stunting.php">
                            <i class="fas fa-info-circle me-1"></i>Informasi Stunting
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo isset($user_path) ? $user_path : 'user/'; ?>cek_stunting.php">
                            <i class="fas fa-search me-1"></i>Cek Stunting
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo isset($user_path) ? $user_path : 'user/'; ?>berita.php">
                            <i class="fas fa-newspaper me-1"></i>Berita
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo isset($admin_path) ? $admin_path : 'admin/'; ?>login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo isset($admin_path) ? $admin_path : ''; ?>dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo isset($admin_path) ? $admin_path : ''; ?>logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>