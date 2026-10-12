<?php
require __DIR__ . '/../config/database.php';

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

// Fungsi Registrasi User (Peminjam Only via front-end)
function registrasi($data) {
    global $conn;

    $nama = htmlspecialchars($data["nama"]);
    $alamat = htmlspecialchars($data["alamat"]);
    $telp = htmlspecialchars($data["telp"]);
    $username = strtolower(stripslashes($data["username"]));
    $password = mysqli_real_escape_string($conn, $data["password"]);

    // Cek username
    $result = mysqli_query($conn, "SELECT username FROM user WHERE username = '$username'");
    if (mysqli_fetch_assoc($result)) {
        echo "<script>alert('Username sudah terdaftar!');</script>";
        return false;
    }

    $password = password_hash($password, PASSWORD_DEFAULT);

    // DIPERBAIKI: Menambahkan 'aktif' untuk kolom status
    $query = "INSERT INTO user VALUES (NULL, '$nama', '$username', '$password', 'peminjam', '$alamat', '$telp', 'aktif')";
    mysqli_query($conn, $query);

    return mysqli_affected_rows($conn);
}

// Fungsi Tambah User (Admin Only)
function tambah_user_admin($data) {
    global $conn;
    $nama = htmlspecialchars($data["nama"]);
    $username = htmlspecialchars($data["username"]);
    $role = htmlspecialchars($data["role"]); // admin or peminjam
    $password = password_hash($data["password"], PASSWORD_DEFAULT);
    $alamat = htmlspecialchars($data["alamat"]);
    $telp = htmlspecialchars($data["telp"]);

    // DIPERBAIKI: Menambahkan 'aktif' untuk kolom status
    $query = "INSERT INTO user VALUES (NULL, '$nama', '$username', '$password', '$role', '$alamat', '$telp', 'aktif')";
    mysqli_query($conn, $query);
    return mysqli_affected_rows($conn);
}

