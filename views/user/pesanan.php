<?php
session_start();
include '../../config/koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    header("Location: index.php");
    exit;
}

// Ambil id ruangan dari parameter URL
if (isset($_GET['id'])) {
    $room_id = $_GET['id'];

    // Query untuk mendapatkan detail ruangan berdasarkan ID
    $query = "SELECT * FROM rooms WHERE id = '$room_id'";
    $result = mysqli_query($koneksi, $query);
    $ruangan = mysqli_fetch_assoc($result);

    if (!$ruangan) {
        // Jika ruangan tidak ditemukan, arahkan ke halaman sebelumnya
        header("Location: index.php");
        exit;
    }
} else {
    // Jika tidak ada ID di URL, arahkan ke halaman sebelumnya
    header("Location: index.php");
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['id_user'];
    $room_id = mysqli_real_escape_string($koneksi, $_POST['room_id']);
    $date = mysqli_real_escape_string($koneksi, $_POST['date']);
    $session = mysqli_real_escape_string($koneksi, $_POST['session']); // Ambil sesi yang dipilih

    // Pecah sesi menjadi waktu mulai dan waktu selesai
    list($start_time, $end_time) = explode('-', $session);
    $start_time = trim($start_time); // Menghapus spasi
    $end_time = trim($end_time); // Menghapus spasi

    $agenda_rapat = mysqli_real_escape_string($koneksi, $_POST['agenda_rapat']);
    $bidang = mysqli_real_escape_string($koneksi, $_POST['bidang']);

    // Query untuk mengecek apakah ruangan sudah dibooking pada jam tersebut
    $queryCheck = "SELECT * FROM bookings 
                   WHERE room_id = '$room_id' 
                     AND date = '$date' 
                     AND start_time = '$start_time'
                     AND end_time = '$end_time'";

    $result = mysqli_query($koneksi, $queryCheck);

    // Jika ada booking yang berbenturan
    if (mysqli_num_rows($result) > 0) {
        $message = "Ruangan sudah di pesan... Silahkan Pilih Sesi Atau Tanggal Lainnya.";
    } else {
        // Jika tidak ada konflik, lanjutkan dengan pemesanan
        $query = "INSERT INTO bookings (user_id, room_id, bidang, agenda_rapat, date, start_time, end_time) 
            VALUES ('$user_id', '$room_id', '$bidang', '$agenda_rapat', '$date', '$start_time', '$end_time')";

        if (mysqli_query($koneksi, $query)) {
            $message = "Pemesanan berhasil!";
        } else {
            $message = "Terjadi kesalahan saat melakukan pemesanan.";
        }
    }
}

// Query untuk mengambil data booking yang akan ditampilkan di marquee
$queryMarquee = "
    SELECT 
        bookings.bidang, 
        bookings.end_time, 
        rooms.name AS room_name
    FROM 
        bookings
    JOIN 
        rooms ON bookings.room_id = rooms.id
    ORDER BY 
        bookings.date DESC, bookings.start_time DESC
    LIMIT 4"; // Ambil 4 data terbaru
$resultMarquee = mysqli_query($koneksi, $queryMarquee);
$bookingsMarquee = [];
while ($row = mysqli_fetch_assoc($resultMarquee)) {
    $bookingsMarquee[] = $row;
}
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <title>Ruang Lestari</title>
    <style>
    .poppins {
        font-family: 'Poppins', sans-serif;
    }

    .marquee {
        white-space: nowrap;
        overflow: hidden;
        box-sizing: border-box;
        background-color: rgb(31, 56, 82);
        color: white;
        padding: 10px 0;
    }

    .marquee span {
        display: inline-block;
        padding-left: 100%;
        animation: marquee 50s linear infinite;
    }

    @keyframes marquee {
        0% {
            transform: translateX(100%);
        }

        100% {
            transform: translateX(-100%);
        }
    }

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
</head>

