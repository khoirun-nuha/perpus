<?php
require 'config/database.php';

// IDs from previous check: 1, 2, 9 (Dynamic check better)
$ids_to_delete = [];

// Find IDs again
$query = "SELECT id_buku FROM buku WHERE id_kategori IS NULL OR id_kategori = 0 OR id_kategori NOT IN (SELECT id_kategori FROM kategori)";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $ids_to_delete[] = $row['id_buku'];
}

if (empty($ids_to_delete)) {
    die("No uncategorized books found to delete.");
}

$id_list = implode(',', $ids_to_delete);
echo "Deleting books with IDs: $id_list <br>";

// 1. Delete dependent detail_peminjaman
$del_details = mysqli_query($conn, "DELETE FROM detail_peminjaman WHERE id_buku IN ($id_list)");
echo "Deleted " . mysqli_affected_rows($conn) . " detail_peminjaman records.<br>";

// 2. Delete the books
$del_books = mysqli_query($conn, "DELETE FROM buku WHERE id_buku IN ($id_list)");
if ($del_books) {
    echo "Successfully deleted " . mysqli_affected_rows($conn) . " books.<br>";
} else {
    echo "Error deleting books: " . mysqli_error($conn);
}
?>
