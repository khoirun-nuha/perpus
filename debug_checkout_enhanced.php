<?php
// Enable error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require 'config/database.php';
require 'functions/functions.php';

echo "Database: " . $db . "<br>";

// 1. Get a valid user
$user_res = mysqli_query($conn, "SELECT id_user, nama_lengkap FROM user WHERE role = 'peminjam' LIMIT 1");
if(mysqli_num_rows($user_res) == 0) die("Error: No 'peminjam' user found.");
$user = mysqli_fetch_assoc($user_res);
$id_user = $user['id_user'];
echo "User: " . $user['nama_lengkap'] . " (ID: $id_user)<br>";

// 2. Get a valid book with stock
$book_res = mysqli_query($conn, "SELECT id_buku, judul_buku, stok FROM buku WHERE stok > 0 LIMIT 1");
if(mysqli_num_rows($book_res) == 0) die("Error: No books with stock > 0 found.");
$book = mysqli_fetch_assoc($book_res);
$id_buku = $book['id_buku'];
echo "Book: " . $book['judul_buku'] . " (ID: $id_buku, Stok: " . $book['stok'] . ")<br>";

// 3. Prepare Cart
$keranjang = [$id_buku => 1];

// 4. Test Checkout
echo "Attempting Checkout...<br>";
try {
    $result = checkout($id_user, $keranjang);
    if ($result === true) {
        echo "<strong style='color:green'>SUCCESS: Checkout returned true.</strong><br>";
        
        // Check if data exists
        $cek_peminjaman = mysqli_query($conn, "SELECT * FROM peminjaman WHERE id_user = $id_user ORDER BY id_peminjaman DESC LIMIT 1");
        $loan = mysqli_fetch_assoc($cek_peminjaman);
        echo "Created Loan ID: " . $loan['id_peminjaman'] . " | Status: " . $loan['status'] . "<br>";
        
        // Check details
        $cek_detail = mysqli_query($conn, "SELECT * FROM detail_peminjaman WHERE id_peminjaman = " . $loan['id_peminjaman']);
        echo "Details count: " . mysqli_num_rows($cek_detail) . "<br>";
        
    } else {
        echo "<strong style='color:red'>FAILED: Checkout returned message: $result</strong><br>";
    }
} catch (Exception $e) {
    echo "<strong style='color:red'>EXCEPTION: " . $e->getMessage() . "</strong><br>";
    echo "Trace: <pre>" . $e->getTraceAsString() . "</pre>";
}
?>
