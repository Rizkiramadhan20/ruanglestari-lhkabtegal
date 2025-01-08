<?php
    session_start();
    include '../../config/koneksi.php';

    // Cek apakah user sudah login
    if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
        header("Location: index.php");
        exit;
    }

    // Ambil id ruangan dari parameter URL
    if (isset($_GET['id'])) {
        $room_id = $_GET['id'];
        
        // Query untuk mendapatkan detail ruangan berdasarkan ID
        $query = "SELECT * FROM rooms WHERE id = '$room_id'";
        $result = mysqli_query($koneksi, $query);
        $ruangan = mysqli_fetch_assoc($result);

        if (!$ruangan) {
            // Jika ruangan tidak ditemukan, arahkan ke halaman sebelumnya
            header("Location: index.php");
            exit;
        }
    } else {
        // Jika tidak ada ID di URL, arahkan ke halaman sebelumnya
        header("Location: index.php");
        exit;
    }

    $message = '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $user_id = $_SESSION['id_user'];
        $room_id = mysqli_real_escape_string($koneksi, $_POST['room_id']);
        $date = mysqli_real_escape_string($koneksi, $_POST['date']);
        $start_time = mysqli_real_escape_string($koneksi, $_POST['start_time']);
        $end_time = mysqli_real_escape_string($koneksi, $_POST['end_time']);

        $queryCheck = "SELECT * FROM bookings WHERE room_id = '$room_id' AND date = '$date' AND 
          ((start_time <= '$start_time' AND end_time > '$start_time') OR 
           (start_time < '$end_time' AND end_time >= '$end_time'))";
        $result = mysqli_query($koneksi, $queryCheck);

        // Jika ada booking yang berbenturan
        if (mysqli_num_rows($result) > 0) {
            $message = "Ruangan sudah dibooking pada jam tersebut.";
        } else {
            $query = "INSERT INTO bookings (user_id, room_id, date, start_time, end_time) 
                VALUES ('$user_id', '$room_id', '$date', '$start_time', '$end_time')";
            
            if (mysqli_query($koneksi, $query)) {
                $message = "Pemesanan berhasil!";
            } else {
                $message = "Terjadi kesalahan saat melakukan pemesanan.";
            }
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
    <title>Pesanan - Ruangan</title>
    <style>
        .poppins {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="poppins">
    <div class="w-full h-max min-h-screen">
        <div class="flex items-center w-full h-full">
            <!-- Sidebar -->
            <?php include '../../components/SidebarUser.php'; ?>

            <!-- Home -->
            <div class="flex w-full h-full ml-0 md:ml-[270px] flex flex-col">
                <?php include '../../components/NavbarUser.php'; ?>
                
                <div class="w-full h-full flex flex-col gap-10 px-7 py-10">
                    <div class="flex items-center gap-3 text-[#3E5879]">
                        <i class="fas fa-hotel text-xl"></i>
                        <h1 class="font-medium">Pesanan</h1>
                    </div>

                    <!-- Displaying the message -->
                    <?php if ($message): ?>
                        <div class="text-center text-lg font-medium text-red-600">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="w-full h-max">
                        <div class="h-max bg-white shadow-md flex flex-col p-5 gap-2">
                            <h1 class="font-medium text-lg"><?php echo htmlspecialchars($ruangan['name']); ?></h1>
                            <p class="text-xs"><?php echo htmlspecialchars($ruangan['price']); ?> / jam</p>
                            <p class="text-xs"><?php echo htmlspecialchars($ruangan['amenities']); ?></p>
                            <p class="text-xs"><?php echo htmlspecialchars($ruangan['description']); ?></p>
                            
                            <!-- Form pemesanan -->
                            <form 
                                method="POST"
                                class="flex flex-col gap-2 mt-2"
                            >
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium">Tanggal</label>
                                    <input 
                                        type="date"
                                        name="date"
                                        placeholder="date"
                                        class="w-full h-10 border rounded focus:outline-blue-500 placeholder:font-light text-sm indent-3"
                                        required
                                    >
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium">Waktu Mulai</label>
                                    <input 
                                        type="time"
                                        name="start_time"
                                        placeholder="start_time"
                                        class="w-full h-10 border rounded focus:outline-blue-500 placeholder:font-light text-sm indent-3"
                                        required
                                    >
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium">Waktu Selesai</label>
                                    <input 
                                        type="time"
                                        name="end_time"
                                        placeholder="end_time"
                                        class="w-full h-10 border rounded focus:outline-blue-500 placeholder:font-light text-sm indent-3"
                                        required
                                    >
                                </div>
                                <input type="hidden" name="room_id" value="<?php echo $ruangan['id']; ?>">
                                <div class="mt-6 flex justify-end">
                                    <button type="submit" class="px-4 py-2 bg-[#3E5879] hover:opacity-80 text-white text-sm rounded">Pesan Ruangan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
   </div>
</body>
</html>
