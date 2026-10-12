<?php
require 'functions/functions.php';

// Script ini akan menginstall Trigger langsung ke database
// tanpa perlu buka phpMyAdmin.

$triggers = [];

// 1. TRIGGER KURANGI STOK (Checkout)
$triggers['kurangi_stok'] = "
CREATE TRIGGER kurangi_stok 
AFTER INSERT ON detail_peminjaman
FOR EACH ROW
BEGIN
    UPDATE buku SET stok = stok - 1 WHERE id_buku = NEW.id_buku;
END";

// 2. TRIGGER KEMBALIKAN STOK (Update Status)
$triggers['kembalikan_stok_update'] = "
CREATE TRIGGER kembalikan_stok_update
AFTER UPDATE ON peminjaman
FOR EACH ROW
BEGIN
    IF (NEW.status IN ('kembali', 'ditolak')) AND (OLD.status NOT IN ('kembali', 'ditolak')) THEN
        UPDATE buku b
        JOIN detail_peminjaman dp ON b.id_buku = dp.id_buku
        SET b.stok = b.stok + 1
        WHERE dp.id_peminjaman = NEW.id_peminjaman;
    END IF;
END";

echo "<h1>Setup Database Triggers</h1>";

foreach ($triggers as $name => $sql) {
    // 1. Drop trigger lama (jika ada/rusak)
    mysqli_query($conn, "DROP TRIGGER IF EXISTS $name");
    
    // 2. Install trigger baru
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color:green'>✅ Trigger <b>$name</b> berhasil diinstall.</p>";
    } else {
        echo "<p style='color:red'>❌ Gagal install <b>$name</b>: " . mysqli_error($conn) . "</p>";
    }
}

echo "<hr><p>Selesai. Silakan coba transaksi lagi.</p>";
echo "<a href='index.php'>Kembali ke Home</a>";
?>
