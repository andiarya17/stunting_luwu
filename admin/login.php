<?php
session_start();
include '../config/database.php';

// Redirect jika sudah login
if (isset($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM login WHERE username = ? AND password = ?");
            $stmt->execute([$username, $password]);
            $user = $stmt->fetch();
            
            if ($user) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = "Username atau password salah!";
            }
        } catch(PDOException $e) {
            $error = "Terjadi kesalahan sistem!";
        }
    } else {
        $error = "Harap isi semua field!";
    }
}

$page_title = "Login Admin - Sistem Informasi Pencegahan Stunting";
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
        .login-split-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            padding: 20px;
        }
        
        .login-split-card {
            width: 100%;
            max-width: 900px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            overflow: hidden;
            min-height: 500px;
            margin: 0 auto;
        }
        
        .login-left {
            padding: 40px;
            border-right: 2px solid #e9ecef;
        }
        
        .login-right {
            padding: 40px;
            background-color:rgb(255, 255, 255);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        
        .login-title {
            color: var(--luwu-green, #2E8B57);
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .system-info {
            color:rgb(46, 139, 87);
            font-size: 1.1rem;
            line-height: 1.6;
            margin-top: 20px;
        }
        
        @media (max-width: 768px) {
            .login-split-card {
                margin: 20px auto;
                min-height: auto;
            }
            
            .login-left {
                border-right: none;
                border-bottom: 2px solid #e9ecef;
                padding: 30px 20px;
            }
            
            .login-right {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-split-container">
        <div class="login-split-card">
            <div class="row g-0 h-100">
                <!-- Left Panel - Login Form -->
                <div class="col-md-6">
                    <div class="login-left">
                        <h2 class="login-title">Login</h2>
                        
                        <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       placeholder="Masukkan Username" required>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Masukkan Password" required>
                            </div>
                            <button type="submit" name="login" class="btn btn-primary w-100 mb-3" 
                                    style="background-color: var(--luwu-green, #2E8B57); border-color: var(--luwu-green, #2E8B57);">
                                Login
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <a href="../index.php" class="text-primary text-decoration-none">
                                Kembali ke Beranda
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Right Panel - Logo & System Info -->
                <div class="col-md-6">
                    <div class="login-right">
                        <img src="https://pfst.cf2.poecdn.net/base/image/cb8d0f034a34f23a9824a116f1403faf0d13fbd49a4b0da7a1f539d61a6d3cbd?w=486&h=600" 
                             alt="Logo Kabupaten Luwu" 
                             width="120" height="120" 
                             class="mb-4">
                        
                        <div class="system-info">
                            <strong>Sistem Informasi<br>
                            Pencegahan Stunting Pada Anak<br>
                            Kabupaten Luwu</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>