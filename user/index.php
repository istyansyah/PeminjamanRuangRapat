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

// Ambil data ruangan
$sql = "SELECT * FROM ruangan ORDER BY nama_ruangan";
$result = $conn->query($sql);
$ruangan = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User - Sistem Peminjaman Ruang Rapat</title>
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
            height: 100%;
        }
        
        .card:hover {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .ruangan-card {
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .ruangan-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .card-title {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }
        
        .card-text {
            color: var(--secondary);
            margin-bottom: 1.5rem;
        }
        
        .card-text strong {
            color: var(--dark);
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
        
        .btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.8rem;
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
        
        .ruangan-icon {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 1rem;
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
        
        .schedule-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .schedule-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .schedule-list li:last-child {
            border-bottom: none;
        }
        
        .badge {
            padding: 0.5rem 0.8rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.75rem;
        }
        
        .badge-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .badge-success {
            background-color: var(--success);
            color: white;
        }
        
        .badge-warning {
            background-color: var(--warning);
            color: white;
        }
        
        .section-title {
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--dark);
            font-size: 1.1rem;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 0.5rem;
            color: var(--primary);
        }
        
        .empty-state {
            text-align: center;
            padding: 1.5rem;
            color: var(--secondary);
        }
        
        .empty-state i {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #cbd5e1;
        }
        
        .text-muted {
            font-size: 0.85rem;
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
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="history.php">
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
        <h1 class="page-title">Daftar Ruang Rapat</h1>
        
        <?php flash('message'); ?>
        
        <div class="row">
            <?php if (!empty($ruangan)): ?>
                <?php foreach ($ruangan as $r): ?>
                <div class="col-md-4 mb-4">
                    <div class="card ruangan-card">
                        <div class="card-body text-center">
                            <div class="ruangan-icon">
                                <i class="fas fa-door-open"></i>
                            </div>
                            <h5 class="card-title"><?= htmlspecialchars($r['nama_ruangan']) ?></h5>
                            <p class="card-text">
                                <strong><i class="fas fa-users me-1"></i>Kapasitas:</strong> <?= $r['kapasitas'] ?> orang<br>
                                <strong><i class="fas fa-map-marker-alt me-1"></i>Lokasi:</strong> <?= htmlspecialchars($r['lokasi']) ?>
                            </p>
                            <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#detailModal<?= $r['id'] ?>">
                                <i class="fas fa-info-circle me-1"></i> Lihat Detail
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal Detail -->
                <div class="modal fade" id="detailModal<?= $r['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="fas fa-door-open me-2"></i><?= htmlspecialchars($r['nama_ruangan']) ?>
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <p><strong><i class="fas fa-users me-1"></i>Kapasitas:</strong> <?= $r['kapasitas'] ?> orang</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong><i class="fas fa-map-marker-alt me-1"></i>Lokasi:</strong> <?= htmlspecialchars($r['lokasi']) ?></p>
                                    </div>
                                </div>
                                
                                <h6 class="section-title">
                                    <i class="fas fa-couch"></i>Fasilitas
                                </h6>
                                <ul class="feature-list">
                                    <?php
                                    $sql_fasilitas = "SELECT * FROM fasilitas WHERE id_ruangan = ?";
                                    $stmt = $conn->prepare($sql_fasilitas);
                                    $stmt->bind_param("i", $r['id']);
                                    $stmt->execute();
                                    $fasilitas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                    
                                    if (!empty($fasilitas)) {
                                        foreach ($fasilitas as $f) {
                                            echo "<li><i class='fas fa-check'></i>" . 
                                                 htmlspecialchars($f['nama_fasilitas']) . 
                                                 " <span class='badge badge-primary'>" . $f['jumlah'] . "</span></li>";
                                        }
                                    } else {
                                        echo "<li class='text-muted'><i class='fas fa-info-circle'></i>Tidak ada fasilitas khusus</li>";
                                    }
                                    ?>
                                </ul>
                                
                                <h6 class="section-title mt-4">
    <i class="fas fa-calendar-day"></i>Jadwal Rapat Mendatang
</h6>
<ul class="schedule-list">
    <?php
    // Ambil jadwal peminjaman yang akan datang (7 hari ke depan)
    $start_date = date('Y-m-d');
    $end_date = date('Y-m-d', strtotime('+7 days'));
    
    $sql = "SELECT p.*, u.nama as nama_user 
            FROM peminjaman p 
            JOIN users u ON p.id_user = u.id 
            WHERE p.id_ruangan = ? 
            AND p.status = 'disetujui'
            AND p.tanggal BETWEEN ? AND ?
            ORDER BY p.tanggal ASC, p.jam_mulai ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $r['id'], $start_date, $end_date);
    $stmt->execute();
    $jadwal = $stmt->get_result();
    
    if ($jadwal->num_rows > 0) {
        while ($j = $jadwal->fetch_assoc()) {
            $tanggal_formatted = date('d/m/Y', strtotime($j['tanggal']));
            $waktu_formatted = date('H:i', strtotime($j['jam_mulai'])) . " - " . date('H:i', strtotime($j['jam_selesai']));
            
            echo "<li>
    <div class='d-flex justify-content-between align-items-start mb-1'>
        <span class='fw-medium'>$tanggal_formatted</span>
        <span class='badge badge-primary'>$waktu_formatted</span>
    </div>
    <div class='d-flex justify-content-between align-items-center'>
        <small class='text-muted'>" . htmlspecialchars($j['nama_user']) . "</small>
        <div class='mx-2'></div> <!-- Spacer -->
        <small class='text-muted text-truncate' style='max-width: 150px;' title='" . htmlspecialchars($j['agenda']) . "'>" . htmlspecialchars($j['agenda']) . "</small>
    </div>
</li>";
        }
    } else {
        echo "<li class='text-muted'>
                <i class='fas fa-calendar-check me-2'></i>Tidak ada peminjaman dalam 7 hari ke depan
              </li>";
    }
    ?>
</ul>
                            </div>
                            <div class="modal-footer">
                                <a href="pinjam.php?ruangan=<?= $r['id'] ?>" class="btn btn-primary">
                                    <i class="fas fa-calendar-plus me-1"></i> Pinjam Ruangan
                                </a>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i> Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="empty-state">
                                <i class="fas fa-door-open"></i>
                                <p>Tidak ada ruangan yang tersedia</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add hover effect to room cards
        document.querySelectorAll('.ruangan-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 8px 15px rgba(0, 0, 0, 0.1)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 1px 3px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.1)';
            });
        });
    </script>
</body>
</html>