<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Cek auth dan role admin
if (!isLoggedIn()) {
    redirect('../login.php');
}

if (!isAdmin()) {
    redirect('../user/index.php');
}

// Ambil data ruangan untuk dropdown
$sql_ruangan = "SELECT * FROM ruangan ORDER BY nama_ruangan";
$ruangan_list = $conn->query($sql_ruangan)->fetch_all(MYSQLI_ASSOC);

// Tambah fasilitas
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_fasilitas'])) {
    $id_ruangan = intval($_POST['id_ruangan']);
    $nama_fasilitas = trim($_POST['nama_fasilitas']);
    $jumlah = intval($_POST['jumlah']);
    
    if (!empty($nama_fasilitas) && $jumlah > 0) {
        $sql = "INSERT INTO fasilitas (id_ruangan, nama_fasilitas, jumlah) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $id_ruangan, $nama_fasilitas, $jumlah);
        
        if ($stmt->execute()) {
            flash('message', 'Fasilitas berhasil ditambahkan', 'alert alert-success');
        } else {
            flash('message', 'Gagal menambahkan fasilitas', 'alert alert-danger');
        }
    } else {
        flash('message', 'Semua field harus diisi dengan benar', 'alert alert-danger');
    }
}

// Edit fasilitas
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_fasilitas'])) {
    $id = intval($_POST['id']);
    $id_ruangan = intval($_POST['id_ruangan']);
    $nama_fasilitas = trim($_POST['nama_fasilitas']);
    $jumlah = intval($_POST['jumlah']);
    
    if (!empty($nama_fasilitas) && $jumlah > 0) {
        $sql = "UPDATE fasilitas SET id_ruangan = ?, nama_fasilitas = ?, jumlah = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isii", $id_ruangan, $nama_fasilitas, $jumlah, $id);
        
        if ($stmt->execute()) {
            flash('message', 'Fasilitas berhasil diperbarui', 'alert alert-success');
        } else {
            flash('message', 'Gagal memperbarui fasilitas', 'alert alert-danger');
        }
    } else {
        flash('message', 'Semua field harus diisi dengan benar', 'alert alert-danger');
    }
}

// Hapus fasilitas
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    
    $sql = "DELETE FROM fasilitas WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        flash('message', 'Fasilitas berhasil dihapus', 'alert alert-success');
    } else {
        flash('message', 'Gagal menghapus fasilitas', 'alert alert-danger');
    }
}

