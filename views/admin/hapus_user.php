<?php
include '../../config/koneksi.php';

$id = $_GET['id']; // Ambil id pengguna yang akan dihapus

// Hapus semua peminjaman yang terkait dengan pengguna tersebut
$queryBookings = "DELETE FROM bookings WHERE user_id = ?";
$stmtBookings = mysqli_prepare($koneksi, $queryBookings);
mysqli_stmt_bind_param($stmtBookings, 'i', $id);
mysqli_stmt_execute($stmtBookings);

// Hapus pengguna
$queryUsers = "DELETE FROM users WHERE id = ?";
$stmtUsers = mysqli_prepare($koneksi, $queryUsers);
mysqli_stmt_bind_param($stmtUsers, 'i', $id);

if (mysqli_stmt_execute($stmtUsers)) {
    header("Location: data-user.php");
    exit(); // Pastikan untuk keluar setelah redirect
} else {
    echo "Gagal menghapus pengguna!";
}

// Tutup statement
mysqli_stmt_close($stmtBookings);
mysqli_stmt_close($stmtUsers);

// Tutup koneksi
mysqli_close($koneksi);
?>