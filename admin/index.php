<?php require 'header.php'; 

// Hitung Data
$jml_buku = count(query("SELECT * FROM buku"));
$jml_anggota = count(query("SELECT * FROM user WHERE role = 'peminjam'"));
$jml_peminjaman = count(query("SELECT * FROM peminjaman WHERE status = 'dipinjam'"));

$transaksi_terbaru = query("SELECT p.*, u.nama_lengkap, b.judul_buku 
                            FROM peminjaman p 
                            JOIN user u ON p.id_user = u.id_user
                            JOIN detail_peminjaman dp ON p.id_peminjaman = dp.id_peminjaman
                            JOIN buku b ON dp.id_buku = b.id_buku
                            GROUP BY p.id_peminjaman
                            ORDER BY p.tanggal_pinjam DESC LIMIT 5");
?>

<div class="row fade-in">
    <div class="col-md-4 mb-4">
        <div class="glass-card text-center p-4 text-primary">
            <i class="fas fa-book fa-3x mb-3"></i>
            <h3><?= $jml_buku; ?></h3>
            <p class="mb-0">Total Buku</p>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="glass-card text-center p-4 text-success">
            <i class="fas fa-users fa-3x mb-3"></i>
            <h3><?= $jml_anggota; ?></h3>
            <p class="mb-0">Total Anggota</p>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="glass-card text-center p-4 text-warning">
            <i class="fas fa-exchange-alt fa-3x mb-3"></i>
            <h3><?= $jml_peminjaman; ?></h3>
            <p class="mb-0">Peminjaman Aktif</p>
        </div>
    </div>
</div>

<div class="glass-card mt-4 fade-in delay-1">
    <h4 class="fw-bold mb-4">Transaksi Terbaru</h4>
    <div class="table-responsive">
        <table class="table table-hover table-custom align-middle text-nowrap">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Peminjam</th>
                    <th>Tanggal Pinjam</th>
                    <th>Batas Kembali</th>
                    <th>Tgl Kembali</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach($transaksi_terbaru as $row) : ?>
                <tr>
                    <td><?= $i++; ?></td>
                    <td><?= $row['nama_lengkap']; ?></td>
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
                        <span class="badge rounded-pill bg-<?= $row['status'] == 'dipinjam' ? 'warning' : 'success'; ?>">
                            <?= ucfirst($row['status']); ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require 'footer.php'; ?>
