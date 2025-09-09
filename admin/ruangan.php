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

// Tambah ruangan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_ruangan'])) {
    $nama_ruangan = trim($_POST['nama_ruangan']);
    $kapasitas = intval($_POST['kapasitas']);
    $lokasi = trim($_POST['lokasi']);
    
    if (!empty($nama_ruangan) && $kapasitas > 0 && !empty($lokasi)) {
        $sql = "INSERT INTO ruangan (nama_ruangan, kapasitas, lokasi) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sis", $nama_ruangan, $kapasitas, $lokasi);
        
        if ($stmt->execute()) {
            flash('message', 'Ruangan berhasil ditambahkan', 'alert alert-success');
        } else {
            flash('message', 'Gagal menambahkan ruangan', 'alert alert-danger');
        }
    } else {
        flash('message', 'Semua field harus diisi dengan benar', 'alert alert-danger');
    }
}

// Edit ruangan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_ruangan'])) {
    $id = intval($_POST['id']);
    $nama_ruangan = trim($_POST['nama_ruangan']);
    $kapasitas = intval($_POST['kapasitas']);
    $lokasi = trim($_POST['lokasi']);
    
    if (!empty($nama_ruangan) && $kapasitas > 0 && !empty($lokasi)) {
        $sql = "UPDATE ruangan SET nama_ruangan = ?, kapasitas = ?, lokasi = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sisi", $nama_ruangan, $kapasitas, $lokasi, $id);
        
        if ($stmt->execute()) {
            flash('message', 'Ruangan berhasil diperbarui', 'alert alert-success');
        } else {
            flash('message', 'Gagal memperbarui ruangan', 'alert alert-danger');
        }
    } else {
        flash('message', 'Semua field harus diisi dengan benar', 'alert alert-danger');
    }
}

// Hapus ruangan
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    
    $sql = "DELETE FROM ruangan WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        flash('message', 'Ruangan berhasil dihapus', 'alert alert-success');
    } else {
        flash('message', 'Gagal menghapus ruangan', 'alert alert-danger');
    }
}

// Ambil data ruangan
$ruangan = [];
$sql = "SELECT * FROM ruangan ORDER BY nama_ruangan";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $ruangan = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Ruangan - Sistem Peminjaman Ruang Rapat</title>
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
        
        .form-control {
            border-radius: 8px;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            transition: var(--transition);
        }
        
        .form-control:focus {
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
                        <a class="nav-link active" href="ruangan.php">
                            <i class="fas fa-door-open me-1"></i> Ruangan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="fasilitas.php">
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
        <h1 class="page-title">Kelola Ruangan</h1>
        
        <?php flash('message'); ?>
        
        <!-- Form Tambah Ruangan -->
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-plus-circle me-2"></i>Tambah Ruangan Baru</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="nama_ruangan" class="form-label">Nama Ruangan</label>
                                <input type="text" class="form-control" id="nama_ruangan" name="nama_ruangan" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="kapasitas" class="form-label">Kapasitas</label>
                                <input type="number" class="form-control" id="kapasitas" name="kapasitas" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="lokasi" class="form-label">Lokasi</label>
                                <input type="text" class="form-control" id="lokasi" name="lokasi" required>
                            </div>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" name="tambah_ruangan" class="btn btn-primary w-100">
                                <i class="fas fa-plus me-1"></i> Tambah
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Daftar Ruangan -->
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-list me-2"></i>Daftar Ruangan</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nama Ruangan</th>
                                <th>Kapasitas</th>
                                <th>Lokasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($ruangan)): ?>
                                <?php foreach ($ruangan as $r): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['nama_ruangan']) ?></td>
                                    <td><span class="badge bg-primary"><?= $r['kapasitas'] ?> orang</span></td>
                                    <td><?= htmlspecialchars($r['lokasi']) ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $r['id'] ?>">
                                                <i class="fas fa-edit me-1"></i> Edit
                                            </button>
                                            <a href="ruangan.php?hapus=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus ruangan ini?')">
                                                <i class="fas fa-trash me-1"></i> Hapus
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- Modal Edit -->
                                <div class="modal fade" id="editModal<?= $r['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Ruangan</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="">
                                                <div class="modal-body">
                                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                                    <div class="mb-3">
                                                        <label for="nama_ruangan_edit" class="form-label">Nama Ruangan</label>
                                                        <input type="text" class="form-control" id="nama_ruangan_edit" name="nama_ruangan" value="<?= htmlspecialchars($r['nama_ruangan']) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="kapasitas_edit" class="form-label">Kapasitas</label>
                                                        <input type="number" class="form-control" id="kapasitas_edit" name="kapasitas" value="<?= $r['kapasitas'] ?>" min="1" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="lokasi_edit" class="form-label">Lokasi</label>
                                                        <input type="text" class="form-control" id="lokasi_edit" name="lokasi" value="<?= htmlspecialchars($r['lokasi']) ?>" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" name="edit_ruangan" class="btn btn-primary">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <i class="fas fa-door-open fa-2x mb-3 text-muted"></i>
                                        <p class="text-muted">Tidak ada data ruangan</p>
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