<?php
session_start();
include '../../config/koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Query untuk menghitung jumlah users dengan role 'user'
$userQuery = "SELECT COUNT(*) AS user_count FROM users WHERE role = 'user'";
$userResult = mysqli_query($koneksi, $userQuery);
$userData = mysqli_fetch_assoc($userResult);
$userCount = $userData['user_count'];

// Query untuk menghitung jumlah semua rooms
$roomQuery = "SELECT COUNT(*) AS room_count FROM rooms";
$roomResult = mysqli_query($koneksi, $roomQuery);
$roomData = mysqli_fetch_assoc($roomResult);
$roomCount = $roomData['room_count'];

// Query untuk menghitung jumlah semua bookings
$bookingQuery = "SELECT COUNT(*) AS booking_count FROM bookings";
$bookingResult = mysqli_query($koneksi, $bookingQuery);
$bookingData = mysqli_fetch_assoc($bookingResult);
$bookingCount = $bookingData['booking_count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Ruang Lestari - Admin Dashboard</title>
    <style>
        .poppins { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body>
    <div class="w-full h-max min-h-screen poppins">
        <div class="flex items-center w-full h-full">
            <?php include '../../components/SidebarAdmin.php'; ?>
            <div class="flex w-full h-full ml-0 md:ml-[270px] flex flex-col">
                <?php include '../../components/NavbarAdmin.php'; ?>
                <div class="w-full h-full flex flex-col gap-10 px-7 py-10">
                    <div class="flex items-center gap-3 text-[#3E5879]">
                        <i class="fas fa-home text-xl"></i>
                        <h1 class="font-medium">Dashboard</h1>
                    </div>
                    <div class="w-full h-max grid xl:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-5">
                        <div class="h-[150px] bg-white shadow flex flex-col items-center gap-3 text-[#3E5879] py-5">
                            <i class="fas fa-hotel text-2xl"></i>
                            <div class="flex flex-col items-center gap-1">
                                <p class="font-medium"><?php echo $roomCount; ?></p>
                                <p>Jumlah Ruangan</p>
                            </div>
                        </div>
                        <div class="h-[150px] bg-white shadow flex flex-col items-center gap-3 text-[#3E5879] py-5">
                            <i class="fas fa-user text-2xl"></i>
                            <div class="flex flex-col items-center gap-1">
                                <p class="font-medium"><?php echo $userCount; ?></p>
                                <p>Jumlah User</p>
                            </div>
                        </div>
                        <div class="h-[150px] bg-white shadow flex flex-col items-center gap-3 text-[#3E5879] py-5">
                            <i class="fas fa-history text-2xl"></i>
                            <div class="flex flex-col items-center gap-1">
                                <p class="font-medium"><?php echo $bookingCount; ?></p>
                                <p>Jumlah Pesanan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>