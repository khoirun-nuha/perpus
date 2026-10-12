<?php
require 'config/database.php';

echo "<h2>Checking for Uncategorized Books...</h2>";

// Check for NULL id_kategori
$query_null = "SELECT * FROM buku WHERE id_kategori IS NULL OR id_kategori = 0";
$result_null = mysqli_query($conn, $query_null);

$count = 0;
while ($row = mysqli_fetch_assoc($result_null)) {
    echo "Found Book (NULL/0 Kategori): " . $row['judul_buku'] . " (ID: " . $row['id_buku'] . ")<br>";
    $count++;
}

// Check for Invalid id_kategori (FK mismatch)
// This finds books where id_kategori exists but is not in the kategori table
$query_invalid = "SELECT b.id_buku, b.judul_buku, b.id_kategori 
                  FROM buku b 
                  LEFT JOIN kategori k ON b.id_kategori = k.id_kategori 
                  WHERE k.id_kategori IS NULL AND b.id_kategori IS NOT NULL AND b.id_kategori > 0";
$result_invalid = mysqli_query($conn, $query_invalid);

while ($row = mysqli_fetch_assoc($result_invalid)) {
    echo "Found Book (Invalid Kategori ID " . $row['id_kategori'] . "): " . $row['judul_buku'] . " (ID: " . $row['id_buku'] . ")<br>";
    $count++;
}

if ($count == 0) {
    echo "No uncategorized books found.";
} else {
    echo "<br><strong>Total found: $count</strong>";
}
?>
