<?php
session_start();
include '../../config/koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    header("Location: index.php");
    exit;
}

$today = date('Y-m-d');

// Query untuk menghitung jumlah semua rooms
$roomQuery = "SELECT COUNT(*) AS room_count FROM rooms";
$roomResult = mysqli_query($koneksi, $roomQuery);
$roomData = mysqli_fetch_assoc($roomResult);
$roomCount = $roomData['room_count'];

$user_id = $_SESSION['id_user'];  // Ensure user_id is stored in session

// Query for booking history count
$orderHistoryQuery = "SELECT COUNT(*) AS booking_count FROM bookings WHERE user_id = '$user_id'";
$orderHistoryResult = mysqli_query($koneksi, $orderHistoryQuery);
$orderHistoryData = mysqli_fetch_assoc($orderHistoryResult);
$orderHistoryCount = $orderHistoryData['booking_count'];  // Corrected key

// Fetch bookings for today based on user_id and current date
$orderTodayQuery = "SELECT COUNT(*) AS booking_count FROM bookings WHERE user_id = '$user_id' AND DATE(created_at) = '$today'";  // Adjusted column name
$orderTodayResult = mysqli_query($koneksi, $orderTodayQuery);
$orderTodayData = mysqli_fetch_assoc($orderTodayResult);
$orderTodayCount = $orderTodayData['booking_count'];  // Corrected key


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="image/dlh.png" type="image/png" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../../styles/accessibility.css">
    <title>Ruang Lestari</title>
    <style>
    .poppins {
        font-family: 'Poppins', sans-serif;
    }

    .montserrat {
        font-family: "Montserrat", serif;
    }

    .inter {
        font-family: "Inter", serif;
    }
    </style>
</head>

