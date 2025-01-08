<?php
    include '../../config/koneksi.php';

    $id = $_GET['id']; // Ambil id_buku yang akan dihapus

    // Hapus peminjaman yang terkait dengan buku tersebut
    $query = "DELETE FROM users WHERE id = '$id'";
    mysqli_query($koneksi, $query);

    if (mysqli_query($koneksi, $query)) {
        header("Location: data-user.php");
    } else {
        echo "Gagal menghapus buku!";
    }
?>
