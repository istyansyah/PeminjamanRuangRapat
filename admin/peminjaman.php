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

// Proses approval peminjaman
if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    
    $sql = "UPDATE peminjaman SET status = 'disetujui' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        flash('message', 'Peminjaman berhasil disetujui', 'alert alert-success');
    } else {
        flash('message', 'Gagal menyetujui peminjaman', 'alert alert-danger');
    }
}

// Proses penolakan peminjaman
if (isset($_GET['reject'])) {
    $id = intval($_GET['reject']);
    
    $sql = "UPDATE peminjaman SET status = 'ditolak' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        flash('message', 'Peminjaman berhasil ditolak', 'alert alert-success');
    } else {
        flash('message', 'Gagal menolak peminjaman', 'alert alert-danger');
    }
}

// Filter status
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Query peminjaman dengan filter
$sql = "SELECT p.*, r.nama_ruangan, u.nama as nama_user 
        FROM peminjaman p 
        JOIN ruangan r ON p.id_ruangan = r.id 
        JOIN users u ON p.id_user = u.id";
        
if ($status_filter != 'all') {
    $sql .= " WHERE p.status = ?";
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $conn->prepare($sql);
if ($status_filter != 'all') {
    $stmt->bind_param("s", $status_filter);
}
$stmt->execute();
$result = $stmt->get_result();
$peminjaman = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Peminjaman - Sistem Peminjaman Ruang Rapat</title>
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
        
        .btn-success {
            background: var(--success);
            border: none;
        }
        
        .btn-success:hover {
            background: #059669;
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
        
        .btn-info {
            background: var(--info);
            border: none;
        }
        
        .btn-info:hover {
            background: #0284c7;
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
        
        .form-select {
            border-radius: 8px;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            transition: var(--transition);
        }
        
        .form-select:focus {
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
        
        .status-badge {
            padding: 0.5rem 0.8rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.75rem;
        }
        
        .badge-pending {
            background-color: var(--warning);
            color: white;
        }
        
        .badge-approved {
            background-color: var(--success);
            color: white;
        }
        
        .badge-rejected {
            background-color: var(--danger);
            color: white;
        }
        
        .filter-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 1.5rem;
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
                        <a class="nav-link" href="fasilitas.php">
                            <i class="fas fa-couch me-1"></i> Fasilitas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="peminjaman.php">
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
        <h1 class="page-title">Kelola Peminjaman</h1>
        
        <?php flash('message'); ?>
        
        <!-- Filter Status -->
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-filter me-2"></i>Filter Peminjaman</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="" class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label for="status" class="form-label">Status Peminjaman</label>
                        <select class="form-select" id="status" name="status" onchange="this.form.submit()">
                            <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>Semua Status</option>
                            <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="disetujui" <?= $status_filter == 'disetujui' ? 'selected' : '' ?>>Disetujui</option>
                            <option value="ditolak" <?= $status_filter == 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <a href="peminjaman.php" class="btn btn-outline-secondary">
                            <i class="fas fa-sync me-1"></i> Reset Filter
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Daftar Peminjaman -->
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-list me-2"></i>Daftar Peminjaman</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Ruangan</th>
                                <th>Peminjam</th>
                                <th>Tanggal</th>
                                <th>Waktu</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($peminjaman)): ?>
                                <?php foreach ($peminjaman as $p): ?>
                                <tr>
                                    <td>
                                        <div class="fw-medium"><?= htmlspecialchars($p['nama_ruangan']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($p['agenda']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($p['nama_user']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($p['tanggal'])) ?></td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?= date('H:i', strtotime($p['jam_mulai'])) ?> - <?= date('H:i', strtotime($p['jam_selesai'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $status_class = '';
                                        if ($p['status'] == 'disetujui') {
                                            $status_class = 'badge-approved';
                                        } elseif ($p['status'] == 'ditolak') {
                                            $status_class = 'badge-rejected';
                                        } else {
                                            $status_class = 'badge-pending';
                                        }
                                        echo "<span class='status-badge $status_class'>" . ucfirst($p['status']) . "</span>";
                                        ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#detailModal<?= $p['id'] ?>">
                                                <i class="fas fa-eye me-1"></i> Detail
                                            </button>
                                            
                                            <?php if ($p['status'] == 'pending'): ?>
                                                <a href="peminjaman.php?approve=<?= $p['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Yakin ingin menyetujui peminjaman ini?')">
                                                    <i class="fas fa-check me-1"></i> Setujui
                                                </a>
                                                <a href="peminjaman.php?reject=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menolak peminjaman ini?')">
                                                    <i class="fas fa-times me-1"></i> Tolak
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- Modal Detail -->
                                <div class="modal fade" id="detailModal<?= $p['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Detail Peminjaman</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-medium text-muted">Agenda</label>
                                                            <p class="fw-medium"><?= htmlspecialchars($p['agenda']) ?></p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-medium text-muted">Ruangan</label>
                                                            <p><?= htmlspecialchars($p['nama_ruangan']) ?></p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-medium text-muted">Tanggal</label>
                                                            <p><?= date('d/m/Y', strtotime($p['tanggal'])) ?></p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-medium text-muted">Waktu</label>
                                                            <p><?= date('H:i', strtotime($p['jam_mulai'])) ?> - <?= date('H:i', strtotime($p['jam_selesai'])) ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-medium text-muted">Status</label>
                                                            <p>
                                                                <?php
                                                                $status_class = '';
                                                                if ($p['status'] == 'disetujui') {
                                                                    $status_class = 'text-success fw-bold';
                                                                } elseif ($p['status'] == 'ditolak') {
                                                                    $status_class = 'text-danger fw-bold';
                                                                } else {
                                                                    $status_class = 'text-warning fw-bold';
                                                                }
                                                                echo "<span class='$status_class'>" . ucfirst($p['status']) . "</span>";
                                                                ?>
                                                            </p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-medium text-muted">Nama Peminjam</label>
                                                            <p><?= htmlspecialchars($p['nama_peminjam']) ?></p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-medium text-muted">Divisi</label>
                                                            <p><?= htmlspecialchars($p['divisi']) ?></p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-medium text-muted">User</label>
                                                            <p><?= htmlspecialchars($p['nama_user']) ?></p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-medium text-muted">Nota Dinas</label>
                                                            <p>
                                                                <a href="../uploads/nota_dinas/<?= $p['nota_dinas'] ?>" target="_blank" class="btn btn-sm btn-primary">
                                                                    <i class="fas fa-download me-1"></i> Download
                                                                </a>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <hr>
                                                
                                                <h6 class="mt-4 mb-3"><i class="fas fa-couch me-2"></i>Fasilitas Tambahan:</h6>
                                                <ul class="list-group">
                                                    <?php
                                                    $sql_fasilitas = "SELECT * FROM peminjaman_fasilitas WHERE id_peminjaman = ?";
                                                    $stmt_f = $conn->prepare($sql_fasilitas);
                                                    $stmt_f->bind_param("i", $p['id']);
                                                    $stmt_f->execute();
                                                    $fasilitas = $stmt_f->get_result()->fetch_all(MYSQLI_ASSOC);
                                                    
                                                    if (!empty($fasilitas)) {
                                                        foreach ($fasilitas as $f) {
                                                            echo "<li class='list-group-item d-flex justify-content-between align-items-center'>" . 
                                                                 htmlspecialchars($f['nama_fasilitas']) . 
                                                                 "<span class='badge bg-primary rounded-pill'>" . $f['jumlah'] . "</span></li>";
                                                        }
                                                    } else {
                                                        echo "<li class='list-group-item text-muted'>Tidak ada fasilitas tambahan</li>";
                                                    }
                                                    ?>
                                                </ul>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                    <i class="fas fa-times me-1"></i> Tutup
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-calendar-times fa-2x mb-3 text-muted"></i>
                                        <p class="text-muted">Tidak ada data peminjaman</p>
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