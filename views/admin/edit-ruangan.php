<?php
    session_start();
    include '../../config/koneksi.php';

    // Cek apakah user sudah login
    if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
        header("Location: index.php");
        exit;
    }

    $message = '';

    // Ambil data ruangan berdasarkan ID
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        $query = "SELECT * FROM rooms WHERE id = '$id'";
        $result = mysqli_query($koneksi, $query);
        
        if (mysqli_num_rows($result) > 0) {
            $room = mysqli_fetch_assoc($result);
        } else {
            $message = 'Ruangan tidak ditemukan!';
        }
    } else {
        header("Location: ruangan.php");
        exit;
    }

    // Proses update data ruangan
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $amenities = $_POST['amenities'];

        $query = "UPDATE rooms SET name = '$name', description = '$description', amenities = '$amenities' WHERE id = '$id'";

        if (mysqli_query($koneksi, $query)) {
            $message = 'Ruangan berhasil diperbarui!';
        } else {
            $message = 'Gagal memperbarui ruangan!';
        }
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="image/dlh.png" type="image/png" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Ruang Lestari</title>
</head>

<body>
    <div class="w-full h-screen poppins">
        <div class="flex items-center w-full h-full ">
            <!-- Sidebar -->
            <?php include '../../components/SidebarAdmin.php'; ?>

            <!-- Edit Ruangan -->
            <div class="flex w-full h-full ml-0 md:ml-[270px] flex flex-col bg-gray-100">
                <?php include '../../components/NavbarAdmin.php'; ?>

                <div class="w-full h-full flex flex-col gap-10 p-7">
                    <div class="flex items-center gap-3 text-[#3E5879]">
                        <i class="fas fa-hotel text-xl"></i>
                        <h1 class="font-medium">Ruangan</h1>
                        <i class="fas fa-chevron-right text-xl"></i>
                        <h1 class="font-medium">Edit</h1>
                    </div>

                    <form method="POST" class="w-full h-full flex flex-col gap-3">
                        <?php if ($message != '') { ?>
                        <p class="text-blue-500 font-medium">
                            <?= $message; ?>
                        </p>
                        <?php } ?>
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium">Nama Ruangan</label>
                            <input type="text" name="name" value="<?= $room['name'] ?? ''; ?>" placeholder="name"
                                class="w-full h-10 border rounded focus:outline-blue-500 placeholder:font-light text-sm indent-3"
                                required>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium">Description</label>
                            <input type="text" name="description" value="<?= $room['description'] ?? ''; ?>"
                                placeholder="description"
                                class="w-full h-10 border rounded focus:outline-blue-500 placeholder:font-light text-sm indent-3"
                                required>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium">Fasilitas</label>
                            <input type="text" name="amenities" value="<?= $room['amenities'] ?? ''; ?>"
                                placeholder="amenities"
                                class="w-full h-10 border rounded focus:outline-blue-500 placeholder:font-light text-sm indent-3"
                                required>
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