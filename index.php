<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Redirect ke halaman yang sesuai berdasarkan status login
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/index.php');
    } else {
        redirect('user/index.php');
    }
} else {
    redirect('login.php');
}
?>