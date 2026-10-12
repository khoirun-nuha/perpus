<?php require 'header.php'; 

// Logic Reset Password
if (isset($_GET["reset"])) {
    $id = $_GET["reset"];
    if (reset_password($id) > 0) {
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Password berhasil direset menjadi 123!'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal reset password!'];
    }
    header("Location: user.php");
    exit;
}

// Logic Blokir / Unblokir
if (isset($_GET["toggle"])) {
    $id = $_GET["toggle"];
    $result = toggle_status_user($id);
    if ($result > 0) {
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Status user berhasil diubah!'];
    } elseif ($result === 0) {
         $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal mengubah status! (Admin tidak bisa diblokir)'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal mengubah status!'];
    }
    header("Location: user.php");
    exit;
} 

$users = query("SELECT * FROM user ORDER BY role ASC, nama_lengkap ASC");
?>

<div class="d-flex justify-content-between align-items-center mb-4 fade-in">
    <h2 class="fw-bold text-dark">Kelola Pengguna</h2>
    <div class="alert alert-info py-2 mb-0">
        <small><i class="fas fa-info-circle me-1"></i> Default Password Reset: <strong>123</strong></small>
    </div>
</div>

<div class="glass-card fade-in delay-1">
    <div class="table-responsive">
        <table class="table table-hover table-custom align-middle data-table text-nowrap">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Lengkap</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Alamat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach($users as $row) : ?>
                <tr>
                    <td><?= $i++; ?></td>
                    <td><?= $row['nama_lengkap']; ?></td>
                    <td><?= $row['username']; ?></td>
                    <td>
                        <span class="badge rounded-pill bg-<?= $row['role'] == 'admin' ? 'primary' : 'success'; ?>">
                            <?= ucfirst($row['role']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge rounded-pill bg-<?= $row['status'] == 'aktif' ? 'info' : 'danger'; ?>">
                            <?= ucfirst($row['status']); ?>
                        </span>
                    </td>
                    <td><?= $row['alamat']; ?></td>
                    <td>
                        <a href="javascript:void(0);" onclick="konfirmasiReset(<?= $row['id_user']; ?>)" class="btn btn-sm btn-warning text-white me-1" title="Reset Password"><i class="fas fa-key"></i></a>
                        
                        <?php if($row['role'] !== 'admin') : ?>
                            <?php if($row['status'] == 'aktif') : ?>
                                <a href="javascript:void(0);" onclick="konfirmasiBlokir(<?= $row['id_user']; ?>)" class="btn btn-sm btn-danger" title="Blokir User"><i class="fas fa-ban"></i></a>
                            <?php else : ?>
                                <a href="javascript:void(0);" onclick="konfirmasiAktifkan(<?= $row['id_user']; ?>)" class="btn btn-sm btn-success" title="Aktifkan User"><i class="fas fa-check-circle"></i></a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

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
    function konfirmasiReset(id) {
        Swal.fire({
            title: 'Reset Password?',
            text: "Password user ini akan direset menjadi 123!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Reset!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'user.php?reset=' + id;
            }
        })
    }

    function konfirmasiBlokir(id) {
        Swal.fire({
            title: 'Blokir User?',
            text: "User tidak akan bisa login ke sistem!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Blokir!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'user.php?toggle=' + id;
            }
        })
    }

    function konfirmasiAktifkan(id) {
        Swal.fire({
            title: 'Aktifkan User?',
            text: "User akan bisa login kembali!",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Aktifkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'user.php?toggle=' + id;
            }
        })
    }
</script>

<?php require 'footer.php'; ?>
