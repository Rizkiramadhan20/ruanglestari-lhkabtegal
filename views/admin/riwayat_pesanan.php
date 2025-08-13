<?php
session_start();
include '../../config/koneksi.php';

$tahun = date("Y");
$folder = __DIR__ . '/exports/';
$files = glob($folder . 'RiwayatPesanan_*.xlsx');
foreach ($files as $file) {
    if (preg_match('/RiwayatPesanan_(\d{4})\.xlsx$/', $file, $match)) {
        if ((int)$match[1] < $tahun) {
            unlink($file);
        }
    }
}

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $deleteQuery = "DELETE FROM bookings WHERE id = '$delete_id'";
    mysqli_query($koneksi, $deleteQuery);
}

$query = "
    SELECT 
        bookings.id, users.username, rooms.name AS room_name, 
        bookings.date, bookings.start_time, bookings.end_time,
        bookings.agenda_rapat, bookings.bidang
    FROM bookings
    JOIN users ON bookings.user_id = users.id
    JOIN rooms ON bookings.room_id = rooms.id
ORDER BY bookings.date DESC, bookings.start_time DESC
";
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="image/dlh.png" type="image/png" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Ruang Lestari - Riwayat Pesanan</title>
    <style>
    .poppins {
        font-family: 'Poppins', sans-serif;
    }

    .btn {
        @apply flex items-center gap-2 px-4 py-2 rounded font-semibold transition-transform transition-shadow duration-300 ease-in-out;
        cursor: pointer;
    }

    .btn-pdf {
        background: linear-gradient(45deg, #ef4444, #dc2626);
        color: white;
        box-shadow: 0 4px 6px rgba(220, 38, 38, 0.4);
    }

    .btn-pdf:hover {
        background: linear-gradient(45deg, #fa5252, #b91c1c);
        transform: scale(1.1);
        box-shadow: 0 8px 15px rgba(220, 38, 38, 0.7);
    }

    .btn-excel {
        background: linear-gradient(45deg, #22c55e, #16a34a);
        color: white;
        box-shadow: 0 4px 6px rgba(34, 197, 94, 0.4);
    }

    .btn-excel:hover {
        background: linear-gradient(45deg, #4ade80, #15803d);
        transform: scale(1.1);
        box-shadow: 0 8px 15px rgba(34, 197, 94, 0.7);
    }
    </style>
</head>

<body>
    <div class="w-full h-max min-h-screen poppins">
        <div class="flex items-center w-full h-full">
            <?php include '../../components/SidebarAdmin.php'; ?>
            <div class="flex w-full h-max min-h-screen ml-0 md:ml-[270px] flex flex-col bg-gray-100">
                <?php include '../../components/NavbarAdmin.php'; ?>
                <div class="w-full h-full flex flex-col gap-10 px-7 py-7">
                    <div class="flex items-center gap-3 text-[#3E5879]">
                        <i class="fas fa-hotel text-xl"></i>
                        <h1 class="font-medium">Riwayat Pesanan</h1>
                    </div>

                    <!-- Tombol Export PDF dan Excel - Responsive -->
                    <div class="flex flex-col sm:flex-row gap-3 mt-5 w-full sm:w-auto">
                        <a href="cetak_pdf.php" class="btn btn-pdf" title="Cetak PDF" target="_blank">
                            <i class="fas fa-file-pdf fa-lg"></i>
                            <span>Laporan PDF</span>
                        </a>
                        <a href="export_excel.php?tanggal=<?= date('Y-m-d') ?>" class="btn btn-excel"
                            title="Cetak Excel">
                            <i class="fas fa-file-excel fa-lg"></i>
                            <span>Laporan Excel</span>
                        </a>
                    </div>


                    <!-- Tabel -->
                    <div class="relative overflow-x-auto h-[500px] max-w-full block">
                        <table class="w-full min-w-full text-sm text-left text-blue-500 shadow-md rounded-lg">
                            <thead class="text-xs text-white uppercase bg-[#3E5879]">
                                <tr>
                                    <th class="px-6 py-3">No</th>
                                    <th class="px-6 py-3">Nama Pemesan</th>
                                    <th class="px-6 py-3">Ruang Rapat</th>
                                    <th class="px-6 py-3">Tanggal</th>
                                    <th class="px-6 py-3">Waktu</th>
                                    <th class="px-6 py-3">Bidang</th>
                                    <th class="px-6 py-3">Agenda</th>
                                    <th class="px-6 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                while ($booking = mysqli_fetch_assoc($result)) {
                                    $tanggal = (new DateTime($booking['date']))->format('d-m-Y');
                                    $waktu = (new DateTime($booking['start_time']))->format('H:i') . " - " . (new DateTime($booking['end_time']))->format('H:i');
                                    echo "<tr class='bg-white even:bg-gray-50 hover:bg-gray-100 border-b text-black text-xs'>
                                        <td class='px-6 py-4'>{$no}</td>
                                        <td class='px-6 py-4'>" . htmlspecialchars($booking['username']) . "</td>
                                        <td class='px-6 py-4'>" . htmlspecialchars($booking['room_name']) . "</td>
                                        <td class='px-6 py-4'>{$tanggal}</td>
                                        <td class='px-6 py-4'>{$waktu}</td>
                                        <td class='px-6 py-4'>" . htmlspecialchars($booking['bidang']) . "</td>
                                        <td class='px-6 py-4'>" . htmlspecialchars($booking['agenda_rapat']) . "</td>
                                        <td class='px-6 py-4'><a href='?delete_id={$booking['id']}' class='text-red-500 hover:text-red-700'>Hapus</a></td>
                                    </tr>";
                                    $no++;
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>