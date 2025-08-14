<?php
session_start();
include '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    $role = mysqli_real_escape_string($koneksi, $_POST['role']);

    $check_query = "SELECT * FROM users WHERE username='$username'";
    $check_result = mysqli_query($koneksi, $check_query);
    if (mysqli_num_rows($check_result) > 0) {
        $error = "Username sudah terdaftar!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $insert_query = "INSERT INTO users (username, password, role) VALUES ('$username', '$hashed_password', '$role')";
        $insert_result = mysqli_query($koneksi, $insert_query);

        if ($insert_result) {
            $success = "Registrasi berhasil! Silakan login.";
        } else {
            $error = "Gagal melakukan registrasi, silakan coba lagi!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Ruang Lestari - Register</title>
    <link rel="icon" href="../image/dlh.png" type="image/png" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/accessibility.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'fade-slide': 'fadeSlide 0.8s ease-out both',
                        'scale-bounce': 'scaleBounce 0.8s ease-out both',
                        'gradient-move': 'gradientMove 15s ease infinite',
                    },
                    keyframes: {
                        fadeSlide: {
                            '0%': {
                                opacity: 0,
                                transform: 'translateY(20px)'
                            },
                            '100%': {
                                opacity: 1,
                                transform: 'translateY(0)'
                            },
                        },
                        scaleBounce: {
                            '0%, 100%': {
                                transform: 'scale(1)'
                            },
                            '50%': {
                                transform: 'scale(1.05)'
                            }
                        },
                        gradientMove: {
                            '0%': {
                                'background-position': '0% 50%'
                            },
                            '50%': {
                                'background-position': '100% 50%'
                            },
                            '100%': {
                                'background-position': '0% 50%'
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
    <div class="fixed bottom-20 md:bottom-10 gap-4 md:right-5 right-4 z-50">
        <!-- Toggle Button Aksesibilitas -->
        <button onclick="window.accessibilityManager.toggleMenu()" title="Aksesibilitas"
            class="bg-blue-600 hover:bg-blue-700 text-white p-3 rounded-full shadow-lg transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
            <i class="fas fa-universal-access text-lg"></i>
        </button>

        <!-- Dropdown Menu Aksesibilitas -->
        <div id="accessibilityMenu"
            class="hidden absolute bottom-16 right-0 w-80 bg-white rounded-lg shadow-xl border border-gray-200 p-6 z-50">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Pengaturan Aksesibilitas</h3>
                <button onclick="window.accessibilityManager.toggleMenu()" class="text-gray-500 hover:text-gray-700">
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
                            class="w-5 h-5 bg-white rounded-full absolute top-0.5 left-0.5 transition-transform"></div>
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

            <!-- Kontras Tinggi -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-sm font-medium text-gray-700">Kontras Tinggi</label>
                    <button onclick="window.accessibilityManager.toggleHighContrast()" id="contrastBtn"
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
            <!-- Left: Register Form -->
            <div class="w-full lg:w-1/2 flex items-center justify-center p-4 sm:p-8">
                <div class="w-full max-w-md space-y-6 sm:space-y-8">
                    <!-- Mobile Image Header -->
                    <div class="lg:hidden relative h-80 sm:h-96 md:h-[500px]">
                        <img src="../image/bg.jpg" alt="Art" class="w-full h-full object-cover" />
                        <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                            <div class="text-center text-white p-4">
                                <!-- Dua logo berdampingan -->
                                <div class="flex justify-center mb-4 sm:mb-5 space-x-4">
                                    <img src="../image/pbjt.png" alt="Logo 1" class="w-28 h-28 object-contain" />
                                    <img src="../image/dlh.png" alt="Logo 2" class="w-28 h-28 object-contain" />
                                </div>
                                <!-- Judul dan deskripsi -->
                                <h2
                                    class="text-2xl sm:text-3xl md:text-4xl font-bold mb-3 sm:mb-4 text-white text-center">
                                    SIR3 DINAS LINGKUNGAN HIDUP KABUPATEN TEGAL
                                </h2>
                                <p class="text-lg sm:text-xl">
                                    Silakan daftarkan akun dan lakukan reservasi untuk agenda Anda
                                </p>
                            </div>
                        </div>
                    </div>

                    <div id="registerBox"
                        class="w-full bg-[#1f293a] p-8 rounded-3xl shadow-lg text-white border border-cyan-400/10 transition-all duration-300 animate-fadeScaleIn">
                        <h2 class="text-2xl font-bold text-center mb-6 text-cyan-400">REGISTER</h2>

                        <?php if (isset($error)) : ?>
                            <p class="text-red-500 text-center text-sm mb-4"><?= $error; ?></p>
                        <?php elseif (isset($success)) : ?>
                            <p class="text-green-500 text-center text-sm mb-4"><?= $success; ?></p>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-5">
                                <label for="username-input"
                                    class="block text-sm font-medium text-gray-300 mb-2">Username</label>
                                <input type="text" name="username" id="username-input" required placeholder="Username"
                                    class="w-full px-4 py-2 rounded-full bg-transparent border border-cyan-400 text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:shadow-[0_0_10px_#0ef]" />
                            </div>
                            <div class="mb-5">
                                <label for="password-input"
                                    class="block text-sm font-medium text-gray-300 mb-2">Password</label>
                                <div class="relative">
                                    <input type="password" name="password" id="password-input" required
                                        placeholder="Password"
                                        class="w-full px-4 py-2 pr-12 rounded-full bg-transparent border border-cyan-400 text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:shadow-[0_0_10px_#0ef]" />
                                    <button type="button" id="togglePassword"
                                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-cyan-400 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-cyan-400 rounded-full p-1"
                                        title="Tampilkan/Sembunyikan Password">
                                        <i class="fas fa-eye" id="eyeIcon"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-6">
                                <label for="role-select" class="block text-sm font-medium text-gray-300 mb-2">Role
                                    Pengguna</label>
                                <select name="role" id="role-select" required
                                    class="w-full px-4 py-2 rounded-full bg-transparent border border-cyan-400 text-white focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:shadow-[0_0_10px_#0ef]"
                                    aria-label="Pilih peran pengguna">
                                    <option value="" disabled selected class="text-gray-400">Pilih Role</option>
                                    <option value="admin" class="text-black">Admin</option>
                                    <option value="user" class="text-black">User</option>
                                </select>
                            </div>

                            <button type="submit"
                                class="w-full bg-cyan-400 text-[#0b1d3a] font-bold py-2 rounded-full transition-all hover:bg-cyan-300 hover:shadow-[0_0_15px_#0ef] focus:shadow-[0_0_20px_#0ef]">
                                Register
                            </button>

                            <p class="text-center text-sm mt-4 text-gray-300">Sudah punya akun?
                                <a href="../index.php" class="text-cyan-400 hover:underline font-medium">Login</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Desktop Image Section -->
            <div class="hidden lg:block lg:w-1/2 relative">
                <img src="../image/bg.jpg" alt="Art" class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                    <div class="text-center text-white p-6 sm:p-8 w-full max-w-2xl">
                        <div class="flex justify-center space-x-4 mb-4 sm:mb-6">
                            <img src="../image/pbjt.png" alt="Logo Kiri" class="w-24 sm:w-36 h-auto object-contain" />
                            <img src="../image/dlh.png" alt="Logo Kanan" class="w-24 sm:w-36 h-auto object-contain" />
                        </div>
                        <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-3 sm:mb-4 text-white text-center">
                            SIR3 DINAS LINGKUNGAN HIDUP KABUPATEN TEGAL
                        </h2>
                        <p class="text-base sm:text-lg">
                            Silakan daftarkan akun dan lakukan reservasi untuk agenda Anda
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="w-full text-center text-xs text-gray-400 py-4 mb-2 z-10 relative">
        &copy; <span class="font-semibold tegisterte">Bambang Harsono</span>,
        <span class="italic">Politeknik Baja Tegal</span>. All rights reserved.
    </footer>


    <!-- Script -->
    <script src="https://unpkg.com/feather-icons"></script>
    <script>
        feather.replace();

        const toggle = document.getElementById('toggleGlow');
        const box = document.getElementById('registerBox');

        if (toggle && box) {
            toggle.addEventListener('change', () => {
                if (toggle.checked) {
                    box.classList.add('shadow-[0_0_30px_#0ef]', 'ring-2', 'ring-cyan-400');
                } else {
                    box.classList.remove('shadow-[0_0_30px_#0ef]', 'ring-2', 'ring-cyan-400');
                }
            });
        }

        // Password Show/Hide Toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password-input');
        const eyeIcon = document.getElementById('eyeIcon');

        if (togglePassword && passwordInput && eyeIcon) {
            togglePassword.addEventListener('click', function() {
                // Toggle password visibility
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    eyeIcon.classList.remove('fa-eye');
                    eyeIcon.classList.add('fa-eye-slash');
                    togglePassword.setAttribute('title', 'Sembunyikan Password');
                } else {
                    passwordInput.type = 'password';
                    eyeIcon.classList.remove('fa-eye-slash');
                    eyeIcon.classList.add('fa-eye');
                    togglePassword.setAttribute('title', 'Tampilkan Password');
                }
            });
        }
    </script>

    <!-- Load accessibility JavaScript -->
    <script src="../styles/accessibility.js"></script>
</body>

</html>