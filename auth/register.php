<?php
require '../functions/functions.php';

if (isset($_POST["register"])) {
    if (registrasi($_POST) > 0) {
        echo "<script>
                alert('Pendaftaran berhasil! Silahkan login.');
                window.location.href = 'login.php';
              </script>";
    } else {
        echo mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - E-Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <div class="auth-container">
        <div class="glass-card auth-box fade-in">
            <div class="text-center auth-header mb-4">
                <h3 class="mt-3">Daftar Akun</h3>
                <p class="text-muted">Buat akun peminjam baru</p>
            </div>

            <form action="" method="post">
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" id="nama" name="nama" required>
                </div>

                <div class="row">
                   <div class="col-md-12 mb-3">
                        <label for="telp" class="form-label">No. Telepon</label>
                        <input type="text" class="form-control" id="telp" name="telp" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                   </div>
                </div>

                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea class="form-control" id="alamat" name="alamat" rows="2" required></textarea>
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <button type="submit" name="register" class="btn btn-premium w-100 mb-3">DAFTAR</button>
                
                <div class="text-center">
                    <p class="mb-0 text-muted">Sudah punya akun? <a href="login.php" class="text-primary text-decoration-none fw-bold">Login disini</a></p>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('nama').addEventListener('input', function() {
            if (this.value.length > 50) {
                this.value = this.value.substring(0, 50);
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Nama Lengkap tidak boleh lebih dari 50 karakter!',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });
    </script>
</body>
</html>
