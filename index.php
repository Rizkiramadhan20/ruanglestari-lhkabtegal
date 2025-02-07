<?php
session_start();
include 'config/koneksi.php';

// Cek jika user sudah login
if (isset($_SESSION['username'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: views/admin/admin.php");
    } else {
        header("Location: views/user/user.php");
    }
    exit;
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    $role = mysqli_real_escape_string($koneksi, $_POST['role']);

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
    <title>Ruang Lestari - Login</title>
    <link rel="stylesheet" href="./styles/global.css">
</head>
<body>
    <div class="container">
    <div class="login-box">
            <h2>Login</h2>
            <?php if (isset($error)) { echo "<p class='text-red-500 text-[8x] text-center'>$error</p>"; } ?>
            <form method="POST" action="#">
                <div class="input-box">
                    <input type="text" name="username" required>
                    <label>Username</label>
                </div>
                <div class="input-box">
                    <input type="password" name="password" required>
                    <label>Password</label>
                </div>
                <div class="input-box">
                    <select 
                        name="role" 
                        required
                        class="w-full h-12 rounded-full bg-transparent border-2 border-[#2c4766] px-2 text-white"
                    >
                        <option class="text-black" value="">Pilih Role</option>
                        <option class="text-black" value="admin">Admin</option>
                        <option class="text-black" value="user">User</option>
                    </select>
                </div>
                <button type="submit" class="w-full h-12 bg-[#3E5879] text-white rounded">Login</button>
                <div class="flex items-center gap-2 mt-4 justify-center">
                    <p class="text-xs">Tidak punya akun?</p>
                    <a href="views/register.php" class="text-xs text-[#0ef]">Register</a>
                </div>
            </form>
            <div class="animation-container">
                <span style="--i:0;"></span>
                <span style="--i:1;"></span>
                <span style="--i:2;"></span>
                <span style="--i:3;"></span>
                <span style="--i:4;"></span>
                <span style="--i:5;"></span>
                <span style="--i:6;"></span>
                <span style="--i:7;"></span>
                <span style="--i:8;"></span>
                <span style="--i:9;"></span>
                <span style="--i:10;"></span>
                <span style="--i:11;"></span>
                <span style="--i:12;"></span>
                <span style="--i:13;"></span>
                <span style="--i:14;"></span>
                <span style="--i:15;"></span>
                <span style="--i:16;"></span>
                <span style="--i:17;"></span>
                <span style="--i:18;"></span>
                <span style="--i:19;"></span>
                <span style="--i:20;"></span>
                <span style="--i:21;"></span>
                <span style="--i:22;"></span>
                <span style="--i:23;"></span>
                <span style="--i:24;"></span>
                <span style="--i:25;"></span>
                <span style="--i:26;"></span>
                <span style="--i:27;"></span>
                <span style="--i:28;"></span>
                <span style="--i:29;"></span>
                <span style="--i:30;"></span>
                <span style="--i:31;"></span>
                <span style="--i:32;"></span>
                <span style="--i:33;"></span>
                <span style="--i:34;"></span>
                <span style="--i:35;"></span>
                <span style="--i:36;"></span>
                <span style="--i:37;"></span>
                <span style="--i:38;"></span>
                <span style="--i:39;"></span>
                <span style="--i:40;"></span>
                <span style="--i:41;"></span>
                <span style="--i:42;"></span>
                <span style="--i:43;"></span>
                <span style="--i:44;"></span>
                <span style="--i:45;"></span>
                <span style="--i:46;"></span>
                <span style="--i:47;"></span>
                <span style="--i:48;"></span>
                <span style="--i:49;"></span>
            </div>
        </div>
    </div>
</body>
</html>