// Ambil data fasilitas dengan join ruangan
$fasilitas = [];
$sql = "SELECT f.*, r.nama_ruangan 
        FROM fasilitas f 
        LEFT JOIN ruangan r ON f.id_ruangan = r.id 
        ORDER BY r.nama_ruangan, f.nama_fasilitas";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $fasilitas = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Fasilitas - Sistem Peminjaman Ruang Rapat</title>
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
            max-width: 1200px;
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
        
        .table-responsive {
            border-radius: 12px;
            overflow: hidden;
        }
        
        .table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .table thead th {
            background-color: var(--primary-light);
            color: var(--dark);
            border: none;
            padding: 1rem;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #f1f5f9;
            font-size: 0.95rem;
        }
        
        .table tbody tr {
            transition: var(--transition);
        }
        
        .table tbody tr:hover {
            background-color: var(--primary-light);
        }
        
        .badge {
            padding: 0.5rem 0.8rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.75rem;
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
        
        .btn-warning {
            background: var(--warning);
            border: none;
            color: white;
        }
        
        .btn-warning:hover {
            background: #d97706;
            transform: translateY(-1px);
            color: white;
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
        
        .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .modal-header {
            border-bottom: 1px solid #f1f5f9;
            padding: 1.25rem 1.5rem;
        }
        
        .modal-title {
            font-weight: 600;
            color: var(--dark);
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .modal-footer {
            border-top: 1px solid #f1f5f9;
            padding: 1rem 1.5rem;
        }
        
        .flash-message {
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border: none;
            box-shadow: var(--card-shadow);
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .text-muted {
            font-size: 0.85rem;
        }
        
        .badge-general {
            background-color: var(--info);
            color: white;
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
                        <a class="nav-link" href="ruangan.php">
                            <i class="fas fa-door-open me-1"></i> Ruangan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="fasilitas.php">
                            <i class="fas fa-couch me-1"></i> Fasilitas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="peminjaman.php">
                            <i class="fas fa-calendar-check me-1"></i> Peminjaman
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="kalender.php">
                            <i class="fas fa-calendar-alt me-1"></i> Kalender
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
        <h1 class="page-title">Kelola Fasilitas</h1>
        
        <?php flash('message'); ?>
        
        <!-- Form Tambah Fasilitas -->
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-plus-circle me-2"></i>Tambah Fasilitas Baru</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="id_ruangan" class="form-label">Ruangan</label>
                                <select class="form-select" id="id_ruangan" name="id_ruangan">
                                    <option value="">-- Pilih Ruangan --</option>
                                    <?php foreach ($ruangan_list as $r): ?>
                                        <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nama_ruangan']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Kosongkan jika fasilitas umum</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="nama_fasilitas" class="form-label">Nama Fasilitas</label>
                                <input type="text" class="form-control" id="nama_fasilitas" name="nama_fasilitas" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="jumlah" class="form-label">Jumlah</label>
                                <input type="number" class="form-control" id="jumlah" name="jumlah" min="1" value="1" required>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" name="tambah_fasilitas" class="btn btn-primary w-100">
                                <i class="fas fa-plus me-1"></i> Tambah
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Daftar Fasilitas -->
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-list me-2"></i>Daftar Fasilitas</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nama Fasilitas</th>
                                <th>Jumlah</th>
                                <th>Ruangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($fasilitas)): ?>
                                <?php foreach ($fasilitas as $f): ?>
                                <tr>
                                    <td><?= htmlspecialchars($f['nama_fasilitas']) ?></td>
                                    <td><span class="badge bg-primary"><?= $f['jumlah'] ?></span></td>
                                    <td>
                                        <?php if ($f['nama_ruangan']): ?>
                                            <span class="badge bg-success"><?= htmlspecialchars($f['nama_ruangan']) ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-general">Umum</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $f['id'] ?>">
                                                <i class="fas fa-edit me-1"></i> Edit
                                            </button>
                                            <a href="fasilitas.php?hapus=<?= $f['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus fasilitas ini?')">
                                                <i class="fas fa-trash me-1"></i> Hapus
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- Modal Edit -->
                                <div class="modal fade" id="editModal<?= $f['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Fasilitas</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="">
                                                <div class="modal-body">
                                                    <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                                    <div class="mb-3">
                                                        <label for="id_ruangan_edit" class="form-label">Ruangan</label>
                                                        <select class="form-select" id="id_ruangan_edit" name="id_ruangan">
                                                            <option value="">-- Pilih Ruangan --</option>
                                                            <?php foreach ($ruangan_list as $r): ?>
                                                                <option value="<?= $r['id'] ?>" <?= $f['id_ruangan'] == $r['id'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($r['nama_ruangan']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <small class="text-muted">Kosongkan jika fasilitas umum</small>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="nama_fasilitas_edit" class="form-label">Nama Fasilitas</label>
                                                        <input type="text" class="form-control" id="nama_fasilitas_edit" name="nama_fasilitas" value="<?= htmlspecialchars($f['nama_fasilitas']) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="jumlah_edit" class="form-label">Jumlah</label>
                                                        <input type="number" class="form-control" id="jumlah_edit" name="jumlah" value="<?= $f['jumlah'] ?>" min="1" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" name="edit_fasilitas" class="btn btn-primary">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <i class="fas fa-couch fa-2x mb-3 text-muted"></i>
                                        <p class="text-muted">Tidak ada data fasilitas</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>