<?php
require 'functions/functions.php';

require 'config/database.php';

// Simulate a user and a cart
// $conn is already available from config/database.php

// 1. Get a valid user
$user_res = mysqli_query($conn, "SELECT id_user FROM user WHERE role = 'peminjam' LIMIT 1");
if(mysqli_num_rows($user_res) == 0) {
    die("Error: No 'peminjam' user found.");
}
$user = mysqli_fetch_assoc($user_res);
$id_user = $user['id_user'];

// 2. Get a valid book
$book_res = mysqli_query($conn, "SELECT id_buku, stok FROM buku WHERE stok > 0 LIMIT 1");
if(mysqli_num_rows($book_res) == 0) {
    die("Error: No books with stock > 0 found.");
}
$book = mysqli_fetch_assoc($book_res);
$id_buku = $book['id_buku'];

// 3. Create a dummy cart (session format usually, but checkout takes array)
$keranjang = [
    $id_buku => 1
];

echo "Testing Checkout for User ID: $id_user with Book ID: $id_buku<br>";

// 4. Call checkout
// Modify checkout temporarily to print errors if needed, or wrap in try-catch if it throws
// But functions.php usually returns affected rows.

$result = checkout($id_user, $keranjang);

if ($result > 0) {
    echo "Success! Checkout returned: $result<br>";
} else {
    echo "Failed! Checkout returned: $result<br>";
    echo "MySQL Error: " . mysqli_error($conn) . "<br>";
}

?>
