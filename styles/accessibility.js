// ========================================
// SISTEM AKSESIBILITAS RUANG LESTARI
// Versi 2.0 - Kualitas Suara Tinggi
// ========================================

// State management dengan localStorage
class AccessibilityManager {
  constructor() {
    this.state = {
      textReaderActive: false,
      highContrastActive: false,
      fontSize: 100,
      voiceSettings: {
        rate: 0.9,
        pitch: 1.0,
        volume: 1.0,
      },
      autoRead: false,
      audioFeedback: true, // Tambah state untuk audio feedback
      soundEffects: true, // Tambah state untuk sound effects
    };

    // Track user interaction
    this.userHasInteracted = false;

    this.init();
  }

  init() {
    this.loadSettings();

    // Reset state yang tidak valid saat refresh
    this.validateAndResetState();

    this.setupEventListeners();
    this.setupAudioFeedback(); // Tambah setup audio feedback
    this.updateUI();
  }

  // Validasi dan reset state yang tidak valid
  validateAndResetState() {
    // Jika speech synthesis tidak tersedia, nonaktifkan fitur terkait
    if (!("speechSynthesis" in window)) {
      this.state.textReaderActive = false;
      this.state.autoRead = false;
      this.saveSettings();
      return;
    }

    // Reset state jika terjadi error berulang
    if (this.state.textReaderActive && !this.state.autoRead) {
      // Jika text reader aktif tapi auto read tidak, ini tidak konsisten
      // Reset ke state yang aman
      this.state.textReaderActive = false;
      this.saveSettings();
    }

    // Tambahan: pastikan state konsisten saat refresh
    // Jika autoRead aktif, pastikan textReader juga aktif
    if (this.state.autoRead && !this.state.textReaderActive) {
      this.state.textReaderActive = true;
      this.saveSettings();
    }
  }

  // Load pengaturan dari localStorage
  loadSettings() {
    try {
      const saved = localStorage.getItem("ruangLestariAccessibility");
      if (saved) {
        const parsed = JSON.parse(saved);
        this.state = { ...this.state, ...parsed };
      }
    } catch (error) {
      // Menggunakan pengaturan default
    }
  }

  // Simpan pengaturan ke localStorage
  saveSettings() {
    try {
      localStorage.setItem(
        "ruangLestariAccessibility",
        JSON.stringify(this.state)
      );
    } catch (error) {
      // Gagal menyimpan pengaturan
    }
  }

  // Setup event listeners
  setupEventListeners() {
    // Track user interaction
    this.setupUserInteractionTracking();

    // Auto-start text reader for specific pages - dengan delay yang cukup untuk refresh
    setTimeout(() => {
      this.autoStartForPage();
    }, 2000); // Delay 2 detik untuk memastikan browser dan speech synthesis siap

    // Handle window resize and orientation changes
    window.addEventListener("resize", () => {
      this.handleResize();
    });

    // Handle orientation change on mobile
    if (window.orientation !== undefined) {
      window.addEventListener("orientationchange", () => {
        setTimeout(() => {
          this.handleResize();
        }, 100);
      });
    }

    // Deteksi perubahan halaman (untuk SPA atau navigasi)
    this.setupPageChangeDetection();
  }

  // Setup detection untuk perubahan halaman
  setupPageChangeDetection() {
    // Deteksi perubahan URL
    let currentUrl = window.location.href;

    // Deteksi perubahan URL dan konten halaman
    setInterval(() => {
      const newUrl = window.location.href;

      if (newUrl !== currentUrl) {
        // Ada perubahan URL (halaman baru)
        currentUrl = newUrl;

        // Hentikan pembacaan yang sedang berlangsung
        this.stopTextReader();

        // Mulai pembacaan baru untuk halaman yang baru
        setTimeout(() => {
          this.autoStartForPage();
        }, 300);
      }
    }, 1000);
  }

  // Buat hash sederhana dari konten halaman untuk deteksi perubahan
  getPageContentHash() {
    try {
      const pageTitle = document.querySelector("h1.font-medium");
      const loginTitle = document.querySelector("h2.text-cyan-400");

      let content = "";
      if (pageTitle) content += pageTitle.textContent.trim();
      if (loginTitle) content += loginTitle.textContent.trim();

      // Tambahkan beberapa elemen kunci lainnya
      const ruanganCards = document.querySelectorAll(".bg-white.shadow-md");
      content += `ruangan:${ruanganCards.length}`;

      return content;
    } catch (error) {
      return "";
    }
  }

  // Setup tracking untuk interaksi user
  setupUserInteractionTracking() {
    const interactionEvents = [
      "click",
      "scroll",
      "keydown",
      "touchstart",
      "mousemove",
    ];

    interactionEvents.forEach((eventType) => {
      document.addEventListener(
        eventType,
        () => {
          this.userHasInteracted = true;
        },
        { once: true, passive: true }
      );
    });
  }

  // Handle window resize and orientation changes
  handleResize() {
    const menu = document.getElementById("accessibilityMenu");
    if (menu && !menu.classList.contains("hidden")) {
      this.positionMenuResponsively();
    }
  }

  // Toggle menu aksesibilitas
  toggleMenu() {
    const menu = document.getElementById("accessibilityMenu");
    if (menu) {
      const isHidden = menu.classList.contains("hidden");

      if (isHidden) {
        // Show menu
        menu.classList.remove("hidden");
        this.positionMenuResponsively();
        this.addMenuCloseListeners();
      } else {
        // Hide menu
        menu.classList.add("hidden");
        this.removeMenuCloseListeners();
      }
    }
  }

  // Position menu responsively based on screen size
  positionMenuResponsively() {
    const menu = document.getElementById("accessibilityMenu");
    if (!menu) return;

    const screenWidth = window.innerWidth;
    const screenHeight = window.innerHeight;

    if (screenWidth <= 768) {
      // Mobile: center the menu
      menu.style.position = "fixed";
      menu.style.top = "50%";
      menu.style.left = "50%";
      menu.style.transform = "translate(-50%, -50%)";
      menu.style.bottom = "auto";
      menu.style.right = "auto";
    } else {
      // Desktop: position relative to button
      menu.style.position = "absolute";
      menu.style.top = "auto";
      menu.style.left = "auto";
      menu.style.transform = "none";
      menu.style.bottom = "16px";
      menu.style.right = "0";
    }
  }

  // Add listeners to close menu when clicking outside or pressing escape
  addMenuCloseListeners() {
    this.removeMenuCloseListeners(); // Remove existing listeners first

    // Close on escape key
    this.escapeListener = (e) => {
      if (e.key === "Escape") {
        this.toggleMenu();
      }
    };
    document.addEventListener("keydown", this.escapeListener);

    // Close on click outside
    this.outsideClickListener = (e) => {
      const menu = document.getElementById("accessibilityMenu");
      const button = document.querySelector(
        '[onclick="window.accessibilityManager.toggleMenu()"]'
      );

      if (menu && !menu.contains(e.target) && !button.contains(e.target)) {
        this.toggleMenu();
      }
    };
    document.addEventListener("click", this.outsideClickListener);
  }

  // Remove menu close listeners
  removeMenuCloseListeners() {
    if (this.escapeListener) {
      document.removeEventListener("keydown", this.escapeListener);
      this.escapeListener = null;
    }
    if (this.outsideClickListener) {
      document.removeEventListener("click", this.outsideClickListener);
      this.outsideClickListener = null;
    }
  }

  // Toggle text reader
  toggleTextReader() {
    this.state.textReaderActive = !this.state.textReaderActive;

    // Jika text reader diaktifkan, aktifkan juga auto-read
    if (this.state.textReaderActive) {
      this.state.autoRead = true;
    }

    if (this.state.textReaderActive) {
      this.startTextReader();
    } else {
      this.stopTextReader();
    }

    this.updateUI();
    this.saveSettings();
  }

  // Toggle high contrast
  toggleHighContrast() {
    this.state.highContrastActive = !this.state.highContrastActive;

    if (this.state.highContrastActive) {
      document.body.classList.add("high-contrast");
    } else {
      document.body.classList.remove("high-contrast");
    }

    this.updateUI();
    this.saveSettings();
    this.showNotification(
      "Kontras tinggi " +
        (this.state.highContrastActive ? "diaktifkan" : "dinonaktifkan")
    );
  }

