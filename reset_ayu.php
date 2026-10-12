<?php
require 'config/database.php';

// 1. Ambil ID User 'ayu'
$user = query("SELECT id_user FROM user WHERE username = 'ayu' OR nama_lengkap LIKE '%Ayu%' LIMIT 1");

if (empty($user)) {
    die("User 'Ayu' tidak ditemukan.");
}

$id_user = $user[0]['id_user'];
echo "Found User ID: " . $id_user . "<br>";

// 2. Hapus Detail Peminjaman (Child)
$query_detail = "DELETE dp FROM detail_peminjaman dp 
                 JOIN peminjaman p ON dp.id_peminjaman = p.id_peminjaman 
                 WHERE p.id_user = $id_user";
mysqli_query($conn, $query_detail);
echo "Deleted details: " . mysqli_affected_rows($conn) . "<br>";

// 3. Hapus Peminjaman (Parent)
$query_header = "DELETE FROM peminjaman WHERE id_user = $id_user";
mysqli_query($conn, $query_header);
echo "Deleted headers: " . mysqli_affected_rows($conn) . "<br>";

echo "<strong>Riwayat peminjaman Ayu berhasil dihapus bersih!</strong>";
?>
