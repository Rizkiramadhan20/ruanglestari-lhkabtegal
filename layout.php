<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="image/dlh.png" type="image/png" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Booking ruangan</title>
    <link rel="stylesheet" href="./styles/global.css">
    <link rel="stylesheet" href="./styles/accessibility.css">
</head>

<body>
    <!-- Skip to content link for accessibility -->
    <a href="#main-content" class="skip-link sr-only">Langsung ke konten utama</a>

    <!-- Fitur Aksesibilitas - Selalu Tersedia -->
    <div class="fixed top-4 right-4 z-50">
        <!-- Toggle Button Aksesibilitas -->
        <button onclick="toggleAccessibility()" title="Aksesibilitas"
            class="bg-blue-600 hover:bg-blue-700 text-white p-3 rounded-full shadow-lg transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
            <i class="fas fa-universal-access text-lg"></i>
        </button>

        <!-- Dropdown Menu Aksesibilitas -->
        <div id="accessibilityMenu"
            class="hidden absolute top-16 right-0 w-80 bg-white rounded-lg shadow-xl border border-gray-200 p-6 z-50">
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

    <main id="main-content">
        <?php include('components/SidebarAdmin.php'); ?>
        <?php include($content); ?>
    </main>

    <!-- Load accessibility JavaScript -->
    <script src="./styles/accessibility.js"></script>
</body>

</html>