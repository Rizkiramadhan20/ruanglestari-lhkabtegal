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

    // Query dengan filter pencarian
    $query = "SELECT * FROM rooms";
    if (!empty($search)) {
        $query .= " WHERE name LIKE '%" . mysqli_real_escape_string($koneksi, $search) . "%'";
    }

    $result = mysqli_query($koneksi, $query);

    // Cek apakah query berhasil

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
    <title>Ruang Lestari</title>
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
<body >
    <div class="w-full h-screen poppins">
        <div class="flex items-center w-full h-full ">
            <!-- Sidebar -->
            <?php include '../../components/SidebarAdmin.php'; ?>
            <!-- Home -->
            <div class="flex w-full h-max min-h-screen ml-0 md:ml-[270px] flex flex-col bg-gray-100">
                <?php include '../../components/NavbarAdmin.php'; ?>
                <div class="w-full h-full flex flex-col gap-10 px-7 py-7">
                    <div class="flex items-center gap-3 text-[#3E5879]">
                        <i class="fas fa-hotel text-xl"></i>
                        <h1 class="font-medium">Ruangan</h1>
                    </div>

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
                            <a 
                                href="tambah-ruangan.php"
                                class="px-4 h-10 bg-[#3E5879] hover:opacity-80 flex items-center justify-center text-sm text-white rounded"
                            >
                                Tambah Ruangan
                            </a>
                        </div>
                        <div class="relative overflow-x-auto h-[500px]">
                            <table class="w-full text-sm text-left  text-blue-500 ">
                                <thead class="text-xs text-white uppercase bg-[#3E5879] ">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">
                                            No
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Nama
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Description
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Amenities
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        $index = 1; // Inisialisasi index
                                        while ($ruangan = mysqli_fetch_assoc($result)) { 
                                    ?>
                                        <tr class="bg-white border-b text-black text-xs">
                                            <td class="px-6 py-4"><?php echo $index++; ?></td> <!-- Nomor -->
                                            <td class="px-6 py-4"><?php echo htmlspecialchars($ruangan['name']); ?></td>
                                            <td class="px-6 py-4"><?php echo htmlspecialchars($ruangan['description']); ?></td>
                                            <td class="px-6 py-4"><?php echo htmlspecialchars($ruangan['amenities']); ?></td>
                                            <td class="px-6 py-4 flex items-center gap-2">
                                                <a href="edit-ruangan.php?id=<?php echo $ruangan['id']; ?>" class="text-green-500">Edit</a> |
                                                <a href="hapus_ruangan.php?id=<?php echo $ruangan['id']; ?>" class="text-red-500" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
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