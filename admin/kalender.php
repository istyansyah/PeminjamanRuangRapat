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

// Ambil data peminjaman untuk kalender
$sql = "SELECT p.*, r.nama_ruangan, u.nama as nama_user 
        FROM peminjaman p 
        JOIN ruangan r ON p.id_ruangan = r.id 
        JOIN users u ON p.id_user = u.id 
        WHERE p.status = 'disetujui'";
$result = $conn->query($sql);
$events = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $events[] = [
            'title' => $row['nama_ruangan'] . ' - ' . $row['nama_user'],
            'start' => $row['tanggal'] . 'T' . $row['jam_mulai'],
            'end' => $row['tanggal'] . 'T' . $row['jam_selesai'],
            'url' => 'peminjaman.php?action=detail&id=' . $row['id'],
            'extendedProps' => [
                'agenda' => $row['agenda'],
                'ruangan' => $row['nama_ruangan'],
                'peminjam' => $row['nama_user']
            ]
        ];
    }
}

$events_json = json_encode($events);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalender - Sistem Peminjaman Ruang Rapat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
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
            --fc-border-color: #e2e8f0;
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
        
        .card-body {
            padding: 1.5rem;
        }
        
        /* FullCalendar Customization */
        #calendar {
            font-family: 'Inter', sans-serif;
        }
        
        .fc .fc-toolbar {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.5rem !important;
        }
        
        .fc .fc-toolbar-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        .fc .fc-button {
            background-color: white;
            border: 1px solid var(--fc-border-color);
            color: var(--secondary);
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .fc .fc-button:hover {
            background-color: var(--primary-light);
            color: var(--primary);
            border-color: var(--primary);
        }
        
        .fc .fc-button-primary:not(:disabled).fc-button-active {
            background-color: var(--primary);
            border-color: var(--primary);
            color: white;
        }
        
        .fc .fc-col-header-cell {
            background-color: var(--primary-light);
            padding: 0.75rem 0;
        }
        
        .fc .fc-col-header-cell-cushion {
            color: var(--dark);
            font-weight: 600;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .fc .fc-daygrid-day-number {
            color: var(--dark);
            font-weight: 500;
            text-decoration: none;
            padding: 0.5rem;
        }
        
        .fc .fc-daygrid-day.fc-day-today {
            background-color: var(--primary-light);
        }
        
        .fc .fc-event {
            border: none;
            border-radius: 6px;
            padding: 0.25rem 0.5rem;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .fc .fc-event:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        
        .fc-event-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .fc-event-success {
            background-color: var(--success);
            border-color: var(--success);
        }
        
        .fc-event-info {
            background-color: var(--info);
            border-color: var(--info);
        }
        
        .fc-event-warning {
            background-color: var(--warning);
            border-color: var(--warning);
        }
        
        @media (min-width: 768px) {
            .fc .fc-toolbar {
                flex-direction: row;
                align-items: center;
            }
        }
        
        .calendar-legend {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1.5rem;
            padding: 1rem;
            background-color: var(--primary-light);
            border-radius: 8px;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }
        
        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 4px;
        }
        
        .fc-daygrid-event-dot {
            display: none;
        }
        
        .custom-event-tooltip {
            position: absolute;
            background: white;
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            max-width: 300px;
            border: 1px solid #e2e8f0;
        }
        
        .custom-event-tooltip h6 {
            margin: 0 0 0.5rem 0;
            color: var(--dark);
            font-weight: 600;
        }
        
        .custom-event-tooltip p {
            margin: 0.25rem 0;
            font-size: 0.9rem;
            color: var(--secondary);
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
                        <a class="nav-link" href="peminjaman.php">
                            <i class="fas fa-calendar-check me-1"></i> Peminjaman
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="kalender.php">
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
        <h1 class="page-title">Kalender Peminjaman Ruangan</h1>
        
        <div class="card">
            <div class="card-body">
                <div id="calendar"></div>
                
                <div class="calendar-legend">
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: var(--primary);"></div>
                        <span>Peminjaman Ruangan</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: var(--success);"></div>
                        <span>Peminjaman Disetujui</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/id.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const events = <?= $events_json ?>;
            
            // Add random colors to events for variety
            const eventColors = [
                'var(--primary)',
                'var(--success)',
                'var(--info)',
                'var(--warning)'
            ];
            
            events.forEach(event => {
                event.color = eventColors[Math.floor(Math.random() * eventColors.length)];
                event.textColor = 'white';
            });
            
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                buttonText: {
                    today: 'Hari Ini',
                    month: 'Bulan',
                    week: 'Minggu',
                    day: 'Hari'
                },
                events: events,
                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    if (info.event.url) {
                        window.open(info.event.url, '_blank');
                    }
                },
                eventDidMount: function(info) {
                    // Add custom tooltip
                    const tooltip = document.createElement('div');
                    tooltip.className = 'custom-event-tooltip';
                    tooltip.innerHTML = `
                        <h6>${info.event.extendedProps.ruangan}</h6>
                        <p><strong>Agenda:</strong> ${info.event.extendedProps.agenda || 'Tidak ada agenda'}</p>
                        <p><strong>Peminjam:</strong> ${info.event.extendedProps.peminjam}</p>
                        <p><strong>Waktu:</strong> ${info.event.start.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})} - ${info.event.end ? info.event.end.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : ''}</p>
                    `;
                    tooltip.style.display = 'none';
                    document.body.appendChild(tooltip);
                    
                    info.el.addEventListener('mouseenter', function(e) {
                        tooltip.style.display = 'block';
                        tooltip.style.left = (e.pageX + 10) + 'px';
                        tooltip.style.top = (e.pageY + 10) + 'px';
                    });
                    
                    info.el.addEventListener('mouseleave', function() {
                        tooltip.style.display = 'none';
                    });
                    
                    info.el.addEventListener('mousemove', function(e) {
                        tooltip.style.left = (e.pageX + 10) + 'px';
                        tooltip.style.top = (e.pageY + 10) + 'px';
                    });
                },
                dayMaxEventRows: 3,
                views: {
                    dayGridMonth: {
                        dayMaxEventRows: 3
                    }
                }
            });
            
            calendar.render();
        });
    </script>
</body>
</html>