<?php
    $host     = "localhost";
    $user     = "root"; // Default XAMPP username
    $password = "";     // Default XAMPP password (kosong)
    $database = "ruangles_reservasi";

    // Try to connect with default XAMPP credentials first
    $koneksi = mysqli_connect($host, $user, $password, $database);

    // Check connection
    if (!$koneksi) {
        // If connection fails, try with the provided credentials
        $user     = "ruangles_userdb";
        $password = "dlhkabtegal2024";
        
        $koneksi = mysqli_connect($host, $user, $password, $database);
        
        if (!$koneksi) {
            die("
            <div style='font-family: Arial, sans-serif; padding: 20px;'>
                <h2>Koneksi Database Gagal</h2>
                <p><strong>Error:</strong> " . mysqli_connect_error() . "</p>
                <h3>Solusi:</h3>
                <ol>
                    <li>Pastikan MySQL di XAMPP sudah dijalankan</li>
                    <li>Buka phpMyAdmin di <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>
                    <li>Buat database baru dengan nama: <strong>ruangles_reservasi</strong></li>
                    <li>Jika menggunakan user 'ruangles_userdb', pastikan user tersebut sudah dibuat dan memiliki akses ke database</li>
                    <li>Atau gunakan user default XAMPP: root (tanpa password)</li>
                </ol>
            </div>
            ");
        }
    }

    // Set charset to utf8
    mysqli_set_charset($koneksi, "utf8");
?>