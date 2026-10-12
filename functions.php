<?php
require 'config.php';

// Fungsi query untuk mengambil data (SELECT)
function query($query) {
    global $conn;
    $result = mysqli_query($conn, $query);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

// Fungsi Registrasi Siswa
function registrasi($data) {
    global $conn;

    $nis = htmlspecialchars($data["nis"]);
    $nama = htmlspecialchars($data["nama"]);
    $alamat = htmlspecialchars($data["alamat"]);
    $telp = htmlspecialchars($data["telp"]);
    $username = strtolower(stripslashes($data["username"]));
    $password = mysqli_real_escape_string($conn, $data["password"]);

    // Cek username sudah ada atau belum
    $result = mysqli_query($conn, "SELECT username FROM anggota WHERE username = '$username'");
    if (mysqli_fetch_assoc($result)) {
        echo "<script>alert('Username sudah terdaftar!');</script>";
        return false;
    }

    // Enkripsi password
    $password = password_hash($password, PASSWORD_DEFAULT);

    // Tambahkan userbaru ke database
    $query = "INSERT INTO anggota VALUES ('', '$nis', '$nama', '$alamat', '$telp', '$username', '$password')";
    mysqli_query($conn, $query);

    return mysqli_affected_rows($conn);
}

// Fungsi Tambah Buku
function tambah_buku($data) {
    global $conn;

    $judul = htmlspecialchars($data["judul"]);
    $pengarang = htmlspecialchars($data["pengarang"]);
    $penerbit = htmlspecialchars($data["penerbit"]);
    $tahun = htmlspecialchars($data["tahun"]);
    $stok = htmlspecialchars($data["stok"]);
    $id_kategori = htmlspecialchars($data["id_kategori"]);
    
    // Upload Cover
    $cover = upload_cover();
    if (!$cover) {
        return false;
    }

    $query = "INSERT INTO buku VALUES ('', '$judul', '$pengarang', '$penerbit', '$tahun', '', '$stok', '$cover', '$id_kategori')";
    mysqli_query($conn, $query);

    return mysqli_affected_rows($conn);
}

function upload_cover() {
    $namaFile = $_FILES['cover']['name'];
    $ukuranFile = $_FILES['cover']['size'];
    $error = $_FILES['cover']['error'];
    $tmpName = $_FILES['cover']['tmp_name'];

    // Cek apakah tidak ada gambar yang diupload
    if ($error === 4) {
        return 'default.jpg';
    }

    // Cek apakah yang diupload adalah gambar
    $ekstensiGambarValid = ['jpg', 'jpeg', 'png'];
    $ekstensiGambar = explode('.', $namaFile);
    $ekstensiGambar = strtolower(end($ekstensiGambar));
    if (!in_array($ekstensiGambar, $ekstensiGambarValid)) {
        echo "<script>alert('Yang anda upload bukan gambar!');</script>";
        return false;
    }

    // Cek jika ukurannya terlalu besar
    if ($ukuranFile > 2000000) {
        echo "<script>alert('Ukuran gambar terlalu besar!');</script>";
        return false;
    }

    // Lolos pengecekan, siap upload
    $namaFileBaru = uniqid();
    $namaFileBaru .= '.';
    $namaFileBaru .= $ekstensiGambar;

    move_uploaded_file($tmpName, 'assets/img/' . $namaFileBaru);

    return $namaFileBaru;
}

// Fungsi Hapus Buku
function hapus_buku($id) {
    global $conn;
    mysqli_query($conn, "DELETE FROM buku WHERE id_buku = $id");
    return mysqli_affected_rows($conn);
}

// Fungsi Ubah Buku
function ubah_buku($data) {
    global $conn;

    $id = $data["id_buku"];
    $judul = htmlspecialchars($data["judul"]);
    $pengarang = htmlspecialchars($data["pengarang"]);
    $penerbit = htmlspecialchars($data["penerbit"]);
    $tahun = htmlspecialchars($data["tahun"]);
    $stok = htmlspecialchars($data["stok"]);
    $id_kategori = htmlspecialchars($data["id_kategori"]);
    $gambarLama = htmlspecialchars($data["gambarLama"]);

    // Cek apakah user pilih gambar baru atau tidak
    if ($_FILES['cover']['error'] === 4) {
        $cover = $gambarLama;
    } else {
        $cover = upload_cover();
    }

    $query = "UPDATE buku SET
                judul_buku = '$judul',
                pengarang = '$pengarang',
                penerbit = '$penerbit',
                tahun_terbit = '$tahun',
                stok = '$stok',
                cover = '$cover',
                id_kategori = '$id_kategori'
              WHERE id_buku = $id
            ";
    mysqli_query($conn, $query);

    return mysqli_affected_rows($conn);
}

// Fungsi Kategori
function tambah_kategori($data) {
    global $conn;
    $nama = htmlspecialchars($data["nama_kategori"]);
    mysqli_query($conn, "INSERT INTO kategori VALUES ('', '$nama')");
    return mysqli_affected_rows($conn);
}

function hapus_kategori($id) {
    global $conn;
    mysqli_query($conn, "DELETE FROM kategori WHERE id_kategori = $id");
    return mysqli_affected_rows($conn);
}

function ubah_kategori($data) {
    global $conn;
    $id = $data["id_kategori"];
    $nama = htmlspecialchars($data["nama_kategori"]);
    mysqli_query($conn, "UPDATE kategori SET nama_kategori = '$nama' WHERE id_kategori = $id");
    return mysqli_affected_rows($conn);
}

// Fungsi Peminjaman
function pinjam_buku($id_anggota, $id_buku) {
    global $conn;
    
    // Cek stok
    $buku = query("SELECT stok FROM buku WHERE id_buku = $id_buku")[0];
    if ($buku['stok'] <= 0) {
        return false;
    }

    $tgl_pinjam = date('Y-m-d');
    $tgl_kembali = date('Y-m-d', strtotime('+7 days')); // 7 hari peminjaman

    $query = "INSERT INTO peminjaman VALUES ('', '$id_anggota', '$id_buku', '$tgl_pinjam', '$tgl_kembali', NULL, 0, 'dipinjam', NULL)";
    mysqli_query($conn, $query);

    // Kurangi stok
    mysqli_query($conn, "UPDATE buku SET stok = stok - 1 WHERE id_buku = $id_buku");

    return mysqli_affected_rows($conn);
}

// Fungsi Pengembalian
function kembalikan_buku($id_peminjaman) {
    global $conn;
    
    $tgl_kembali_real = date('Y-m-d');
    
    // Hitung denda
    $peminjaman = query("SELECT * FROM peminjaman WHERE id_peminjaman = $id_peminjaman")[0];
    $tgl_jatuh_tempo = $peminjaman['tanggal_kembali_rencana'];
    
    $denda = 0;
    if ($tgl_kembali_real > $tgl_jatuh_tempo) {
        $selisih = strtotime($tgl_kembali_real) - strtotime($tgl_jatuh_tempo);
        $hari = floor($selisih / (60 * 60 * 24));
        $denda = $hari * 1000; // Denda 1000 per hari
    }

    $query = "UPDATE peminjaman SET 
              tanggal_kembali_real = '$tgl_kembali_real',
              status = 'kembali',
              denda = $denda
              WHERE id_peminjaman = $id_peminjaman";
    
    mysqli_query($conn, $query);

    // Tambah stok
    $id_buku = $peminjaman['id_buku'];
    mysqli_query($conn, "UPDATE buku SET stok = stok + 1 WHERE id_buku = $id_buku");

    return mysqli_affected_rows($conn);
}
?>