// Fungsi Checkout (Dari Keranjang)
// Fungsi Checkout (Dari Keranjang)
function checkout($id_user, $keranjang) {
    global $conn;

    if (empty($keranjang)) {
        return false;
    }

    $tgl_pinjam = date('Y-m-d');
    $tgl_kembali = date('Y-m-d', strtotime('+7 days'));

    // --- MULAI TRANSAKSI ---
    mysqli_begin_transaction($conn);

    try {
        // --- 1. VALIDASI LIMIT PEMINJAMAN (MAX 3) ---
        // Hitung total item yang sedang dipinjam ATAU diajukan (belum kembali)
        // Status: 'dipinjam', 'diajukan', 'minta_kembali' dianggap aktif meminjam/memesan quota
        $cek_item_aktif = mysqli_query($conn, "
            SELECT COUNT(*) as total_buku 
            FROM detail_peminjaman dp
            JOIN peminjaman p ON dp.id_peminjaman = p.id_peminjaman
            WHERE p.id_user = $id_user AND p.status IN ('dipinjam', 'diajukan', 'minta_kembali')
        ");
        
        $data_item = mysqli_fetch_assoc($cek_item_aktif);
        $jumlah_aktif = $data_item['total_buku'];
        $jumlah_akan_dipinjam = count($keranjang);
        
        if (($jumlah_aktif + $jumlah_akan_dipinjam) > 3) {
            throw new Exception("Limit peminjaman maksimal 3 buku! Anda sedang meminjam/mengajukan $jumlah_aktif buku.");
        }

        // --- 2. VALIDASI PRE-INSERT (Stok & Duplikat) ---
        foreach ($keranjang as $id_buku => $jumlah) {
            // Cek Stok
            $data_buku = query("SELECT stok, judul_buku FROM buku WHERE id_buku = $id_buku");
            if (empty($data_buku)) throw new Exception("Buku tidak ditemukan (ID: $id_buku).");
            
            $stok = $data_buku[0]['stok'];
            if ($stok < 1) {
                throw new Exception("Stok buku '{$data_buku[0]['judul_buku']}' habis!");
            }

            // Cek Duplikat (Sedang dipinjam/diajukan user ini?)
            $cek_duplikat = mysqli_query($conn, "
                SELECT dp.id_buku FROM detail_peminjaman dp
                JOIN peminjaman p ON dp.id_peminjaman = p.id_peminjaman
                WHERE p.id_user = $id_user 
                AND p.status IN ('dipinjam', 'diajukan', 'minta_kembali')
                AND dp.id_buku = $id_buku
            ");
            
            if (mysqli_num_rows($cek_duplikat) > 0) {
                throw new Exception("Anda sedang meminjam atau mengajukan buku '{$data_buku[0]['judul_buku']}'.");
            }
        }

        // --- 3. INSERT HEADER PEMINJAMAN ---
        // Insert dengan status 'diajukan'
        $query_header = "INSERT INTO peminjaman VALUES (NULL, '$id_user', '$tgl_pinjam', '$tgl_kembali', NULL, 0, 'diajukan')";
        if (!mysqli_query($conn, $query_header)) {
            throw new Exception("Gagal membuat data peminjaman: " . mysqli_error($conn));
        }
        
        $id_peminjaman = mysqli_insert_id($conn);

        foreach ($keranjang as $id_buku => $jumlah) {
             mysqli_query($conn, "INSERT INTO detail_peminjaman VALUES (NULL, '$id_peminjaman', '$id_buku')");
        }

        // Jika semua lancar, COMMIT
        mysqli_commit($conn);
        return true;

    } catch (Exception $e) {
        // Jika ada error, ROLLBACK semua perubahan
        mysqli_rollback($conn);
        return $e->getMessage();
    }
}

// --- FUNGSI TRANSAKSI BARU ---

function terima_pinjaman($id_peminjaman) {
    global $conn;
    // Ubah status jadi 'dipinjam' (Stok sudah berkurang saat checkout)
    mysqli_query($conn, "UPDATE peminjaman SET status = 'dipinjam' WHERE id_peminjaman = $id_peminjaman");
    return mysqli_affected_rows($conn);
}

function tolak_pinjaman($id_peminjaman) {
    global $conn;
    
    // Ubah status jadi 'ditolak'
    mysqli_query($conn, "UPDATE peminjaman SET status = 'ditolak' WHERE id_peminjaman = $id_peminjaman");

    // KEMBALIKAN STOK (SUDAH DITANGANI TRIGGER kembalikan_stok_update)
    // Tidak perlu update manual
    
    return mysqli_affected_rows($conn);
}

function ajukan_kembali($id_peminjaman) {
    global $conn;
    // Siswa mengajukan pengembalian
    mysqli_query($conn, "UPDATE peminjaman SET status = 'minta_kembali' WHERE id_peminjaman = $id_peminjaman");
    return mysqli_affected_rows($conn);
}

// Fungsi Pengembalian (Update Header & Restore Stok)
function kembalikan_buku($id_peminjaman) {
    global $conn;
    
    $tgl_kembali_real = date('Y-m-d');
    
    // Hitung denda
    $result_pinjam = query("SELECT * FROM peminjaman WHERE id_peminjaman = $id_peminjaman");
    if (empty($result_pinjam)) return 0;
    
    $peminjaman = $result_pinjam[0];
    
    // Jika sudah dikembalikan, skip
    if ($peminjaman['status'] == 'kembali') return 0;
    
    $tgl_jatuh_tempo = $peminjaman['tanggal_kembali_rencana']; // Pastikan nama kolom sesuai DB
    
    $denda = 0;
    if ($tgl_kembali_real > $tgl_jatuh_tempo) {
        $selisih = strtotime($tgl_kembali_real) - strtotime($tgl_jatuh_tempo);
        $hari = floor($selisih / (60 * 60 * 24));
        $denda = $hari * 1000; 
    }

    $query = "UPDATE peminjaman SET 
              tanggal_kembali_real = '$tgl_kembali_real',
              status = 'kembali',
              denda = $denda
              WHERE id_peminjaman = $id_peminjaman";
    
    mysqli_query($conn, $query);

    // Kembalikan Stok (SUDAH DITANGANI TRIGGER kembalikan_stok_update)
    // Tidak perlu query manual di sini

    return mysqli_affected_rows($conn);
}

// -- Fungsi CRUD Lainnya --

function tambah_buku($data) {
    global $conn;
    $judul = htmlspecialchars($data["judul"]);
    $pengarang = htmlspecialchars($data["pengarang"]);
    $penerbit = htmlspecialchars($data["penerbit"]);
    $tahun = htmlspecialchars($data["tahun"]);
    $stok = htmlspecialchars($data["stok"]);
    $id_kategori = htmlspecialchars($data["id_kategori"]);
    $id_rak = htmlspecialchars($data["id_rak"]);
    $cover = upload_cover();
    if (!$cover) return false;

    // DIPERBAIKI: Menggunakan NULL
    $query = "INSERT INTO buku VALUES (NULL, '$judul', '$pengarang', '$penerbit', '$tahun', '', '$stok', '$cover', '$id_kategori', '$id_rak')";
    mysqli_query($conn, $query);
    return mysqli_affected_rows($conn);
}

function upload_cover() {
    $namaFile = $_FILES['cover']['name'];
    $ukuranFile = $_FILES['cover']['size'];
    $error = $_FILES['cover']['error'];
    $tmpName = $_FILES['cover']['tmp_name'];
    if ($error === 4) return 'default.jpg';
    $ekstensiGambarValid = ['jpg', 'jpeg', 'png'];
    $ekstensiGambar = explode('.', $namaFile);
    $ekstensiGambar = strtolower(end($ekstensiGambar));
    if (!in_array($ekstensiGambar, $ekstensiGambarValid)) return false;
    $namaFileBaru = uniqid();
    $namaFileBaru .= '.';
    $namaFileBaru .= $ekstensiGambar;
    move_uploaded_file($tmpName, __DIR__ . '/../assets/img/' . $namaFileBaru);
    return $namaFileBaru;
}

function ubah_buku($data) {
    global $conn;
    $id = $data["id_buku"];
    $judul = htmlspecialchars($data["judul"]);
    $pengarang = htmlspecialchars($data["pengarang"]);
    $penerbit = htmlspecialchars($data["penerbit"]);
    $tahun = htmlspecialchars($data["tahun"]);
    $stok = htmlspecialchars($data["stok"]);
    $id_kategori = htmlspecialchars($data["id_kategori"]);
    $id_rak = htmlspecialchars($data["id_rak"]);
    $gambarLama = htmlspecialchars($data["gambarLama"]);
    if ($_FILES['cover']['error'] === 4) {
        $cover = $gambarLama;
    } else {
        $cover = upload_cover();
    }
    $query = "UPDATE buku SET judul_buku='$judul', pengarang='$pengarang', penerbit='$penerbit', tahun_terbit='$tahun', stok='$stok', cover='$cover', id_kategori='$id_kategori', id_rak='$id_rak' WHERE id_buku=$id";
    
    try {
        $result = mysqli_query($conn, $query);
        if (!$result) {
            throw new Exception(mysqli_error($conn), mysqli_errno($conn));
        }
        return mysqli_affected_rows($conn);
    } catch (Exception $e) {
        // Handle FK Constraint Failure
        if ($e->getCode() == 1452) { // Cannot add or update a child row
            return "Kategori atau Rak yang dipilih tidak valid!";
        }
        return "Gagal mengubah data: " . $e->getMessage();
    }
}

function hapus_buku($id) {
    global $conn;
    try {
        $result = mysqli_query($conn, "DELETE FROM buku WHERE id_buku = $id");
        // Jika query gagal (return false), cek errornya
        if (!$result) {
            throw new Exception(mysqli_error($conn), mysqli_errno($conn));
        }
        return mysqli_affected_rows($conn);
    } catch (Exception $e) {
        // Error 1451: Foreign Key Constraint
        if ($e->getCode() == 1451) {
            return "Buku tidak dapat dihapus karena masih ada di data peminjaman/transaksi!";
        }
        return "Gagal menghapus: " . $e->getMessage();
    }
}

// Kategori Functions
function tambah_kategori($data) {
    global $conn;
    $nama = htmlspecialchars($data["nama_kategori"]);
    // DIPERBAIKI: Menggunakan NULL
    mysqli_query($conn, "INSERT INTO kategori VALUES (NULL, '$nama')");
    return mysqli_affected_rows($conn);
}
function hapus_kategori($id) {
    global $conn;
    try {
        $result = mysqli_query($conn, "DELETE FROM kategori WHERE id_kategori = $id");
        if (!$result) {
             throw new Exception(mysqli_error($conn), mysqli_errno($conn));
        }
        return mysqli_affected_rows($conn);
    } catch (Exception $e) {
        if ($e->getCode() == 1451) {
            return "Kategori tidak dapat dihapus karena sedang digunakan oleh Buku!";
        }
        return "Gagal menghapus: " . $e->getMessage();
    }
}
function ubah_kategori($data) {
    global $conn;
    $id = $data["id_kategori"];
    $nama = htmlspecialchars($data["nama_kategori"]);
    mysqli_query($conn, "UPDATE kategori SET nama_kategori = '$nama' WHERE id_kategori = $id");
    return mysqli_affected_rows($conn);
}

// Function Reset Password
function reset_password($id) {
    global $conn;
    // Default password '123'
    $password = password_hash("123", PASSWORD_DEFAULT);
    mysqli_query($conn, "UPDATE user SET password = '$password' WHERE id_user = $id");
    return mysqli_affected_rows($conn);
}

// Function Toggle Status
function toggle_status_user($id) {
    global $conn;
    
    // Get current status and role
    $result = mysqli_query($conn, "SELECT status, role FROM user WHERE id_user = $id");
    $row = mysqli_fetch_assoc($result);
    
    // Cegah blokir admin
    if ($row['role'] == 'admin') {
        return 0;
    }

    $current_status = $row['status'];
    
    $new_status = ($current_status == 'aktif') ? 'blokir' : 'aktif';
    
    mysqli_query($conn, "UPDATE user SET status = '$new_status' WHERE id_user = $id");
    return mysqli_affected_rows($conn);
}
?>