<?php
    include '../../config/koneksi.php';

    $id = $_GET['id']; // Ambil id room yang akan dihapus

    // Periksa apakah ada data booking yang terkait dengan room
    $queryBooking = "SELECT * FROM bookings WHERE room_id = '$id'";
    $result = mysqli_query($koneksi, $queryBooking);

    if (mysqli_num_rows($result) > 0) {
        // Jika ada booking terkait, hapus data booking terlebih dahulu
        $queryDeleteBooking = "DELETE FROM bookings WHERE room_id = '$id'";
        mysqli_query($koneksi, $queryDeleteBooking);
    }

    // Hapus data dari tabel rooms
    $queryDeleteRoom = "DELETE FROM rooms WHERE id = '$id'";
    if (mysqli_query($koneksi, $queryDeleteRoom)) {
        header("Location: ruangan.php"); // Redirect setelah berhasil
    } else {
        echo "Gagal menghapus room!";
    }
?>