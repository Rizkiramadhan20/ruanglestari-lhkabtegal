<?php
    session_start();
    include '../../config/koneksi.php';

    // Cek apakah user sudah login
    if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
        header("Location: index.php");
        exit;
    }

    // Ambil nilai pencarian jika ada
    $search = isset($_GET['search']) ? $_GET['search'] : '';

    // Query dengan filter pencarian untuk data user
    $query = "SELECT * FROM users WHERE role = 'user'";  // Only select users with 'user' role
    if (!empty($search)) {
        $query .= " AND username LIKE '%" . mysqli_real_escape_string($koneksi, $search) . "%'";
    }

    $result = mysqli_query($koneksi, $query);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = mysqli_real_escape_string($koneksi, $_POST['username']);
        $password = mysqli_real_escape_string($koneksi, $_POST['password']);

        $query = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', 'user')";
            if (mysqli_query($koneksi, $query)) {
                echo "<script>alert('User berhasil ditambahkan!'); window.location.href='data-user.php';</script>";
            } else {
                echo "<script>alert('Gagal menambahkan user.');</script>";
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <title>Data User</title>
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
    <div class="w-full h-max min-h-screen poppins">
        <div class="flex items-center w-full h-full ">
            <!-- Sidebar -->
            <?php include '../../components/SidebarAdmin.php'; ?>
            <!-- Home -->
            <div class="flex w-full h-max min-h-screen ml-0 md:ml-[270px] flex flex-col bg-gray-100">
                <?php include '../../components/NavbarAdmin.php'; ?>
                <div class="w-full h-full flex flex-col gap-10 px-7 py-7">
                    <div class="flex items-center gap-3 text-[#3E5879]">
                        <i class="fas fa-users text-xl"></i>
                        <h1 class="font-medium">Data User</h1>
                    </div>

                    <form 
                        method="POST"
                        action=""
                        class="flex flex-col py-5 bg-white w-full h-max rounded shadow gap-3 px-5"
                    >
                        <h1 class="text-sm font-medium">Tambah User</h1>
                        <hr>
                        <div class="flex items-center md:flex-row flex-col gap-4">
                            <div class="flex flex-col gap-2">
                                <label class="text-xs font-medium">Username</label>
                                <input 
                                    type="text"
                                    name="username"
                                    placeholder="Username"
                                    class="w-full h-10 border rounded focus:outline-blue-500 placeholder:font-light text-sm indent-3"
                                    required
                                >
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-xs font-medium">Password</label>
                                <input 
                                    type="password"
                                    name="password"
                                    placeholder="Password"
                                    class="w-full h-10 border rounded focus:outline-blue-500 placeholder:font-light text-sm indent-3"
                                    required
                                >
                            </div>
                            <button
                                type="submit"
                                class="px-5 md:mt-6 h-10 bg-[#3E5879] text-white text-sm font-medium rounded"
                            >
                                Save
                            </button>
                        </div>
                    </form>
                    
                    <div class="w-full h-full flex flex-col gap-6">
                        <div class="w-full h-max flex items-center justify-between">
                            <form method="GET" class="flex items-center">
                                <input 
                                    type="text"
                                    name="search"
                                    class="h-10 w-42 rounded placeholder:font-light text-sm border indent-3 focus:outline-[#3E5879]"
                                    placeholder="Search..."
                                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                                >
                                <button type="submit" class="ml-2 px-4 h-10 bg-[#3E5879] hover:opacity-80 flex items-center justify-center text-sm text-white rounded">
                                    Cari
                                </button>
                            </form>
                        </div>
                        <div class="relative overflow-x-auto h-[400px]">
                            <table class="w-full text-sm text-left text-blue-500">
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
                                        $index = 1; // Inisialisasi index
                                        while ($user = mysqli_fetch_assoc($result)) { 
                                    ?>
                                        <tr class="bg-white border-b text-black text-xs">
                                            <td class="px-6 py-4"><?php echo $index++; ?></td> <!-- Nomor -->
                                            <td class="px-6 py-4"><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td class="px-6 py-4"><?php echo htmlspecialchars($user['password']); ?></td>
                                            <td class="px-6 py-4 flex items-center gap-2">
                                                <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="text-green-500">Edit</a> |
                                                <a href="hapus_user.php?id=<?php echo $user['id']; ?>" class="text-red-500" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                                            </td>
                                        </tr>
                                    <?php 
                                        }    
                                    ?>
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
