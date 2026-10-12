<?php
session_start();
require '../config/database.php';

if (isset($_SESSION["login"])) {
    if ($_SESSION["role"] == "admin") {
        header("Location: ../admin/index.php");
    } else {
        header("Location: ../siswa/index.php");
    }
    exit;
}

if (isset($_POST["login"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];
    
    // Cek di tabel user
    $result = mysqli_query($conn, "SELECT * FROM user WHERE username = '$username'");
    
    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row["password"])) {
            
            if ($row['status'] == 'blokir') {
                $error_blokir = true;
            } else {
                $_SESSION["login"] = true;
                $_SESSION["id_user"] = $row["id_user"];
                $_SESSION["nama"] = $row["nama_lengkap"];
                $_SESSION["role"] = $row["role"]; // 'admin' atau 'peminjam'
                
                if ($row["role"] == "admin") {
                    header("Location: ../admin/index.php");
                } else {
                    header("Location: ../siswa/index.php");
                }
                exit;
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
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <div class="auth-container">
        <div class="glass-card auth-box fade-in">
            <div class="text-center auth-header mb-4">
                <i class="fas fa-book-reader fa-3x text-primary mb-3"></i>
                <h3>E-Perpustakaan</h3>
                <p class="text-muted">Silahkan login untuk melanjutkan</p>
            </div>

            <?php if(isset($error)) : ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Username atau Password salah!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if(isset($error_blokir)) : ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-ban me-2"></i> Akun Anda diblokir! Silahkan hubungi petugas perpustakaan.
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
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-lock text-muted"></i></span>
                        <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" placeholder="Masukkan password" required>
                    </div>
                </div>
                <!-- Role selection removed, handled by DB role -->
                
                <button type="submit" name="login" class="btn btn-premium w-100 mb-3">LOGIN</button>
                
                <div class="text-center">
                    <p class="mb-0 text-muted">Belum punya akun? <a href="register.php" class="text-primary text-decoration-none fw-bold">Daftar disini</a></p>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>
