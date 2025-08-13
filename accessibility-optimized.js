// ========================================
// SISTEM AKSESIBILITAS RUANG LESTARI
// Versi 3.0 - Optimized & Simplified
// ========================================

class AccessibilityManager {
  constructor() {
    this.state = {
      textReaderActive: false,
      highContrastActive: false,
      fontSize: 100,
      autoRead: false,
      audioFeedback: true
    };
    
    this.autoStartExecuted = false;
    this.init();
  }

  init() {
    this.loadSettings();
    this.setupEventListeners();
    this.updateUI();
  }

  loadSettings() {
    try {
      const saved = localStorage.getItem("ruangLestariAccessibility");
      if (saved) {
        this.state = { ...this.state, ...JSON.parse(saved) };
      }
    } catch (error) {
      // Use default settings
    }
  }

  saveSettings() {
    try {
      localStorage.setItem("ruangLestariAccessibility", JSON.stringify(this.state));
    } catch (error) {
      // Failed to save settings
    }
  }

  setupEventListeners() {
    // Auto-start after 2 seconds
    setTimeout(() => this.autoStartForPage(), 2000);
    
    // Handle page changes
    window.addEventListener("popstate", () => this.resetForNewPage());
    window.addEventListener("hashchange", () => this.resetForNewPage());
    
    // Handle window events
    window.addEventListener("beforeunload", () => this.stopTextReader());
    document.addEventListener("visibilitychange", () => {
      if (document.hidden) {
        this.stopTextReader();
      } else if (this.state.autoRead && this.state.textReaderActive) {
        this.resetForNewPage();
      }
    });
  }

  toggleMenu() {
    const menu = document.getElementById("accessibilityMenu");
    if (menu) {
      const isHidden = menu.classList.contains("hidden");
      if (isHidden) {
        menu.classList.remove("hidden");
        this.positionMenuResponsively();
        this.addMenuCloseListeners();
      } else {
        menu.classList.add("hidden");
        this.removeMenuCloseListeners();
      }
    }
  }

  positionMenuResponsively() {
    const menu = document.getElementById("accessibilityMenu");
    if (!menu) return;

    const screenWidth = window.innerWidth;
    if (screenWidth <= 768) {
      menu.style.position = "fixed";
      menu.style.top = "50%";
      menu.style.left = "50%";
      menu.style.transform = "translate(-50%, -50%)";
    } else {
      menu.style.position = "absolute";
      menu.style.bottom = "16px";
      menu.style.right = "0";
    }
  }

  addMenuCloseListeners() {
    this.removeMenuCloseListeners();
    
    this.escapeListener = (e) => {
      if (e.key === "Escape") this.toggleMenu();
    };
    document.addEventListener("keydown", this.escapeListener);
    
    this.outsideClickListener = (e) => {
      const menu = document.getElementById("accessibilityMenu");
      const button = document.querySelector('[onclick*="accessibilityManager"]');
      if (menu && !menu.contains(e.target) && !button.contains(e.target)) {
        this.toggleMenu();
      }
    };
    document.addEventListener("click", this.outsideClickListener);
  }

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

  toggleTextReader() {
    this.state.textReaderActive = !this.state.textReaderActive;
    this.state.autoRead = this.state.textReaderActive;
    
    if (this.state.textReaderActive) {
      this.autoStartExecuted = false;
      this.showNotification("Pembaca otomatis diaktifkan", "success");
      setTimeout(() => this.autoStartForPage(), 1000);
    } else {
      this.showNotification("Pembaca otomatis dinonaktifkan", "info");
      this.stopTextReader();
    }
    
    this.updateUI();
    this.saveSettings();
  }

  toggleHighContrast() {
    this.state.highContrastActive = !this.state.highContrastActive;
    
    if (this.state.highContrastActive) {
      document.body.classList.add("high-contrast");
    } else {
      document.body.classList.remove("high-contrast");
    }
    
    this.updateUI();
    this.saveSettings();
    this.showNotification(`Kontras tinggi ${this.state.highContrastActive ? "diaktifkan" : "dinonaktifkan"}`);
  }

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
    
