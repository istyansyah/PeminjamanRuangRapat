<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Cek auth
if (!isLoggedIn()) {
    redirect('../login.php');
}

if (isAdmin()) {
    redirect('../admin/index.php');
}

// Ambil data ruangan jika ada parameter
$id_ruangan = isset($_GET['ruangan']) ? intval($_GET['ruangan']) : 0;
$ruangan = null;

if ($id_ruangan > 0) {
    $sql = "SELECT * FROM ruangan WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_ruangan);
    $stmt->execute();
    $result = $stmt->get_result();
    $ruangan = $result->fetch_assoc();
}

// Proses form peminjaman
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_ruangan = intval($_POST['id_ruangan']);
    $agenda = trim($_POST['agenda']);
    $nama_peminjam = trim($_POST['nama_peminjam']);
    $divisi = trim($_POST['divisi']);
    $tanggal = $_POST['tanggal'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    $fasilitas_tambahan = isset($_POST['fasilitas_tambahan']) ? $_POST['fasilitas_tambahan'] : [];
    
    // Validasi input
    $errors = [];
    
    if (empty($nama_peminjam)) {
        $errors[] = "Nama peminjam harus diisi";
    }
    
    if (empty($divisi)) {
        $errors[] = "Divisi harus diisi";
    }
    
    if (empty($tanggal)) {
        $errors[] = "Tanggal harus diisi";
    } elseif (strtotime($tanggal) < strtotime(date('Y-m-d'))) {
        $errors[] = "Tanggal tidak boleh di masa lalu";
    }
    
    if (empty($jam_mulai) || empty($jam_selesai)) {
        $errors[] = "Jam mulai dan selesai harus diisi";
    } elseif (strtotime($jam_mulai) >= strtotime($jam_selesai)) {
        $errors[] = "Jam selesai harus setelah jam mulai";
    }
    
    // Validasi file upload
    $nota_dinas = '';
    if (isset($_FILES['nota_dinas']) && $_FILES['nota_dinas']['error'] == UPLOAD_ERR_OK) {
        $file_ext = strtolower(pathinfo($_FILES['nota_dinas']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['pdf', 'doc', 'docx'];
        
        if (!in_array($file_ext, $allowed_ext)) {
            $errors[] = "Format file nota dinas harus PDF, DOC, atau DOCX";
        } elseif ($_FILES['nota_dinas']['size'] > 5 * 1024 * 1024) { // 5MB
            $errors[] = "Ukuran file nota dinas maksimal 5MB";
        } else {
            // Generate unique filename
            $nota_dinas = uniqid() . '_' . time() . '.' . $file_ext;
            $upload_path = '../uploads/nota_dinas/' . $nota_dinas;
            
            if (!move_uploaded_file($_FILES['nota_dinas']['tmp_name'], $upload_path)) {
                $errors[] = "Gagal mengupload nota dinas";
            }
        }
    } else {
        $errors[] = "Nota dinas harus diupload";
    }
    
    // Validasi waktu tidak bentrok
    if (empty($errors) && !isWaktuValid($conn, $id_ruangan, $tanggal, $jam_mulai, $jam_selesai)) {
        $errors[] = "Ruangan sudah dipesan pada tanggal dan jam tersebut";
    }
    
    // Jika tidak ada error, simpan peminjaman
    if (empty($errors)) {
        $conn->begin_transaction();
        
        try {
            // Simpan data peminjaman - PERBAIKAN DI SINI
            $sql = "INSERT INTO peminjaman (id_user, id_ruangan, agenda, nama_peminjam, divisi, tanggal, jam_mulai, jam_selesai, nota_dinas) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            // PERBAIKAN: Sesuaikan jumlah parameter dengan placeholder
            $stmt->bind_param("iisssssss", $_SESSION['user_id'], $id_ruangan, $agenda, $nama_peminjam, $divisi, $tanggal, $jam_mulai, $jam_selesai, $nota_dinas);
            $stmt->execute();
            
            $id_peminjaman = $conn->insert_id;
            
            // Simpan fasilitas tambahan
            if (!empty($fasilitas_tambahan)) {
                foreach ($fasilitas_tambahan as $fasilitas) {
                    if (!empty($fasilitas['nama']) && !empty($fasilitas['jumlah'])) {
                        $sql = "INSERT INTO peminjaman_fasilitas (id_peminjaman, nama_fasilitas, jumlah) 
                                VALUES (?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("isi", $id_peminjaman, $fasilitas['nama'], $fasilitas['jumlah']);
                        $stmt->execute();
                    }
                }
            }
            
            $conn->commit();
            flash('message', 'Peminjaman berhasil diajukan. Menunggu persetujuan admin.', 'alert alert-success');
            redirect('history.php');
        } catch (Exception $e) {
            $conn->rollback();
            // Hapus file yang sudah diupload jika gagal
            if (!empty($upload_path) && file_exists($upload_path)) {
                unlink($upload_path);
            }
            $errors[] = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
    
    if (!empty($errors)) {
        $error_msg = implode("<br>", $errors);
        flash('message', $error_msg, 'alert alert-danger');
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pinjam Ruangan - Sistem Peminjaman Ruang Rapat</title>
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
            --info: #0ea5e9;
            --light: #f8fafc;
            --dark: #1e293b;
            --card-shadow: 0 1px 3px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.1);
            --transition: all 0.2s ease;
        }
        
        body {
            background-color: #f1f5f9;
            font-family: 'Inter', sans-serif;
            color: var(--dark);
            font-weight: 400;
        }
        
        .navbar {
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            padding: 0.8rem 0;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.4rem;
            color: var(--primary) !important;
        }
        
        .nav-link {
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            margin: 0 0.1rem;
            transition: var(--transition);
            color: var(--secondary) !important;
        }
        
        .nav-link:hover, .nav-link.active {
            background-color: var(--primary-light);
            color: var(--primary) !important;
        }
        
        .container {
            max-width: 1000px;
        }
        
        .page-title {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            overflow: hidden;
            background: white;
        }
        
        .card:hover {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #f1f5f9;
            padding: 1.25rem 1.5rem;
        }
        
        .card-header h5 {
            margin: 0;
            font-weight: 600;
            color: var(--dark);
            font-size: 1.1rem;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            transition: var(--transition);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }
        
        .btn {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: var(--transition);
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: var(--primary);
            border: none;
        }
        
        .btn-primary:hover {
            background: #3a0ca3;
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: var(--secondary);
            border: none;
        }
        
        .btn-secondary:hover {
            background: #475569;
            transform: translateY(-1px);
        }
        
        .btn-danger {
            background: var(--danger);
            border: none;
        }
        
        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }
        
        .btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.8rem;
        }
        
        .flash-message {
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border: none;
            box-shadow: var(--card-shadow);
        }
        
        .ruangan-info {
            background-color: var(--primary-light);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .ruangan-info h6 {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }
        
        .fasilitas-item {
            background-color: var(--primary-light);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.5rem;
        }
        
        .file-upload-info {
            font-size: 0.85rem;
            color: var(--secondary);
            margin-top: 0.25rem;
        }
        
        .required-field::after {
            content: " *";
            color: var(--danger);
        }
        
        .time-inputs {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .time-separator {
            color: var(--secondary);
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .time-inputs {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .time-separator {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-chalkboard me-2"></i>MeetingRoom
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="history.php">
                            <i class="fas fa-history me-1"></i> History
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="page-title">Form Peminjaman Ruangan</h1>
        
        <?php flash('message'); ?>
        
        <?php if ($ruangan): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-door-open me-2"></i>Detail Ruangan</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <p><strong><i class="fas fa-door-open me-2 text-primary"></i>Nama Ruangan:</strong><br>
                        <?= htmlspecialchars($ruangan['nama_ruangan']) ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong><i class="fas fa-users me-2 text-primary"></i>Kapasitas:</strong><br>
                        <?= $ruangan['kapasitas'] ?> orang</p>
                    </div>
                    <div class="col-md-4">
                        <p><strong><i class="fas fa-map-marker-alt me-2 text-primary"></i>Lokasi:</strong><br>
                        <?= htmlspecialchars($ruangan['lokasi']) ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-calendar-plus me-2"></i>Form Peminjaman</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="id_ruangan" value="<?= $id_ruangan ?>">
                    
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label for="agenda" class="form-label required-field">Agenda Rapat</label>
                            <input type="text" class="form-control" id="agenda" name="agenda" required 
                                   placeholder="Masukkan agenda rapat">
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="nama_peminjam" class="form-label required-field">Nama Peminjam</label>
                            <input type="text" class="form-control" id="nama_peminjam" name="nama_peminjam" required 
                                   placeholder="Nama lengkap peminjam">
                        </div>
                        <div class="col-md-6">
                            <label for="divisi" class="form-label required-field">Divisi/Bagian</label>
                            <input type="text" class="form-control" id="divisi" name="divisi" required 
                                   placeholder="Divisi atau bagian">
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="tanggal" class="form-label required-field">Tanggal</label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" 
                                   min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label required-field">Waktu</label>
                            <div class="time-inputs">
                                <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" required>
                                <span class="time-separator">sampai</span>
                                <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="nota_dinas" class="form-label required-field">Nota Dinas dan Layout</label>
                        <input type="file" class="form-control" id="nota_dinas" name="nota_dinas" 
                               accept=".pdf,.doc,.docx" required>
                        <div class="file-upload-info">
                            <i class="fas fa-info-circle me-1"></i>Format: PDF, DOC, DOCX | Maksimal: 5MB
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Fasilitas Tambahan</label>
                        <div id="fasilitas-container">
                            <div class="fasilitas-item row mb-2">
                                <div class="col-md-5 mb-2">
                                    <input type="text" class="form-control" name="fasilitas_tambahan[0][nama]" 
                                           placeholder="Nama fasilitas">
                                </div>
                                <div class="col-md-5 mb-2">
                                    <input type="number" class="form-control" name="fasilitas_tambahan[0][jumlah]" 
                                           placeholder="Jumlah" min="1">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <button type="button" class="btn btn-danger btn-sm w-100 remove-fasilitas">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm mt-2" id="tambah-fasilitas">
                            <i class="fas fa-plus me-1"></i> Tambah Fasilitas
                        </button>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i> Ajukan Peminjaman
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script untuk menambah dan menghapus fasilitas
        document.addEventListener('DOMContentLoaded', function() {
            let fasilitasCount = 1;
            
            document.getElementById('tambah-fasilitas').addEventListener('click', function() {
                const container = document.getElementById('fasilitas-container');
                const newItem = document.createElement('div');
                newItem.className = 'fasilitas-item row mb-2';
                newItem.innerHTML = `
                    <div class="col-md-5 mb-2">
                        <input type="text" class="form-control" name="fasilitas_tambahan[${fasilitasCount}][nama]" placeholder="Nama fasilitas">
                    </div>
                    <div class="col-md-5 mb-2">
                        <input type="number" class="form-control" name="fasilitas_tambahan[${fasilitasCount}][jumlah]" placeholder="Jumlah" min="1">
                    </div>
                    <div class="col-md-2 mb-2">
                        <button type="button" class="btn btn-danger btn-sm w-100 remove-fasilitas">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
                container.appendChild(newItem);
                fasilitasCount++;
            });
            
            document.getElementById('fasilitas-container').addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-fasilitas') || e.target.closest('.remove-fasilitas')) {
                    const item = e.target.closest('.fasilitas-item');
                    if (document.querySelectorAll('.fasilitas-item').length > 1) {
                        item.remove();
                    } else {
                        // Reset values for the first item instead of removing it
                        item.querySelector('input[type="text"]').value = '';
                        item.querySelector('input[type="number"]').value = '';
                    }
                }
            });

            // Set minimum time for time inputs based on current time if today is selected
            const tanggalInput = document.getElementById('tanggal');
            const jamMulaiInput = document.getElementById('jam_mulai');
            
            tanggalInput.addEventListener('change', function() {
                const today = new Date().toISOString().split('T')[0];
                const selectedDate = this.value;
                
                if (selectedDate === today) {
                    const now = new Date();
                    const currentHour = now.getHours().toString().padStart(2, '0');
                    const currentMinute = now.getMinutes().toString().padStart(2, '0');
                    jamMulaiInput.min = `${currentHour}:${currentMinute}`;
                } else {
                    jamMulaiInput.min = '00:00';
                }
            });

            // Set jam_selesai minimum based on jam_mulai
            jamMulaiInput.addEventListener('change', function() {
                const jamSelesaiInput = document.getElementById('jam_selesai');
                jamSelesaiInput.min = this.value;
                
                // If jam_selesai is earlier than jam_mulai, reset it
                if (jamSelesaiInput.value && jamSelesaiInput.value < this.value) {
                    jamSelesaiInput.value = '';
                }
            });
        });
    </script>
</body>
</html>