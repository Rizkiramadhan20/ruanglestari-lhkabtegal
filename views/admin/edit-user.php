<?php
    session_start();
    include '../../config/koneksi.php';

    // Cek apakah user sudah login
    if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
        header("Location: index.php");
        exit;
    }

    $message = '';

    // Ambil data user berdasarkan ID
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        $query = "SELECT * FROM users WHERE id = '$id' AND role = 'user'";  // Only for user role
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
        $username = $_POST['username'];
        $password = $_POST['password'];

        $query = "UPDATE users SET username = '$username', password = '$password' WHERE id = '$id'";

        if (mysqli_query($koneksi, $query)) {
            $message = 'User berhasil diperbarui!';
        } else {
            $message = 'Gagal memperbarui user!';
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Edit User</title>
</head>
<body>
    <div class="w-full h-screen poppins">
        <div class="flex items-center w-full h-full ">
            <!-- Sidebar -->
            <?php include '../../components/SidebarAdmin.php'; ?>

            <!-- Edit User -->
            <div class="flex w-full h-full ml-0 md:ml-[270px] flex flex-col bg-gray-100">
                <?php include '../../components/NavbarAdmin.php'; ?>

                <div class="w-full h-full flex flex-col gap-10 p-7">
                    <div class="flex items-center gap-3 text-[#3E5879]">
                        <i class="fas fa-user text-xl"></i>
                        <h1 class="font-medium">Edit User</h1>
                    </div>

                    <form method="POST" class="w-full h-full flex flex-col gap-3">
                        <?php if ($message != '') { ?>
                            <p class="text-blue-500 font-medium">
                                <?= $message; ?>
                            </p>
                        <?php } ?>
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium">Username</label>
                            <input 
                                type="text"
                                name="username"
                                value="<?= $user['username'] ?? ''; ?>"
                                placeholder="Username"
                                class="w-full h-10 border rounded focus:outline-blue-500 placeholder:font-light text-sm indent-3"
                                required
                            >
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium">Password</label>
                            <input 
                                type="text"
                                name="password"
                                value="<?= $user['password'] ?? ''; ?>"
                                placeholder="Password"
                                class="w-full h-10 border rounded focus:outline-blue-500 placeholder:font-light text-sm indent-3"
                                required
                            >
                        </div>
                        <button type="submit" class="w-full h-10 bg-blue-500 hover:bg-blue-600 rounded mt-5 text-white font-medium">
                            Update
                        </button>
                    </form>
                </div>
            </div>
        </div>
   </div>
</body>
</html>
