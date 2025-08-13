<?php
session_start();
include '../../config/koneksi.php';

// Cek apakah user sudah login dan admin
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Ambil nilai pencarian jika ada
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query data user (role user saja)
$query = "SELECT * FROM users WHERE role = 'user'";
if (!empty($search)) {
    $query .= " AND username LIKE '%" . mysqli_real_escape_string($koneksi, $search) . "%'";
}

$result = mysqli_query($koneksi, $query);

// Proses tambah user
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT); // <-- diperbaiki

    $query = "INSERT INTO users (username, password, role) VALUES ('$username', '$hashed_password', 'user')";
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('User berhasil ditambahkan!'); window.location.href='data-user.php';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan user.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ruang Lestari</title>
    <link rel="icon" href="image/dlh.png" type="image/png" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
    .poppins {
        font-family: 'Poppins', sans-serif;
    }
    </style>
</head>

<body>
    <div class="w-full h-max min-h-screen poppins">
        <div class="flex items-center w-full h-full">
            <?php include '../../components/SidebarAdmin.php'; ?>
            <div class="flex w-full min-h-screen ml-0 md:ml-[270px] flex-col bg-gray-100">
                <?php include '../../components/NavbarAdmin.php'; ?>
                <div class="w-full flex flex-col gap-10 px-7 py-7">
                    <div class="flex items-center gap-3 text-[#3E5879]">
                        <i class="fas fa-users text-xl"></i>
                        <h1 class="font-medium">Data User</h1>
                    </div>

                    <form method="POST" class="flex flex-col py-5 bg-white rounded shadow gap-3 px-5">
                        <h1 class="text-sm font-medium">Tambah User</h1>
                        <hr>
                        <div class="flex items-center md:flex-row flex-col gap-4">
                            <div class="flex flex-col gap-2">
                                <label class="text-xs font-medium">Username</label>
                                <input type="text" name="username" required placeholder="Username"
                                    class="w-full h-10 border rounded focus:outline-blue-500 placeholder:font-light text-sm indent-3">
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-xs font-medium">Password</label>
                                <input type="password" name="password" required placeholder="Password"
                                    class="w-full h-10 border rounded focus:outline-blue-500 placeholder:font-light text-sm indent-3">
                            </div>
                            <button type="submit"
                                class="px-5 md:mt-6 h-10 bg-[#3E5879] text-white text-sm font-medium rounded">
                                Save
                            </button>
                        </div>
                    </form>

                    <div class="w-full flex flex-col gap-6">
                        <div class="w-full flex items-center justify-between">
                            <form method="GET" class="flex items-center">
                                <input type="text" name="search"
                                    class="h-10 w-42 rounded placeholder:font-light text-sm border indent-3 focus:outline-[#3E5879]"
                                    placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
                                <button type="submit"
                                    class="ml-2 px-4 h-10 bg-[#3E5879] hover:opacity-80 text-sm text-white rounded">
                                    Cari
                                </button>
                            </form>
                        </div>
                        <div class="relative overflow-x-auto h-[400px]">
                            <table class="w-full text-sm text-left text-blue-500 shadow-md rounded-lg overflow-hidden">
                                <thead class="text-xs text-white uppercase bg-[#3E5879]">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">No</th>
                                        <th scope="col" class="px-6 py-3">Username</th>
                                        <th scope="col" class="px-6 py-3">Password</th>
                                        <th scope="col" class="px-6 py-3">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                $index = 1;
                                while ($user = mysqli_fetch_assoc($result)) : ?>
                                    <tr class="bg-white even:bg-gray-50 hover:bg-gray-100 border-b text-black text-xs">
                                        <td class="px-6 py-4"><?= $index++ ?></td>
                                        <td class="px-6 py-4"><?= htmlspecialchars($user['username']) ?></td>
                                        <td class="px-6 py-4 text-gray-400 italic">Terenkripsi</td>
                                        <td class="px-6 py-4 flex items-center gap-2">
                                            <a href="edit-user.php?id=<?= $user['id'] ?>"
                                                class="text-green-500">Edit</a> |
                                            <a href="hapus_user.php?id=<?= $user['id'] ?>" class="text-red-500"
                                                onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</body>

</html>