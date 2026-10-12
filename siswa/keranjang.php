<?php 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kode Anda di bawah sini...
session_start();
if (!isset($_SESSION["login"]) || $_SESSION["role"] != "peminjam") {
    header("Location: ../auth/login.php");
    exit;
}
require '../functions/functions.php';

// Remove Item logic
if (isset($_GET["hapus"])) {
    $id_buku = $_GET["hapus"];
    unset($_SESSION["keranjang"][$id_buku]);
    header("Location: keranjang.php");
    exit;
}

// Checkout Logic
if (isset($_POST["checkout"])) {
    $hasil_checkout = checkout($_SESSION["id_user"], $_SESSION["keranjang"]);
    if ($hasil_checkout === true) {
        unset($_SESSION["keranjang"]);
        $show_success_alert = true;
    } else {
        $error_msg = $hasil_checkout;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - E-Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-glass fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="#">E-Perpus</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Katalog</a></li>
                    <li class="nav-item"><a class="nav-link active" href="keranjang.php">Keranjang</a></li>
                    <li class="nav-item"><a class="nav-link" href="riwayat.php">Riwayat</a></li>
                    <li class="nav-item ms-3"><a href="../auth/logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-3">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px;">
        <h2 class="fw-bold mb-4">Keranjang Peminjaman</h2>

        <div class="alert alert-warning d-flex align-items-center mb-4 fade-in" role="alert">
            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
            <div>
                <strong>Perhatian:</strong>
                <ul class="mb-0 ps-3">
                    <li>Batas peminjaman buku adalah <strong>7 hari</strong>.</li>
                    <li>Keterlambatan pengembalian akan dikenakan denda sebesar <strong>Rp 1.000</strong> per buku sand per hari.</li>
                </ul>
            </div>
        </div>

        <div class="glass-card mb-4">
            <table class="table table-custom align-middle">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Buku</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                if(empty($_SESSION["keranjang"])) {
                    echo "<tr><td colspan='4' class='text-center text-muted py-5'>
                            <p class='mb-3'>Keranjang kosong</p>
                            <a href='index.php' class='btn btn-premium rounded-pill px-4'>
                                <i class='fas fa-search me-2'></i>Cari Buku
                            </a>
                          </td></tr>";
                } else {
                    $no = 1;
                    foreach ($_SESSION["keranjang"] as $id_buku => $jumlah) : 
                        $buku = query("SELECT * FROM buku WHERE id_buku = $id_buku")[0];
                ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= $buku['judul_buku']; ?> (<?= $buku['pengarang']; ?>)</td>
                    <td>
                        <span class="badge bg-<?= $buku['stok'] > 0 ? 'info' : 'danger'; ?>">
                            <?= $buku['stok']; ?>
                        </span>
                    </td>
                    <td>
                        <a href="keranjang.php?hapus=<?= $id_buku; ?>" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Hapus</a>
                    </td>
                </tr>
                <?php endforeach; 
                } ?>
            </tbody>
        </table>
    </div>

    <?php if(!empty($_SESSION["keranjang"])): ?>
    <form action="" method="post" class="text-end">
        <a href="index.php" class="btn btn-outline-primary btn-lg rounded-pill px-4 me-2">
            <i class="fas fa-plus me-2"></i>Tambah Buku Lain
        </a>
        <input type="hidden" name="checkout" value="1">
        <button class="btn btn-success btn-lg rounded-pill px-5" id="btn-checkout">
            <i class="fas fa-paper-plane me-2"></i> Ajukan Peminjaman
        </button>
    </form>
    <?php endif; ?>
    
    <script>
        document.querySelector('#btn-checkout')?.addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Ajukan Peminjaman?',
                text: "Buku harus dikembalikan dalam 7 hari. Denda keterlambatan Rp 1.000/hari.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Ajukan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.closest('form').submit();
                }
            });
        });
    </script>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php if(isset($show_success_alert) && $show_success_alert) : ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Peminjaman berhasil diajukan. Silahkan tunggu konfirmasi admin.',
                confirmButtonText: 'Lihat Riwayat',
                allowOutsideClick: false
            }).then((result) => {
                window.location.href = 'riwayat.php';
            });
        </script>
    <?php endif; ?>

    <?php if(isset($error_msg)) : ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Peminjaman Gagal',
                text: '<?= addslashes($error_msg); ?>',
                confirmButtonColor: '#d33'
            });
        </script>
    <?php endif; ?>
</body>
</html>