  // Change font size
  changeFontSize(action) {
    switch (action) {
      case "increase":
        this.state.fontSize = Math.min(200, this.state.fontSize + 10);
        break;
      case "decrease":
        this.state.fontSize = Math.max(80, this.state.fontSize - 10);
        break;
      case "reset":
        this.state.fontSize = 100;
        break;
    }

    // Terapkan ukuran font ke body
    document.body.style.fontSize = this.state.fontSize + "%";

    // Pastikan semua elemen mewarisi ukuran font dari body
    this.applyFontSizeToAllElements();

    this.updateUI();
    this.saveSettings();
    this.showNotification(`Ukuran font: ${this.state.fontSize}%`);

    // Debug log
    // Font size changed to: ${this.state.fontSize}%
    // Body font-size style: ${document.body.style.fontSize}
  }

  // Apply font size to all elements
  applyFontSizeToAllElements() {
    try {
      // Dapatkan semua elemen yang perlu diubah ukuran fontnya
      const elements = document.querySelectorAll(
        "h1, h2, h3, h4, h5, h6, p, span, div, button, a, input, textarea, label, .text-xs, .text-sm, .text-base, .text-lg, .text-xl, .text-2xl"
      );

      elements.forEach((element) => {
        // Skip elemen yang sudah memiliki ukuran font yang spesifik
        if (element.style.fontSize && !element.style.fontSize.includes("%")) {
          return;
        }

        // Terapkan ukuran font yang relatif
        element.style.fontSize = "inherit";
      });

      // Applied font size to ${elements.length} elements
    } catch (error) {
      // Error applying font size to elements
    }
  }

  // Update UI elements
  updateUI() {
    // Text reader button
    const textReaderBtn = document.getElementById("textReaderBtn");
    const textReaderToggle = document.getElementById("textReaderToggle");

    if (textReaderBtn && textReaderToggle) {
      if (this.state.textReaderActive) {
        textReaderBtn.classList.remove("bg-gray-300");
        textReaderBtn.classList.add("bg-blue-500");
        textReaderToggle.classList.add("translate-x-6");
      } else {
        textReaderBtn.classList.remove("bg-blue-500");
        textReaderBtn.classList.add("bg-gray-300");
        textReaderToggle.classList.remove("translate-x-6");
      }
    }

    // High contrast button
    const contrastBtn = document.getElementById("contrastBtn");
    const contrastToggle = document.getElementById("contrastToggle");

    if (contrastBtn && contrastToggle) {
      if (this.state.highContrastActive) {
        contrastBtn.classList.remove("bg-gray-300");
        contrastBtn.classList.add("bg-blue-500");
        contrastToggle.classList.add("translate-x-6");
      } else {
        contrastBtn.classList.remove("bg-blue-500");
        contrastBtn.classList.add("bg-gray-300");
        contrastToggle.classList.remove("translate-x-6");
      }
    }

    // Audio feedback button
    const audioFeedbackBtn = document.getElementById("audioFeedbackBtn");
    const audioFeedbackToggle = document.getElementById("audioFeedbackToggle");

    if (audioFeedbackBtn && audioFeedbackToggle) {
      if (this.state.audioFeedback) {
        audioFeedbackBtn.classList.remove("bg-gray-300");
        audioFeedbackBtn.classList.add("bg-blue-500");
        audioFeedbackToggle.classList.add("translate-x-6");
      } else {
        audioFeedbackBtn.classList.remove("bg-blue-500");
        audioFeedbackBtn.classList.add("bg-gray-300");
        audioFeedbackToggle.classList.remove("translate-x-6");
      }
    }
  }

  // Wait for voices to be ready
  async waitForVoices() {
    return new Promise((resolve) => {
      // Jika voices sudah tersedia
      if (speechSynthesis.getVoices().length > 0) {
        resolve();
        return;
      }

      // Jika voices belum tersedia, tunggu
      const checkVoices = () => {
        if (speechSynthesis.getVoices().length > 0) {
          resolve();
        } else {
          setTimeout(checkVoices, 100);
        }
      };

      // Mulai pengecekan
      checkVoices();

      // Fallback: timeout setelah 5 detik
      setTimeout(() => {
        resolve();
      }, 5000);
    });
  }

  // Start text reader
  async startTextReader() {
    try {
      // Pastikan speech synthesis tersedia
      if (!("speechSynthesis" in window)) {
        this.showNotification(
          "Pembaca teks tidak didukung di browser ini",
          "error"
        );
        return;
      }

      // Tunggu voices siap
      await this.waitForVoices();

      // Pastikan voices tersedia
      if (speechSynthesis.getVoices().length === 0) {
        this.showNotification(
          "Suara pembaca teks tidak tersedia. Silakan coba lagi.",
          "warning"
        );
        return;
      }

      // Cancel any ongoing speech terlebih dahulu
      speechSynthesis.cancel();

      // Tunggu sebentar untuk memastikan speech synthesis siap
      await new Promise((resolve) => setTimeout(resolve, 100));

      const content = this.getPageContent();
      // Konten yang akan dibaca

      if (!content || content.trim().length === 0) {
        this.showNotification("Tidak ada konten untuk dibaca", "warning");
        // Konten kosong, tidak ada yang bisa dibaca
        return;
      }

      this.showNotification("Memulai pembacaan konten...", "info");

      // Pastikan content tidak terlalu panjang untuk mencegah error
      const maxLength = 5000; // Batasi panjang teks
      const truncatedContent =
        content.length > maxLength
          ? content.substring(0, maxLength) + "..."
          : content;

      await this.speakText(truncatedContent);
    } catch (error) {
      // Error dalam text reader

      // Berikan pesan error yang lebih spesifik
      let errorMessage = "";

      if (error.name === "NotAllowedError") {
        errorMessage =
          "Izin untuk menggunakan pembaca teks ditolak. Pastikan browser mengizinkan akses suara.";
      } else if (error.name === "NetworkError") {
        errorMessage = "Gagal memuat suara. Periksa koneksi internet Anda.";
      } else if (error.name === "NotSupportedError") {
        errorMessage = "Fitur pembaca teks tidak didukung di browser ini.";
      } else if (error.message) {
        errorMessage = `Error: ${error.message}`;
      } else {
        // Jika tidak ada error spesifik, berikan pesan yang lebih umum
        errorMessage = "Fitur Pembaca Teks Otomatis Dimatikan.";
      }

      this.showNotification(errorMessage, "error");
    }
  }

  // Stop text reader
  stopTextReader() {
    if ("speechSynthesis" in window) {
      speechSynthesis.cancel();
    }
    this.showNotification("Pembacaan dihentikan", "info");
  }

