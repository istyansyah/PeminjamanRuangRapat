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

// Ambil statistik
$sql_ruangan = "SELECT COUNT(*) as total FROM ruangan";
$total_ruangan = $conn->query($sql_ruangan)->fetch_assoc()['total'];

$sql_peminjaman = "SELECT COUNT(*) as total FROM peminjaman";
$total_peminjaman = $conn->query($sql_peminjaman)->fetch_assoc()['total'];

$sql_pending = "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'pending'";
$total_pending = $conn->query($sql_pending)->fetch_assoc()['total'];

$sql_user = "SELECT COUNT(*) as total FROM users WHERE role = 'user'";
$total_user = $conn->query($sql_user)->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Peminjaman Ruang Rapat</title>
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
        
        .stat-card {
            padding: 1.5rem;
            text-align: center;
            border-radius: 12px;
            color: white;
            position: relative;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: -20px;
            right: -20px;
            width: 70px;
            height: 70px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
        }
        
        .stat-card .card-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }
        
        .stat-card .card-text {
            font-size: 0.9rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        .stat-card i {
            position: absolute;
            bottom: 15px;
            left: 15px;
            font-size: 1.8rem;
            opacity: 0.2;
            z-index: 0;
        }
        
        .bg-primary { background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%); }
        .bg-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .bg-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        .bg-info { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); }
        
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
        
        .text-center .btn {
            border-radius: 8px;
        }
        
        .flash-message {
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border: none;
            box-shadow: var(--card-shadow);
        }
        
        .section-title {
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--dark);
            font-size: 1.2rem;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 0.5rem;
            color: var(--primary);
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
        <h1 class="page-title">Dashboard Admin</h1>
        
        <?php flash('message'); ?>
        
        <!-- Statistik -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stat-card bg-primary">
                    <i class="fas fa-door-open"></i>
                    <div class="card-body p-0">
                        <h5 class="card-title"><?= $total_ruangan ?></h5>
                        <p class="card-text">Total Ruangan</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card bg-success">
                    <i class="fas fa-calendar-check"></i>
                    <div class="card-body p-0">
                        <h5 class="card-title"><?= $total_peminjaman ?></h5>
                        <p class="card-text">Total Peminjaman</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card bg-warning">
                    <i class="fas fa-clock"></i>
                    <div class="card-body p-0">
                        <h5 class="card-title"><?= $total_pending ?></h5>
                        <p class="card-text">Peminjaman Pending</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card bg-info">
                    <i class="fas fa-users"></i>
                    <div class="card-body p-0">
                        <h5 class="card-title"><?= $total_user ?></h5>
                        <p class="card-text">Total User</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Peminjaman Terbaru -->
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-history me-2"></i>Peminjaman Terbaru</h5>
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
                            <?php
                            $sql = "SELECT p.*, r.nama_ruangan, u.nama as nama_user 
                                    FROM peminjaman p 
                                    JOIN ruangan r ON p.id_ruangan = r.id 
                                    JOIN users u ON p.id_user = u.id 
                                    ORDER BY p.created_at DESC 
                                    LIMIT 5";
                            $result = $conn->query($sql);
                            
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $status_class = '';
                                    if ($row['status'] == 'disetujui') {
                                        $status_class = 'badge bg-success';
                                    } elseif ($row['status'] == 'ditolak') {
                                        $status_class = 'badge bg-danger';
                                    } else {
                                        $status_class = 'badge bg-warning text-dark';
                                    }
                                    
                                    echo "<tr>
                                        <td>" . htmlspecialchars($row['nama_ruangan']) . "</td>
                                        <td>" . htmlspecialchars($row['nama_user']) . "</td>
                                        <td>" . date('d/m/Y', strtotime($row['tanggal'])) . "</td>
                                        <td>" . date('H:i', strtotime($row['jam_mulai'])) . " - " . date('H:i', strtotime($row['jam_selesai'])) . "</td>
                                        <td><span class='$status_class'>" . ucfirst($row['status']) . "</span></td>
                                        <td>
                                            <a href='peminjaman.php?action=detail&id=" . $row['id'] . "' class='btn btn-sm btn-info'>
                                                <i class='fas fa-eye me-1'></i> Detail
                                            </a>
                                        </td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center py-4'>Tidak ada data peminjaman</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="peminjaman.php" class="btn btn-primary">
                        <i class="fas fa-list me-1"></i> Lihat Semua Peminjaman
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>