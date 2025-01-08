<?php
session_start();
include '../config/koneksi.php';

// Proses login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    $role = mysqli_real_escape_string($koneksi, $_POST['role']); // Ambil role dari form

    // Cek apakah username sudah terdaftar
    $check_query = "SELECT * FROM users WHERE username='$username'";
    $check_result = mysqli_query($koneksi, $check_query);
    if (mysqli_num_rows($check_result) > 0) {
        $error = "Username sudah terdaftar!";
    } else {
        // Simpan data ke database
        $insert_query = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";
        $insert_result = mysqli_query($koneksi, $insert_query);

        if ($insert_result) {
            $error = "Registrasi berhasil!";
        } else {
            $error = "Gagal melakukan registrasi, silakan coba lagi!";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <title>Perpustakaan</title>
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
    <div class="w-full h-screen bg-[#CBDBFF] flex items-center justify-center poppins">
        <div class="w-[90%] max-w-[450px] h-max bg-white shadow rounded flex flex-col p-10">
            <div class="w-full h-full flex flex-col items-center gap-8">
                <div class="flex flex-col items-center gap-1">
                    <h1 class="font-semibold text-2xl uppercase montserrat">Perpustakaan</h1>
                    <h4 class="font-medium text-lg">Register</h4>
                </div>
                <form 
                    method="POST"
                    class="w-full h-max flex flex-col gap-3"
                    autocomplete="off"
                >
                    <?php if (isset($error)) { ?>
                        <div class="text-red-500 text-sm mb-4">
                            <?php echo $error; ?>
                        </div>
                    <?php } ?>
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium">Username</label>
                        <input 
                            type="text"
                            name="username"
                            placeholder="Username"
                            class="w-full h-10 border rounded focus:outline-blue-500 placeholder:font-light text-sm indent-3"
                            required
                            autocomplete="off"
                        >
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium">Password</label>
                        <input 
                            type="password"
                            name="password"
                            placeholder="Password"
                            class="w-full h-10 border rounded focus:outline-blue-500 placeholder:font-light text-sm indent-3"
                            required
                            autocomplete="off"
                        >
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium">Role</label>
                        <select 
                            name="role" 
                            required
                            class="w-full h-10 border rounded focus:outline-blue-500 text-sm indent-3"
                        >
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                    <button
                        type="submit"
                        class="w-full h-10 bg-blue-500 text-white font-medium rounded hover:bg-blue-600 text-white font-medium mt-5"
                    >
                        Register
                    </button>
                    <div class="flex items-center gap-3 justify-center mt-6 text-sm">
                        <p>Sudah punya akun?</p>
                        <a 
                            href="../index.php"
                            class="text-blue-500"
                        >
                            Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>