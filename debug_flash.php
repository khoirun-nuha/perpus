<?php
session_start();
$_SESSION['flash'] = [
    'type' => 'success',
    'title' => 'Test Flash Berhasil',
    'message' => 'Jika Anda melihat ini, berarti sistem notifikasi berfungsi!'
];
echo "Set flash message. Redirecting to riwayat.php...";
header("Location: siswa/riwayat.php");
exit;
?>
