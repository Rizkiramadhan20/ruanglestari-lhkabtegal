<?php
session_start();
include 'config/koneksi.php';

if (isset($_SESSION['username'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: views/admin/admin.php");
    } else {
        header("Location: views/user/user.php");
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    $role = mysqli_real_escape_string($koneksi, $_POST['role']);

    $query = "SELECT * FROM users WHERE username='$username' AND role='$role'";
    $result = mysqli_query($koneksi, $query);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['id_user'] = $user['id'];

        if ($user['role'] == 'admin') {
            header("Location: views/admin/admin.php");
        } else {
            header("Location: views/user/user.php");
        }
        exit;
    } else {
        $error = "Username, Password, atau Role salah!";
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ruang Lestari - Login</title>
    <link rel="icon" href="image/dlh.png" type="image/png" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./styles/accessibility.css">
    <script>
    tailwind.config = {
        theme: {
            extend: {
                animation: {
                    'fade-slide': 'fadeSlide 0.8s ease-out both',
                    'marquee': 'marquee 15s linear infinite',
                },
                keyframes: {
                    fadeSlide: {
                        '0%': {
                            opacity: 0,
                            transform: 'translateY(40px)'
                        },
                        '100%': {
                            opacity: 1,
                            transform: 'translateY(0)'
                        },
                    },
                    marquee: {
                        '0%': {
                            transform: 'translateX(100%)'
                        },
                        '100%': {
                            transform: 'translateX(-100%)'
                        }
                    }
                }
            }
        }
    }
    </script>
</head>

<body
    class="bg-gradient-to-r from-blue-950 via-cyan-800 to-blue-950 font-[Poppins] min-h-screen flex flex-col justify-between p-4 relative animate-bgGradient">

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
        </div>
    </div>

    <main id="main-content" class="flex-grow flex items-center justify-center">
        <section
            class="flex flex-col lg:flex-row min-h-[600px] max-w-6xl w-full bg-[#1f293a] rounded-3xl shadow-lg overflow-hidden z-10">
            <!-- Left: Login Form -->
            <div class="w-full lg:w-1/2 flex items-center justify-center p-4 sm:p-8">
                <div class="w-full max-w-md space-y-6 sm:space-y-8">
                    <!-- Mobile Image Header -->
                    <div class="lg:hidden relative h-80 sm:h-96 md:h-[500px]">
                        <img src="image/bg.jpg" alt="Art" class="w-full h-full object-cover" />
                        <div
                            class="absolute inset-0 bg-black bg-opacity-50 flex flex-col items-center justify-center p-4">
                            <!-- Container logo menggantikan dua logo secara berdampingan dengan flex -->
                            <div class="flex justify-center space-x-4 mb-4 sm:mb-5 w-full max-w-xs">
                                <img src="image/pbjt.png" alt="Logo 1" class="w-16 sm:w-24 h-auto object-contain" />
                                <img src="image/dlh.png" alt="Logo 2" class="w-16 sm:w-24 h-auto object-contain" />
                            </div>
                            <marquee behavior="scroll" direction="left"
                                class="text-2xl sm:text-3xl md:text-4xl font-bold mb-3 sm:mb-4 text-white text-center">
                                SISTEM INFORMASI RESERVASI RUANG RAPAT DINAS LINGKUNGAN HIDUP KABUPATEN TEGAL
                            </marquee>
                            <p class="text-lg sm:text-xl text-center text-white">Semoga acaramu berjalan dengan sukses
                                dan
                                lancar</p>
                        </div>
                    </div>

                    <div id="loginBox"
                        class="w-full bg-[#1f293a] p-8 rounded-3xl shadow-lg text-white border border-cyan-400/10 transition-all duration-300 animate-fadeScaleIn">
                        <h2 class="text-2xl font-bold text-center mb-6 text-cyan-400">LOGIN </h2>

                        <?php if (isset($error)): ?>
                        <p class="text-red-500 text-center text-sm mb-4"><?= $error ?></p>
                        <?php endif; ?>

                        <form method="POST" action="#">
                            <div class="mb-5">
                                <input type="text" name="username" required
                                    class="w-full px-4 py-2 rounded-full bg-transparent border border-cyan-400 text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:shadow-[0_0_10px_#0ef]"
                                    placeholder="Masukkan username">
                            </div>

                            <div class="mb-5">
                                <input type="password" name="password" required
                                    class="w-full px-4 py-2 rounded-full bg-transparent border border-cyan-400 text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:shadow-[0_0_10px_#0ef]"
                                    placeholder="Masukkan password">
                            </div>

                            <div class="mb-6">
                                <select name="role" required
                                    class="w-full px-4 py-2 rounded-full bg-transparent border border-cyan-400 text-white focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:shadow-[0_0_10px_#0ef]">
                                    <option value="" disabled selected class="text-gray-400">Pilih Role</option>
                                    <option value="admin" class="text-black">Admin</option>
                                    <option value="user" class="text-black">User</option>
                                </select>
                            </div>

                            <button type="submit"
                                class="w-full bg-cyan-400 text-[#0b1d3a] font-bold py-2 rounded-full transition-all hover:bg-cyan-300 hover:shadow-[0_0_15px_#0ef] focus:shadow-[0_0_20px_#0ef]">
                                Login
                            </button>

                            <p class="text-center text-sm mt-4 text-gray-300">Tidak punya akun?
                                <a href="views/register.php"
                                    class="text-cyan-400 hover:underline font-medium">Register</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Desktop Image Section -->
            <div class="hidden lg:block lg:w-1/2 relative">
                <img src="image/bg.jpg" alt="Art" class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                    <div class="text-center text-white p-6 sm:p-8 w-full max-w-xl">
                        <!-- Container logo -->
                        <div class="flex justify-center space-x-4 mb-4 sm:mb-6">
                            <!-- Logo kiri -->
                            <img src="image/pbjt.png" alt="Logo 1" class="w-24 sm:w-36 h-auto object-contain" />
                            <!-- Logo kanan -->
                            <img src="image/dlh.png" alt="Logo 2" class="w-24 sm:w-36 h-auto object-contain" />
                        </div>
                        <marquee behavior="scroll" direction="left">
                            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-3 sm:mb-4">
                                SISTEM INFORMASI RESERVASI RUANG RAPAT DINAS LINGKUNGAN HIDUP KABUPATEN TEGAL
                            </h2>
                        </marquee>
                        </h2>
                        <p class="text-base sm:text-lg">Semoga acaramu berjalan dengan sukses dan
                            lancar</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="w-full text-center text-xs text-gray-400 py-4 mb-2 z-10 relative">
        &copy; <span class="font-semibold text-white">Bambang Harsono</span>,
        <span class="italic">Politeknik Baja Tegal</span>. All rights reserved.

        <!-- Script -->
        <script>
        feather.replace();

        // Cek apakah elemen ada sebelum menambahkan event listener
        const toggle = document.getElementById('toggleGlow');
        const box = document.getElementById('loginBox');

        if (toggle && box) {
            toggle.addEventListener('change', () => {
                if (toggle.checked) {
                    box.classList.add('shadow-[0_0_30px_#0ef]', 'ring-2', 'ring-emerald-400');
                } else {
                    box.classList.remove('shadow-[0_0_30px_#0ef]', 'ring-2', 'ring-emerald-400');
                }
            });
        }
        </script>

        <!-- Load accessibility JavaScript -->
        <script src="./styles/accessibility.js"></script>
    </footer>

</body>

</html>