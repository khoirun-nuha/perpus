<?php
require 'config/database.php';

// Force delete all loans for user 2 (Ayu)
mysqli_query($conn, "DELETE FROM detail_peminjaman WHERE id_peminjaman IN (SELECT id_peminjaman FROM peminjaman WHERE id_user = 2)");
mysqli_query($conn, "DELETE FROM peminjaman WHERE id_user = 2");

echo "Deleted all loans for User ID 2.<br>";

// Check if clean
$res = mysqli_query($conn, "SELECT COUNT(*) as c FROM peminjaman WHERE id_user = 2");
$row = mysqli_fetch_assoc($res);
echo "Remaining loans: " . $row['c'];
?>