  // Get page content based on current page
  getPageContent() {
    // Hentikan pembacaan yang sedang berlangsung sebelum mengambil konten baru
    if ("speechSynthesis" in window) {
      speechSynthesis.cancel();
    }

    const pageTitle = document.querySelector("h1.font-medium");
    const loginTitle = document.querySelector("h2.text-cyan-400");
    let content = [];

    // Debug: log informasi halaman
    // Debug - pageTitle: ${pageTitle ? pageTitle.textContent.trim() : "Tidak ditemukan"}
    // Debug - loginTitle: ${loginTitle ? loginTitle.textContent.trim() : "Tidak ditemukan"}

    // Debug: cek semua h1 yang ada
    const allH1 = document.querySelectorAll("h1");
    // Debug - Semua h1 yang ada: ${Array.from(allH1).map((h) => h.textContent.trim())}

    // Debug: cek semua h2 yang ada
    const allH2 = document.querySelectorAll("h2");
    // Debug - Elemen dengan class text-cyan-400: ${Array.from(allH2).map((h) => h.textContent.trim())}

    // Debug: cek semua elemen dengan class text-cyan-400
    const cyanElements = document.querySelectorAll(".text-cyan-400");
    // Debug - Elemen dengan class text-cyan-400: ${Array.from(cyanElements).map((el) => el.textContent.trim())}

    // Debug: cek semua elemen yang mengandung kata REGISTER atau LOGIN
    const allElements = document.querySelectorAll("*");
    const registerLoginElements = Array.from(allElements).filter(
      (el) =>
        el.textContent &&
        (el.textContent.includes("REGISTER") ||
          el.textContent.includes("LOGIN"))
    );
    // Debug - Elemen yang mengandung REGISTER/LOGIN

    // Debug: cek semua konten yang ada di halaman
    const bodyText = document.body.textContent;
    const hasRegister = bodyText.includes("REGISTER");
    const hasLogin = bodyText.includes("LOGIN");
    // Debug - Halaman mengandung REGISTER: ${hasRegister}
    // Debug - Halaman mengandung LOGIN: ${hasLogin}
    // Debug - Sample konten body: ${bodyText.substring(0, 500)}

    // Deteksi halaman berdasarkan konten yang ada
    // Cek dulu apakah ada cards ruangan
    const ruanganCards = document.querySelectorAll(".bg-white.shadow-md");
    const hasRuanganCards = ruanganCards.length > 0;

    // Cek apakah ada elemen yang menunjukkan halaman ruangan
    const ruanganTitle = document.querySelector("h1.font-medium");
    const isRuanganPage =
      ruanganTitle && ruanganTitle.textContent.includes("Ruangan");

    // Debug - Jumlah cards ruangan: ${ruanganCards.length}
    // Debug - Apakah halaman ruangan: ${isRuanganPage}

    // Prioritas deteksi: Ruangan > Register/Login > Lainnya
    if (hasRuanganCards && isRuanganPage) {
      // Debug - Halaman terdeteksi: Ruangan (prioritas tinggi)
      content = this.getRuanganContent();
    } else if (loginTitle && loginTitle.textContent.includes("REGISTER")) {
      // Debug - Halaman terdeteksi: REGISTER
      content = this.getRegisterContent();
    } else if (loginTitle && loginTitle.textContent.includes("LOGIN")) {
      // Debug - Halaman terdeteksi: LOGIN
      content = this.getLoginContent();
    } else if (pageTitle) {
      const title = pageTitle.textContent.trim();
      // Debug - Judul halaman: ${title}

      if (title.includes("Dashboard")) {
        // Debug - Halaman terdeteksi: Dashboard
        content = this.getDashboardContent();
      } else if (title.includes("Ruangan")) {
        // Debug - Halaman terdeteksi: Ruangan
        content = this.getRuanganContent();
      } else if (title.includes("Pesanan") && !title.includes("Riwayat")) {
        // Debug - Halaman terdeteksi: Pesanan
        content = this.getPesananContent();
      } else if (title.includes("Riwayat Pesanan")) {
        // Debug - Halaman terdeteksi: Riwayat Pesanan
        content = this.getRiwayatContent();
      } else {
        // Debug - Halaman tidak dikenali, menggunakan general content
        content = this.getGeneralContent();
      }
    } else {
      // Debug - Tidak ada pageTitle, coba fallback detection

      // Fallback detection: cek apakah ada elemen yang menunjukkan halaman ruangan
      if (hasRuanganCards) {
        // Debug - Fallback: Ditemukan cards ruangan, gunakan ruangan content
        content = this.getRuanganContent();
      } else {
        // Debug - Fallback: Tidak ada indikator spesifik, gunakan general content
        content = this.getGeneralContent();
      }
    }

    // Debug - Content yang akan dibaca: ${content}
    return content.join(". ");
  }

  // Get register page content
  getRegisterContent() {
    const content = [];

    // Header halaman
    content.push("Halaman Register Ruang Lestari");

    // Deskripsi sistem
    content.push(
      "Sistem Informasi Reservasi Ruang Rapat Dinas Lingkungan Hidup Kabupaten Tegal"
    );

    // Form fields dengan bahasa Indonesia
    content.push("Form registrasi memiliki beberapa field yang harus diisi:");
    content.push("Field nomor satu: Username atau nama pengguna");
    content.push("Field nomor dua: Password atau kata sandi");
    content.push(
      "Field nomor tiga: Role atau peran pengguna, pilih antara Admin atau User"
    );

    // Tombol dan link
    content.push(
      "Setelah mengisi semua field, klik tombol Register untuk membuat akun baru"
    );
    content.push(
      "Jika sudah punya akun, klik link Login untuk masuk ke sistem"
    );

    // Pesan selamat datang
    content.push("Semoga acara Anda berjalan dengan sukses dan lancar");

    return content;
  }

  // Get login page content
  getLoginContent() {
    const content = [];

    // Header halaman
    content.push("Halaman Login Ruang Lestari");

    // Deskripsi sistem
    content.push(
      "Sistem Informasi Reservasi Ruang Rapat Dinas Lingkungan Hidup Kabupaten Tegal"
    );

    // Form fields dengan bahasa Indonesia
    content.push("Form login memiliki beberapa field yang harus diisi:");
    content.push("Field nomor satu: Username atau nama pengguna");
    content.push("Field nomor dua: Password atau kata sandi");
    content.push(
      "Field nomor tiga: Role atau peran pengguna, pilih antara Admin atau User"
    );

    // Tombol dan link
    content.push(
      "Setelah mengisi semua field, klik tombol Login untuk masuk ke sistem"
    );
    content.push(
      "Jika belum punya akun, klik link Register di bagian bawah form"
    );

    // Pesan selamat datang
    content.push("Semoga acara Anda berjalan dengan sukses dan lancar");

    return content;
  }

  // Get dashboard content
  getDashboardContent() {
    const content = [];

    // Header halaman
    content.push("Selamat datang di Dashboard Ruang Lestari");

    // Cari card statistik dashboard
    const statistikCards = document.querySelectorAll(
      ".bg-white.shadow.flex.flex-col.items-center"
    );

    if (statistikCards.length > 0) {
      content.push(
        `Dashboard menampilkan ${this.angkaKeBahasaIndonesia(
          statistikCards.length
        )} statistik utama`
      );

      // Baca setiap statistik
      statistikCards.forEach((card, index) => {
        const angkaElement = card.querySelector("p.font-medium");
        const labelElement = card.querySelector("p:not(.font-medium)");

        if (angkaElement && labelElement) {
          const angka = parseInt(angkaElement.textContent.trim());
          const label = labelElement.textContent.trim();

          if (!isNaN(angka)) {
            const angkaIndonesia = this.angkaKeBahasaIndonesia(angka);
            content.push(`${label}: ${angkaIndonesia}`);
          } else {
            content.push(`${label}: ${angkaElement.textContent.trim()}`);
          }
        }
      });
    }

    // Deskripsi halaman
    content.push("Ini adalah halaman beranda dashboard user Ruang Lestari");
    content.push(
      "Anda dapat melihat statistik ruangan dan pesanan Anda di sini"
    );

    return content;
  }

  // Get ruangan content
  getRuanganContent() {
    const content = ["Halaman Ruangan Ruang Lestari"];

    // Debug: cek semua elemen dengan class yang sesuai
    const allCards = document.querySelectorAll(".bg-white.shadow-md");
    // Debug - Total cards found: ${allCards.length}

    const cards = document.querySelectorAll(".bg-white.shadow-md");
    if (cards.length > 0) {
      content.push(
        `Terdapat ${cards.length} ruangan yang tersedia untuk dipesan`
      );

      // Debug: log setiap card yang ditemukan
      cards.forEach((card, index) => {
        // Debug - Card ${index + 1}

        const namaRuangan = card.querySelector("h1.font-medium");
        if (namaRuangan) {
          // Debug - Nama ruangan ${index + 1}: ${namaRuangan.textContent.trim()}

          // Baca nama ruangan
          content.push(
            `Ruangan ${index + 1}: ${namaRuangan.textContent.trim()}`
          );

          // Baca fasilitas ruangan
          const fasilitas = card.querySelector(
            "div.flex.flex-col.gap-1.text-xs p:first-child"
          );
          if (fasilitas) {
            const fasilitasText = fasilitas.textContent.trim();
            if (fasilitasText) {
              content.push(`Fasilitas: ${fasilitasText}`);
            }
          }

          // Baca deskripsi ruangan
          const deskripsi = card.querySelector(
            "div.flex.flex-col.gap-1.text-xs p.t"
          );
          if (deskripsi) {
            const deskripsiText = deskripsi.textContent.trim();
            if (deskripsiText) {
              content.push(`Deskripsi: ${deskripsiText}`);
            }
          }
        } else {
          // Debug - Tidak ada nama ruangan di card ${index + 1}
        }
      });
    } else {
      // Debug - Tidak ada cards yang ditemukan
      // Fallback: coba cari dengan selector yang lebih umum
      const fallbackCards = document.querySelectorAll(".bg-white");
      // Debug - Fallback cards found: ${fallbackCards.length}

      if (fallbackCards.length > 0) {
        content.push(
          `Terdapat ${fallbackCards.length} ruangan yang tersedia untuk dipesan`
        );

        fallbackCards.forEach((card, index) => {
          const namaRuangan = card.querySelector("h1");
          if (namaRuangan) {
            content.push(
              `Ruangan ${index + 1}: ${namaRuangan.textContent.trim()}`
            );

            // Baca fasilitas dan deskripsi untuk fallback
            const fasilitas = card.querySelector("p");
            if (fasilitas) {
              const fasilitasText = fasilitas.textContent.trim();
              if (fasilitasText) {
                content.push(`Fasilitas: ${fasilitasText}`);
              }
            }

            const deskripsi = card.querySelectorAll("p")[1];
            if (deskripsi) {
              const deskripsiText = deskripsi.textContent.trim();
              if (deskripsiText) {
                content.push(`Deskripsi: ${deskripsiText}`);
              }
            }
          }
        });
      }
    }

    content.push(
      "Setiap ruangan dapat dipesan dengan mengklik tombol Pesan pada ruangan yang diinginkan"
    );
    return content;
  }

