<?php
require_once 'config.php';
require_once 'functions.php';

// Cek jika user sudah login, redirect ke dashboard sesuai role
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('../admin/index.php');
    } else {
        redirect('../user/index.php');
    }
}
?>