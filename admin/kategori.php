<?php require 'header.php'; 

// Logic POST
if (isset($_POST["tambah"])) {
    if (tambah_kategori($_POST) > 0) {
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Kategori berhasil ditambahkan!'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menambahkan kategori!'];
    }
    header("Location: kategori.php");
    exit;
}

if (isset($_POST["ubah"])) {
    if (ubah_kategori($_POST) > 0) {
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Kategori berhasil diubah!'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal mengubah data!'];
    }
    header("Location: kategori.php");
    exit;
}

if (isset($_GET["hapus"])) {
    $result = hapus_kategori($_GET["hapus"]);
    if ($result > 0 && is_int($result)) {
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Kategori berhasil dihapus!'];
    } else {
        $msg = is_string($result) ? $result : 'Gagal menghapus kategori!';
        $_SESSION['flash'] = ['type' => 'error', 'message' => $msg];
    }
    header("Location: kategori.php");
    exit;
}

$kategori = query("SELECT * FROM kategori");
?>

<div class="d-flex justify-content-between align-items-center mb-4 fade-in">
    <h2 class="fw-bold text-dark">Data Kategori</h2>
    <button class="btn btn-premium" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="fas fa-plus me-2"></i>Tambah Kategori
    </button>
</div>

<div class="glass-card fade-in delay-1">
    <div class="table-responsive">
        <table class="table table-hover table-custom align-middle data-table text-nowrap">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Kategori</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach($kategori as $row) : ?>
                <tr>
                    <td><?= $i++; ?></td>
                    <td><?= $row['nama_kategori']; ?></td>
                    <td>
                        <button class="btn btn-sm btn-info text-white me-1" data-bs-toggle="modal" data-bs-target="#modalUbah<?= $row['id_kategori']; ?>"><i class="fas fa-edit"></i></button>
                        <a href="#" class="btn btn-sm btn-danger" onclick="confirmDelete('kategori.php?hapus=<?= $row['id_kategori']; ?>')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah (Outside Table) -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Kategori Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" class="form-control" name="nama_kategori" required>
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

<!-- Modals Ubah (Generated Loop Outside Table) -->
<?php foreach($kategori as $row) : ?>
<div class="modal fade" id="modalUbah<?= $row['id_kategori']; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="post">
                <div class="modal-body">
                    <input type="hidden" name="id_kategori" value="<?= $row['id_kategori']; ?>">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" class="form-control" name="nama_kategori" value="<?= $row['nama_kategori']; ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="ubah" class="btn btn-primary">Simpan</button>
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

<?php require 'footer.php'; ?>
