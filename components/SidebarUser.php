<aside
    class="w-full md:w-[270px] h-16 md:h-screen bg-[#213555] flex flex-col items-center justify-center md:justify-start p-5 gap-6 fixed z-50 bottom-0">
    <h1 class="text-2xl font-semibold text-white text-center md:flex hidden">Ruang Lestari</h1>

    <div class="w-full h-max flex md:flex-col flex-row gap-2 md:justify-start justify-center">
        <div onclick="window.location.href='user.php'" title="Beranda"
            class="w-10 md:w-full h-10 md:h-12 flex items-center pl-0 md:pl-4 rounded hover:bg-[#3E5879] text-white gap-3 border border-[#3E5879] cursor-pointer">
            <div class="h-10 w-10 flex items-center justify-center">
                <i class="fas fa-home"></i>
            </div>
            <p class="text-sm md:flex hidden">Beranda</p>
        </div>

        <div onclick="window.location.href='ruangan.php'" title="Ruangan"
            class="w-10 md:w-full h-10 md:h-12 flex items-center pl-0 md:pl-4 rounded hover:bg-[#3E5879] text-white gap-3 border border-[#3E5879] cursor-pointer">
            <div class="h-10 w-10 flex items-center justify-center">
                <i class="fas fa-hotel text-lg"></i>
            </div>
            <p class="text-sm md:flex hidden">Ruangan</p>
        </div>

        <div onclick="window.location.href='riwayat-pesanan.php'" title="Riwayat Pesanan"
            class="w-10 md:w-full h-10 md:h-12 flex items-center pl-0 md:pl-4 rounded hover:bg-[#3E5879] text-white gap-3 border border-[#3E5879] cursor-pointer">
            <div class="h-10 w-10 flex items-center justify-center">
                <i class="fas fa-history"></i>
            </div>
            <p class="text-sm md:flex hidden">Riwayat Pesanan</p>
        </div>



        <div onclick="window.location.href='../../logout.php'" title="Logout"
            class="w-10 md:w-full h-10 md:h-12 flex items-center pl-0 md:pl-4 rounded hover:bg-[#3E5879] text-white gap-3 border border-[#3E5879] cursor-pointer">
            <div class="h-12 w-10 flex items-center justify-center">
                <i class="fas fa-sign-out-alt text-lg"></i>
            </div>
            <p class="text-sm md:flex hidden">Logout</p>
        </div>
    </div>

    <footer class="hidden md:block text-xs text-center text-white mt-auto">
        &copy;<span class="font-bold text-white">Bambang Harsono</span>,
        <span class="italic">Politeknik Baja Tegal</span>. All rights reserved.
    </footer>
</aside>