<?php
// Fungsi untuk redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Fungsi untuk menampilkan pesan flash
function flash($name, $message = '', $class = 'alert alert-success') {
    if (!empty($message)) {
        $_SESSION[$name] = $message;
        $_SESSION[$name . '_class'] = $class;
    } else if (!empty($_SESSION[$name])) {
        $class = !empty($_SESSION[$name . '_class']) ? $_SESSION[$name . '_class'] : 'alert alert-success';
        echo '<div class="' . $class . '" id="msg-flash">' . $_SESSION[$name] . '</div>';
        unset($_SESSION[$name]);
        unset($_SESSION[$name . '_class']);
    }
}

// Fungsi untuk cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk cek role admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';
}

// Fungsi untuk validasi waktu tidak bentrok
function isWaktuValid($conn, $id_ruangan, $tanggal, $jam_mulai, $jam_selesai, $exclude_id = null) {
    $sql = "SELECT * FROM peminjaman 
            WHERE id_ruangan = ? 
            AND tanggal = ? 
            AND status = 'disetujui'
            AND (
                (jam_mulai <= ? AND jam_selesai > ?) OR
                (jam_mulai < ? AND jam_selesai >= ?) OR
                (jam_mulai >= ? AND jam_selesai <= ?)
            )";
    
    if ($exclude_id) {
        $sql .= " AND id != ?";
    }
    
    $stmt = $conn->prepare($sql);
    
    if ($exclude_id) {
        $stmt->bind_param("isssssssi", $id_ruangan, $tanggal, $jam_mulai, $jam_mulai, $jam_selesai, $jam_selesai, $jam_mulai, $jam_selesai, $exclude_id);
    } else {
        $stmt->bind_param("isssssss", $id_ruangan, $tanggal, $jam_mulai, $jam_mulai, $jam_selesai, $jam_selesai, $jam_mulai, $jam_selesai);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows == 0;
}

// Fungsi untuk mendapatkan jadwal ruangan
function getJadwalRuangan($conn, $id_ruangan, $tanggal = null) {
    $sql = "SELECT p.*, u.nama as nama_user 
            FROM peminjaman p 
            JOIN users u ON p.id_user = u.id 
            WHERE p.id_ruangan = ? 
            AND p.status = 'disetujui'";
    
    $params = [$id_ruangan];
    $types = "i";
    
    if ($tanggal) {
        $sql .= " AND p.tanggal = ?";
        $params[] = $tanggal;
        $types .= "s";
    }
    
    $sql .= " ORDER BY p.tanggal, p.jam_mulai";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    
    return $stmt->get_result();
}
?>