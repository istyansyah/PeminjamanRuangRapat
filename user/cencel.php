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

// Proses pembatalan peminjaman
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];
    
    // Cek apakah peminjaman milik user yang login
    $sql = "SELECT * FROM peminjaman WHERE id = ? AND id_user = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $peminjaman = $result->fetch_assoc();
        
        // Hanya bisa membatalkan jika status masih pending
        if ($peminjaman['status'] == 'pending') {
            // Hapus file nota dinas
            $nota_path = '../uploads/nota_dinas/' . $peminjaman['nota_dinas'];
            if (file_exists($nota_path)) {
                unlink($nota_path);
            }
            
            // Hapus data fasilitas tambahan
            $sql_delete_fasilitas = "DELETE FROM peminjaman_fasilitas WHERE id_peminjaman = ?";
            $stmt_f = $conn->prepare($sql_delete_fasilitas);
            $stmt_f->bind_param("i", $id);
            $stmt_f->execute();
            
            // Hapus data peminjaman
            $sql_delete = "DELETE FROM peminjaman WHERE id = ?";
            $stmt_d = $conn->prepare($sql_delete);
            $stmt_d->bind_param("i", $id);
            
            if ($stmt_d->execute()) {
                flash('message', 'Peminjaman berhasil dibatalkan', 'alert alert-success');
            } else {
                flash('message', 'Gagal membatalkan peminjaman', 'alert alert-danger');
            }
        } else {
            flash('message', 'Tidak dapat membatalkan peminjaman yang sudah diproses', 'alert alert-danger');
        }
    } else {
        flash('message', 'Peminjaman tidak ditemukan', 'alert alert-danger');
    }
}

redirect('history.php');
?>