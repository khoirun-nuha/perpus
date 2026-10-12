<?php require 'header.php'; 

// Logic POST untuk tambah/ubah/hapus
if (isset($_POST["tambah"])) {
    if (tambah_buku($_POST) > 0) {
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Buku berhasil ditambahkan!'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menambahkan buku!'];
    }
    header("Location: buku.php");
    exit;
}

if (isset($_POST["ubah"])) {
    $result = ubah_buku($_POST);
    if ($result > 0 && is_int($result)) {
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Buku berhasil diubah!'];
    } else {
        // If result is string (error message) or 0 (no changes/fail)
        $msg = is_string($result) ? $result : 'Gagal mengubah data! (Tidak ada perubahan atau error)';
        $_SESSION['flash'] = ['type' => 'error', 'message' => $msg];
    }
    header("Location: buku.php");
    exit;
}

if (isset($_GET["hapus"])) {
    $result = hapus_buku($_GET["hapus"]);
    if ($result > 0 && is_int($result)) {
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Buku berhasil dihapus!'];
    } else {
        $msg = is_string($result) ? $result : 'Gagal menghapus buku!';
        $_SESSION['flash'] = ['type' => 'error', 'message' => $msg];
    }
    header("Location: buku.php");
    exit;
}

// Fetch buku with kategori using LEFT JOIN
$buku = query("SELECT b.*, k.nama_kategori, r.nama_rak 
               FROM buku b 
               LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
               LEFT JOIN rak r ON b.id_rak = r.id_rak
               ORDER BY b.id_buku DESC");

$kategori = query("SELECT * FROM kategori");
$rak = query("SELECT * FROM rak");
?>

<div class="d-flex justify-content-between align-items-center mb-4 fade-in">
    <h2 class="fw-bold text-dark">Kelola Data Buku</h2>
    <button class="btn btn-premium" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="fas fa-plus me-2"></i>Tambah Buku
    </button>
</div>

<div class="glass-card fade-in delay-1">
    <div class="table-responsive">
        <table class="table table-hover table-custom align-middle data-table text-nowrap">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Cover</th>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Pengarang</th>
                    <th>Stok</th>
                    <th>Lokasi Rak</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach($buku as $row) : ?>
                <tr>
                    <td><?= $i++; ?></td>
                    <td>
                        <?php if($row['cover']): ?>
                            <img src="../assets/img/<?= $row['cover']; ?>" alt="Cover" width="50" class="rounded shadow-sm">
                        <?php else: ?>
                            <span class="text-muted"><i class="fas fa-book fa-2x"></i></span>
                        <?php endif; ?>
                    </td>
                    <td><?= $row['judul_buku']; ?></td>
                    <td><span class="badge bg-secondary"><?= $row['nama_kategori'] ?? 'Uncategorized'; ?></span></td>
                    <td><?= $row['pengarang']; ?></td>
                    <td><?= $row['stok']; ?></td>
                    <td><span class="badge bg-secondary"><?= $row['nama_rak']; ?></span></td>
                    <td>
                        <button class="btn btn-sm btn-info text-white me-1" data-bs-toggle="modal" data-bs-target="#modalUbah<?= $row['id_buku']; ?>"><i class="fas fa-edit"></i></button>
                        <a href="javascript:void(0);" onclick="konfirmasiHapus(<?= $row['id_buku']; ?>)" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Buku Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Judul Buku</label>
                        <input type="text" class="form-control" name="judul" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select class="form-select" name="id_kategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach($kategori as $kat) : ?>
                                <option value="<?= $kat['id_kategori']; ?>"><?= $kat['nama_kategori']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lokasi Rak</label>
                        <select class="form-select" name="id_rak" required>
                            <option value="">-- Pilih Rak --</option>
                            <?php foreach($rak as $r) : ?>
                                <option value="<?= $r['id_rak']; ?>"><?= $r['nama_rak']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pengarang</label>
                        <input type="text" class="form-control" name="pengarang" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Penerbit</label>
                        <input type="text" class="form-control" name="penerbit" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tahun Terbit</label>
                        <input type="text" class="form-control" name="tahun" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stok</label>
                        <input type="number" class="form-control" name="stok" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cover</label>
                        <input type="file" class="form-control" name="cover" accept=".jpg, .jpeg, .png">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modals Ubah (Loop Again) -->
<?php foreach($buku as $row) : ?>
<div class="modal fade" id="modalUbah<?= $row['id_buku']; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Data Buku</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id_buku" value="<?= $row['id_buku']; ?>">
                    <input type="hidden" name="gambarLama" value="<?= $row['cover']; ?>">
                    <div class="mb-3">
                        <label class="form-label">Judul Buku</label>
                        <input type="text" class="form-control" name="judul" value="<?= $row['judul_buku']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select class="form-select" name="id_kategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach($kategori as $kat) : ?>
                                <option value="<?= $kat['id_kategori']; ?>" <?= $row['id_kategori'] == $kat['id_kategori'] ? 'selected' : ''; ?>>
                                    <?= $kat['nama_kategori']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lokasi Rak</label>
                        <select class="form-select" name="id_rak" required>
                            <option value="">-- Pilih Rak --</option>
                            <?php foreach($rak as $r) : ?>
                                <option value="<?= $r['id_rak']; ?>" <?= $row['id_rak'] == $r['id_rak'] ? 'selected' : ''; ?>>
                                    <?= $r['nama_rak']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pengarang</label>
                        <input type="text" class="form-control" name="pengarang" value="<?= $row['pengarang']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Penerbit</label>
                        <input type="text" class="form-control" name="penerbit" value="<?= $row['penerbit']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tahun Terbit</label>
                        <input type="text" class="form-control" name="tahun" value="<?= $row['tahun_terbit']; ?>" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stok</label>
                        <input type="number" class="form-control" name="stok" value="<?= $row['stok']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cover (Biarkan kosong jika tidak diubah)</label>
                        <input type="file" class="form-control" name="cover" accept=".jpg, .jpeg, .png">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="ubah" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php if (isset($_SESSION['flash'])) : ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: '<?= $_SESSION['flash']['type']; ?>',
                title: '<?= ucfirst($_SESSION['flash']['type']); ?>',
                text: '<?= $_SESSION['flash']['message']; ?>',
                timer: 3000,
                showConfirmButton: false
            });
        });
    </script>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<script>
    function konfirmasiHapus(id) {
        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: "Data buku yang dihapus tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'buku.php?hapus=' + id;
            }
        })
    }
</script>

<?php require 'footer.php'; ?>
