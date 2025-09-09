<?php
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi input
    $errors = [];
    
    if (empty($nama)) {
        $errors[] = "Nama harus diisi";
    }
    
    if (empty($email)) {
        $errors[] = "Email harus diisi";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }
    
    if (empty($password)) {
        $errors[] = "Password harus diisi";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Konfirmasi password tidak sesuai";
    }
    
    // Cek email sudah terdaftar
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $errors[] = "Email sudah terdaftar";
    }
    
    // Jika tidak ada error, simpan user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (nama, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $nama, $email, $hashed_password);
        
        if ($stmt->execute()) {
            flash('register_success', 'Pendaftaran berhasil. Silakan login.', 'alert alert-success');
            redirect('login.php');
        } else {
            $errors[] = "Terjadi kesalahan. Silakan coba lagi.";
        }
    }
    
    if (!empty($errors)) {
        $error_msg = implode("<br>", $errors);
        flash('register_error', $error_msg, 'alert alert-danger');
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sistem Peminjaman Ruang Rapat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #eef2ff;
            --secondary: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
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
        
        .register-container {
            width: 100%;
            max-width: 450px;
        }
        
        .register-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .register-card:hover {
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
        
        .btn-register {
            background: linear-gradient(135deg, var(--primary) 0%, #3a0ca3 100%);
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(67, 97, 238, 0.3);
        }
        
        .flash-message {
            border-radius: 12px;
            margin-bottom: 20px;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .login-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        
        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .login-link a:hover {
            color: #3a0ca3;
            text-decoration: underline;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
        
        .password-strength {
            height: 6px;
            margin-top: 8px;
            border-radius: 3px;
            background: #e2e8f0;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            border-radius: 3px;
            width: 0%;
            transition: all 0.3s ease;
        }
        
        .password-feedback {
            font-size: 0.85rem;
            margin-top: 5px;
        }
        
        .password-match {
            font-size: 0.85rem;
            margin-top: 5px;
        }
        
        /* Responsive design */
        @media (max-width: 576px) {
            .register-container {
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
        
        .register-card {
            animation: fadeIn 0.5s ease-out;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="logo-section">
                <div class="logo-icon">
                    <i class="fas fa-chalkboard"></i>
                </div>
                <h1 class="app-name">MeetingRoom</h1>
                <p class="app-desc">Buat Akun Baru</p>
            </div>
            
            <div class="form-section">
                <h2 class="form-title">Daftar Akun Baru</h2>
                
                <?php flash('register_error'); ?>
                
                <form method="POST" action="" id="registerForm">
                    <div class="form-group">
                        <label for="nama" class="form-label">Nama Lengkap</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="nama" name="nama" placeholder="Masukkan nama lengkap" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="email@contoh.com" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Minimal 6 karakter" required>
                        </div>
                        <div class="password-strength mt-2">
                            <div class="password-strength-bar" id="passwordStrengthBar"></div>
                        </div>
                        <div class="password-feedback" id="passwordStrengthText">Kekuatan password: -</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Konfirmasi password" required>
                        </div>
                        <div class="password-match" id="passwordMatchText"></div>
                    </div>
                    
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-register">
                            <i class="fas fa-user-plus me-2"></i>Daftar
                        </button>
                    </div>
                </form>
                
                <div class="login-link">
                    <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validasi kekuatan password
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const strengthBar = document.getElementById('passwordStrengthBar');
        const strengthText = document.getElementById('passwordStrengthText');
        const matchText = document.getElementById('passwordMatchText');
        
        passwordInput.addEventListener('input', checkPasswordStrength);
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
        
        function checkPasswordStrength() {
            const password = passwordInput.value;
            let strength = 0;
            let message = '';
            let color = '';
            
            // Jika password kosong
            if (password.length === 0) {
                strengthBar.style.width = '0%';
                strengthBar.style.backgroundColor = '';
                strengthText.textContent = 'Kekuatan password: -';
                strengthText.className = 'password-feedback text-muted';
                return;
            }
            
            // Jika password kurang dari 6 karakter
            if (password.length < 6) {
                strengthBar.style.width = '20%';
                strengthBar.style.backgroundColor = '#ef4444';
                strengthText.textContent = 'Kekuatan password: terlalu pendek';
                strengthText.className = 'password-feedback text-danger';
                return;
            }
            
            // Kriteria kekuatan password
            if (password.length >= 6) strength += 20;
            if (password.length >= 8) strength += 20;
            if (/[A-Z]/.test(password)) strength += 20;
            if (/[0-9]/.test(password)) strength += 20;
            if (/[^A-Za-z0-9]/.test(password)) strength += 20;
            
            // Update tampilan kekuatan password
            strengthBar.style.width = strength + '%';
            
            if (strength < 40) {
                strengthBar.style.backgroundColor = '#ef4444';
                message = 'Kekuatan password: lemah';
                color = 'text-danger';
            } else if (strength < 80) {
                strengthBar.style.backgroundColor = '#f59e0b';
                message = 'Kekuatan password: sedang';
                color = 'text-warning';
            } else {
                strengthBar.style.backgroundColor = '#10b981';
                message = 'Kekuatan password: kuat';
                color = 'text-success';
            }
            
            strengthText.textContent = message;
            strengthText.className = 'password-feedback ' + color;
        }
        
        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (confirmPassword.length === 0) {
                matchText.textContent = '';
                matchText.className = 'password-match';
                return;
            }
            
            if (password === confirmPassword) {
                matchText.textContent = '✓ Password cocok';
                matchText.className = 'password-match text-success';
            } else {
                matchText.textContent = '✗ Password tidak cocok';
                matchText.className = 'password-match text-danger';
            }
        }
        
        // Validasi form sebelum submit
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password minimal harus 6 karakter');
                return false;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Konfirmasi password tidak sesuai');
                return false;
            }
        });
        
        // Inisialisasi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            checkPasswordStrength();
            checkPasswordMatch();
        });
    </script>
</body>
</html>