  // Get pesanan content
  getPesananContent() {
    const content = ["Halaman Pesanan Ruangan Ruang Lestari"];

    const ruanganInfo = document.querySelector(".bg-white.shadow-md");
    if (ruanganInfo) {
      const namaRuangan = ruanganInfo.querySelector("h1.font-medium");
      if (namaRuangan) {
        content.push(`Ruangan yang dipilih: ${namaRuangan.textContent.trim()}`);
      }

      // Baca fasilitas ruangan
      const fasilitas = ruanganInfo.querySelector("p.text-xs:first-of-type");
      if (fasilitas) {
        const fasilitasText = fasilitas.textContent.trim();
        if (fasilitasText) {
          content.push(`Fasilitas ruangan: ${fasilitasText}`);
        }
      }

      // Baca deskripsi ruangan
      const deskripsi = ruanganInfo.querySelector("p.text-xs:last-of-type");
      if (deskripsi) {
        const deskripsiText = deskripsi.textContent.trim();
        if (deskripsiText) {
          content.push(`Deskripsi ruangan: ${deskripsiText}`);
        }
      }
    }

    content.push(
      "Form pemesanan tersedia dengan field: Tanggal, Sesi, Bidang, dan Agenda Rapat"
    );
    content.push(
      "Pilih tanggal, sesi waktu, bidang kerja, dan isi agenda rapat untuk melakukan pemesanan"
    );
    return content;
  }

  // Get riwayat content
  getRiwayatContent() {
    const content = ["Halaman Riwayat Pesanan Ruang Lestari"];

    const table = document.querySelector("table tbody");
    if (table) {
      const rows = table.querySelectorAll("tr");
      if (rows.length > 0) {
        content.push(
          `Terdapat ${this.angkaKeBahasaIndonesia(
            rows.length
          )} riwayat pesanan yang tersimpan`
        );
        content.push("Berikut adalah daftar lengkap riwayat pesanan:");

        // Baca setiap baris data dengan jeda yang jelas
        rows.forEach((row, index) => {
          const cells = row.querySelectorAll("td");
          if (cells.length >= 7) {
            const nomor = cells[0].textContent.trim();
            const namaPemesan = cells[1].textContent.trim();
            const ruangRapat = cells[2].textContent.trim();
            const tanggal = cells[3].textContent.trim();
            const waktu = cells[4].textContent.trim();
            const bidang = cells[5].textContent.trim();
            const agendaRapat = cells[6].textContent.trim();

            // Debug: log nomor yang diambil
            // Debug - Nomor asli: "${nomor}" (tipe: ${typeof nomor})
            const nomorIndonesia = this.angkaKeBahasaIndonesia(nomor);
            // Debug - Nomor Indonesia: "${nomorIndonesia}"

            // Konversi format tanggal ke bahasa Indonesia
            const tanggalIndonesia = this.formatTanggalIndonesia(tanggal);

            // Konversi format waktu ke bahasa Indonesia
            const waktuIndonesia = this.formatWaktuIndonesia(waktu);

            // Buat struktur dengan jeda yang jelas
            content.push(`Pesanan nomor ${nomorIndonesia}`);
            content.push(`Nama pemesan: ${namaPemesan}`);
            content.push(`Ruang rapat: ${ruangRapat}`);
            content.push(`Tanggal: ${tanggalIndonesia}`);
            content.push(`Waktu: ${waktuIndonesia}`);
            content.push(`Bidang: ${bidang}`);

            // Agenda rapat dengan jeda yang lebih jelas
            if (agendaRapat.length > 50) {
              // Jika agenda panjang, buat jeda di tengah kalimat
              const words = agendaRapat.split(" ");
              const midPoint = Math.ceil(words.length / 2);
              const firstHalf = words.slice(0, midPoint).join(" ");
              const secondHalf = words.slice(midPoint).join(" ");

              content.push(
                `Agenda rapat: ${firstHalf}. Jeda sebentar. ${secondHalf}`
              );
            } else {
              content.push(`Agenda rapat: ${agendaRapat}`);
            }

            // Tambahkan jeda antar pesanan (kecuali pesanan terakhir)
            if (index < rows.length - 1) {
              content.push("Jeda sebentar untuk pesanan berikutnya");
            }
          }
        });
      } else {
        content.push("Belum ada riwayat pesanan yang tersimpan");
      }
    } else {
      content.push("Tabel riwayat pesanan tidak ditemukan");
    }

    // Tambahkan informasi tambahan
    content.push(
      "Anda dapat melihat semua pesanan yang telah dilakukan dan melakukan pencarian berdasarkan nama ruangan menggunakan form pencarian di atas tabel"
    );
    return content;
  }

  // Konversi angka ke bahasa Indonesia
  angkaKeBahasaIndonesia(angka) {
    try {
      const angkaStr = angka.toString().trim();
      const angkaInt = parseInt(angkaStr);

      // Jika parsing berhasil, gunakan angka integer
      if (!isNaN(angkaInt)) {
        // Untuk angka 0-31, gunakan mapping langsung
        if (angkaInt >= 0 && angkaInt <= 31) {
          const angkaIndonesia = {
            0: "nol",
            1: "satu",
            2: "dua",
            3: "tiga",
            4: "empat",
            5: "lima",
            6: "enam",
            7: "tujuh",
            8: "delapan",
            9: "sembilan",
            10: "sepuluh",
            11: "sebelas",
            12: "dua belas",
            13: "tiga belas",
            14: "empat belas",
            15: "lima belas",
            16: "enam belas",
            17: "tujuh belas",
            18: "delapan belas",
            19: "sembilan belas",
            20: "dua puluh",
            21: "dua puluh satu",
            22: "dua puluh dua",
            23: "dua puluh tiga",
            24: "dua puluh empat",
            25: "dua puluh lima",
            26: "dua puluh enam",
            27: "dua puluh tujuh",
            28: "dua puluh delapan",
            29: "dua puluh sembilan",
            30: "tiga puluh",
            31: "tiga puluh satu",
          };

          return angkaIndonesia[angkaInt];
        }

        // Untuk angka yang lebih besar, gunakan logika konversi
        if (angkaInt > 31) {
          if (angkaInt <= 99) {
            const puluhan = Math.floor(angkaInt / 10);
            const satuan = angkaInt % 10;

            let result = "";
            if (puluhan === 1) {
              result = "sepuluh";
            } else if (puluhan === 2) {
              result = "dua puluh";
            } else if (puluhan === 3) {
              result = "tiga puluh";
            } else if (puluhan === 4) {
              result = "empat puluh";
            } else if (puluhan === 5) {
              result = "lima puluh";
            } else if (puluhan === 6) {
              result = "enam puluh";
            } else if (puluhan === 7) {
              result = "tujuh puluh";
            } else if (puluhan === 8) {
              result = "delapan puluh";
            } else if (puluhan === 9) {
              result = "sembilan puluh";
            }

            if (satuan > 0) {
              result += " " + this.angkaKeBahasaIndonesia(satuan);
            }

            return result;
          } else {
            // Untuk angka 100+, gunakan format "seratus", "dua ratus", dll.
            return angkaInt.toString(); // Fallback ke angka asli untuk sementara
          }
        }
      }

      // Fallback: coba konversi string langsung
      const angkaIndonesiaString = {
        0: "nol",
        1: "satu",
        2: "dua",
        3: "tiga",
        4: "empat",
        5: "lima",
        6: "enam",
        7: "tujuh",
        8: "delapan",
        9: "sembilan",
        10: "sepuluh",
        11: "sebelas",
        12: "dua belas",
        13: "tiga belas",
        14: "empat belas",
        15: "lima belas",
        16: "enam belas",
        17: "tujuh belas",
        18: "delapan belas",
        19: "sembilan belas",
        20: "dua puluh",
        21: "dua puluh satu",
        22: "dua puluh dua",
        23: "dua puluh tiga",
        24: "dua puluh empat",
        25: "dua puluh lima",
        26: "dua puluh enam",
        27: "dua puluh tujuh",
        28: "dua puluh delapan",
        29: "dua puluh sembilan",
        30: "tiga puluh",
        31: "tiga puluh satu",
      };

      if (angkaIndonesiaString[angkaStr]) {
        return angkaIndonesiaString[angkaStr];
      }

      // Debug: log jika tidak ditemukan
      // Debug - Angka tidak ditemukan: "${angka}" (tipe: ${typeof angka})
      return angka; // Return original if not found
    } catch (e) {
      // Error in angkaKeBahasaIndonesia
      return angka; // Return original if error
    }
  }

