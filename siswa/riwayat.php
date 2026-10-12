<?php 
session_start();
if (!isset($_SESSION["login"]) || $_SESSION["role"] != "peminjam") {
    header("Location: ../auth/login.php");
    exit;
}
require '../functions/functions.php';

// Logic Ajukan Pengembalian
if (isset($_GET["kembali"])) {
    $id = $_GET["kembali"];
    if (ajukan_kembali($id) > 0) {
        $success_msg = "Pengajuan pengembalian berhasil dikirim ke admin.";
    } else {
        $error_msg = "Gagal mengajukan pengembalian.";
    }
}

$id_user = $_SESSION["id_user"];
$id_user = $_SESSION["id_user"];
$history = query("SELECT * FROM peminjaman WHERE id_user = $id_user ORDER BY id_peminjaman DESC");

// Helper untuk hitung selisih hari & denda (Real-time Calculation)
function hitung_denda_sementara($tgl_kembali_rencana) {
    $now = date('Y-m-d');
    if ($now > $tgl_kembali_rencana) {
        $selisih = strtotime($now) - strtotime($tgl_kembali_rencana);
        $hari = floor($selisih / (60 * 60 * 24));
        return $hari * 1000;
    }
    return 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat - E-Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <!-- Navbar same as index -->
    <nav class="navbar navbar-expand-lg navbar-glass fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="#">E-Perpus</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="index.php">Katalog</a></li>
                    <li class="nav-item"><a class="nav-link" href="keranjang.php">Keranjang</a></li>
                    <li class="nav-item"><a class="nav-link active" href="riwayat.php">Riwayat</a></li>
                    <li class="nav-item ms-3"><a href="../auth/logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-3">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px;">
        <h2 class="fw-bold mb-4 fade-in">Riwayat Peminjaman</h2>
        
        <div class="glass-card fade-in delay-1">
             <table class="table table-custom align-middle">
                <thead>
                    <tr>
                        <th>No</th>
                    <tr>
                        <th>No</th>
                        <th>Tgl Pinjam</th>
                        <th>Batas Kembali</th>
                        <th>Tgl Kembali</th>
                        <th>Detail Buku</th>
                        <th>Status</th>
                        <th>Denda</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; foreach($history as $row) : 
                        $id_pmj = $row['id_peminjaman'];
                        $details = query("SELECT b.judul_buku FROM detail_peminjaman dp JOIN buku b ON dp.id_buku = b.id_buku WHERE dp.id_peminjaman = $id_pmj");
                    ?>
                    <tr>
                        <td><?= $i++; ?></td>
                        <td><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                        <td><?= date('d/m/Y', strtotime($row['tanggal_kembali_rencana'])); ?></td>
                        <td>
                            <?php 
                                if ($row['tanggal_kembali_real']) {
                                    echo date('d/m/Y', strtotime($row['tanggal_kembali_real']));
                                } else {
                                    echo '-';
                                }
                            ?>
                        </td>
                        <td>
                            <ul class="mb-0 ps-3 small">
                                <?php foreach($details as $d): ?>
                                    <li><?= $d['judul_buku']; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                        <td>
                             <?php 
                                $status = $row['status'];
                                $badge_class = 'secondary';
                                $status_text = $status;

                                switch ($status) {
                                    case 'diajukan':
                                        $badge_class = 'warning text-dark';
                                        $status_text = 'Menunggu Konfirmasi';
                                        break;
                                    case 'minta_kembali':
                                        $badge_class = 'info text-dark';
                                        $status_text = 'Menunggu Verifikasi';
                                        break;
                                    case 'dipinjam':
                                        $badge_class = 'primary';
                                        $status_text = 'Sedang Dipinjam';
                                        break;
                                    case 'kembali':
                                        $badge_class = 'success';
                                        $status_text = 'Selesai';
                                        break;
                                    case 'ditolak':
                                        $badge_class = 'danger';
                                        $status_text = 'Ditolak';
                                        break;
                                }
                             ?>
                             <span class="badge rounded-pill bg-<?= $badge_class; ?>">
                                <?= $status_text; ?>
                            </span>
                            
                            <?php 
                            // Tampilkan JIKA TERLAMBAT (Status Dipinjam tapi lewat tanggal)
                            if ($status == 'dipinjam' && date('Y-m-d') > $row['tanggal_kembali_rencana']): 
                                $hari_telat = floor((strtotime(date('Y-m-d')) - strtotime($row['tanggal_kembali_rencana']))/(60*60*24));
                            ?>
                                <div class="mt-1">
                                    <span class="badge bg-danger">Terlambat <?= $hari_telat; ?> Hari</span>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                                // Jika sudah selesai/kembali/ditolak, pakai denda dari DB
                                if ($status == 'kembali' || $status == 'ditolak') {
                                    echo 'Rp ' . number_format($row['denda'], 0, ',', '.');
                                } 
                                // Jika masih dipinjam/diajukan/minta_kembali, hitung estimasi
                                else {
                                    $est_denda = hitung_denda_sementara($row['tanggal_kembali_rencana']);
                                    if ($est_denda > 0) {
                                        echo '<span class="text-danger fw-bold">Rp ' . number_format($est_denda, 0, ',', '.') . ' (Est)</span>';
                                    } else {
                                        echo '-';
                                    }
                                }
                            ?>
                        </td>
                        <td>
                            <?php if($row['status'] == 'dipinjam') : ?>
                                <a href="riwayat.php?kembali=<?= $row['id_peminjaman']; ?>" class="btn btn-sm btn-outline-primary rounded-pill btn-confirm-return">
                                    <i class="fas fa-undo me-1"></i> Ajukan Kembali
                                </a>
                            <?php elseif($row['status'] == 'diajukan') : ?>
                                <span class="text-muted small fs-7">Proses Admin</span>
                            <?php elseif($row['status'] == 'minta_kembali') : ?>
                                <span class="text-info small fs-7"><i class="fas fa-clock"></i> Cek Admin</span>
                            <?php else: ?>
                                <i class="fas fa-check-circle text-success"></i>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
             </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Global Handler for Confirm Buttons using Event Delegation
        document.addEventListener('click', function(e) {
            if (e.target && (e.target.matches('.btn-confirm-return') || e.target.closest('.btn-confirm-return'))) {
                e.preventDefault();
                const btn = e.target.matches('.btn-confirm-return') ? e.target : e.target.closest('.btn-confirm-return');
                const link = btn.getAttribute('href');
                
                Swal.fire({
                    title: 'Ajukan Pengembalian?',
                    text: "Apakah Anda ingin mengajukan pengembalian untuk buku ini?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Ajukan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = link;
                    }
                });
            }
        });
    </script>

    <?php if(isset($success_msg)) : ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '<?= $success_msg; ?>',
                confirmButtonColor: '#3085d6',
            }).then(() => {
                window.location = 'riwayat.php';
            });
        </script>
    <?php endif; ?>

    <?php if(isset($error_msg)) : ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: '<?= $error_msg; ?>',
            });
        </script>
    <?php endif; ?>

    <!-- Flash Message Handler -->
    <?php if(isset($_SESSION['flash'])) : ?>
        <script>
            Swal.fire({
                icon: '<?= $_SESSION['flash']['type']; ?>',
                title: '<?= $_SESSION['flash']['title']; ?>',
                text: '<?= $_SESSION['flash']['message']; ?>',
                confirmButtonColor: '#3085d6',
            });
        </script>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

</body>
</html>
