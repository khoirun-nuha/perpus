<?php 
session_start();
if (!isset($_SESSION["login"]) || $_SESSION["role"] != "peminjam") {
    header("Location: ../auth/login.php");
    exit;
}
require '../functions/functions.php';

// Logic Tambah ke Keranjang
if (isset($_GET["add"])) {
    $id_buku = $_GET["add"];
    
    if (!isset($_SESSION["keranjang"])) {
        $_SESSION["keranjang"] = [];
    }

    $id_user = $_SESSION["id_user"];

    // 1. Cek Duplikat (Sedang dipinjam)
    $cek_pinjam = query("SELECT * FROM peminjaman p 
                         JOIN detail_peminjaman dp ON p.id_peminjaman = dp.id_peminjaman 
                         WHERE p.id_user = '$id_user' AND dp.id_buku = '$id_buku' AND p.status = 'dipinjam'");

    // 2. Hitung Total Buku (Dipinjam + Keranjang)
    $cek_item_dipinjam = mysqli_query($conn, "
        SELECT COUNT(*) as total_buku 
        FROM detail_peminjaman dp
        JOIN peminjaman p ON dp.id_peminjaman = p.id_peminjaman
        WHERE p.id_user = $id_user AND p.status = 'dipinjam'
    ");
    $data_item = mysqli_fetch_assoc($cek_item_dipinjam);
    $jumlah_dipinjam_saat_ini = $data_item['total_buku'];
    $jumlah_di_keranjang = count($_SESSION['keranjang']);

    if (count($cek_pinjam) > 0) {
        // CASE: Buku yang sama sedang dipinjam
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Gagal!',
                    text: 'Anda sedang meminjam buku ini. Harap kembalikan terlebih dahulu.',
                }).then(() => {
                    window.location = 'index.php';
                });
            });
        </script>";
    } elseif (($jumlah_dipinjam_saat_ini + $jumlah_di_keranjang) >= 3) {
        // CASE: Limit Maksimal 3 Tercapai
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Batas Maksimal!',
                    text: 'Anda hanya boleh meminjam maksimal 3 buku. (Dipinjam: $jumlah_dipinjam_saat_ini, Keranjang: $jumlah_di_keranjang)',
                }).then(() => {
                    window.location = 'index.php';
                });
            });
         </script>";
    } else {
        // CASE: Lanjut Cek Stok
        $stok_query = query("SELECT stok FROM buku WHERE id_buku = $id_buku");
        
        if (!empty($stok_query) && $stok_query[0]['stok'] > 0) {
            
            if (isset($_SESSION["keranjang"][$id_buku])) {
                // CASE: Sudah ada di keranjang
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Perhatian',
                            text: 'Buku sudah ada di keranjang!',
                        }).then(() => {
                            window.location = 'index.php';
                        });
                    });
                </script>";
            } else {
                // SUCCESS: Tambah ke Keranjang
                $_SESSION["keranjang"][$id_buku] = 1;
                
                // Cek apakah setelah ditambah, total menjadi pas 3 (untuk notifikasi warning/info)
                $total_sekarang = $jumlah_dipinjam_saat_ini + count($_SESSION["keranjang"]);

                if ($total_sekarang == 3) {
                    echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: 'info',
                                title: 'Limit Tercapai!',
                                text: 'Anda sudah memilih 3 buku (Max). Silakan konfirmasi peminjaman.',
                                confirmButtonText: 'Lihat Keranjang',
                                showCancelButton: true,
                                cancelButtonText: 'Lanjut Lihat Buku',
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location = 'keranjang.php';
                                } else {
                                    window.location = 'index.php';
                                }
                            });
                        });
                    </script>";
                } else {
                    echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Buku masuk keranjang!',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location = 'keranjang.php';
                            });
                        });
                    </script>";
                }
            }
        } else {
            // CASE: Stok Habis
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Stok Habis!',
                        text: 'Maaf, stok buku ini sedang kosong.',
                    }).then(() => {
                        window.location = 'index.php';
                    });
                });
            </script>";
        }
    }
}

// Fetch Filter Data
$kategori = query("SELECT * FROM kategori");
$rak = query("SELECT * FROM rak");