    document.body.style.fontSize = this.state.fontSize + "%";
    this.updateUI();
    this.saveSettings();
    this.showNotification(`Ukuran font: ${this.state.fontSize}%`);
  }

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
  }

  async startTextReader() {
    try {
      if (!("speechSynthesis" in window)) {
        this.showNotification("Pembaca teks tidak didukung di browser ini", "error");
        return;
      }

      this.stopTextReader();
      await new Promise(resolve => setTimeout(resolve, 300));

      const content = this.getPageContent();
      if (!content || content.trim().length === 0) {
        this.showNotification("Tidak ada konten untuk dibaca", "warning");
        return;
      }

      this.showNotification("Memulai pembacaan konten...", "info");
      await this.speakText(content.substring(0, 3000));
    } catch (error) {
      console.error("Error dalam text reader:", error);
      this.showNotification("Fitur Pembaca Teks Otomatis Dimatikan.", "error");
      this.state.autoRead = false;
      this.saveSettings();
    }
  }

  stopTextReader() {
    if ("speechSynthesis" in window) {
      speechSynthesis.cancel();
    }
  }

  getPageContent() {
    const pageTitle = document.querySelector("h1.font-medium");
    const loginTitle = document.querySelector("h2.text-cyan-400");
    const ruanganCards = document.querySelectorAll(".bg-white.shadow-md");
    
    let content = [];

    if (loginTitle && loginTitle.textContent.includes("LOGIN")) {
      content = this.getLoginContent();
    } else if (loginTitle && loginTitle.textContent.includes("REGISTER")) {
      content = this.getRegisterContent();
    } else if (pageTitle) {
      const title = pageTitle.textContent.trim();
      if (title.includes("Dashboard")) content = this.getDashboardContent();
      else if (title.includes("Ruangan")) content = this.getRuanganContent();
      else if (title.includes("Pesanan") && !title.includes("Riwayat")) content = this.getPesananContent();
      else if (title.includes("Riwayat Pesanan")) content = this.getRiwayatContent();
      else content = this.getGeneralContent();
    } else {
      content = ruanganCards.length > 0 ? this.getRuanganContent() : this.getGeneralContent();
    }

    return content.join(". ");
  }

  getLoginContent() {
    return [
      "Halaman Login Ruang Lestari",
      "Sistem Informasi Reservasi Ruang Rapat Dinas Lingkungan Hidup Kabupaten Tegal",
      "Form login memiliki field: Username, Password, dan Role",
      "Pilih antara Admin atau User untuk role",
      "Klik tombol Login untuk masuk ke sistem",
      "Jika belum punya akun, klik link Register"
    ];
  }

  getRegisterContent() {
    return [
      "Halaman Register Ruang Lestari",
      "Sistem Informasi Reservasi Ruang Rapat Dinas Lingkungan Hidup Kabupaten Tegal",
      "Form registrasi memiliki field: Username, Password, dan Role",
      "Pilih antara Admin atau User untuk role",
      "Klik tombol Register untuk membuat akun baru",
      "Jika sudah punya akun, klik link Login"
    ];
  }

  getDashboardContent() {
    const content = ["Selamat datang di Dashboard Ruang Lestari"];
    
    const statistikCards = document.querySelectorAll(".bg-white.shadow.flex.flex-col.items-center");
    if (statistikCards.length > 0) {
      content.push(`Dashboard menampilkan ${statistikCards.length} statistik utama`);
      
      statistikCards.forEach((card, index) => {
        const angkaElement = card.querySelector("p.font-medium");
        const labelElement = card.querySelector("p:not(.font-medium)");
        if (angkaElement && labelElement) {
          content.push(`${labelElement.textContent.trim()}: ${angkaElement.textContent.trim()}`);
        }
      });
    }
    
    return content;
  }

  getRuanganContent() {
    const content = ["Halaman Ruangan Ruang Lestari"];
    
    const cards = document.querySelectorAll(".bg-white.shadow-md");
    if (cards.length > 0) {
      content.push(`Terdapat ${cards.length} ruangan yang tersedia untuk dipesan`);
      
      cards.forEach((card, index) => {
        const namaRuangan = card.querySelector("h1.font-medium");
        if (namaRuangan) {
          content.push(`Ruangan ${index + 1}: ${namaRuangan.textContent.trim()}`);
          
          const fasilitas = card.querySelector("div.flex.flex-col.gap-1.text-xs p:first-child");
          if (fasilitas && fasilitas.textContent.trim()) {
            content.push(`Fasilitas: ${fasilitas.textContent.trim()}`);
          }
        }
      });
    }
    
    return content;
  }

  getPesananContent() {
    return [
      "Halaman Pesanan Ruangan Ruang Lestari",
      "Form pemesanan tersedia dengan field: Tanggal, Sesi, Bidang, dan Agenda Rapat",
      "Pilih tanggal, sesi waktu, bidang kerja, dan isi agenda rapat untuk melakukan pemesanan"
    ];
  }

  getRiwayatContent() {
    const content = ["Halaman Riwayat Pesanan Ruang Lestari"];
    
    const table = document.querySelector("table tbody");
    if (table) {
      const rows = table.querySelectorAll("tr");
      if (rows.length > 0) {
        const maxRows = Math.min(rows.length, 5);
        content.push(`Terdapat ${maxRows} riwayat pesanan yang tersimpan`);
        
        for (let i = 0; i < maxRows; i++) {
          const cells = rows[i].querySelectorAll("td");
          if (cells.length >= 4) {
            const namaPemesan = cells[1].textContent.trim();
            const ruangRapat = cells[2].textContent.trim();
            const tanggal = cells[3].textContent.trim();
            content.push(`Pesanan ${i + 1}: ${namaPemesan}, ${ruangRapat}, ${tanggal}`);
          }
        }
      } else {
        content.push("Belum ada riwayat pesanan yang tersimpan");
      }
    }
    
    return content;
  }

  getGeneralContent() {
    const content = [];
    const pageTitle = document.title || "Halaman web";
    content.push(`Judul halaman: ${pageTitle}`);
    
    const headings = document.querySelectorAll("h1, h2, h3");
    headings.forEach((heading, index) => {
      const text = heading.textContent.trim();
      if (text && text.length > 3 && !text.includes("Aksesibilitas")) {
        content.push(`Heading ${index + 1}: ${text}`);
      }
    });
    
    if (content.length <= 1) {
      content.push("Halaman ini berisi konten yang dapat diakses melalui fitur aksesibilitas");
    }
    
    return content;
  }

  async speakText(text) {
    return new Promise((resolve, reject) => {
      try {
        if (speechSynthesis.speaking || speechSynthesis.pending) {
          speechSynthesis.cancel();
          setTimeout(() => this.createAndSpeakUtterance(text, resolve, reject), 200);
          return;
        }
        this.createAndSpeakUtterance(text, resolve, reject);
      } catch (error) {
        reject(error);
      }
    });
  }

  createAndSpeakUtterance(text, resolve, reject) {
    try {
      const utterance = new SpeechSynthesisUtterance(text);
      
      const voices = speechSynthesis.getVoices();
      const bestVoice = voices.find(voice => 
        voice.lang.includes("id") || 
        voice.lang.includes("en") ||
        voice.default
      ) || voices[0];
      
      if (bestVoice) {
        utterance.voice = bestVoice;
        utterance.lang = bestVoice.lang;
      }
      
      utterance.rate = 0.8;
      utterance.pitch = 1.0;
      utterance.volume = 1.0;
      
      utterance.onstart = () => this.showNotification("Sedang membaca konten...", "info");
      utterance.onend = () => {
        this.showNotification("Pembacaan selesai", "success");
        resolve();
      };
      utterance.onerror = (event) => {
        this.showNotification(`Error: ${event.error}`, "error");
        reject(event.error);
      };
      
      speechSynthesis.speak(utterance);
    } catch (error) {
      reject(error);
    }
  }

  async autoStartForPage() {
    try {
      if (!this.state.autoRead || !this.state.textReaderActive || this.autoStartExecuted) {
        return;
      }

      if (!("speechSynthesis" in window)) {
        this.state.autoRead = false;
        this.saveSettings();
        return;
      }

      if (speechSynthesis.speaking || speechSynthesis.pending) {
        setTimeout(() => this.autoStartForPage(), 1000);
        return;
      }

      const pageTitle = document.querySelector("h1.font-medium");
      const loginTitle = document.querySelector("h2.text-cyan-400");
      
      let shouldStartReading = false;
      
      if (loginTitle && loginTitle.textContent.includes("LOGIN")) {
        shouldStartReading = true;
        this.showNotification("Selamat datang di Halaman Login Ruang Lestari", "info");
      } else if (loginTitle && loginTitle.textContent.includes("REGISTER")) {
        shouldStartReading = true;
        this.showNotification("Selamat datang di Halaman Register Ruang Lestari", "info");
      } else if (pageTitle) {
        const title = pageTitle.textContent.trim();
        if (title.includes("Dashboard") || title.includes("Ruangan") || 
            title.includes("Pesanan") || title.includes("Riwayat")) {
          shouldStartReading = true;
          this.showNotification(`Selamat datang di ${title}`, "info");
        }
      }

      if (shouldStartReading) {
        this.autoStartExecuted = true;
        setTimeout(() => this.startTextReader(), 500);
      }
    } catch (error) {
      console.error("Error dalam autoStartForPage:", error);
      this.state.autoRead = false;
      this.saveSettings();
    }
  }

  readPageContent() {
    this.stopTextReader();
    this.autoStartExecuted = false;
    
    if (!this.state.textReaderActive) {
      this.state.textReaderActive = true;
      this.state.autoRead = true;
      this.updateUI();
      this.saveSettings();
      this.showNotification("Pembaca teks diaktifkan", "success");
    } else {
      this.showNotification("Memulai pembacaan konten halaman...", "info");
    }
    
    setTimeout(() => this.startTextReader(), 300);
  }

  showNotification(message, type = "info") {
    const existing = document.querySelector(".accessibility-notification");
    if (existing) existing.remove();

    const notification = document.createElement("div");
    notification.className = `accessibility-notification fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-white text-sm font-medium transition-all duration-300 transform translate-x-full`;

    const colors = {
      success: "bg-green-500",
      error: "bg-red-500",
      warning: "bg-yellow-500",
      info: "bg-blue-500"
    };

    notification.classList.add(colors[type] || colors.info);
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => notification.classList.remove("translate-x-full"), 100);
    setTimeout(() => {
      notification.classList.add("translate-x-full");
      setTimeout(() => {
        if (notification.parentNode) notification.remove();
      }, 300);
    }, 3000);
  }

  resetForNewPage() {
    this.autoStartExecuted = false;
    this.stopTextReader();
    
    if (this.state.autoRead && this.state.textReaderActive) {
      setTimeout(() => this.autoStartForPage(), 1000);
    }
  }

  cleanup() {
    this.stopTextReader();
    this.removeMenuCloseListeners();
  }
}

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", function() {
  window.accessibilityManager = new AccessibilityManager();
});

// Cleanup when page unloads
window.addEventListener("beforeunload", function() {
  if (window.accessibilityManager) {
    window.accessibilityManager.cleanup();
  }
});

// Export for global access
window.AccessibilityManager = AccessibilityManager;