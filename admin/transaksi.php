<?php require 'header.php'; 

// --- Logic Action ---

// 1. Terima Peminjaman (diajukan -> dipinjam)
if (isset($_GET["terima"])) {
    $id = $_GET["terima"];
    if (terima_pinjaman($id) > 0) {
        $_SESSION['flash'] = [
            'icon' => 'success',
            'title' => 'Berhasil!',
            'text' => 'Peminjaman disetujui.'
        ];
    } else {
        $_SESSION['flash'] = [
            'icon' => 'error',
            'title' => 'Gagal',
            'text' => 'Terjadi kesalahan sistem.'
        ];
    }
    echo "<script>document.location.href = 'transaksi.php';</script>";
}

// 2. Tolak Peminjaman (diajukan -> ditolak)
if (isset($_GET["tolak"])) {
    $id = $_GET["tolak"];
    if (tolak_pinjaman($id) > 0) {
         $_SESSION['flash'] = [
            'icon' => 'info',
            'title' => 'Ditolak',
            'text' => 'Peminjaman ditolak & stok dikembalikan.'
        ];
    } else {
         $_SESSION['flash'] = [
            'icon' => 'error',
            'title' => 'Gagal',
            'text' => 'Gagal menolak peminjaman.'
        ];
    }
    echo "<script>document.location.href = 'transaksi.php';</script>";
}

// 3. Konfirmasi Pengembalian (minta_kembali -> kembali)
if (isset($_GET["konfirmasi_kembali"])) {
    $id = $_GET["konfirmasi_kembali"];
    if (kembalikan_buku($id) > 0) {
        $_SESSION['flash'] = [
            'icon' => 'success',
            'title' => 'Buku Dikembalikan',
            'text' => 'Pengembalian berhasil dikonfirmasi.'
        ];
    } else {
        $_SESSION['flash'] = [
            'icon' => 'error',
            'title' => 'Gagal',
            'text' => 'Gagal memproses pengembalian.'
        ];
    }
    echo "<script>document.location.href = 'transaksi.php';</script>";
}

// Query Tabs
$transaksi_diajukan = query("SELECT p.*, u.nama_lengkap FROM peminjaman p JOIN user u ON p.id_user = u.id_user WHERE p.status = 'diajukan' ORDER BY p.tanggal_pinjam ASC");
$transaksi_dipinjam = query("SELECT p.*, u.nama_lengkap FROM peminjaman p JOIN user u ON p.id_user = u.id_user WHERE p.status = 'dipinjam' ORDER BY p.tanggal_pinjam ASC");
$transaksi_minta_kembali = query("SELECT p.*, u.nama_lengkap FROM peminjaman p JOIN user u ON p.id_user = u.id_user WHERE p.status = 'minta_kembali' ORDER BY p.tanggal_pinjam ASC");
$transaksi_riwayat = query("SELECT p.*, u.nama_lengkap FROM peminjaman p JOIN user u ON p.id_user = u.id_user WHERE p.status IN ('kembali', 'ditolak') ORDER BY p.tanggal_pinjam DESC LIMIT 50");

?>

<div class="d-flex justify-content-between align-items-center mb-4 fade-in">
    <h2 class="fw-bold text-dark">Manajemen Transaksi</h2>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-pills mb-4 gap-2" id="pills-tab" role="tablist">
  <li class="nav-item">
    <button class="nav-link active bg-white shadow-sm" id="tab-diajukan" data-bs-toggle="pill" data-bs-target="#content-diajukan" type="button">
        <i class="fas fa-clock text-warning me-2"></i>Menunggu Persetujuan 
        <?php if(count($transaksi_diajukan) > 0): ?>
            <span class="badge bg-danger rounded-pill ms-1"><?= count($transaksi_diajukan); ?></span>
        <?php endif; ?>
    </button>
  </li>
  <li class="nav-item">
    <button class="nav-link bg-white shadow-sm" id="tab-minta-kembali" data-bs-toggle="pill" data-bs-target="#content-minta-kembali" type="button">
        <i class="fas fa-undo text-info me-2"></i>Permintaan Kembali
        <?php if(count($transaksi_minta_kembali) > 0): ?>
            <span class="badge bg-danger rounded-pill ms-1"><?= count($transaksi_minta_kembali); ?></span>
        <?php endif; ?>
    </button>
  </li>
  <li class="nav-item">
    <button class="nav-link bg-white shadow-sm" id="tab-dipinjam" data-bs-toggle="pill" data-bs-target="#content-dipinjam" type="button">
        <i class="fas fa-book-reader text-primary me-2"></i>Sedang Dipinjam
    </button>
  </li>
  <li class="nav-item">
    <button class="nav-link bg-white shadow-sm" id="tab-riwayat" data-bs-toggle="pill" data-bs-target="#content-riwayat" type="button">
        <i class="fas fa-history text-secondary me-2"></i>Riwayat
    </button>
  </li>
</ul>

