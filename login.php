<?php
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validasi input
    if (empty($email) || empty($password)) {
        flash('login_error', 'Email dan password harus diisi', 'alert alert-danger');
    } else {
        // Cek user di database
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verifikasi password
            if (password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nama'] = $user['nama'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Redirect ke dashboard sesuai role
                if ($user['role'] == 'admin') {
                    redirect('admin/index.php');
                } else {
                    redirect('user/index.php');
                }
            } else {
                flash('login_error', 'Password salah', 'alert alert-danger');
            }
        } else {
            flash('login_error', 'Email tidak ditemukan', 'alert alert-danger');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Peminjaman Ruang Rapat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #eef2ff;
            --secondary: #64748b;
            --success: #10b981;
            --dark: #1e293b;
            --light: #f8fafc;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
        }
        
        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .login-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
        }
        
        .logo-section {
            background: linear-gradient(135deg, var(--primary) 0%, #3a0ca3 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }
        
        .logo-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        .logo-icon i {
            font-size: 2.5rem;
        }
        
        .app-name {
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }
        
        .app-desc {
            font-size: 0.95rem;
            opacity: 0.9;
            font-weight: 400;
        }
        
        .form-section {
            padding: 40px 30px;
        }
        
        .form-title {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 30px;
            font-size: 1.5rem;
            text-align: center;
        }
        
        .form-control {
            border-radius: 12px;
            padding: 14px 20px;
            border: 2px solid #e2e8f0;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--light);
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
            background: white;
        }
        
        .input-group-text {
            background: var(--light);
            border: 2px solid #e2e8f0;
            border-right: none;
            border-radius: 12px 0 0 12px;
            padding: 0 20px;
        }
        
        .input-group .form-control {
            border-left: none;
            border-radius: 0 12px 12px 0;
        }
        
        .input-group:focus-within .input-group-text {
            border-color: var(--primary);
            background: white;
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary) 0%, #3a0ca3 100%);
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(67, 97, 238, 0.3);
        }
        
        .flash-message {
            border-radius: 12px;
            margin-bottom: 20px;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .register-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        
        .register-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .register-link a:hover {
            color: #3a0ca3;
            text-decoration: underline;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
        
        /* Responsive design */
        @media (max-width: 576px) {
            .login-container {
                max-width: 100%;
            }
            
            .logo-section {
                padding: 30px 20px;
            }
            
            .form-section {
                padding: 30px 20px;
            }
            
            .logo-icon {
                width: 70px;
                height: 70px;
            }
            
            .logo-icon i {
                font-size: 2rem;
            }
            
            .app-name {
                font-size: 1.5rem;
            }
        }
        
        /* Animation for focus states */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-card {
            animation: fadeIn 0.5s ease-out;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-section">
                <div class="logo-icon">
                    <i class="fas fa-chalkboard"></i>
                </div>
                <h1 class="app-name">MeetingRoom</h1>
                <p class="app-desc">Sistem Peminjaman Ruang Rapat</p>
            </div>
            
            <div class="form-section">
                <h2 class="form-title">Masuk ke Akun Anda</h2>
                
                <?php flash('login_error'); ?>
                <?php flash('register_success'); ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="email@contoh.com" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                        </div>
                    </div>
                    
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-login">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </div>
                </form>
                
                <div class="register-link">
                    <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>