// Search & Filter Logic
// Modified: Show all books even if stock is 0
$query = "SELECT b.*, r.nama_rak FROM buku b LEFT JOIN rak r ON b.id_rak = r.id_rak WHERE 1=1";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $keyword = isset($_POST["keyword"]) ? $_POST["keyword"] : '';
    $id_kategori = isset($_POST["id_kategori"]) ? $_POST["id_kategori"] : '';
    $id_rak = isset($_POST["id_rak"]) ? $_POST["id_rak"] : '';

    $query .= " AND (b.judul_buku LIKE '%$keyword%' OR b.pengarang LIKE '%$keyword%')";

    if (!empty($id_kategori)) {
        $query .= " AND b.id_kategori = '$id_kategori'";
    }

    if (!empty($id_rak)) {
        $query .= " AND b.id_rak = '$id_rak'";
    }
}
$books = query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Peminjam - E-Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-glass fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="#"><i class="fas fa-book-reader me-2"></i>E-Perpus</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Daftar Buku</a>
                    </li>
                    <li class="nav-item position-relative">
                        <a class="nav-link" href="keranjang.php">
                            <i class="fas fa-shopping-cart"></i> Keranjang
                            <?php if(!empty($_SESSION['keranjang'])): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?= count($_SESSION['keranjang']); ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="riwayat.php">Riwayat</a>
                    </li>
                    <li class="nav-item ms-3">
                        <span class="me-3 text-muted">Hi, <?= $_SESSION['nama']; ?></span>
                        <a href="../auth/logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-3">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px;">
        <!-- Hero Section -->
        <div class="glass-card mb-5 fade-in text-center py-5">
            <h1 class="fw-bold mb-3">Katalog Buku</h1>
            <p class="text-muted mb-4">Cari dan pinjam buku favoritmu</p>
            <form action="" method="post" class="d-flex justify-content-center gap-2">
                <select name="id_kategori" class="form-select rounded-pill w-auto" style="width: 200px;" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    <?php foreach($kategori as $k) : ?>
                        <option value="<?= $k['id_kategori']; ?>" <?= (isset($_POST['id_kategori']) && $_POST['id_kategori'] == $k['id_kategori']) ? 'selected' : ''; ?>><?= $k['nama_kategori']; ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="id_rak" class="form-select rounded-pill w-auto" style="width: 150px;" onchange="this.form.submit()">
                    <option value="">Semua Rak</option>
                    <?php foreach($rak as $r) : ?>
                        <option value="<?= $r['id_rak']; ?>" <?= (isset($_POST['id_rak']) && $_POST['id_rak'] == $r['id_rak']) ? 'selected' : ''; ?>><?= $r['nama_rak']; ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="input-group w-50">
                    <input type="text" class="form-control rounded-pill-start border-end-0" placeholder="Cari judul/pengarang..." name="keyword" value="<?= isset($_POST['keyword']) ? $_POST['keyword'] : ''; ?>">
                    <button class="btn btn-primary rounded-pill-end px-4" type="submit" name="cari"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>

        <!-- Book List -->
        <div class="row g-4 fade-in delay-1">
            <?php foreach($books as $row) : ?>
            <div class="col-md-3 col-6">
                <div class="glass-card h-100 p-3 booking-card position-relative">
                    <div class="text-center mb-3">
                        <?php if($row['cover']): ?>
                            <img src="../assets/img/<?= $row['cover']; ?>" alt="Cover" class="img-fluid rounded shadow" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light rounded d-flex align-items-center justify-content-center shadow-sm" style="height: 200px;">
                                <i class="fas fa-book fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h5 class="fw-bold mb-1 text-truncate" title="<?= $row['judul_buku']; ?>"><?= $row['judul_buku']; ?></h5>
                    <p class="text-muted small mb-2"><?= $row['pengarang']; ?></p>
                    <div class="mb-2">
                        <span class="badge bg-secondary"><i class="fas fa-map-marker-alt me-1"></i><?= $row['nama_rak'] ?? 'Rak Umum'; ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <?php if($row['stok'] > 0): ?>
                            <span class="badge bg-info bg-opacity-10 text-info rounded-pill">Stok: <?= $row['stok']; ?></span>
                            <a href="index.php?add=<?= $row['id_buku']; ?>" class="btn btn-sm btn-premium rounded-pill px-3">+ Keranjang</a>
                        <?php else: ?>
                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">Stok Habis</span>
                            <button class="btn btn-sm btn-secondary rounded-pill px-3" disabled>Tidak Tersedia</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="margin-bottom: 5rem;"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>