  // Format tahun ke bahasa Indonesia
  formatTahunIndonesia(tahun) {
    try {
      const tahunStr = tahun.toString();
      if (tahunStr.length === 4) {
        const ribuan = tahunStr.substring(0, 2);
        const puluhan = tahunStr.substring(2, 4);

        if (ribuan === "20") {
          if (puluhan === "00") {
            return "dua ribu";
          } else if (puluhan === "01") {
            return "dua ribu satu";
          } else if (puluhan === "02") {
            return "dua ribu dua";
          } else if (puluhan === "03") {
            return "dua ribu tiga";
          } else if (puluhan === "04") {
            return "dua ribu empat";
          } else if (puluhan === "05") {
            return "dua ribu lima";
          } else if (puluhan === "06") {
            return "dua ribu enam";
          } else if (puluhan === "07") {
            return "dua ribu tujuh";
          } else if (puluhan === "08") {
            return "dua ribu delapan";
          } else if (puluhan === "09") {
            return "dua ribu sembilan";
          } else if (puluhan === "10") {
            return "dua ribu sepuluh";
          } else if (puluhan === "11") {
            return "dua ribu sebelas";
          } else if (puluhan === "12") {
            return "dua ribu dua belas";
          } else if (puluhan === "13") {
            return "dua ribu tiga belas";
          } else if (puluhan === "14") {
            return "dua ribu empat belas";
          } else if (puluhan === "15") {
            return "dua ribu lima belas";
          } else if (puluhan === "16") {
            return "dua ribu enam belas";
          } else if (puluhan === "17") {
            return "dua ribu tujuh belas";
          } else if (puluhan === "18") {
            return "dua ribu delapan belas";
          } else if (puluhan === "19") {
            return "dua ribu sembilan belas";
          } else if (puluhan === "20") {
            return "dua ribu dua puluh";
          } else if (puluhan === "21") {
            return "dua ribu dua puluh satu";
          } else if (puluhan === "22") {
            return "dua ribu dua puluh dua";
          } else if (puluhan === "23") {
            return "dua ribu dua puluh tiga";
          } else if (puluhan === "24") {
            return "dua ribu dua puluh empat";
          } else if (puluhan === "25") {
            return "dua ribu dua puluh lima";
          } else if (puluhan === "26") {
            return "dua ribu dua puluh enam";
          } else if (puluhan === "27") {
            return "dua ribu dua puluh tujuh";
          } else if (puluhan === "28") {
            return "dua ribu dua puluh delapan";
          } else if (puluhan === "29") {
            return "dua ribu dua puluh sembilan";
          } else if (puluhan === "30") {
            return "dua ribu tiga puluh";
          } else if (puluhan === "31") {
            return "dua ribu tiga puluh satu";
          } else if (puluhan === "32") {
            return "dua ribu tiga puluh dua";
          } else if (puluhan === "33") {
            return "dua ribu tiga puluh tiga";
          } else if (puluhan === "34") {
            return "dua ribu tiga puluh empat";
          } else if (puluhan === "35") {
            return "dua ribu tiga puluh lima";
          } else if (puluhan === "36") {
            return "dua ribu tiga puluh enam";
          } else if (puluhan === "37") {
            return "dua ribu tiga puluh tujuh";
          } else if (puluhan === "38") {
            return "dua ribu tiga puluh delapan";
          } else if (puluhan === "39") {
            return "dua ribu tiga puluh sembilan";
          } else if (puluhan === "40") {
            return "dua ribu empat puluh";
          } else if (puluhan === "41") {
            return "dua ribu empat puluh satu";
          } else if (puluhan === "42") {
            return "dua ribu empat puluh dua";
          } else if (puluhan === "43") {
            return "dua ribu empat puluh tiga";
          } else if (puluhan === "44") {
            return "dua ribu empat puluh empat";
          } else if (puluhan === "45") {
            return "dua ribu empat puluh lima";
          } else if (puluhan === "46") {
            return "dua ribu empat puluh enam";
          } else if (puluhan === "47") {
            return "dua ribu empat puluh tujuh";
          } else if (puluhan === "48") {
            return "dua ribu empat puluh delapan";
          } else if (puluhan === "49") {
            return "dua ribu empat puluh sembilan";
          } else if (puluhan === "50") {
            return "dua ribu lima puluh";
          } else if (puluhan === "51") {
            return "dua ribu lima puluh satu";
          } else if (puluhan === "52") {
            return "dua ribu lima puluh dua";
          } else if (puluhan === "53") {
            return "dua ribu lima puluh tiga";
          } else if (puluhan === "54") {
            return "dua ribu lima puluh empat";
          } else if (puluhan === "55") {
            return "dua ribu lima puluh lima";
          } else if (puluhan === "56") {
            return "dua ribu lima puluh enam";
          } else if (puluhan === "57") {
            return "dua ribu lima puluh tujuh";
          } else if (puluhan === "58") {
            return "dua ribu lima puluh delapan";
          } else if (puluhan === "59") {
            return "dua ribu lima puluh sembilan";
          } else if (puluhan === "60") {
            return "dua ribu enam puluh";
          } else if (puluhan === "61") {
            return "dua ribu enam puluh satu";
          } else if (puluhan === "62") {
            return "dua ribu enam puluh dua";
          } else if (puluhan === "63") {
            return "dua ribu enam puluh tiga";
          } else if (puluhan === "64") {
            return "dua ribu enam puluh empat";
          } else if (puluhan === "65") {
            return "dua ribu enam puluh lima";
          } else if (puluhan === "66") {
            return "dua ribu enam puluh enam";
          } else if (puluhan === "67") {
            return "dua ribu enam puluh tujuh";
          } else if (puluhan === "68") {
            return "dua ribu enam puluh delapan";
          } else if (puluhan === "69") {
            return "dua ribu enam puluh sembilan";
          } else if (puluhan === "70") {
            return "dua ribu tujuh puluh";
          } else if (puluhan === "71") {
            return "dua ribu tujuh puluh satu";
          } else if (puluhan === "72") {
            return "dua ribu tujuh puluh dua";
          } else if (puluhan === "73") {
            return "dua ribu tujuh puluh tiga";
          } else if (puluhan === "74") {
            return "dua ribu tujuh puluh empat";
          } else if (puluhan === "75") {
            return "dua ribu tujuh puluh lima";
          } else if (puluhan === "76") {
            return "dua ribu tujuh puluh enam";
          } else if (puluhan === "77") {
            return "dua ribu tujuh puluh tujuh";
          } else if (puluhan === "78") {
            return "dua ribu tujuh puluh delapan";
          } else if (puluhan === "79") {
            return "dua ribu tujuh puluh sembilan";
          } else if (puluhan === "80") {
            return "dua ribu delapan puluh";
          } else if (puluhan === "81") {
            return "dua ribu delapan puluh satu";
          } else if (puluhan === "82") {
            return "dua ribu delapan puluh dua";
          } else if (puluhan === "83") {
            return "dua ribu delapan puluh tiga";
          } else if (puluhan === "84") {
            return "dua ribu delapan puluh empat";
          } else if (puluhan === "85") {
            return "dua ribu delapan puluh lima";
          } else if (puluhan === "86") {
            return "dua ribu delapan puluh enam";
          } else if (puluhan === "87") {
            return "dua ribu delapan puluh tujuh";
          } else if (puluhan === "88") {
            return "dua ribu delapan puluh delapan";
          } else if (puluhan === "89") {
            return "dua ribu delapan puluh sembilan";
          } else if (puluhan === "90") {
            return "dua ribu sembilan puluh";
          } else if (puluhan === "91") {
            return "dua ribu sembilan puluh satu";
          } else if (puluhan === "92") {
            return "dua ribu sembilan puluh dua";
          } else if (puluhan === "93") {
            return "dua ribu sembilan puluh tiga";
          } else if (puluhan === "94") {
            return "dua ribu sembilan puluh empat";
          } else if (puluhan === "95") {
            return "dua ribu sembilan puluh lima";
          } else if (puluhan === "96") {
            return "dua ribu sembilan puluh enam";
          } else if (puluhan === "97") {
            return "dua ribu sembilan puluh tujuh";
          } else if (puluhan === "98") {
            return "dua ribu sembilan puluh delapan";
          } else if (puluhan === "99") {
            return "dua ribu sembilan puluh sembilan";
          }
        }
      }
    } catch (e) {
      // Error formatting tahun
    }
    return tahun; // Return original if parsing fails
  }

