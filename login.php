<?php
session_start();
require 'config.php';

if (isset($_SESSION["login"])) {
    if ($_SESSION["role"] == "admin" || $_SESSION["role"] == "petugas") {
        header("Location: admin/index.php");
    } else {
        header("Location: siswa/index.php");
    }
    exit;
}

if (isset($_POST["login"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $role = $_POST["role"];

    // Cek di tabel user
    $result = mysqli_query($conn, "SELECT * FROM user WHERE username = '$username'");

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // Verifikasi Password
        if (password_verify($password, $row["password"])) {
            
            // CEK STATUS: Jika diblokir, tolak login
            if ($row['status'] == 'blokir') {
                $error_blocked = true; 
            } else {
                
                // Cek kesesuaian role yang dipilih di form dengan role di database
                // Mapping: form role 'siswa' -> db role 'peminjam'
                //          form role 'admin' -> db role 'admin'
                
                $db_role = $row["role"]; // 'admin' atau 'peminjam'
                $form_role = $role;      // 'admin' atau 'siswa'
    
                $role_match = false;
                
                if ($form_role == 'admin' && $db_role == 'admin') {
                    $role_match = true;
                } elseif ($form_role == 'siswa' && $db_role == 'peminjam') {
                    $role_match = true;
                }
    
                if ($role_match) {
                    $_SESSION["login"] = true;
                    $_SESSION["id_user"] = $row["id_user"];
                    $_SESSION["nama"] = $row["nama_lengkap"];
                    $_SESSION["role"] = $form_role; // Simpan role sesuai form ('admin'/'siswa') untuk logic redirect
                    
                    if ($form_role == "admin") {
                        header("Location: admin/index.php");
                    } else {
                        header("Location: siswa/index.php");
                    }
                    exit;
                }
            }
        }
    }

    $error = true;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <div class="auth-container">
        <div class="glass-card auth-box fade-in">
            <div class="text-center auth-header mb-4">
                <i class="fas fa-book-reader fa-3x text-primary mb-3"></i>
                <h3>E-Perpustakaan</h3>
                <p class="text-muted">Silahkan login untuk melanjutkan</p>
            </div>

            <?php if(isset($error_blocked)) : ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-ban me-2"></i> Akun Anda telah diblokir! Hubungi petugas.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if(isset($error)) : ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Username atau Password salah!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="" method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-user text-muted"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" id="username" name="username" placeholder="Masukkan username" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-lock text-muted"></i></span>
                        <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" placeholder="Masukkan password" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Masuk Sebagai</label>
                    <div class="d-flex gap-3">
                        <div class="form-check flex-fill p-0">
                            <input type="radio" class="btn-check" name="role" id="roleSiswa" value="siswa" checked>
                            <label class="btn btn-outline-primary w-100 rounded-pill" for="roleSiswa">Siswa</label>
                        </div>
                        <div class="form-check flex-fill p-0">
                            <input type="radio" class="btn-check" name="role" id="roleAdmin" value="admin">
                            <label class="btn btn-outline-primary w-100 rounded-pill" for="roleAdmin">Petugas</label>
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="login" class="btn btn-premium w-100 mb-3">LOGIN</button>
                
                <div class="text-center">
                    <p class="mb-0 text-muted">Belum punya akun? <a href="register.php" class="text-primary text-decoration-none fw-bold">Daftar disini</a></p>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/script.js"></script>

    <?php if(isset($error_blocked)) : ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Akses Ditolak',
                text: 'Akun Anda telah diblokir! Hubungi petugas.',
                confirmButtonColor: '#d33'
            });
        </script>
    <?php endif; ?>

    <?php if(isset($error)) : ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Login Gagal',
                text: 'Username atau Password salah!',
                confirmButtonColor: '#d33'
            });
        </script>
    <?php endif; ?>
</body>
</html>
