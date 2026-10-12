<?php
session_start();
if (!isset($_SESSION["login"]) || ($_SESSION["role"] != "admin" && $_SESSION["role"] != "petugas")) {
    header("Location: ../auth/login.php");
    exit;
}
require '../functions/functions.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - E-Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: #f8f9fc; }
        .sidebar {
            background: white;
            min-height: 100vh;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            position: fixed;
            width: 250px;
            z-index: 1000;
        }
        .main-content { margin-left: 250px; padding: 2rem; }
        .nav-link { color: var(--dark) !important; padding: 0.8rem 1.5rem; border-radius: 0 50px 50px 0; margin-bottom: 0.2rem; }
        .nav-link:hover, .nav-link.active { background: #eef2ff; color: var(--primary) !important; font-weight: 600; }
        .nav-link i { width: 25px; }
    </style>
</head>
<body>

    <div class="sidebar d-flex flex-column p-0">
        <div class="p-4 text-center border-bottom">
            <h4 class="text-primary fw-bold"><i class="fas fa-book-reader me-2"></i>E-Perpus</h4>
        </div>
        <ul class="nav flex-column flex-grow-1 pt-3 pe-3">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'kategori.php' ? 'active' : ''; ?>" href="kategori.php">
                    <i class="fas fa-tags"></i> Data Kategori
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'buku.php' ? 'active' : ''; ?>" href="buku.php">
                    <i class="fas fa-book"></i> Data Buku
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'user.php' ? 'active' : ''; ?>" href="user.php">
                    <i class="fas fa-users"></i> Data User
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'transaksi.php' ? 'active' : ''; ?>" href="transaksi.php">
                    <i class="fas fa-exchange-alt"></i> Transaksi
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'active' : ''; ?>" href="laporan.php">
                    <i class="fas fa-file-alt"></i> Laporan
                </a>
            </li>
        </ul>
        <div class="p-3 border-top">
            <a href="../auth/logout.php" class="nav-link text-danger w-100"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