  // Format tanggal ke bahasa Indonesia
  formatTanggalIndonesia(tanggal) {
    try {
      // Parse tanggal dari format DD-MM-YYYY
      const parts = tanggal.split("-");
      if (parts.length === 3) {
        const day = parseInt(parts[0]);
        const month = parseInt(parts[1]);
        const year = parseInt(parts[2]);

        const namaBulan = [
          "Januari",
          "Februari",
          "Maret",
          "April",
          "Mei",
          "Juni",
          "Juli",
          "Agustus",
          "September",
          "Oktober",
          "November",
          "Desember",
        ];

        return `${this.angkaKeBahasaIndonesia(day)} ${
          namaBulan[month - 1]
        } ${this.formatTahunIndonesia(year)}`;
      }
    } catch (e) {
      // Error formatting tanggal
    }
    return tanggal; // Return original if parsing fails
  }

  // Format waktu ke bahasa Indonesia
  formatWaktuIndonesia(waktu) {
    try {
      // Parse waktu dari format HH:MM - HH:MM
      if (waktu.includes("-")) {
        const [start, end] = waktu.split("-").map((t) => t.trim());

        // Konversi jam ke bahasa Indonesia
        const startTime = this.formatJamIndonesia(start);
        const endTime = this.formatJamIndonesia(end);

        return `dari ${startTime} sampai ${endTime}`;
      }
    } catch (e) {
      // Error formatting waktu
    }
    return waktu; // Return original if parsing fails
  }

  // Format jam ke bahasa Indonesia
  formatJamIndonesia(jam) {
    try {
      if (jam.includes(":")) {
        const [hour, minute] = jam.split(":").map((n) => parseInt(n));

        // Konversi ke format bahasa Indonesia yang lebih natural
        if (hour === 0) {
          if (minute === 0) {
            return "jam dua belas malam";
          } else {
            return `jam dua belas lewat ${this.angkaKeBahasaIndonesia(
              minute
            )} menit`;
          }
        } else if (hour < 12) {
          if (minute === 0) {
            return `jam ${this.angkaKeBahasaIndonesia(hour)} pagi`;
          } else {
            return `jam ${this.angkaKeBahasaIndonesia(
              hour
            )} lewat ${this.angkaKeBahasaIndonesia(minute)} menit pagi`;
          }
        } else if (hour === 12) {
          if (minute === 0) {
            return "jam dua belas siang";
          } else {
            return `jam dua belas lewat ${this.angkaKeBahasaIndonesia(
              minute
            )} menit siang`;
          }
        } else {
          const hour12 = hour - 12;
          if (minute === 0) {
            return `jam ${this.angkaKeBahasaIndonesia(hour12)} siang`;
          } else {
            return `jam ${this.angkaKeBahasaIndonesia(
              hour12
            )} lewat ${this.angkaKeBahasaIndonesia(minute)} menit siang`;
          }
        }
      }
    } catch (e) {
      // Error formatting jam
    }
    return jam; // Return original if parsing fails
  }

  // Get general content
  getGeneralContent() {
    const content = [];

    // Baca judul halaman
    const pageTitle = document.title || "Halaman web";
    content.push(`Judul halaman: ${pageTitle}`);

    // Baca heading utama
    const headings = document.querySelectorAll("h1, h2, h3");
    headings.forEach((heading, index) => {
      const text = heading.textContent.trim();
      if (text && text.length > 3 && !text.includes("Aksesibilitas")) {
        content.push(`Heading ${index + 1}: ${text}`);
      }
    });

    // Baca paragraf yang panjang
    const paragraphs = document.querySelectorAll("p");
    paragraphs.forEach((p, index) => {
      const text = p.textContent.trim();
      if (text && text.length > 10 && !text.includes("Aksesibilitas")) {
        content.push(`Paragraf ${index + 1}: ${text.substring(0, 100)}...`);
      }
    });

    // Baca konten dari main section
    const mainContent = document.querySelector(
      "main, #main-content, .main-content"
    );
    if (mainContent) {
      const mainText = mainContent.textContent.trim();
      if (mainText && mainText.length > 50) {
        const cleanText = mainText.replace(/\s+/g, " ").substring(0, 200);
        content.push(`Konten utama: ${cleanText}...`);
      }
    }

    // Jika tidak ada konten yang ditemukan, berikan informasi default
    if (content.length <= 1) {
      content.push(
        "Halaman ini berisi konten yang dapat diakses melalui fitur aksesibilitas. Gunakan tombol Baca Konten Halaman untuk mendengarkan informasi lebih detail."
      );
    }

    return content;
  }

  // Speak text with high quality voice
  async speakText(text) {
    return new Promise((resolve, reject) => {
      // Buat utterance langsung tanpa perlu menunggu voices
      const utterance = new SpeechSynthesisUtterance(text);

      // Set pengaturan suara untuk kejelasan maksimal
      utterance.lang = "id-ID";
      utterance.rate = this.state.voiceSettings.rate;
      utterance.pitch = this.state.voiceSettings.pitch;
      utterance.volume = this.state.voiceSettings.volume;

      // Event handlers
      utterance.onstart = () => {
        this.showNotification("Sedang membaca konten...", "info");
      };

      utterance.onend = () => {
        this.showNotification("Pembacaan selesai", "success");
        resolve();
      };

      utterance.onerror = (event) => {
        // Speech error: ${event.error}
        this.showNotification(`Error: ${event.error}`, "error");
        reject(event.error);
      };

      // Mulai membaca
      speechSynthesis.speak(utterance);
    });
  }

