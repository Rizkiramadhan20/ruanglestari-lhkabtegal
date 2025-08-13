<?php
    session_start();
    include '../../config/koneksi.php';

    // Cek apakah user sudah login
    if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
        header("Location: index.php");
        exit;
    }

    $message = '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $amenities = $_POST['amenities'];
    
        $query = "INSERT INTO rooms (name, description,  amenities)
                  VALUES ('$name', '$description', '$$amenities')";

        if (mysqli_query($koneksi, $query)) {
            $message = 'berhasil ditambahkan!';
        } else {
            $message = 'Gagal Menambahkan!';
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
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

<body>
    <div class="w-full h-screen poppins">
        <div class="flex items-center w-full h-full ">
            <!-- Sidebar -->
            <?php include '../../components/SidebarAdmin.php'; ?>

            <!-- Home -->
            <div class="flex w-full h-full ml-0 md:ml-[270px] flex flex-col bg-gray-100">
                <?php include '../../components/NavbarAdmin.php'; ?>
                <div class="w-full h-full flex flex-col gap-10 p-7">
                    <div class="flex items-center gap-3 text-[#3E5879]">
                        <i class="fas fa-hotel text-xl"></i>
                        <h1 class="font-medium">Ruangan</h1>
                        <i class="fas fa-chevron-right text-xl"></i>
                        <h1 class="font-medium">Tambah</h1>
                    </div>

                    <form method="POST" class="w-full h-full flex flex-col gap-3">
                        <?php if ($message != '') { ?>
                        <p class="text-blue-500 font-medium">
                            <?= $message; ?>
                        </p>
                        <?php } ?>
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium">Nama Ruangan</label>
                            <input type="text" name="name" placeholder="name"
                                class="w-full h-10 border rounded focus:outline-blue-500 placeholder:font-light text-sm indent-3"
                                required>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium">Description</label>
                            <input type="text" name="description" placeholder="description"
                                class="w-full h-10 border rounded focus:outline-blue-500 placeholder:font-light text-sm indent-3"
                                required>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium">Fasilitas</label>
                            <input type="text" name="amenities" placeholder="amenities"
                                class="w-full h-10 border rounded focus:outline-blue-500 placeholder:font-light text-sm indent-3"
                                required>
                        </div>
                        <button type="submit" a
                            class="w-full h-10 bg-blue-500 hover:bg-blue-600 rounded mt-5 text-white font-medium ">
                            Save
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>