<div class="tab-content" id="pills-tabContent">
    
    <!-- TAB 1: PENGAJUAN PINJAM -->
    <div class="tab-pane fade show active" id="content-diajukan">
        <div class="glass-card">
            <h5 class="fw-bold mb-3 text-warning">Peminjaman Baru (Perlu Konfirmasi)</h5>
            <?php renderTable($transaksi_diajukan, 'diajukan'); ?>
        </div>
    </div>

    <!-- TAB 2: PERMINTAAN KEMBALI -->
    <div class="tab-pane fade" id="content-minta-kembali">
         <div class="glass-card">
            <h5 class="fw-bold mb-3 text-info">Siswa Mengajukan Pengembalian w/ Barang</h5>
            <?php renderTable($transaksi_minta_kembali, 'minta_kembali'); ?>
        </div>
    </div>

    <!-- TAB 3: DIPINJAM -->
    <div class="tab-pane fade" id="content-dipinjam">
         <div class="glass-card">
            <h5 class="fw-bold mb-3 text-primary">Buku Sedang Dipegang Siswa</h5>
            <?php renderTable($transaksi_dipinjam, 'dipinjam'); ?>
        </div>
    </div>

    <!-- TAB 4: RIWAYAT -->
    <div class="tab-pane fade" id="content-riwayat">
         <div class="glass-card">
            <h5 class="fw-bold mb-3 text-secondary">Riwayat Transaksi</h5>
            <?php renderTable($transaksi_riwayat, 'riwayat'); ?>
        </div>
    </div>

</div>

<?php 
// Helper Function untuk Render Table
function renderTable($data, $type) {
    if(empty($data)) {
        echo "<div class='text-center text-muted py-5'><i class='fas fa-inbox fa-3x mb-3'></i><p>Tidak ada data</p></div>";
        return;
    }
?>
    <div class="table-responsive">
        <table class="table table-hover table-custom align-middle text-nowrap">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Peminjam</th>
                    <th>Detail Buku</th>
                    <th>Tgl Pinjam</th>
                    <th>Batas Kembali</th>
                    <th>Tgl Kembali</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach($data as $row) : 
                    // Fetch Details
                    $id_pmj = $row['id_peminjaman'];
                    $details = query_simple("SELECT b.judul_buku FROM detail_peminjaman dp JOIN buku b ON dp.id_buku = b.id_buku WHERE dp.id_peminjaman = $id_pmj");
                ?>
                <tr>
                    <td><?= $i++; ?></td>
                    <td><?= $row['nama_lengkap']; ?></td>
                    <td>
                        <ul class="mb-0 ps-3 small">
                            <?php foreach($details as $d): ?>
                                <li><?= $d['judul_buku']; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </td>
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
                        <?php 
                            $badge = match($row['status']) {
                                'diajukan' => 'warning',
                                'minta_kembali' => 'info',
                                'dipinjam' => 'primary',
                                'kembali' => 'success',
                                'ditolak' => 'danger',
                                default => 'secondary'
                            };
                        ?>
                        <span class="badge bg-<?= $badge; ?>"><?= ucfirst(str_replace('_', ' ', $row['status'])); ?></span>
                    </td>
                    <td>
                        <?php if($type == 'diajukan'): ?>
                            <a href="transaksi.php?terima=<?= $row['id_peminjaman']; ?>" class="btn btn-sm btn-success me-1 btn-action-terima"><i class="fas fa-check"></i> Setuju</a>
                            <a href="transaksi.php?tolak=<?= $row['id_peminjaman']; ?>" class="btn btn-sm btn-danger btn-action-tolak"><i class="fas fa-times"></i> Tolak</a>
                        
                        <?php elseif($type == 'minta_kembali'): ?>
                            <a href="transaksi.php?konfirmasi_kembali=<?= $row['id_peminjaman']; ?>" class="btn btn-sm btn-info text-white btn-action-kembali"><i class="fas fa-check-double"></i> Terima Buku</a>
                        
                        <?php else: ?>
                            <span class="text-muted small">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php } 

// Helper lokal karena query() di functions return array, kita butuh cepat
function query_simple($q) {
    global $conn;
    $res = mysqli_query($conn, $q);
    $rows = [];
    while($r = mysqli_fetch_assoc($res)) $rows[] = $r;
    return $rows;
}
?>

<script>
    // SweetAlert Flash Handling
    <?php if(isset($_SESSION['flash'])): ?>
        Swal.fire({
            icon: '<?= $_SESSION['flash']['icon']; ?>',
            title: '<?= $_SESSION['flash']['title']; ?>',
            text: '<?= $_SESSION['flash']['text']; ?>',
            showConfirmButton: false,
            timer: 2000
        });
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>
</script>

<script>
    // Global Confirmation Handler
    document.addEventListener('click', function(e) {
        const target = e.target.closest('a');
        if (!target) return;

        if (target.classList.contains('btn-action-terima')) {
            e.preventDefault();
            Swal.fire({
                title: 'Setujui Peminjaman?',
                text: "Status akan berubah menjadi 'Dipinjam'.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Setujui',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if(result.isConfirmed) window.location.href = target.href;
            });
        }
        
        if (target.classList.contains('btn-action-tolak')) {
            e.preventDefault();
            Swal.fire({
                title: 'Tolak Peminjaman?',
                text: "Stok buku akan dikembalikan otomatis.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Tolak',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if(result.isConfirmed) window.location.href = target.href;
            });
        }

        if (target.classList.contains('btn-action-kembali')) {
            e.preventDefault();
            Swal.fire({
                title: 'Konfirmasi Pengembalian?',
                text: "Pastikan fisik buku sudah diterima dengan baik.",
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#0dcaf0',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Terima Buku',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if(result.isConfirmed) window.location.href = target.href;
            });
        }
    });
</script>

<?php require 'footer.php'; ?>
