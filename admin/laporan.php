<?php require 'header.php'; 

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

<div class="d-flex justify-content-between align-items-center mb-4 fade-in">
    <h2 class="fw-bold text-dark">Laporan Peminjaman</h2>
    <a href="cetak_laporan.php?tgl_awal=<?= $tgl_awal; ?>&tgl_akhir=<?= $tgl_akhir; ?>" target="_blank" class="btn btn-premium">
        <i class="fas fa-print me-2"></i>Cetak Laporan
    </a>
</div>

<div class="glass-card mb-4 fade-in delay-1">
    <form action="" method="get" class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Tanggal Awal</label>
            <input type="date" class="form-control" name="tgl_awal" value="<?= $tgl_awal; ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Tanggal Akhir</label>
            <input type="date" class="form-control" name="tgl_akhir" value="<?= $tgl_akhir; ?>">
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-2"></i>Filter</button>
        </div>
    </form>
</div>

<div class="glass-card fade-in delay-2">
    <div class="d-flex justify-content-end mb-3">
        <button onclick="exportExcel()" class="btn btn-success me-2">
            <i class="fas fa-file-excel me-2"></i>Export Excel
        </button>
        <button onclick="exportPDF()" class="btn btn-danger">
            <i class="fas fa-file-pdf me-2"></i>Export PDF
        </button>
    </div>
    <div class="table-responsive" id="tabel-laporan">
        <table class="table table-hover table-custom align-middle text-nowrap">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Peminjam</th>
                    <th>Judul Buku</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tanggal Kembali (Rencana)</th>
                    <th>Tanggal Kembali (Real)</th>
                    <th>Denda</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($laporan)) : ?>
                    <tr>
                        <td colspan="8" class="text-center py-4">Tidak ada data pada periode ini</td>
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
                        <td>
                            <span class="badge rounded-pill bg-<?= $row['status'] == 'dipinjam' ? 'warning' : 'success'; ?>">
                                <?= ucfirst($row['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
    function exportExcel() {
        const table = document.getElementById('tabel-laporan').querySelector('table');
        const wb = XLSX.utils.table_to_book(table, {sheet: "Laporan"});
        XLSX.writeFile(wb, 'Laporan_Peminjaman_<?= $tgl_awal; ?>_sd_<?= $tgl_akhir; ?>.xlsx');
    }

    function exportPDF() {
        const element = document.getElementById('tabel-laporan');
        const opt = {
            margin:       0.5,
            filename:     'Laporan_Peminjaman_<?= $tgl_awal; ?>_sd_<?= $tgl_akhir; ?>.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2 },
            jsPDF:        { unit: 'in', format: 'letter', orientation: 'landscape' }
        };
        html2pdf().set(opt).from(element).save();
    }
</script>

<?php require 'footer.php'; ?>
