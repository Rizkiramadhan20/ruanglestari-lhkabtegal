<?php
session_start();
include '../../config/koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Hapus booking jika ada permintaan
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $deleteQuery = "DELETE FROM bookings WHERE id = '$delete_id'";
    mysqli_query($koneksi, $deleteQuery);
}

// Ambil data booking
$query = "
    SELECT 
        bookings.id, 
        users.username AS username, 
        rooms.name AS room_name, 
        bookings.date, 
        bookings.start_time, 
        bookings.end_time,
        bookings.agenda_rapat,
        bookings.bidang
    FROM 
        bookings
    JOIN 
        users ON bookings.user_id = users.id
    JOIN 
        rooms ON bookings.room_id = rooms.id
     ORDER BY 
        bookings.date ASC, bookings.start_time ASC
"; // Mengurutkan berdasarkan tanggal dan waktu
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Ruang Lestari - Riwayat Pesanan</title>
    <style>
        .poppins { font-family: 'Poppins', sans-serif; }
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
                    <!-- Tambahkan tombol Share dan Print di sini -->
<div class="flex gap-3 mt-5">
    <button onclick="window.print()" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition duration-200 ease-in-out">
        <i class="fas fa-print"></i> Print
    </button>
    <button onclick="sharePage()" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 transition duration-200 ease-in-out">
        <i class="fas fa-share"></i> Share
    </button>
</div>

<script>
    // Fungsi untuk membagikan halaman
    function sharePage() {
        if (navigator.share) {
            navigator.share({
                title: 'Riwayat Pesanan Ruang Lestari',
                url: window.location.href
            }).then(() => {
                console.log('Terima kasih telah membagikan!');
            }).catch((error) => {
                console.error('Error sharing:', error);
                alert('Gagal membagikan. Silakan salin URL secara manual.');
            });
        } else {
            // Fallback untuk browser yang tidak mendukung navigator.share
            alert('Browser Anda tidak mendukung fitur share. Silakan salin URL secara manual: ' + window.location.href);
        }
    }
</script>
                    <div class="relative overflow-x-auto h-[500px]">
                        <table class="w-full text-sm text-left text-blue-500">
                            <thead class="text-xs text-white uppercase bg-[#3E5879]">
                                <tr>
                                    <th scope="col" class="px-6 py-3">No</th>
                                    <th scope="col" class="px-6 py-3">Nama Pemesan</th>
                                    <th scope="col" class="px-6 py-3">Ruang Rapat</th>
                                    <th scope="col" class="px-6 py-3">Date</th>
                                    <th scope="col" class="px-6 py-3">Waktu</th>
                                    <th scope="col" class="px-6 py-3">Bidang</th>
                                    <th scope="col" class="px-6 py-3">Agenda Rapat</th>
                                    <th scope="col" class="px-6 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                 $index = 1; 
                                 while ($booking = mysqli_fetch_assoc($result)) { 
                                     // Mengubah format tanggal
                                     $date = new DateTime($booking['date']);
                                     $formattedDate = $date->format('d-m-Y'); // Format DD-MM-YYYY
                             ?>
                                    <tr class="bg-white border-b text-black text-xs">
                                        <td class="px-6 py-4"><?php echo $index++; ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($booking['username']); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($booking['room_name']); ?></td>
                                        <td class="px-6 py-4"><?php echo $formattedDate; ?></td> <!-- Tampilkan tanggal yang sudah diformat -->
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($booking['start_time']); ?> - <?php echo htmlspecialchars($booking['end_time']); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($booking['bidang']); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($booking['agenda_rapat']); ?></td>
                                        <td class="px-6 py-4">
                                            <a href="?delete_id=<?php echo $booking['id']; ?>" class="text-red-500 hover:text-red-700">Hapus</a>
                                        </td>
                                    </tr>
                                <?php 
                                }    
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Fungsi untuk membagikan halaman
        function sharePage() {
            if (navigator.share) {
                navigator.share({
                    title: 'Riwayat Pesanan Ruang Lestari',
                    url: window.location.href
                }).then(() => {
                    console.log('Terima kasih telah membagikan!');
                }).catch(console.error);
            } else {
                alert('Browser Anda tidak mendukung fitur share. Silakan salin URL secara manual.');
            }
        }
    </script>
</body>
</html>
</body>
</html>