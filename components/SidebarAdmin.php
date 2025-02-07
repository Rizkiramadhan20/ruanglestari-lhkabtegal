<!-- Navigation Sidebar -->
<aside class="fixed bottom-0 z-50 w-full md:w-[270px] h-16 md:h-screen bg-[#213555] flex flex-col items-center justify-center md:justify-start p-5 gap-6">
  <!-- Branding -->
  <h1 class="text-2xl font-semibold text-white text-center md:flex hidden">Ruang Lestari</h1>

  <!-- Navigation Links -->
  <nav class="w-full h-max flex md:flex-col flex-row gap-2 md:justify-start justify-center">
    <!-- Link 1: Beranda -->
    <a href="admin.php" class="w-10 md:w-full h-10 md:h-12 flex items-center pl-0 md:pl-4 rounded hover:bg-[#3E5879] text-white gap-3 border border-[#3E5879]">
      <div class="h-10 w-10 flex items-center justify-center">
        <i class="fas fa-home"></i>
      </div>
      <p class="text-sm md:flex hidden">Beranda</p>
    </a>

    <!-- Link 2: Data User -->
    <a href="data-user.php" class="w-10 md:w-full h-10 md:h-12 flex items-center pl-0 md:pl-4 rounded hover:bg-[#3E5879] text-white gap-3 border border-[#3E5879]">
      <div class="h-10 w-10 flex items-center justify-center">
        <i class="fas fa-user text-lg"></i>
      </div>
      <p class="text-sm md:flex hidden">Data User</p>
    </a>

    <!-- Link 3: Ruangan -->
    <a href="ruangan.php" class="w-10 md:w-full h-10 md:h-12 flex items-center pl-0 md:pl-4 rounded hover:bg-[#3E5879] text-white gap-3 border border-[#3E5879]">
      <div class="h-10 w-10 flex items-center justify-center">
        <i class="fas fa-hotel text-lg"></i>
      </div>
      <p class="text-sm md:flex hidden">Ruangan</p>
    </a>

    <!-- Link 4: Riwayat Pesanan -->
    <a href="riwayat_pesanan.php" class="w-10 md:w-full h-10 md:h-12 flex items-center pl-0 md:pl-4 rounded hover:bg-[#3E5879] text-white gap-3 border border-[#3E5879]">
      <div class="h-10 w-10 flex items-center justify-center">
        <i class="fas fa-history"></i>
      </div>
      <p class="text-sm md:flex hidden">Riwayat Pesanan</p>
    </a>

    <!-- Link 5: Logout -->
    <a href="../../logout.php" class="w-10 md:w-full h-10 md:h-12 flex items-center pl-0 md:pl-4 rounded hover:bg-[#3E5879] text-white gap-3 border border-[#3E5879]">
      <div class="h-12 w-10 flex items-center justify-center">
        <i class="fas fa-sign-out-alt text-lg"></i>
      </div>
      <p class="text-sm md:flex hidden">Logout</p>
    </a>
  </nav>
</aside>