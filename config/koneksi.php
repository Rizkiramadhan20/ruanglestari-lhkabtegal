<?php
    $koneksi = mysqli_connect("localhost", "root", "", "booking_room");

    // Check connection
    if (!$koneksi) {
        die("Koneksi database gagal: " . mysqli_connect_error());
    } 
    // else {
    //     echo "Koneksi berhasil!";

    // }
?>
