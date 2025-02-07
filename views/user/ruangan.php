<?php
    session_start();
    include '../../config/koneksi.php';

    // Cek apakah user sudah login
    if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
        header("Location: index.php");
        exit;
    }

    $query = "SELECT * FROM rooms";
    $result = mysqli_query($koneksi, $query);
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
    <div class="w-full h-max min-h-screen poppins">
        <div class="flex items-center w-full h-full ">
            <!-- Sidebar -->
            <?php include '../../components/SidebarUser.php'; ?>

            <!-- Home -->
            <div class="flex w-full h-full ml-0 md:ml-[270px] flex flex-col ">
                <?php include '../../components/NavbarUser.php'; ?>
                
                <div class="w-full h-full flex flex-col gap-10 px-7 py-10">
                    <div class="flex items-center gap-3 text-[#3E5879]">
                        <i class="fas fa-hotel text-xl"></i>
                        <h1 class="font-medium">Ruangan</h1>
                    </div>

                    <div class="w-full h-max grid xl:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-4">
                        <?php 
                            $index = 1; // Inisialisasi index
                            while ($ruangan = mysqli_fetch_assoc($result)) { 
                                // Determine which image to show based on the index
                                if ($index == 1) {
                                    $imageSrc = "../../image/Ruang Adipura.jpg";
                                } else {
                                    $imageSrc = "../../image/Ruang Kalpataru.jpg";
                                }
                        ?>
                            <div class="h-max bg-white shadow-md flex flex-col p-5 gap-2">
                                 <img src="<?php echo $imageSrc; ?>" class="w-full h-[150px] object-cover" alt="">
                                <h1 class="font-medium text-lg"><?php echo htmlspecialchars($ruangan['name']); ?></h1>
                                <div class="flex flex-col gap-1 text-xs">
                                    <p><?php echo htmlspecialchars($ruangan['amenities']); ?></p>
                                    <p class="t">
                                        <?php
                                            // Batasi deskripsi hingga 40 karakter
                                            $description = htmlspecialchars($ruangan['description']);
                                            if (strlen($description) > 70) {
                                                // Jika deskripsi lebih dari 40 karakter, tambahkan "..."
                                                echo substr($description, 0, 70) . '...';
                                            } else {
                                                // Jika deskripsi kurang dari 40 karakter, tampilkan seperti biasa
                                                echo $description;
                                            }
                                        ?>
                                    </p>
                                </div>
                                <div class="flex items-center justify-end w-full h-max mt-2">
                                    <a href="pesanan.php?id=<?php echo $ruangan['id']; ?>">
                                        <button
                                            class="h-10 px-4 bg-[#3E5879] hover:opacity-80 rounded text-white text-sm"
                                        >   
                                            Pesan
                                        </button>
                                    </a>
                                </div>
                            </div>
                        <?php 
                            $index++;
                            }    
                        ?>
                    </div>
                </div>
            </div>
        </div>
   </div>
</body>
</html>