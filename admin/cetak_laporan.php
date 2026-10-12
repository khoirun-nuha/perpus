<?php 
require '../functions/functions.php';

// Cek sesi (opsional jika ingin diproteksi ketat, tapi biasanya cetak butuh akses langsung)
session_start();
if (!isset($_SESSION["login"]) || ($_SESSION["role"] != "admin" && $_SESSION["role"] != "petugas")) {
    header("Location: ../auth/login.php");
    exit;
}

$tgl_awal = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-01');
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');

$query = "SELECT p.*, u.nama_lengkap, b.judul_buku 
          FROM peminjaman p 
          JOIN user u ON p.id_user = u.id_user
          JOIN detail_peminjaman dp ON p.id_peminjaman = dp.id_peminjaman
          JOIN buku b ON dp.id_buku = b.id_buku
          WHERE p.tanggal_pinjam BETWEEN '$tgl_awal' AND '$tgl_akhir'
          GROUP BY p.id_peminjaman 
          ORDER BY p.tanggal_pinjam DESC";

$laporan = query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan - E-Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: sans-serif; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid black; padding-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 5px 0 0; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container mt-4">
    <div class="container mt-4">
        <div class="header d-flex align-items-center justify-content-center border-bottom border-dark pb-3 mb-4">
            <!-- Ganti src dengan logo sekolah/instansi jika ada -->
            <!-- <img src="../assets/img/logo.png" width="80" class="me-3"> -->
            <div class="text-center">
                <h3 class="fw-bold m-0">PERPUSTAKAAN DIGITAL</h3>
                <h4 class="fw-bold m-0">SEKOLAH MENEGAH KEJURUAN MERAK</h4>
                <p class="m-0 small">Jl. Raya Merak No. 123, Kota Cilegon, Banten</p>
                <p class="m-0 small">Telp: (0254) 123456 | Email: perpus@smkmerak.sch.id</p>
            </div>
        </div>

        <div class="text-center mb-4">
            <h5 class="fw-bold text-uppercase">Laporan Peminjaman Buku</h5>
            <p class="mb-0">Periode: <?= date('d/m/Y', strtotime($tgl_awal)); ?> s.d. <?= date('d/m/Y', strtotime($tgl_akhir)); ?></p>
        </div>

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Peminjam</th>
                    <th>Judul Buku</th>
                    <th>Tgl Pinjam</th>
                    <th>Tgl Kembali (Rencana)</th>
                    <th>Tgl Kembali (Real)</th>
                    <th>Denda</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($laporan)) : ?>
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada data</td>
                    </tr>
                <?php else : ?>
                    <?php $i = 1; foreach($laporan as $row) : ?>
                    <tr>
                        <td><?= $i++; ?></td>
                        <td><?= $row['nama_lengkap']; ?></td>
                        <td><?= $row['judul_buku']; ?></td>
                        <td><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                        <td><?= date('d/m/Y', strtotime($row['tanggal_kembali_rencana'])); ?></td>
                        <td><?= $row['tanggal_kembali_real'] ? date('d/m/Y', strtotime($row['tanggal_kembali_real'])) : '-'; ?></td>
                        <td>Rp. <?= number_format($row['denda'], 0, ',', '.'); ?></td>
                        <td><?= ucfirst($row['status']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="text-end mt-5">
            <p>Diketahui Oleh,</p>
            <br><br><br>
            <p class="fw-bold">Kepala Perpustakaan</p>
        </div>
    </div>
</body>
</html>