  // Auto-start for specific pages
  async autoStartForPage() {
    try {
      // Hanya jalankan jika autoRead aktif dan textReader juga aktif
      if (!this.state.autoRead || !this.state.textReaderActive) {
        // Auto-start tidak dijalankan: autoRead atau textReader tidak aktif
        return;
      }

      // Pastikan DOM sudah siap
      if (!document.body || !document.querySelector) {
        // DOM belum siap, tunda auto-start
        setTimeout(() => this.autoStartForPage(), 1000);
        return;
      }

      // Pastikan speech synthesis tersedia dan siap
      if (!("speechSynthesis" in window)) {
        // Speech synthesis tidak tersedia, nonaktifkan autoRead
        this.state.autoRead = false;
        this.saveSettings();
        return;
      }

      // Tunggu speech synthesis benar-benar siap
      if (speechSynthesis.speaking || speechSynthesis.pending) {
        // Masih ada speech yang berjalan, tunda auto-start
        setTimeout(() => this.autoStartForPage(), 1000);
        return;
      }

      // Tambahan: pastikan speech synthesis benar-benar siap
      if (speechSynthesis.getVoices().length === 0) {
        // Voices belum siap, tunda auto-start
        setTimeout(() => this.autoStartForPage(), 1000);
        return;
      }

      const pageTitle = document.querySelector("h1.font-medium");
      const loginTitle = document.querySelector("h2.text-cyan-400");

      // Debug logging
      // Auto-start check - pageTitle: ${pageTitle?.textContent}
      // Auto-start check - loginTitle: ${loginTitle?.textContent}

      if (loginTitle && loginTitle.textContent.includes("REGISTER")) {
        // Halaman register terdeteksi, memulai pembaca teks otomatis...
        this.showNotification(
          "Selamat datang di Halaman Register Ruang Lestari. Pembaca teks akan membaca informasi halaman ini.",
          "info"
        );

        // Mulai membaca konten register langsung tanpa delay
        if (
          this.state.textReaderActive &&
          this.state.autoRead &&
          !speechSynthesis.speaking
        ) {
          this.startTextReader();
        }
      } else if (loginTitle && loginTitle.textContent.includes("LOGIN")) {
        // Halaman login terdeteksi, memulai pembaca teks otomatis...
        this.showNotification(
          "Selamat datang di Halaman Login Ruang Lestari. Pembaca teks akan membaca informasi halaman ini.",
          "info"
        );

        // Mulai membaca konten login langsung tanpa delay
        if (
          this.state.textReaderActive &&
          this.state.autoRead &&
          !speechSynthesis.speaking
        ) {
          this.startTextReader();
        }
      } else if (pageTitle) {
        const title = pageTitle.textContent.trim();

        if (title.includes("Dashboard")) {
          // Halaman dashboard terdeteksi, memulai pembaca teks otomatis...
          this.showNotification(
            "Selamat datang di Dashboard Ruang Lestari. Pembaca teks akan membaca konten beranda.",
            "info"
          );

          // Mulai membaca langsung tanpa delay
          if (
            this.state.textReaderActive &&
            this.state.autoRead &&
            !speechSynthesis.speaking
          ) {
            this.startTextReader();
          }
        } else if (title.includes("Ruangan")) {
          // Halaman ruangan terdeteksi, memulai pembaca teks otomatis...
          this.showNotification(
            "Selamat datang di Halaman Ruangan Ruang Lestari. Pembaca teks akan membaca daftar ruangan yang tersedia.",
            "info"
          );

          // Mulai membaca langsung tanpa delay
          if (
            this.state.textReaderActive &&
            this.state.autoRead &&
            !speechSynthesis.speaking
          ) {
            this.startTextReader();
          }
        } else if (title.includes("Pesanan")) {
          // Halaman pesanan terdeteksi, memulai pembaca teks otomatis...
          this.showNotification(
            "Selamat datang di Halaman Pesanan Ruang Lestari. Pembaca teks akan membaca detail ruangan dan form pemesanan.",
            "info"
          );

          // Mulai membaca langsung tanpa delay
          if (
            this.state.textReaderActive &&
            this.state.autoRead &&
            !speechSynthesis.speaking
          ) {
            this.startTextReader();
          }
        } else if (title.includes("Riwayat Pesanan")) {
          // Halaman riwayat pesanan terdeteksi, memulai pembaca teks otomatis...
          this.showNotification(
            "Selamat datang di Halaman Riwayat Pesanan Ruang Lestari. Pembaca teks akan membaca riwayat pesanan yang tersimpan.",
            "info"
          );

          // Mulai membaca langsung tanpa delay
          if (
            this.state.textReaderActive &&
            this.state.autoRead &&
            !speechSynthesis.speaking
          ) {
            this.startTextReader();
          }
        }
      } else {
        // Halaman tidak dikenali untuk auto-start
      }
    } catch (error) {
      // Error dalam autoStartForPage
      // Jangan tampilkan error ke user untuk auto-start
      // Nonaktifkan autoRead jika terjadi error berulang
      this.state.autoRead = false;
      this.saveSettings();
    }
  }

  // Read page content (called from button)
  readPageContent() {
    if (!this.state.textReaderActive) {
      this.state.textReaderActive = true;
      this.updateUI();
    }
    this.startTextReader();
  }

  // Show notification
  showNotification(message, type = "info") {
    // Remove existing notifications
    const existing = document.querySelector(".accessibility-notification");
    if (existing) existing.remove();

    // Create new notification
    const notification = document.createElement("div");
    notification.className = `accessibility-notification fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-white text-sm font-medium transition-all duration-300 transform translate-x-full`;

    // Set color based on type
    const colors = {
      success: "bg-green-500",
      error: "bg-red-500",
      warning: "bg-yellow-500",
      info: "bg-blue-500",
    };

    notification.classList.add(colors[type] || colors.info);
    notification.textContent = message;
    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => notification.classList.remove("translate-x-full"), 100);