<body>
    <!-- Skip to content link for accessibility -->
    <a href="#main-content" class="skip-link sr-only">Langsung ke konten utama</a>

    <!-- Fitur Aksesibilitas - Selalu Tersedia -->
    <div class="fixed bottom-4 right-4 z-50">
        <!-- Toggle Button Aksesibilitas -->
        <button onclick="toggleAccessibility()" title="Aksesibilitas"
            class="bg-blue-600 hover:bg-blue-700 text-white p-3 rounded-full shadow-lg transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
            <i class="fas fa-universal-access text-lg"></i>
        </button>

        <!-- Dropdown Menu Aksesibilitas -->
        <div id="accessibilityMenu"
            class="hidden absolute bottom-16 right-0 w-80 bg-white rounded-lg shadow-xl border border-gray-200 p-6 z-50">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Pengaturan Aksesibilitas</h3>
                <button onclick="toggleAccessibility()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Pembaca Teks Otomatis -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-sm font-medium text-gray-700">Pembaca Teks Otomatis</label>
                    <button onclick="toggleTextReader()" id="textReaderBtn"
                        class="w-12 h-6 bg-gray-300 rounded-full relative transition-colors">
                        <div id="textReaderToggle"
                            class="w-5 h-5 bg-white rounded-full absolute top-0.5 left-0.5 transition-transform"></div>
                    </button>
                </div>
                <p class="text-xs text-gray-500">Aktifkan untuk membaca konten halaman secara otomatis</p>

                <!-- Tombol Baca Ulang -->
                <div class="mt-3">
                    <button onclick="startTextReader()"
                        class="w-full px-3 py-2 bg-green-500 text-white rounded text-sm hover:bg-green-600 transition-colors">
                        <i class="fas fa-play mr-2"></i>Baca Ulang Sekarang
                    </button>
                </div>
            </div>

            <!-- Pengaturan Ukuran Font -->
            <div class="mb-6">
                <label class="text-sm font-medium text-gray-700 mb-3 block">Ukuran Font</label>
                <div class="flex gap-2">
                    <button onclick="changeFontSize('decrease')"
                        class="px-4 py-2 bg-blue-500 text-white rounded text-sm hover:bg-blue-600 transition-colors">
                        <i class="fas fa-minus"></i> A-
                    </button>
                    <button onclick="changeFontSize('reset')"
                        class="px-4 py-2 bg-gray-500 text-white rounded text-sm hover:bg-gray-600 transition-colors">
                        Reset
                    </button>
                    <button onclick="changeFontSize('increase')"
                        class="px-4 py-2 bg-blue-500 text-white rounded text-sm hover:bg-blue-600 transition-colors">
                        <i class="fas fa-plus"></i> A+
                    </button>
                </div>
            </div>

            <!-- Kontras Tinggi -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-sm font-medium text-gray-700">Kontras Tinggi</label>
                    <button onclick="toggleHighContrast()" id="contrastBtn"
                        class="w-12 h-6 bg-gray-300 rounded-full relative transition-colors">
                        <div id="contrastToggle"
                            class="w-5 h-5 bg-white rounded-full absolute top-0.5 left-0.5 transition-transform"></div>
                    </button>
                </div>
                <p class="text-xs text-gray-500">Tingkatkan kontras untuk kemudahan membaca</p>
            </div>

            <!-- Keyboard Shortcuts Info -->
            <div class="bg-gray-50 p-3 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Keyboard Shortcuts:</h4>
                <ul class="text-xs text-gray-600 space-y-1">
                    <li><kbd class="bg-gray-200 px-1 rounded">Ctrl + Alt + A</kbd> - Toggle Aksesibilitas</li>
                    <li><kbd class="bg-gray-200 px-1 rounded">Ctrl + Alt + T</kbd> - Pembaca Teks</li>
                    <li><kbd class="bg-gray-200 px-1 rounded">Ctrl + Alt + C</kbd> - Kontras Tinggi</li>
                    <li><kbd class="bg-gray-200 px-1 rounded">Ctrl + Alt + +/-</kbd> - Ukuran Font</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="w-full h-max min-h-screen poppins">
        <div class="flex items-center w-full h-full ">
            <!-- Sidebar -->
            <?php include '../../components/SidebarUser.php'; ?>

            <!-- Home -->
            <div class="flex w-full h-full ml-0 md:ml-[270px] flex flex-col ">
                <?php include '../../components/NavbarUser.php'; ?>

                <div class="w-full h-full flex flex-col gap-10 px-7 py-10">
                    <div class="flex items-center gap-3 text-[#3E5879]">
                        <i class="fas fa-home text-xl"></i>
                        <h1 class="font-medium">Dashboard</h1>
                    </div>
                    <div class="w-full h-max grid xl:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-5">
                        <div class="h-[150px] bg-white shadow flex flex-col items-center gap-3 text-[#3E5879] py-5">
                            <i class="fas fa-hotel text-2xl "></i>
                            <div class="flex flex-col items-center gap-1">
                                <p class="font-medium"><?php echo $roomCount; ?></p>
                                <p>Jumlah Ruangan</p>
                            </div>
                        </div>
                        <div class="h-[150px] bg-white shadow flex flex-col items-center gap-3 text-[#3E5879] py-5">
                            <i class="fas fa-history text-2xl "></i>
                            <div class="flex flex-col items-center gap-1">
                                <p class="font-medium"><?php echo $orderHistoryCount; ?></p>
                                <p>Riwayat Pesanan</p>
                            </div>
                        </div>
                        <div class="h-[150px] bg-white shadow flex flex-col items-center gap-3 text-[#3E5879] py-5">
                            <i class="fas fa-history text-2xl "></i>
                            <div class="flex flex-col items-center gap-1">
                                <p class="font-medium"><?php echo $orderTodayCount; ?></p>
                                <p>Pesanan Hari ini</p>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
        ...
    </div> <!-- penutup konten utama -->

    <!-- Floating Button WhatsApp -->
    <a href="https://wa.me/6285640446387" target="_blank" title="Hubungi Admin via WhatsApp"
        class="fixed bottom-20 right-6 z-50 bg-green-500 hover:bg-green-600 text-white rounded-full shadow-lg p-4 flex items-center justify-center slide-down">
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="h-6 w-6" viewBox="0 0 16 16">
            <path
                d="M13.601 2.326A7.875 7.875 0 008.005.07C3.58.07 0 3.657 0 8.085a7.9 7.9 0 001.113 4.08L0 16l3.93-1.024a7.945 7.945 0 004.067 1.064c4.425 0 8.005-3.587 8.005-8.015a7.976 7.976 0 00-2.401-5.699zm-5.59 11.51a6.6 6.6 0 01-3.349-.908l-.24-.144-2.333.607.622-2.27-.157-.233a6.533 6.533 0 01-1.007-3.498 6.572 6.572 0 016.595-6.56 6.526 6.526 0 014.668 1.929 6.536 6.536 0 011.927 4.674 6.586 6.586 0 01-6.595 6.603zm3.632-4.965c-.2-.1-1.178-.582-1.36-.648-.182-.067-.315-.1-.448.1s-.515.648-.63.782c-.116.134-.232.15-.432.05-.2-.1-.84-.31-1.6-.99-.592-.527-.99-1.178-1.107-1.378-.116-.2-.012-.308.088-.408.091-.09.2-.232.3-.348.1-.116.134-.2.2-.334.067-.134.033-.25-.017-.35-.05-.1-.448-1.08-.613-1.482-.162-.39-.327-.337-.448-.344l-.382-.007c-.134 0-.35.05-.534.25s-.7.682-.7 1.66.717 1.926.817 2.057c.1.134 1.407 2.15 3.413 3.01.478.206.85.33 1.14.422.478.152.915.13 1.26.079.385-.058 1.178-.48 1.345-.944.166-.465.166-.865.116-.944-.05-.075-.182-.116-.382-.216z" />
        </svg>
    </a>

    <style>
    /* Animasi detak jantung */
    @keyframes heartbeat {
        0% {
            transform: scale(1);
        }

        25% {
            transform: scale(1.08);
        }

        40% {
            transform: scale(1);
        }

        60% {
            transform: scale(1.08);
        }

        100% {
            transform: scale(1);
        }
    }

    /* Animasi turun dari atas dengan posisi akhir agak terangkat */
    @keyframes slideDown {
        0% {
            transform: translateY(-250px);
            opacity: 0;
        }

        80% {
            transform: translateY(-10px);
            /* sedikit overshoot ke atas */
            opacity: 1;
        }

        100% {
            transform: translateY(-15px);
            /* berhenti lebih tinggi dari posisi default */
            opacity: 1;
        }
    }

    /* Gabungan animasi */
    .slide-down {
        animation:
            slideDown 1s ease-out forwards,
            heartbeat 4s ease-in-out infinite;
        animation-delay: 0s, 1s;
    }
    </style>

    <!-- Load accessibility JavaScript -->
    <script src="../../styles/accessibility.js"></script>