<body class="poppins">
    <div class="w-full h-max min-h-screen">
        <!-- Container Berjalan -->
        <div class="marquee">
            <span>
                <?php
                if (!empty($bookingsMarquee)) {
                    foreach ($bookingsMarquee as $booking) {
                        echo "Bidang: " . htmlspecialchars($booking['bidang']) .
                            " | Ruangan: " . htmlspecialchars($booking['room_name']) .
                            " | Selesai: " . htmlspecialchars($booking['end_time']) . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                    }
                } else {
                    echo "Tidak ada pemesanan ruangan saat ini.";
                }
                ?>
            </span>
        </div>

        <!-- Skip to content link for accessibility -->
        <a href="#main-content" class="skip-link sr-only">Langsung ke konten utama</a>

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
                            class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times"></i>
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
                        <div class="flex gap-2">
                            <button onclick="window.accessibilityManager.changeFontSize('decrease')"
                                class="px-4 py-2 bg-blue-500 text-white rounded text-sm hover:bg-blue-600 transition-colors">
                                <i class="fas fa-minus"></i> A-
                            </button>
                            <button onclick="window.accessibilityManager.changeFontSize('reset')"
                                class="px-4 py-2 bg-gray-500 text-white rounded text-sm hover:bg-gray-600 transition-colors">
                                Reset
                            </button>
                            <button onclick="window.accessibilityManager.changeFontSize('increase')"
                                class="px-4 py-2 bg-blue-500 text-white rounded text-sm hover:bg-blue-600 transition-colors">
                                <i class="fas fa-plus"></i> A+
                            </button>
                        </div>
                    </div>

                    <!-- Pengaturan Kontras Tinggi -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-gray-700">Kontras Tinggi</label>
                            <button onclick="window.accessibilityManager.toggleHighContrast()" id="highContrastBtn"
                                class="w-12 h-6 bg-gray-300 rounded-full relative transition-colors">
                                <div id="highContrastToggle"
                                    class="w-5 h-5 bg-white rounded-full absolute top-0.5 left-0.5 transition-transform">
                                </div>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500">Tingkatkan kontras untuk kemudahan membaca</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center w-full h-full">
            <!-- Sidebar -->
            <?php include '../../components/SidebarUser.php'; ?>

            <!-- Home -->
            <div class="flex w-full h-full ml-0 md:ml-[270px] flex flex-col">
                <?php include '../../components/NavbarUser.php'; ?>

                <div class="w-full h-full flex flex-col gap-10 px-7 py-10">
                    <div class="flex items-center gap-3 text-[#3E5879]">
                        <i class="fas fa-hotel text-xl"></i>
                        <h1 class="font-medium">Pesanan</h1>
                    </div>

                    <!-- Displaying the message -->
                    <?php if ($message): ?>
                    <div class="text-center text-lg font-medium text-red-600">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                    <?php endif; ?>

                    <div class="w-full h-max">
                        <div class="h-max bg-white shadow-md flex flex-col p-5 gap-2">
                            <h1 class="font-medium text-lg"><?php echo htmlspecialchars($ruangan['name']); ?></h1>
                            <p class="text-xs"><?php echo htmlspecialchars($ruangan['amenities']); ?></p>
                            <p class="text-xs"><?php echo htmlspecialchars($ruangan['description']); ?></p>

                            <!-- Form pemesanan -->
                            <form method="POST" class="flex flex-col gap-2 mt-2">
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium">Tanggal</label>
                                    <input type="date" name="date" placeholder="date"
                                        class="w-full h-10 border rounded focus:outline-blue-500 placeholder:font-light text-sm indent-3 flatpickr"
                                        id="datePicker" required>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium">Sesi</label>
                                    <select name="session"
                                        class="w-full h-10 border rounded focus:outline-blue-500 text-sm indent-3"
                                        required>
                                        <option value="" disabled selected>Pilih Sesi</option>
                                        <option value="08:00-12:00">Sesi 1 (08:00 - 12:00)</option>
                                        <option value="13:00-16:00">Sesi 2 (13:00 - 16:00)</option>
                                    </select>
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium">Bidang</label>
                                    <select name="bidang" required
                                        class="w-full h-10 border rounded focus:outline-blue-500 placeholder:font-light text-sm indent-3">
                                        <option value="">Pilih Bidang</option>
                                        <option value="DALWAS">DALWAS</option>
                                        <option value="TL">TL</option>
                                        <option value="UPTD LAB">UPTD LAB</option>
                                        <option value="PSLB3">PSLB3</option>
                                        <option value="UPTD PASL">UPTD PASL</option>
                                        <option value="UMPEG">UMPEG</option>
                                        <option value="SEKRETARIAT">SEKRETARIAT</option>
                                        <option value="PERENCANAAN">PERENCANAAN</option>
                                        <option value="KEUANGAN">KEUANGAN</option>
                                    </select>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium">Agenda Rapat</label>
                                    <input type="text" name="agenda_rapat" placeholder="Agenda"
                                        class="w-full h-10 border rounded focus:outline-blue-500 placeholder:font-light text-sm indent-3"
                                        required>
                                </div>
                                <input type="hidden" name="room_id" value="<?php echo $ruangan['id']; ?>">
                                <div class="mt-6 flex justify-end">
                                    <button type="submit"
                                        class="px-4 py-2 bg-[#3E5879] hover:opacity-80 text-white text-sm rounded">Pesan
                                        Ruangan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tambahkan JavaScript Flatpickr -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
    // Inisialisasi Flatpickr
    flatpickr("#datePicker", {
        dateFormat: "Y-m-d", // Format tanggal
        minDate: "today", // Hanya izinkan tanggal hari ini dan seterusnya
        disable: [
            function(date) {
                // Nonaktifkan hari Minggu
                return (date.getDay() === 0);
            }
        ]
    });
    </script>

    <!-- Load accessibility JavaScript -->
    <script src="../../styles/accessibility.js"></script>
</body>

</html>