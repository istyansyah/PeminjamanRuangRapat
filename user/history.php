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

// Ambil history peminjaman user
$user_id = $_SESSION['user_id'];
$sql = "SELECT p.*, r.nama_ruangan 
        FROM peminjaman p 
        JOIN ruangan r ON p.id_ruangan = r.id 
        WHERE p.id_user = ? 
        ORDER BY p.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$peminjaman = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History Peminjaman - Sistem Peminjaman Ruang Rapat</title>
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
        
        .btn-info {
            background: var(--info);
            border: none;
        }
        
        .btn-info:hover {
            background: #0284c7;
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
        
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .detail-item {
            padding: 1rem;
            background-color: var(--primary-light);
            border-radius: 8px;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .detail-value {
            color: var(--dark);
            font-weight: 500;
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .feature-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
        }
        
        .feature-list li:last-child {
            border-bottom: none;
        }
        
        .feature-list i {
            color: var(--primary);
            margin-right: 0.5rem;
            width: 20px;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--secondary);
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #cbd5e1;
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
                        <a class="nav-link active" href="history.php">
                            <i class="fas fa-history me-1"></i> History Peminjaman
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
        <h1 class="page-title">History Peminjaman</h1>
        
        <?php flash('message'); ?>
        
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
                                                <a href="cancel.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin membatalkan peminjaman?')">
                                                    <i class="fas fa-times me-1"></i> Batalkan
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
                                                <div class="detail-grid">
                                                    <div class="detail-item">
                                                        <div class="detail-label">Agenda</div>
                                                        <div class="detail-value"><?= htmlspecialchars($p['agenda']) ?></div>
                                                    </div>
                                                    <div class="detail-item">
                                                        <div class="detail-label">Ruangan</div>
                                                        <div class="detail-value"><?= htmlspecialchars($p['nama_ruangan']) ?></div>
                                                    </div>
                                                    <div class="detail-item">
                                                        <div class="detail-label">Tanggal</div>
                                                        <div class="detail-value"><?= date('d/m/Y', strtotime($p['tanggal'])) ?></div>
                                                    </div>
                                                    <div class="detail-item">
                                                        <div class="detail-label">Waktu</div>
                                                        <div class="detail-value"><?= date('H:i', strtotime($p['jam_mulai'])) ?> - <?= date('H:i', strtotime($p['jam_selesai'])) ?></div>
                                                    </div>
                                                    <div class="detail-item">
                                                        <div class="detail-label">Status</div>
                                                        <div class="detail-value">
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
                                                        </div>
                                                    </div>
                                                    <div class="detail-item">
                                                        <div class="detail-label">Nama Peminjam</div>
                                                        <div class="detail-value"><?= htmlspecialchars($p['nama_peminjam']) ?></div>
                                                    </div>
                                                    <div class="detail-item">
                                                        <div class="detail-label">Divisi</div>
                                                        <div class="detail-value"><?= htmlspecialchars($p['divisi']) ?></div>
                                                    </div>
                                                    <div class="detail-item">
                                                        <div class="detail-label">Nota Dinas</div>
                                                        <div class="detail-value">
                                                            <a href="../uploads/nota_dinas/<?= $p['nota_dinas'] ?>" target="_blank" class="btn btn-sm btn-primary">
                                                                <i class="fas fa-download me-1"></i> Download
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <h6 class="mt-4 mb-3"><i class="fas fa-couch me-2"></i>Fasilitas Tambahan:</h6>
                                                <ul class="feature-list">
                                                    <?php
                                                    $sql_fasilitas = "SELECT * FROM peminjaman_fasilitas WHERE id_peminjaman = ?";
                                                    $stmt_f = $conn->prepare($sql_fasilitas);
                                                    $stmt_f->bind_param("i", $p['id']);
                                                    $stmt_f->execute();
                                                    $fasilitas = $stmt_f->get_result()->fetch_all(MYSQLI_ASSOC);
                                                    
                                                    if (!empty($fasilitas)) {
                                                        foreach ($fasilitas as $f) {
                                                            echo "<li><i class='fas fa-check'></i>" . 
                                                                 htmlspecialchars($f['nama_fasilitas']) . 
                                                                 " <span class='badge bg-primary'>" . $f['jumlah'] . "</span></li>";
                                                        }
                                                    } else {
                                                        echo "<li class='text-muted'><i class='fas fa-info-circle'></i>Tidak ada fasilitas tambahan</li>";
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
                                    <td colspan="5" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="fas fa-history"></i>
                                            <p>Belum ada history peminjaman</p>
                                            <a href="index.php" class="btn btn-primary mt-2">
                                                <i class="fas fa-door-open me-1"></i> Pinjam Ruangan
                                            </a>
                                        </div>
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