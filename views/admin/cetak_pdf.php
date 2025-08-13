<?php
session_start();
include '../../config/koneksi.php';

// Set zona waktu ke Asia/Jakarta (WIB)
date_default_timezone_set('Asia/Jakarta');

// Cek apakah user sudah login dan role adalah admin
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Query untuk mengambil data pemesanan
$query = "
    SELECT 
        bookings.id, users.username, rooms.name AS room_name, 
        bookings.date, bookings.start_time, bookings.end_time,
        bookings.agenda_rapat, bookings.bidang
    FROM bookings
    JOIN users ON bookings.user_id = users.id
    JOIN rooms ON bookings.room_id = rooms.id
    ORDER BY bookings.date ASC, bookings.start_time ASC
";
$result = mysqli_query($koneksi, $query);

// Data untuk Kop Surat
$namaInstansi = "DINAS LINGKUNGAN HIDUP KABUPATEN TEGAL"; // Ganti sesuai nama instansi Anda
$alamatInstansi = "Jalan Professor Muhammad Yamin, Kudaile, Kec. Slawi, Kab.Tegal."; // Ganti sesuai alamat instansi Anda
$teleponInstansi = "(0283) 491159"; // Ganti sesuai nomor telepon instansi Anda
$emailInstansi = "dlhkabtegal@gmail.com"; // Ganti sesuai email instansi Anda

// Data untuk Penanggung Jawab
$namaPenanggungJawab = "EDY SUCIPTO, S.K.M,. M.Si"; // Ganti dengan nama penanggung jawab
$jabatanPenanggungJawab = "Kepala Dinas"; // Ganti dengan jabatan penanggung jawab
$nipPenanggungJawab = "NIP.197109071998031007"; // Ganti dengan NIP penanggung jawab

$tanggalCetak = date('d-m-Y'); // Tanggal cetak saat ini (akan sesuai WIB)
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penggunaan Ruang Rapat</title>
    <style>
    body {
        font-family: 'Times New Roman', Times, serif;
        margin: 0;
        padding: 20px;
        font-size: 12pt;
    }

    .container {
        width: 100%;
        margin: 0 auto;
    }

    .header {
        text-align: center;
        margin-bottom: 30px;
        border-bottom: 3px solid #000;
        padding-bottom: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .header img {
        max-height: 80px;
        /* Sesuaikan ukuran logo */
        margin-right: 20px;
    }

    .header-text {
        text-align: center;
        flex-grow: 1;
    }

    .header-text h1 {
        margin: 0;
        font-size: 18pt;
        text-transform: uppercase;
    }

    .header-text h2 {
        margin: 0;
        font-size: 14pt;
        text-transform: uppercase;
    }

    .header-text p {
        margin: 0;
        font-size: 10pt;
    }

    .title {
        text-align: center;
        font-size: 16pt;
        font-weight: bold;
        margin-bottom: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    table,
    th,
    td {
        border: 1px solid black;
    }

    th,
    td {
        padding: 8px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }

    .signature-section {
        display: flex;
        justify-content: flex-end;
        /* Pindahkan ke kanan */
        margin-top: 50px;
        page-break-inside: avoid;
        /* Mencegah section ini terpotong antar halaman */
    }

    .signature-block {
        text-align: center;
        width: 300px;
        /* Lebar blok tanda tangan */
    }

    .signature-block p {
        margin: 5px 0;
    }

    @media print {
        body {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="../../image/dlh.png" alt="Logo Instansi">
            <div class="header-text">
                <h1><?= $namaInstansi ?></h1>
                <h2>PROVINSI JAWA TENGAH</h2>
                <p><?= $alamatInstansi ?> - Telp: <?= $teleponInstansi ?> - Email: <?= $emailInstansi ?></p>
            </div>
        </div>

        <div class="title">
            LAPORAN PENGGUNAAN RUANG RAPAT
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Pemesan</th>
                    <th>Ruang Rapat</th>
                    <th>Tanggal</th>
                    <th>Waktu</th>
                    <th>Bidang</th>
                    <th>Agenda</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                // Pastikan result set di-reset sebelum looping, berguna jika $result sudah pernah diakses sebelumnya
                mysqli_data_seek($result, 0); 
                while ($booking = mysqli_fetch_assoc($result)) {
                    $tanggal = (new DateTime($booking['date']))->format('d-m-Y');
                    $waktu = (new DateTime($booking['start_time']))->format('H:i') . " - " . (new DateTime($booking['end_time']))->format('H:i');
                    echo "<tr>
                        <td>{$no}</td>
                        <td>" . htmlspecialchars($booking['username']) . "</td>
                        <td>" . htmlspecialchars($booking['room_name']) . "</td>
                        <td>{$tanggal}</td>
                        <td>{$waktu}</td>
                        <td>" . htmlspecialchars($booking['bidang']) . "</td>
                        <td>" . htmlspecialchars($booking['agenda_rapat']) . "</td>
                    </tr>";
                    $no++;
                }
                ?>
            </tbody>
        </table>

        <div class="signature-section">
            <div class="signature-block">
                <p>Tegal, <?= $tanggalCetak ?></p>
                <p><?= $jabatanPenanggungJawab ?></p>
                <br><br><br>
                <p><b><?= $namaPenanggungJawab ?></b></p>
                <p><?= $nipPenanggungJawab ?></p>
            </div>
        </div>
    </div>

    <script>
    // Otomatis cetak saat halaman dimuat
    window.onload = function() {
        window.print();
    };
    </script>
</body>

</html>