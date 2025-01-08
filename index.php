<?php
session_start();
include 'config/koneksi.php';

// Cek jika user sudah login
if (isset($_SESSION['username'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: views/admin/admin.php"); // Admin ke halaman dashboard admin
    } else {
        header("Location: views/user/user.php"); // User ke halaman dashboard user
    }
    exit;
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    $role = mysqli_real_escape_string($koneksi, $_POST['role']); // Ambil role dari form

    $query = "SELECT * FROM users WHERE username='$username' AND password='$password' AND role='$role'";
    $result = mysqli_query($koneksi, $query);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        // Simpan data user ke session
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['id_user'] = $user['id'];

        // Redirect berdasarkan role
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <title>Booking ruangan</title>
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
                    <h1 class="font-semibold text-2xl uppercase montserrat">Booking Ruangan</h1>
                    <h4 class="font-medium text-lg">Login</h4>
                </div>
                <form 
                    method="POST"
                    action=""
                    class="w-full h-max flex flex-col gap-3"
                >
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium">Username</label>
                        <input 
                            type="text"
                            name="username"
                            placeholder="Username"
                            class="w-full h-10 border rounded focus:outline-blue-500 placeholder:font-light text-sm indent-3"
                            required
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
                    <?php if (isset($error)) { echo "<p class='text-red-500 text-[10px]'>$error</p>"; } ?>
                    <button
                        type="submit"
                        class="w-full h-10 bg-blue-500 text-white font-medium rounded hover:bg-blue-600 text-white font-medium mt-5"
                    >
                        Login
                    </button>
                    <div class="flex items-center gap-3 justify-center mt-6 text-sm">
                        <p>Tidak punya akun?</p>
                        <a 
                            href="views/register.php"
                            class="text-blue-500"
                        >
                            Register
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>