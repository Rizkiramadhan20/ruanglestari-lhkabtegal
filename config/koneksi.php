<?php
    $koneksi = mysqli_connect("localhost", "root", "", "booking");

    // Check connection
    if (!$koneksi) {
        die("Koneksi database gagal: " . mysqli_connect_error());
    } 
    // else {
    //     echo "Koneksi berhasil!";

    // }
?>
