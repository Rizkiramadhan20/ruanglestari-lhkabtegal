<?php
session_start();
include '../../config/koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    header("Location: index.php");
    exit;
}

// Ambil nilai pencarian jika ada
$search = isset($_GET['search']) ? $_GET['search'] : '';

$id = $_SESSION['id_user'];

// Mulai query dasar
$query_base = "
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
";

// Tambahkan filter pencarian jika ada
if (!empty($search)) {
    // Escape string agar aman dari SQL Injection
    $search_esc = mysqli_real_escape_string($koneksi, $search);
    $query_base .= " WHERE rooms.name LIKE '%$search_esc%'";
}

// Urutkan berdasarkan tanggal dan waktu
$query = $query_base . " ORDER BY bookings.date DESC, bookings.start_time DESC";

// Eksekusi query
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="image/dlh.png" type="image/png" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet" />
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

    <div class="w-full h-max min-h-screen poppins">
        <div class="flex items-center w-full h-full ">
            <!-- Sidebar -->
            <?php include '../../components/SidebarUser.php'; ?>

            <!-- Home -->
            <div class="flex w-full h-max min-h-screen ml-0 md:ml-[270px] flex flex-col bg-gray-100">
                <?php include '../../components/NavbarUser.php'; ?>
                <div class="w-full h-full flex flex-col gap-10 px-7 py-7">
                    <div class="flex items-center gap-3 text-[#3E5879]">
                        <i class="fas fa-hotel text-xl"></i>
                        <h1 class="font-medium">Riwayat Pesanan</h1>
                    </div>

                    <div class="w-full h-full flex flex-col gap-6">
                        <div class="w-full h-max flex items-center justify-between">
                            <!-- Form pencarian -->
                            <form method="GET" class="flex items-center">
                                <input type="text" name="search"
                                    class="h-10 w-42 rounded placeholder:font-light text-sm border indent-3 focus:outline-[#3E5879]"
                                    placeholder="Search Ruangan..."
                                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                <button type="submit"
                                    class="ml-2 px-4 h-10 bg-[#3E5879] hover:opacity-80 flex items-center justify-center text-sm text-white rounded">
                                    Cari
                                </button>
                            </form>
                        </div>
                        <div class="relative overflow-x-auto h-[500px] max-w-full block">
                            <table
                                class="w-full min-w-full text-sm text-left text-blue-500 shadow-md rounded-lg overflow-hidden">
                                <thead class="text-xs text-white uppercase bg-[#3E5879]">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">No</th>
                                        <th scope="col" class="px-6 py-3">Nama Pemesan</th>
                                        <th scope="col" class="px-6 py-3">Ruang Rapat</th>
                                        <th scope="col" class="px-6 py-3">Date</th>
                                        <th scope="col" class="px-6 py-3">Waktu</th>
                                        <th scope="col" class="px-6 py-3">Bidang</th>
                                        <th scope="col" class="px-6 py-3">Agenda Rapat</th>
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
                                    <tr
                                        class="bg-white even:bg-gray-50 hover:bg-gray-100 border-b text-black text-xs transition-colors duration-200">
                                        <td class="px-6 py-4"><?php echo $index++; ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($booking['username']); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($booking['room_name']); ?>
                                        </td>
                                        <td class="px-6 py-4"><?php echo $formattedDate; ?></td>
                                        <td class="px-6 py-4">
                                            <?php
                                                $startTime = new DateTime($booking['start_time']);
                                                $endTime = new DateTime($booking['end_time']);
                                                echo $startTime->format('H:i') . ' - ' . $endTime->format('H:i');
                                                ?>
                                        </td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($booking['bidang']); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($booking['agenda_rapat']); ?>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="fixed bottom-20 md:bottom-10 flex flex-col gap-4 md:right-5 right-4 z-50">
        <!-- Floating Button WhatsApp -->
        <a href="https://wa.me/6285640446387" target="_blank" title="Hubungi Admin via WhatsApp"
            class="z-50 bg-green-500 hover:bg-green-600 text-white rounded-full shadow-lg p-4 flex items-center justify-center heartbeat">
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="h-6 w-6" viewBox="0 0 16 16">
                <path
                    d="M13.601 2.326A7.875 7.875 0 008.005.07C3.58.07 0 3.657 0 8.085a7.9 7.9 0 001.113 4.08L0 16l3.93-1.024a7.945 7.945 0 004.067 1.064c4.425 0 8.005-3.587 8.005-8.015a7.976 7.976 0 00-2.401-5.699zm-5.59 11.51a6.6 6.6 0 01-3.349-.908l-.24-.144-2.333.607.622-2.27-.157-.233a6.533 6.533 0 01-1.007-3.498 6.572 6.572 0 016.595-6.56 6.526 6.526 0 014.668 1.929 6.536 6.536 0 011.927 4.674 6.586 6.586 0 01-6.595 6.603zm3.632-4.965c-.2-.1-1.178-.582-1.36-.648-.182-.067-.315-.1-.448.1s-.515.648-.63.782c-.116.134-.232.15-.432.05-.2-.1-.84-.31-1.6-.99-.592-.527-.99-1.178-1.107-1.378-.116-.2-.012-.308.088-.408.091-.09.2-.232.3-.348.1-.116.134-.2.2-.334.067-.134.033-.25-.017-.35-.05-.1-.448-1.08-.613-1.482-.162-.39-.327-.337-.448-.344l-.382-.007c-.134 0-.35.05-.534.25s-.7.682-.7 1.66.717 1.926.817 2.057c.1.134 1.407 2.15 3.413 3.01.478.206.85.33 1.14.422.478.152.915.13 1.26.079.385-.058 1.178-.48 1.345-.944.166-.465.166-.865.116-.944-.05-.075-.182-.116-.382-.216z" />
            </svg>
        </a>

        <!-- Fitur Aksesibilitas - Selalu Tersedia -->
        <div class="z-50">
            <!-- Toggle Button Aksesibilitas -->
            <button onclick="window.accessibilityManager.toggleMenu()" title="Aksesibilitas"
                class="bg-blue-600 hover:bg-blue-700 text-white p-4 rounded-full shadow-lg transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
                <i class="fas fa-universal-access text-lg"></i>
            </button>

            <!-- Dropdown Menu Aksesibilitas -->
            <div id="accessibilityMenu"
                class="hidden absolute bottom-16 right-0 w-80 bg-white rounded-lg shadow-xl border border-gray-200 p-6 z-50">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Pengaturan Aksesibilitas</h3>
                    <button onclick="window.accessibilityManager.toggleMenu()"
                        class="text-gray-500 hover:text-gray-700 transition-colors duration-200">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <!-- Pembaca Teks Otomatis -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-medium text-gray-700">Pembaca Teks Otomatis</label>
                        <button onclick="window.accessibilityManager.toggleTextReader()" id="textReaderBtn"
                            class="w-12 h-6 bg-gray-300 rounded-full relative transition-colors">
                            <div id="textReaderToggle"
                                class="w-5 h-5 bg-white rounded-full absolute top-0.5 left-0.5 transition-transform">
                            </div>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500">Aktifkan untuk membaca konten halaman secara otomatis</p>

                    <!-- Tombol Baca Konten -->
                    <div class="mt-3">
                        <button onclick="window.accessibilityManager.readPageContent()"
                            class="w-full px-3 py-2 bg-green-500 text-white rounded text-sm hover:bg-green-600 transition-colors">
                            <i class="fas fa-play mr-2"></i>Baca Konten Halaman
                        </button>
                    </div>
                </div>

                <!-- Pengaturan Ukuran Font -->
                <div class="mb-6">
                    <label class="text-sm font-medium text-gray-700 mb-3 block">Ukuran Font</label>
                    <div class="flex gap-2 flex-wrap">
                        <button onclick="window.accessibilityManager.changeFontSize('decrease')"
                            class="flex-1 px-4 py-2 bg-blue-500 text-white rounded text-sm hover:bg-blue-600 transition-colors min-w-[80px]">
                            <i class="fas fa-minus mr-1"></i> A-
                        </button>
                        <button onclick="window.accessibilityManager.changeFontSize('reset')"
                            class="flex-1 px-4 py-2 bg-gray-500 text-white rounded text-sm hover:bg-gray-600 transition-colors min-w-[80px]">
                            Reset
                        </button>
                        <button onclick="window.accessibilityManager.changeFontSize('increase')"
                            class="flex-1 px-4 py-2 bg-blue-500 text-white rounded text-sm hover:bg-blue-600 transition-colors min-w-[80px]">
                            <i class="fas fa-plus mr-1"></i> A+
                        </button>
                    </div>
                </div>

                <!-- Kontras Tinggi -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-medium text-gray-700">Kontras Tinggi</label>
                        <button onclick="window.accessibilityManager.toggleHighContrast()" id="contrastBtn"
                            class="w-12 h-6 bg-gray-300 rounded-full relative transition-colors">
                            <div id="contrastToggle"
                                class="w-5 h-5 bg-white rounded-full absolute top-0.5 left-0.5 transition-transform">
                            </div>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500">Tingkatkan kontras untuk kemudahan membaca</p>
                </div>
            </div>
        </div>
    </div>

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

    /* Kelas animasi */
    .heartbeat {
        animation: heartbeat 4s infinite;
        animation-timing-function: ease-in-out;
    }
    </style>

    <!-- Load accessibility JavaScript -->
    <script src="../../styles/accessibility.js"></script>
</body>

</html>