    // Auto hide after 3 seconds
    setTimeout(() => {
      notification.classList.add("translate-x-full");
      setTimeout(() => {
        if (notification.parentNode) notification.remove();
      }, 300);
    }, 3000);
  }

  // Voice settings functions
  changeVoiceRate(action) {
    switch (action) {
      case "slower":
        this.state.voiceSettings.rate = Math.max(
          0.5,
          this.state.voiceSettings.rate - 0.1
        );
        break;
      case "faster":
        this.state.voiceSettings.rate = Math.min(
          1.5,
          this.state.voiceSettings.rate + 0.1
        );
        break;
      case "reset":
        this.state.voiceSettings.rate = 0.9;
        break;
    }

    this.saveSettings();
    this.showNotification(
      `Kecepatan suara: ${(this.state.voiceSettings.rate * 100).toFixed(0)}%`
    );
  }

  changeVoicePitch(action) {
    switch (action) {
      case "lower":
        this.state.voiceSettings.pitch = Math.max(
          0.5,
          this.state.voiceSettings.pitch - 0.1
        );
        break;
      case "higher":
        this.state.voiceSettings.pitch = Math.min(
          2.0,
          this.state.voiceSettings.pitch + 0.1
        );
        break;
      case "reset":
        this.state.voiceSettings.pitch = 1.0;
        break;
    }

    this.saveSettings();
    this.showNotification(
      `Tinggi suara: ${(this.state.voiceSettings.pitch * 100).toFixed(0)}%`
    );
  }

  // Setup audio feedback untuk field dan input
  setupAudioFeedback() {
    // Daftar elemen yang akan mendapat audio feedback
    const interactiveElements = [
      'input[type="text"]',
      'input[type="email"]',
      'input[type="password"]',
      'input[type="number"]',
      'input[type="date"]',
      'input[type="time"]',
      "textarea",
      "select",
      "button",
      "a",
      "label",
      '[role="button"]',
      "[tabindex]",
    ];

    // Tambah event listener untuk setiap elemen
    interactiveElements.forEach((selector) => {
      const elements = document.querySelectorAll(selector);
      elements.forEach((element) => {
        // Skip elemen aksesibilitas
        if (!this.isAccessibilityElement(element)) {
          this.addAudioFeedbackToElement(element);
        }
      });
    });

    // Observer untuk elemen yang ditambahkan secara dinamis
    this.setupMutationObserver();
  }

  // Tambah audio feedback ke elemen
  addAudioFeedbackToElement(element) {
    // Skip jika sudah ada event listener
    if (element.hasAttribute("data-audio-feedback")) {
      return;
    }

    // Skip elemen aksesibilitas untuk mencegah feedback yang tidak diinginkan
    if (this.isAccessibilityElement(element)) {
      return;
    }

    // Mark elemen sudah ada audio feedback
    element.setAttribute("data-audio-feedback", "true");

    // Focus event
    element.addEventListener("focus", (e) => {
      this.handleElementFocus(e.target);
    });

    // Change event untuk select dropdown
    if (element.tagName.toLowerCase() === "select") {
      element.addEventListener("change", (e) => {
        this.handleElementChange(e.target);
      });
    }
  }

  // Cek apakah elemen adalah bagian dari sistem aksesibilitas
  isAccessibilityElement(element) {
    // Skip jika elemen berada di dalam menu aksesibilitas
    if (element.closest("#accessibilityMenu")) {
      return true;
    }

    // Skip tombol aksesibilitas utama
    if (element.closest('[onclick*="accessibilityManager"]')) {
      return true;
    }

    // Skip elemen dengan class atau id yang berhubungan dengan aksesibilitas
    const accessibilityClasses = [
      "accessibility-notification",
      "accessibility-control",
      "accessibility-button",
    ];

    for (const className of accessibilityClasses) {
      if (element.classList.contains(className)) {
        return true;
      }
    }

    // Skip elemen dengan aria-label yang berhubungan dengan aksesibilitas
    const ariaLabel = element.getAttribute("aria-label") || "";
    if (
      ariaLabel.toLowerCase().includes("aksesibilitas") ||
      ariaLabel.toLowerCase().includes("accessibility")
    ) {
      return true;
    }

    return false;
  }

  // Handle focus pada elemen
  handleElementFocus(element) {
    if (!this.state.audioFeedback) return;

    const elementType = this.getElementType(element);
    const elementName = this.getElementName(element);

    // Play sound effect (selalu aktif ketika audio feedback aktif)
    this.playSoundEffect("focus");

    // Announce element dengan voice
    if (this.state.textReaderActive) {
      this.announceElement(elementType, elementName, "focus");
    }
  }

  // Handle change pada elemen (khusus untuk select dropdown)
  handleElementChange(element) {
    if (!this.state.audioFeedback) return;

    const elementType = this.getElementType(element);
    const elementName = this.getElementName(element);
    const selectedValue = this.getSelectedValue(element);

    // Play sound effect
    this.playSoundEffect("focus");

    // Announce perubahan nilai dengan voice
    if (this.state.textReaderActive) {
      this.announceElementChange(elementType, elementName, selectedValue);
    }
  }

  // Dapatkan tipe elemen
  getElementType(element) {
    const tagName = element.tagName.toLowerCase();
    const type = element.type || "";
    const role = element.getAttribute("role") || "";

    if (tagName === "input") {
      switch (type) {
        case "text":
          return "field teks";
        case "email":
          return "field email";
        case "password":
          return "field password";
        case "number":
          return "field angka";
        case "date":
          return "field tanggal";
        case "time":
          return "field waktu";
        case "submit":
          return "tombol kirim";
        case "button":
          return "tombol";
        default:
          return "field input";
      }
    } else if (tagName === "textarea") {
      return "area teks";
    } else if (tagName === "select") {
      return "pilihan dropdown";
    } else if (tagName === "button") {
      return "tombol";
    } else if (tagName === "a") {
      return "link";
    } else if (tagName === "label") {
      return "label";
    } else if (role === "button") {
      return "tombol";
    } else {
      return "elemen";
    }
  }

  // Dapatkan nama elemen
  getElementName(element) {
    // Cari label yang terkait
    let name = "";

    // Cek label yang terkait (menggunakan for attribute)
    const label = document.querySelector(`label[for="${element.id}"]`);
    if (label && label.textContent.trim()) {
      name = label.textContent.trim();
    }
    // Cek placeholder
    else if (element.placeholder) {
      name = element.placeholder;
    }
    // Cek aria-label
    else if (element.getAttribute("aria-label")) {
      name = element.getAttribute("aria-label");
    }
    // Cek title
    else if (element.title) {
      name = element.title;
    }
    // Cek text content untuk button/link
    else if (element.textContent && element.textContent.trim()) {
      name = element.textContent.trim();
    }
    // Cek name attribute
    else if (element.name) {
      name = element.name;
    }
    // Cek id
    else if (element.id) {
      name = element.id.replace(/[-_]/g, " ");
    }

    // Clean up nama
    if (name) {
      name = name.replace(/[^\w\s]/g, " ").trim();
      if (name.length > 50) {
        name = name.substring(0, 50) + "...";
      }
    }

    return name || "tanpa nama";
  }

  // Dapatkan nilai yang dipilih dari select dropdown
  getSelectedValue(element) {
    if (element.tagName.toLowerCase() === "select") {
      const selectedOption = element.options[element.selectedIndex];
      if (selectedOption && selectedOption.value) {
        // Konversi nilai ke bahasa Indonesia yang lebih natural
        const value = selectedOption.value.toLowerCase();
        const text = selectedOption.textContent.trim();

        if (value === "admin") {
          return "Admin";
        } else if (value === "user") {
          return "User";
        } else {
          return text || value;
        }
      }
    }
    return "tidak ada pilihan";
  }

  // Announce elemen dengan voice
  announceElement(elementType, elementName, action) {
    let message = "";

    if (action === "focus") {
      message = `${elementType} ${elementName} aktif`;
    } else if (action === "click") {
      message = `${elementType} ${elementName} diklik`;
    }

    if (message && this.state.textReaderActive) {
      // Gunakan speech synthesis untuk announcement
      const utterance = new SpeechSynthesisUtterance(message);
      utterance.lang = "id-ID";
      utterance.rate = this.state.voiceSettings.rate;
      utterance.pitch = this.state.voiceSettings.pitch;
      utterance.volume = this.state.voiceSettings.volume;

      // Cancel ongoing speech dan play announcement
      setTimeout(() => {
        speechSynthesis.speak(utterance);
      }, 100);
    }
  }

  // Announce perubahan nilai elemen
  announceElementChange(elementType, elementName, selectedValue) {
    let message = "";

    if (elementType === "pilihan dropdown") {
      message = `${elementName} dipilih: ${selectedValue}`;
    } else {
      message = `${elementName} berubah menjadi: ${selectedValue}`;
    }

    if (message && this.state.textReaderActive) {
      // Gunakan speech synthesis untuk announcement
      const utterance = new SpeechSynthesisUtterance(message);
      utterance.lang = "id-ID";
      utterance.rate = this.state.voiceSettings.rate;
      utterance.pitch = this.state.voiceSettings.pitch;
      utterance.volume = this.state.voiceSettings.volume;

      // Cancel ongoing speech dan play announcement
      speechSynthesis.cancel();
      setTimeout(() => {
        speechSynthesis.speak(utterance);
      }, 100);
    }
  }

  // Play sound effects
  playSoundEffect(type) {
    try {
      // Buat audio context untuk sound effects
      if (!this.audioContext) {
        this.audioContext = new (window.AudioContext ||
          window.webkitAudioContext)();
      }

      let frequency, duration, type_osc;

      switch (type) {
        case "focus":
          frequency = 800; // Hz
          duration = 0.1; // seconds
          type_osc = "sine";
          break;
        default:
          frequency = 600;
          duration = 0.1;
          type_osc = "sine";
      }

      // Buat oscillator
      const oscillator = this.audioContext.createOscillator();
      const gainNode = this.audioContext.createGain();

      oscillator.connect(gainNode);
      gainNode.connect(this.audioContext.destination);

      oscillator.frequency.setValueAtTime(
        frequency,
        this.audioContext.currentTime
      );
      oscillator.type = type_osc;

      // Set volume fade
      gainNode.gain.setValueAtTime(0.3, this.audioContext.currentTime);
      gainNode.gain.exponentialRampToValueAtTime(
        0.01,
        this.audioContext.currentTime + duration
      );

      // Play sound
      oscillator.start(this.audioContext.currentTime);
      oscillator.stop(this.audioContext.currentTime + duration);
    } catch (error) {
      // Fallback: gunakan beep sederhana jika Web Audio API tidak tersedia
      this.fallbackBeep();
    }
  }

  // Fallback beep sederhana
  fallbackBeep() {
    try {
      // Buat audio element sederhana
      const audio = new Audio();
      audio.src =
        "data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIG2m98OScTgwOUarm7blmGgU7k9n1unEiBC13yO/eizEIHWq+8+OWT";
      audio.volume = 0.3;
      audio.play().catch(() => {
        // Jika gagal, coba buat beep dengan speaker
        this.createBeepWithSpeaker();
      });
    } catch (error) {
      // Jika semua gagal, skip sound effect
    }
  }

  // Buat beep dengan speaker (fallback terakhir)
  createBeepWithSpeaker() {
    try {
      const audioContext = new (window.AudioContext ||
        window.webkitAudioContext)();
      const oscillator = audioContext.createOscillator();
      const gainNode = audioContext.createGain();

      oscillator.connect(gainNode);
      gainNode.connect(audioContext.destination);

      oscillator.frequency.value = 800;
      oscillator.type = "sine";
      gainNode.gain.value = 0.1;

      oscillator.start();
      setTimeout(() => oscillator.stop(), 100);
    } catch (error) {
      // Skip sound effect jika tidak bisa dibuat
    }
  }

  // Setup mutation observer untuk elemen yang ditambahkan dinamis
  setupMutationObserver() {
    try {
      this.mutationObserver = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          if (mutation.type === "childList") {
            mutation.addedNodes.forEach((node) => {
              if (node.nodeType === Node.ELEMENT_NODE) {
                // Cek elemen yang ditambahkan
                this.addAudioFeedbackToElement(node);

                // Cek child elements
                const childElements = node.querySelectorAll(
                  'input, textarea, select, button, a, label, [role="button"], [tabindex]'
                );
                childElements.forEach((element) => {
                  // Skip elemen aksesibilitas
                  if (!this.isAccessibilityElement(element)) {
                    this.addAudioFeedbackToElement(element);
                  }
                });
              }
            });
          }
        });
      });

      // Observe perubahan pada document body
      this.mutationObserver.observe(document.body, {
        childList: true,
        subtree: true,
      });
    } catch (error) {
      // MutationObserver tidak tersedia
    }
  }

  // Toggle audio feedback
  toggleAudioFeedback() {
    this.state.audioFeedback = !this.state.audioFeedback;

    // Ketika audio feedback diaktifkan, sound effects juga otomatis aktif
    if (this.state.audioFeedback) {
      this.state.soundEffects = true;
    }

    this.updateUI();
    this.saveSettings();

    const message = this.state.audioFeedback
      ? "Audio feedback diaktifkan (termasuk sound effects)"
      : "Audio feedback dinonaktifkan";
    this.showNotification(message, "info");
  }
}

// Initialize accessibility manager when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  window.accessibilityManager = new AccessibilityManager();
});

// Export for global access
window.AccessibilityManager = AccessibilityManager;
