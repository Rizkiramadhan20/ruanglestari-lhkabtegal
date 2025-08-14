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
    };

    this.autoStartExecuted = false;
    this.lastSpokenInput = null;
    this.lastSpokenText = null;
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
      localStorage.setItem(
        "ruangLestariAccessibility",
        JSON.stringify(this.state)
      );
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

    // Add input field accessibility with delay to ensure DOM is ready
    setTimeout(() => this.setupInputAccessibility(), 500);

    // Also setup input accessibility when DOM content is loaded
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", () => {
        setTimeout(() => this.setupInputAccessibility(), 100);
      });
    }

    // Setup mutation observer to handle dynamically added inputs
    this.setupMutationObserver();
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
      const button = document.querySelector(
        '[onclick*="accessibilityManager"]'
      );
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
      this.showNotification("Suara otomatis diaktifkan", "success");
      setTimeout(() => this.autoStartForPage(), 1000);
    } else {
      this.showNotification("Suara otomatis dimatikan", "info");
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
    this.showNotification(
      `Kontras tinggi ${
        this.state.highContrastActive ? "diaktifkan" : "dinonaktifkan"
      }`
    );
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
        this.showNotification(
          "Text-to-speech tidak didukung di perangkat ini",
          "warning"
        );
        return;
      }

      // Check if speech synthesis is available and working
      if (speechSynthesis.speaking || speechSynthesis.pending) {
        speechSynthesis.cancel();
      }

      // Wait a bit for mobile devices
      await new Promise((resolve) => setTimeout(resolve, 500));

      const content = this.getPageContent();
      if (!content || content.trim().length === 0) {
        return;
      }

      // Use chunked reading for long content to ensure all data is spoken
      if (content.length > 2000) {
        // Split content into sentences for better chunking
        const sentences = content
          .split(/[.!?]+/)
          .filter((s) => s.trim().length > 0);
        await this.speakLongContent(sentences);
      } else {
        await this.speakText(content);
      }
    } catch (error) {
      console.error("Error dalam text reader:", error);
      this.state.autoRead = false;
      this.saveSettings();
      this.showNotification("Gagal memulai text reader", "error");
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
      else if (title.includes("Pesanan") && !title.includes("Riwayat"))
        content = this.getPesananContent();
      else if (title.includes("Riwayat Pesanan"))
        content = this.getRiwayatContent();
      else content = this.getGeneralContent();
    } else {
      content =
        ruanganCards.length > 0
          ? this.getRuanganContent()
          : this.getGeneralContent();
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
      "Jika belum punya akun, klik link Register",
    ];
  }

  getRegisterContent() {
    return [
      "Halaman Register Ruang Lestari",
      "Sistem Informasi Reservasi Ruang Rapat Dinas Lingkungan Hidup Kabupaten Tegal",
      "Form registrasi memiliki field: Username, Password, dan Role",
      "Pilih antara Admin atau User untuk role",
      "Klik tombol Register untuk membuat akun baru",
      "Jika sudah punya akun, klik link Login",
    ];
  }

  getDashboardContent() {
    const content = ["Selamat datang di Dashboard Ruang Lestari"];

    const statistikCards = document.querySelectorAll(
      ".bg-white.shadow.flex.flex-col.items-center"
    );
    if (statistikCards.length > 0) {
      content.push(
        `Dashboard menampilkan ${statistikCards.length} statistik utama`
      );

      statistikCards.forEach((card, index) => {
        const angkaElement = card.querySelector("p.font-medium");
        const labelElement = card.querySelector("p:not(.font-medium)");
        if (angkaElement && labelElement) {
          content.push(
            `${labelElement.textContent.trim()}: ${angkaElement.textContent.trim()}`
          );
        }
      });
    }

    return content;
  }

  getRuanganContent() {
    const content = ["Halaman Ruangan Ruang Lestari"];

    const cards = document.querySelectorAll(".bg-white.shadow-md");
    if (cards.length > 0) {
      content.push(
        `Terdapat ${cards.length} ruangan yang tersedia untuk dipesan`
      );

      cards.forEach((card, index) => {
        const namaRuangan = card.querySelector("h1.font-medium");
        if (namaRuangan) {
          content.push(
            `Ruangan ${index + 1}: ${namaRuangan.textContent.trim()}`
          );

          const fasilitas = card.querySelector(
            "div.flex.flex-col.gap-1.text-xs p:first-child"
          );
          if (fasilitas && fasilitas.textContent.trim()) {
            content.push(`Fasilitas: ${fasilitas.textContent.trim()}`);
          }
        }
      });
    }

    return content;
  }

  getPesananContent() {
    const content = ["Halaman Pesanan Ruangan Ruang Lestari"];

    // Cari informasi ruangan yang dipilih - struktur di pesanan.php
    const selectedRoom = document.querySelector(
      ".bg-white.shadow-md.flex.flex-col.p-5.gap-2"
    );
    if (selectedRoom) {
      const roomName = selectedRoom.querySelector("h1.font-medium.text-lg");
      if (roomName) {
        content.push(`Ruangan yang dipilih: ${roomName.textContent.trim()}`);
      }

      // Cari fasilitas - di pesanan.php menggunakan p.text-xs
      const facilities = selectedRoom.querySelectorAll("p.text-xs");
      if (facilities.length > 0) {
        const facilityList = Array.from(facilities)
          .map((f) => f.textContent.trim())
          .filter((f) => f);
        if (facilityList.length > 0) {
          // Ambil fasilitas (biasanya yang pertama)
          if (facilityList[0]) {
            content.push(`Fasilitas ruangan: ${facilityList[0]}`);
          }
          // Ambil deskripsi (biasanya yang kedua)
          if (facilityList[1]) {
            content.push(`Deskripsi: ${facilityList[1]}`);
          }
        }
      }
    }

    // Tambahkan informasi form
    content.push(
      "Form pemesanan tersedia dengan field: Tanggal, Sesi, Bidang, dan Agenda Rapat"
    );
    content.push(
      "Pilih tanggal, sesi waktu, bidang kerja, dan isi agenda rapat untuk melakukan pemesanan"
    );

    return content;
  }

  getRiwayatContent() {
    const content = ["Halaman Riwayat Pesanan Ruang Lestari"];

    const table = document.querySelector("table tbody");
    if (table) {
      const rows = table.querySelectorAll("tr");
      console.log(`Found ${rows.length} rows in riwayat table`);

      if (rows.length > 0) {
        content.push(`Terdapat ${rows.length} riwayat pesanan yang tersimpan`);

        // Read all rows without limitation
        for (let i = 0; i < rows.length; i++) {
          const cells = rows[i].querySelectorAll("td");
          if (cells.length >= 7) {
            const namaPemesan = cells[1].textContent.trim();
            const ruangRapat = cells[2].textContent.trim();
            const tanggal = cells[3].textContent.trim();
            const waktu = cells[4].textContent.trim();
            const bidang = cells[5].textContent.trim();
            const agenda = cells[6].textContent.trim();

            // Create more concise description for better speech synthesis
            const pesananText = `Pesanan ${
              i + 1
            }: ${namaPemesan}, ruang ${ruangRapat}, tanggal ${tanggal}, waktu ${waktu}, bidang ${bidang}, agenda ${agenda}`;
            content.push(pesananText);
            console.log(`Added pesanan ${i + 1} to content`);
          }
        }

        // Confirm all data has been read
        content.push(
          `Semua ${rows.length} riwayat pesanan telah dibaca lengkap`
        );
        console.log(`Total content items: ${content.length}`);
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
      content.push(
        "Halaman ini berisi konten yang dapat diakses melalui fitur aksesibilitas"
      );
    }

    return content;
  }

  async speakText(text) {
    return new Promise((resolve, reject) => {
      try {
        if (speechSynthesis.speaking || speechSynthesis.pending) {
          speechSynthesis.cancel();
          setTimeout(
            () => this.createAndSpeakUtterance(text, resolve, reject),
            200
          );
          return;
        }
        this.createAndSpeakUtterance(text, resolve, reject);
      } catch (error) {
        reject(error);
      }
    });
  }

  async speakLongContent(contentArray) {
    try {
      if (!Array.isArray(contentArray)) {
        // If it's a string, split by sentences
        const sentences = contentArray
          .split(/[.!?]+/)
          .filter((s) => s.trim().length > 0);
        return this.speakLongContent(sentences);
      }

      // For very long content, read in smaller chunks to ensure all data is spoken
      const chunkSize = 5; // Read 5 items at a time for better comprehension
      const totalChunks = Math.ceil(contentArray.length / chunkSize);

      for (let i = 0; i < totalChunks; i++) {
        const start = i * chunkSize;
        const end = Math.min(start + chunkSize, contentArray.length);
        const chunk = contentArray.slice(start, end);

        // Join chunk with proper punctuation
        const chunkText = chunk.join(". ") + ".";

        // Speak this chunk
        await this.speakText(chunkText);

        // Add pause between chunks for better comprehension
        if (i < totalChunks - 1) {
          await new Promise((resolve) => setTimeout(resolve, 800));
        }
      }
    } catch (error) {
      console.error("Error speaking long content:", error);
      // Fallback to regular speech if chunked reading fails
      const fallbackText = Array.isArray(contentArray)
        ? contentArray.join(". ")
        : contentArray;
      await this.speakText(fallbackText);
    }
  }

  createAndSpeakUtterance(text, resolve, reject) {
    try {
      const utterance = new SpeechSynthesisUtterance(text);

      // Wait for voices to be loaded (especially important on mobile)
      const setupVoice = () => {
        const voices = speechSynthesis.getVoices();

        if (voices.length === 0) {
          // If no voices available, try again after a short delay
          setTimeout(setupVoice, 100);
          return;
        }

        // For mobile devices, prefer system default voice
        let bestVoice = null;

        // Try to find Indonesian voice first
        bestVoice = voices.find(
          (voice) => voice.lang.includes("id") || voice.lang.includes("id-ID")
        );

        // If no Indonesian, try English
        if (!bestVoice) {
          bestVoice = voices.find(
            (voice) => voice.lang.includes("id") || voice.lang.includes("id-ID")
          );
        }

        // If still no voice, use default
        if (!bestVoice && voices.length > 0) {
          bestVoice = voices[0];
        }

        if (bestVoice) {
          utterance.voice = bestVoice;
          utterance.lang = bestVoice.lang;
        }

        // Adjust settings for mobile
        const isMobile = window.innerWidth <= 768;
        utterance.rate = isMobile ? 0.9 : 0.8;
        utterance.pitch = 1.0;
        utterance.volume = 1.0;

        utterance.onstart = () => {
          console.log("Speech started:", text.substring(0, 50) + "...");
        };

        utterance.onend = () => {
          console.log("Speech ended");
          resolve();
        };

        utterance.onerror = (event) => {
          console.error("Speech synthesis error:", event.error);
          if (event.error !== "interrupted") {
            this.showNotification(`Error: ${event.error}`, "error");
          }
          resolve(); // Resolve instead of reject to prevent unhandled promise
        };

        // Start speaking
        speechSynthesis.speak(utterance);
      };

      // Initialize voice setup
      setupVoice();
    } catch (error) {
      console.error("Error creating utterance:", error);
      reject(error);
    }
  }

  async autoStartForPage() {
    try {
      if (
        !this.state.autoRead ||
        !this.state.textReaderActive ||
        this.autoStartExecuted
      ) {
        return;
      }

      if (!("speechSynthesis" in window)) {
        this.state.autoRead = false;
        this.saveSettings();
        this.showNotification(
          "Text-to-speech tidak didukung di perangkat ini",
          "warning"
        );
        return;
      }

      // Wait longer for mobile devices
      const isMobile = window.innerWidth <= 768;
      const waitTime = isMobile ? 2000 : 1000;

      if (speechSynthesis.speaking || speechSynthesis.pending) {
        setTimeout(() => this.autoStartForPage(), waitTime);
        return;
      }

      const pageTitle = document.querySelector("h1.font-medium");
      const loginTitle = document.querySelector("h2.text-cyan-400");

      let shouldStartReading = false;

      if (loginTitle && loginTitle.textContent.includes("LOGIN")) {
        shouldStartReading = true;
      } else if (loginTitle && loginTitle.textContent.includes("REGISTER")) {
        shouldStartReading = true;
      } else if (pageTitle) {
        const title = pageTitle.textContent.trim();
        if (
          title.includes("Dashboard") ||
          title.includes("Ruangan") ||
          title.includes("Pesanan") ||
          title.includes("Riwayat")
        ) {
          shouldStartReading = true;
        }
      }

      if (shouldStartReading) {
        this.autoStartExecuted = true;
        const startDelay = isMobile ? 1000 : 500;
        setTimeout(() => this.startTextReader(), startDelay);
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
      info: "bg-blue-500",
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

  setupInputAccessibility() {
    // Remove existing listeners to prevent duplicates
    this.removeInputListeners();

    // Bind methods to preserve context
    this.boundHandleInputFocus = this.handleInputFocus.bind(this);
    this.boundHandleInputClick = this.handleInputClick.bind(this);
    this.boundHandleInputChange = this.handleInputChange.bind(this);
    this.boundHandleRoomClick = this.handleRoomClick.bind(this);

    // Add focus listeners to all input fields with more comprehensive selectors
    const inputs = document.querySelectorAll(
      'input, textarea, select, button[type="submit"], button[type="button"]'
    );

    inputs.forEach((input, index) => {
      // Add focus event listener
      input.addEventListener("focus", this.boundHandleInputFocus);

      // Add click event listener for better accessibility
      input.addEventListener("click", this.boundHandleInputClick);

      // Add change event listener for form fields
      if (input.tagName === "INPUT" || input.tagName === "SELECT") {
        input.addEventListener("change", this.boundHandleInputChange);
        input.addEventListener("input", this.boundHandleInputChange);
      }

      // Store reference for cleanup
      if (!this.inputListeners) this.inputListeners = [];
      this.inputListeners.push(input);
    });

    // Add click listeners to room cards
    this.setupRoomAccessibility();
  }

  handleInputFocus(event) {
    if (!this.state.textReaderActive) return;

    const input = event.target;
    const label = this.getInputLabel(input);

    console.log("Input focused:", input.name || input.id, "Label:", label);

    if (label) {
      this.speakInputInfo(label, input);
    }
  }

  handleInputClick(event) {
    if (!this.state.textReaderActive) return;

    const input = event.target;
    const label = this.getInputLabel(input);

    console.log("Input clicked:", input.name || input.id, "Label:", label);

    if (label) {
      this.speakInputInfo(label, input);
    }
  }

  handleInputChange(event) {
    if (!this.state.textReaderActive) return;

    const input = event.target;
    const label = this.getInputLabel(input);

    console.log(
      "Input changed:",
      input.name || input.id,
      "Value:",
      input.value,
      "Label:",
      label
    );

    if (label && input.value) {
      this.speakInputSelection(label, input);
    }
  }

  getInputLabel(input) {
    // Try to find label by for attribute
    if (input.id) {
      const label = document.querySelector(`label[for="${input.id}"]`);
      if (label) return label.textContent.trim();
    }

    // Try to find label by parent or sibling
    const parent = input.parentElement;
    if (parent) {
      const label = parent.querySelector("label");
      if (label) return label.textContent.trim();
    }

    // Check for placeholder
    if (input.placeholder) return input.placeholder;

    // Check for aria-label
    if (input.getAttribute("aria-label"))
      return input.getAttribute("aria-label");

    // Check for title attribute
    if (input.title) return input.title;

    // Check for name attribute and format it nicely
    if (input.name) {
      let name = input.name.replace(/([A-Z])/g, " $1").toLowerCase();
      // Capitalize first letter
      name = name.charAt(0).toUpperCase() + name.slice(1);
      return name;
    }

    // For select elements, try to get the first option text
    if (input.tagName === "SELECT" && input.options.length > 0) {
      const firstOption = input.options[0];
      if (firstOption && firstOption.text && !firstOption.disabled) {
        return firstOption.text;
      }
    }

    return null;
  }

  async speakInputInfo(label, input) {
    try {
      // Generate unique identifier for this input
      const inputId = this.getInputIdentifier(input);
      let text = label;

      // Add input type information
      const inputType = input.type || input.tagName.toLowerCase();
      switch (inputType) {
        case "text":
        case "email":
        case "password":
        case "number":
        case "tel":
        case "url":
          text += `, field ${inputType}`;
          break;
        case "textarea":
          text += ", area teks";
          break;
        case "select":
          text += ", pilihan dropdown";
          break;
        case "submit":
          text += ", tombol kirim";
          break;
        case "button":
          text += ", tombol";
          break;
        default:
          text += ", field input";
      }

      // Add required field indication
      if (input.required) {
        text += ", wajib diisi";
      }

      // Check if this is the same input as last time
      if (this.lastSpokenInput === inputId) {
        // Same input, don't repeat the field info
        return;
      }

      // Stop any current speech to prevent overlap
      this.stopTextReader();

      // Wait longer for mobile devices
      const isMobile = window.innerWidth <= 768;
      const waitTime = isMobile ? 200 : 100;
      await new Promise((resolve) => setTimeout(resolve, waitTime));

      // Update tracking
      this.lastSpokenInput = inputId;
      this.lastSpokenText = text;

      // Use the same voice selection logic
      await this.speakText(text);
    } catch (error) {
      console.error("Error speaking input info:", error);
    }
  }

  async speakInputSelection(label, input) {
    try {
      let text = "";
      const inputType = input.type || input.tagName.toLowerCase();

      // Handle different input types
      switch (inputType) {
        case "date":
          const date = new Date(input.value);
          const formattedDate = date.toLocaleDateString("id-ID", {
            weekday: "long",
            year: "numeric",
            month: "long",
            day: "numeric",
          });
          text = `Tanggal dipilih: ${formattedDate}`;
          break;

        case "select":
          const selectedOption = input.options[input.selectedIndex];
          if (selectedOption && selectedOption.text) {
            text = `${label}: ${selectedOption.text}`;
          }
          break;

        case "text":
        case "email":
        case "password":
        case "number":
        case "tel":
        case "url":
          text = `${label}: ${input.value}`;
          break;

        default:
          text = `${label}: ${input.value}`;
      }

      // Stop any current speech
      this.stopTextReader();

      // Wait longer for mobile devices
      const isMobile = window.innerWidth <= 768;
      const waitTime = isMobile ? 200 : 100;
      await new Promise((resolve) => setTimeout(resolve, waitTime));

      await this.speakText(text);
    } catch (error) {
      console.error("Error speaking input selection:", error);
    }
  }

  getInputIdentifier(input) {
    // Create a unique identifier for the input
    const id = input.id || "";
    const name = input.name || "";
    const type = input.type || input.tagName.toLowerCase();
    const value = input.value || "";

    return `${id}-${name}-${type}-${value}`;
  }

  setupRoomAccessibility() {
    // Remove existing room listeners
    this.removeRoomListeners();

    // Add click listeners to room cards
    const roomCards = document.querySelectorAll(".bg-white.shadow-md");

    roomCards.forEach((room) => {
      room.addEventListener("click", this.boundHandleRoomClick);

      // Store reference for cleanup
      if (!this.roomListeners) this.roomListeners = [];
      this.roomListeners.push(room);
    });
  }

  handleRoomClick(event) {
    if (!this.state.textReaderActive) return;

    const room = event.currentTarget;
    const roomInfo = this.getRoomInfo(room);

    if (roomInfo) {
      this.speakRoomInfo(roomInfo);
    }
  }

  getRoomInfo(room) {
    const roomName = room.querySelector("h1.font-medium");
    if (!roomName) return null;

    const info = {
      name: roomName.textContent.trim(),
      facilities: [],
      description: "",
    };

    // Get facilities
    const facilities = room.querySelectorAll(
      "div.flex.flex-col.gap-1.text-xs p"
    );
    if (facilities.length > 0) {
      info.facilities = Array.from(facilities)
        .map((f) => f.textContent.trim())
        .filter((f) => f);
    }

    // Get description
    const description = room.querySelector("p.text-gray-600");
    if (description && description.textContent.trim()) {
      info.description = description.textContent.trim();
    }

    return info;
  }

  async speakRoomInfo(roomInfo) {
    try {
      let text = `Ruangan: ${roomInfo.name}`;

      if (roomInfo.facilities.length > 0) {
        text += `. Fasilitas: ${roomInfo.facilities.join(", ")}`;
      }

      if (roomInfo.description) {
        text += `. Deskripsi: ${roomInfo.description}`;
      }

      // Stop any current speech
      this.stopTextReader();
      await new Promise((resolve) => setTimeout(resolve, 100));

      await this.speakText(text);
    } catch (error) {
      // console.error("Error speaking room info:", error);
    }
  }

  removeRoomListeners() {
    if (this.roomListeners) {
      this.roomListeners.forEach((room) => {
        if (this.boundHandleRoomClick) {
          room.removeEventListener("click", this.boundHandleRoomClick);
        }
      });
      this.roomListeners = [];
    }
  }

  removeInputListeners() {
    if (this.inputListeners) {
      this.inputListeners.forEach((input) => {
        if (this.boundHandleInputFocus) {
          input.removeEventListener("focus", this.boundHandleInputFocus);
        }
        if (this.boundHandleInputClick) {
          input.removeEventListener("click", this.boundHandleInputClick);
        }
        if (this.boundHandleInputChange) {
          input.removeEventListener("change", this.boundHandleInputChange);
          input.removeEventListener("input", this.boundHandleInputChange);
        }
      });
      this.inputListeners = [];
    }
  }

  setupMutationObserver() {
    // Create a mutation observer to watch for new input elements
    this.mutationObserver = new MutationObserver((mutations) => {
      let shouldReSetup = false;

      mutations.forEach((mutation) => {
        if (mutation.type === "childList") {
          mutation.addedNodes.forEach((node) => {
            if (node.nodeType === Node.ELEMENT_NODE) {
              // Check if the added node is an input or contains inputs
              if (
                node.matches &&
                (node.matches(
                  'input, textarea, select, button[type="submit"], button[type="button"]'
                ) ||
                  node.querySelector(
                    'input, textarea, select, button[type="submit"], button[type="button"]'
                  ))
              ) {
                shouldReSetup = true;
              }
            }
          });
        }
      });

      if (shouldReSetup) {
        console.log("New inputs detected, re-setting up accessibility");
        setTimeout(() => this.setupInputAccessibility(), 100);
      }
    });

    // Start observing
    this.mutationObserver.observe(document.body, {
      childList: true,
      subtree: true,
    });
  }

  cleanup() {
    this.stopTextReader();
    this.removeMenuCloseListeners();
    this.removeInputListeners();
    this.removeRoomListeners();

    // Disconnect mutation observer
    if (this.mutationObserver) {
      this.mutationObserver.disconnect();
    }
  }
}

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  console.log("DOM Content Loaded - Initializing Accessibility Manager");
  window.accessibilityManager = new AccessibilityManager();
});

// Also initialize immediately if DOM is already loaded
if (
  document.readyState === "complete" ||
  document.readyState === "interactive"
) {
  console.log(
    "DOM already ready - Initializing Accessibility Manager immediately"
  );
  window.accessibilityManager = new AccessibilityManager();
}

// Cleanup when page unloads
window.addEventListener("beforeunload", function () {
  if (window.accessibilityManager) {
    window.accessibilityManager.cleanup();
  }
});

// Export for global access
window.AccessibilityManager = AccessibilityManager;
