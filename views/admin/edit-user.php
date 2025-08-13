<?php
session_start();
include '../../config/koneksi.php';

// Cek apakah user sudah login dan admin
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

$message = '';

// Ambil data user berdasarkan ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $query = "SELECT * FROM users WHERE id = '$id' AND role = 'user'";
    $result = mysqli_query($koneksi, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
    } else {
        $message = 'User tidak ditemukan!';
    }
} else {
    header("Location: data-user.php");
    exit;
}

// Proses update data user
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    // Jika password dikosongkan, tidak diubah
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET username = '$username', password = '$hashed_password' WHERE id = '$id'";
    } else {
        $query = "UPDATE users SET username = '$username' WHERE id = '$id'";
    }

    if (mysqli_query($koneksi, $query)) {
        $message = 'User berhasil diperbarui!';
        // Refresh data user
        $result = mysqli_query($koneksi, "SELECT * FROM users WHERE id = '$id'");
        $user = mysqli_fetch_assoc($result);
    } else {
        $message = 'Gagal memperbarui user!';
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Ruang Lestari</title>
    <link rel="icon" href="image/dlh.png" type="image/png" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
</head>

<body>
    <div class="w-full h-screen poppins">
        <div class="flex items-center w-full h-full">
            <?php include '../../components/SidebarAdmin.php'; ?>

            <div class="flex w-full h-full ml-0 md:ml-[270px] flex-col bg-gray-100">
                <?php include '../../components/NavbarAdmin.php'; ?>

                <div class="w-full flex flex-col gap-10 p-7">
                    <div class="flex items-center gap-3 text-[#3E5879]">
                        <i class="fas fa-user text-xl"></i>
                        <h1 class="font-medium">Edit User</h1>
                    </div>

                    <form method="POST" class="w-full flex flex-col gap-3">
                        <?php if ($message != '') : ?>
                        <p class="text-blue-500 font-medium"><?= $message; ?></p>
                        <?php endif; ?>

                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium">Username</label>
                            <input type="text" name="username" value="<?= htmlspecialchars($user['username'] ?? '') ?>"
                                class="w-full h-10 border rounded focus:outline-blue-500 text-sm indent-3" required>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium">Password Baru (kosongkan jika tidak ingin ubah)</label>
                            <input type="text" name="password" placeholder="Biarkan kosong jika tidak diubah"
                                class="w-full h-10 border rounded focus:outline-blue-500 text-sm indent-3">
                        </div>

                        <button type="submit"
                            class="w-full h-10 bg-blue-500 hover:bg-blue-600 rounded mt-5 text-white font-medium">
                            Update
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>