// Variabel global untuk state aksesibilitas
let textReaderActive = false;
let highContrastActive = false;
let currentFontSize = 100;
let selectedVoice = null;
let voiceRate = 0.7;
let voicePitch = 1.0;

// Fungsi untuk menampilkan notifikasi aksesibilitas
function showAccessibilityNotification(message, type = "info") {
  // Hapus notifikasi yang sudah ada
  const existingNotification = document.querySelector(
    ".accessibility-notification"
  );
  if (existingNotification) {
    existingNotification.remove();
  }

  // Buat notifikasi baru
  const notification = document.createElement("div");
  notification.className = `accessibility-notification fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-white text-sm font-medium transition-all duration-300 transform translate-x-full`;

  // Set warna berdasarkan tipe
  switch (type) {
    case "success":
      notification.className += " bg-green-500";
      break;
    case "error":
      notification.className += " bg-red-500";
      break;
    case "warning":
      notification.className += " bg-yellow-500";
      break;
    default:
      notification.className += " bg-blue-500";
  }

  notification.textContent = message;
  document.body.appendChild(notification);

  // Animasi masuk
  setTimeout(() => {
    notification.classList.remove("translate-x-full");
  }, 100);

  // Auto hide setelah 3 detik
  setTimeout(() => {
    notification.classList.add("translate-x-full");
    setTimeout(() => {
      if (notification.parentNode) {
        notification.remove();
      }
    }, 300);
  }, 3000);
}

// Toggle menu aksesibilitas
function toggleAccessibility() {
  const menu = document.getElementById("accessibilityMenu");
  if (menu) {
    menu.classList.toggle("hidden");
  }
}

// Toggle pembaca teks otomatis
function toggleTextReader() {
  textReaderActive = !textReaderActive;

  if (textReaderActive) {
    localStorage.setItem("textReaderActive", "true");
    showAccessibilityNotification("Pembaca teks diaktifkan", "success");
    startTextReader();
  } else {
    localStorage.setItem("textReaderActive", "false");
    showAccessibilityNotification("Pembaca teks dinonaktifkan", "info");
    stopTextReader();
  }

  updateTextReaderUI();
}

// Update UI berdasarkan state
function updateTextReaderUI() {
  const btn = document.getElementById("textReaderBtn");
  const toggle = document.getElementById("textReaderToggle");

  if (btn && toggle) {
    if (textReaderActive) {
      btn.classList.remove("bg-gray-300");
      btn.classList.add("bg-blue-500");
      toggle.classList.add("translate-x-6");
    } else {
      btn.classList.remove("bg-blue-500");
      btn.classList.add("bg-gray-300");
      toggle.classList.remove("translate-x-6");
    }
  }
}

// Toggle kontras tinggi
function toggleHighContrast() {
  highContrastActive = !highContrastActive;
  const btn = document.getElementById("contrastBtn");
  const toggle = document.getElementById("contrastToggle");

  if (btn && toggle) {
    if (highContrastActive) {
      btn.classList.remove("bg-gray-300");
      btn.classList.add("bg-blue-500");
      toggle.classList.add("translate-x-6");
      document.body.classList.add("high-contrast");
      localStorage.setItem("highContrast", "true");
    } else {
      btn.classList.remove("bg-blue-500");
      btn.classList.add("bg-gray-300");
      toggle.classList.remove("translate-x-6");
      document.body.classList.remove("high-contrast");
      localStorage.setItem("highContrast", "false");
    }
  }
}

// Ubah ukuran font
function changeFontSize(action) {
  const body = document.body;

  switch (action) {
    case "increase":
      currentFontSize += 10;
      break;
    case "decrease":
      currentFontSize -= 10;
      break;
    case "reset":
      currentFontSize = 100;
      break;
  }

  // Batasi ukuran font antara 80% dan 200%
  currentFontSize = Math.max(80, Math.min(200, currentFontSize));

  body.style.fontSize = currentFontSize + "%";
  localStorage.setItem("fontSize", currentFontSize);
}

// Fungsi untuk memilih suara yang lebih jelas
function selectBestVoice() {
  if (!("speechSynthesis" in window)) return null;

  // Tunggu sampai voices tersedia
  return new Promise((resolve) => {
    let voices = speechSynthesis.getVoices();

    if (voices.length > 0) {
      resolve(findBestVoice(voices));
    } else {
      speechSynthesis.onvoiceschanged = () => {
        voices = speechSynthesis.getVoices();
        resolve(findBestVoice(voices));
      };
    }
  });
}

// Fungsi untuk mencari suara terbaik
function findBestVoice(voices) {
  // Prioritas: Indonesia > Wanita > Pria > Default
  let bestVoice = null;

  // Cari suara Indonesia
  bestVoice = voices.find(
    (voice) => voice.lang.includes("id") || voice.lang.includes("ID")
  );

  if (!bestVoice) {
    // Cari suara wanita (biasanya lebih jelas)
    bestVoice = voices.find(
      (voice) =>
        voice.name.toLowerCase().includes("female") ||
        voice.name.toLowerCase().includes("wanita") ||
        voice.name.toLowerCase().includes("sari") ||
        voice.name.toLowerCase().includes("rina")
    );
  }

  if (!bestVoice) {
    // Cari suara dengan kualitas tinggi
    bestVoice = voices.find(
      (voice) =>
        voice.name.toLowerCase().includes("premium") ||
        voice.name.toLowerCase().includes("enhanced") ||
        voice.name.toLowerCase().includes("natural")
    );
  }

  if (!bestVoice) {
    // Fallback ke suara default
    bestVoice = voices[0];
  }

  return bestVoice;
}

// Fungsi untuk mengatur kecepatan suara
function changeVoiceRate(action) {
  switch (action) {
    case "slower":
      voiceRate = Math.max(0.3, voiceRate - 0.1);
      break;
    case "faster":
      voiceRate = Math.min(1.0, voiceRate + 0.1);
      break;
    case "reset":
      voiceRate = 0.7;
      break;
  }

  localStorage.setItem("voiceRate", voiceRate.toString());
  showAccessibilityNotification(
    `Kecepatan suara: ${(voiceRate * 100).toFixed(0)}%`,
    "info"
  );
}

// Fungsi untuk mengatur pitch suara
function changeVoicePitch(action) {
  switch (action) {
    case "lower":
      voicePitch = Math.max(0.5, voicePitch - 0.1);
      break;
    case "higher":
      voicePitch = Math.min(2.0, voicePitch + 0.1);
      break;
    case "reset":
      voicePitch = 1.0;
      break;
  }

  localStorage.setItem("voicePitch", voicePitch.toString());
  showAccessibilityNotification(
    `Tinggi suara: ${(voicePitch * 100).toFixed(0)}%`,
    "info"
  );
}

// Pembaca teks otomatis yang fokus pada isi halaman
async function startTextReader() {
  if (!("speechSynthesis" in window)) {
    showAccessibilityNotification(
      "Pembaca teks tidak didukung di browser ini",
      "error"
    );
    return;
  }

  try {
    // Hentikan pembacaan sebelumnya
    speechSynthesis.cancel();

    // Pilih suara terbaik jika belum dipilih
    if (!selectedVoice) {
      selectedVoice = await selectBestVoice();
      if (selectedVoice) {
        console.log(
          "Suara yang dipilih:",
          selectedVoice.name,
          selectedVoice.lang
        );
        showAccessibilityNotification(
          `Suara: ${selectedVoice.name}`,
          "success"
        );
      }
    }

    // Ambil konten utama halaman
    let text = "";

    // Fokus pada konten utama
    const mainContent =
      document.querySelector("main") || document.querySelector("#main-content");
    if (mainContent) {
      text = mainContent.innerText || mainContent.textContent;
    } else {
      // Fallback ke body jika tidak ada main
      text = document.body.innerText || document.body.textContent;
    }

    // Bersihkan teks
    text = text.replace(/\s+/g, " ").trim();

    // Jika teks kosong, gunakan judul halaman
    if (!text || text.length < 10) {
      text = document.title || "Halaman web";
    }

    // Bagi teks menjadi bagian-bagian yang lebih pendek untuk kejelasan
    const textChunks = splitTextIntoChunks(text);

    console.log("Jumlah bagian teks:", textChunks.length);
    console.log("Teks yang akan dibaca:", textChunks);

    // Mulai membaca bagian pertama
    readTextChunks(textChunks, 0);
  } catch (error) {
    showAccessibilityNotification("Terjadi error saat membaca teks", "error");
  }
}

// Fungsi untuk membagi teks menjadi chunk yang lebih pendek
function splitTextIntoChunks(text) {
  // Bagi berdasarkan kalimat
  const sentences = text.split(/[.!?]+/).filter((s) => s.trim().length > 0);
  const chunks = [];

  sentences.forEach((sentence) => {
    const trimmed = sentence.trim();
    if (trimmed.length > 60) {
      // Bagi kalimat panjang menjadi beberapa bagian
      const words = trimmed.split(" ");
      let currentChunk = "";

      words.forEach((word) => {
        if ((currentChunk + " " + word).length > 50) {
          if (currentChunk) {
            chunks.push(currentChunk.trim() + ".");
            currentChunk = word;
          } else {
            currentChunk = word;
          }
        } else {
          currentChunk += (currentChunk ? " " : "") + word;
        }
      });

      if (currentChunk) {
        chunks.push(currentChunk.trim() + ".");
      }
    } else {
      chunks.push(trimmed + ".");
    }
  });

  return chunks;
}

// Fungsi untuk membaca teks chunk per chunk
function readTextChunks(chunks, index) {
  if (index >= chunks.length) {
    showAccessibilityNotification("Pembacaan selesai", "info");
    return;
  }

  const currentChunk = chunks[index];
  console.log(`Membaca bagian ${index + 1}/${chunks.length}:`, currentChunk);

  // Buat utterance dengan pengaturan suara yang lebih jelas
  const utterance = new SpeechSynthesisUtterance(currentChunk);

  // Set suara yang dipilih
  if (selectedVoice) {
    utterance.voice = selectedVoice;
  }

  // Set parameter suara untuk kejelasan
  utterance.lang = "id-ID";
  utterance.rate = voiceRate;
  utterance.pitch = voicePitch;
  utterance.volume = 1.0;

  // Event handlers
  utterance.onstart = function () {
    if (index === 0) {
      showAccessibilityNotification("Membaca teks halaman...", "success");
    } else {
      showAccessibilityNotification(
        `Bagian ${index + 1} dari ${chunks.length}`,
        "info"
      );
    }
  };

  utterance.onend = function () {
    // Lanjut ke bagian berikutnya dengan jeda
    setTimeout(() => {
      readTextChunks(chunks, index + 1);
    }, 400);
  };

  utterance.onerror = function (event) {
    console.log("Error pembaca teks:", event.error);
    if (event.error === "not-allowed") {
      showAccessibilityNotification(
        "Browser memblokir autoplay. Klik 'Baca Ulang Sekarang' untuk memulai.",
        "warning"
      );
      textReaderActive = false;
      localStorage.setItem("textReaderActive", "false");
      updateTextReaderUI();
    } else {
      showAccessibilityNotification(`Error: ${event.error}`, "error");
    }
  };

  // Mulai membaca
  speechSynthesis.speak(utterance);
}

// Hentikan pembaca teks
function stopTextReader() {
  if ("speechSynthesis" in window) {
    speechSynthesis.cancel();
  }
  textReaderActive = false;
  localStorage.setItem("textReaderActive", "false");
}

// Load pengaturan saat halaman dimuat
document.addEventListener("DOMContentLoaded", function () {
  // Load state pembaca teks
  const savedTextReader = localStorage.getItem("textReaderActive");
  if (savedTextReader === "true") {
    textReaderActive = true;
  }

  // Load ukuran font
  const savedFontSize = localStorage.getItem("fontSize");
  if (savedFontSize) {
    currentFontSize = parseInt(savedFontSize);
    document.body.style.fontSize = currentFontSize + "%";
  }

  // Load kontras tinggi
  const savedContrast = localStorage.getItem("highContrast");
  if (savedContrast === "true") {
    highContrastActive = true;
    document.body.classList.add("high-contrast");
  }

  // Load pengaturan suara
  const savedVoiceRate = localStorage.getItem("voiceRate");
  if (savedVoiceRate) {
    voiceRate = parseFloat(savedVoiceRate);
  }

  const savedVoicePitch = localStorage.getItem("voicePitch");
  if (savedVoicePitch) {
    voicePitch = parseFloat(savedVoicePitch);
  }

  // Update UI dengan retry mechanism
  let retryCount = 0;
  const maxRetries = 10;

  function tryUpdateUI() {
    const btn = document.getElementById("textReaderBtn");
    const toggle = document.getElementById("textReaderToggle");

    if (btn && toggle) {
      updateTextReaderUI();

      // Update UI kontras tinggi
      const contrastBtn = document.getElementById("contrastBtn");
      const contrastToggle = document.getElementById("contrastToggle");
      if (contrastBtn && contrastToggle && highContrastActive) {
        contrastBtn.classList.remove("bg-gray-300");
        contrastBtn.classList.add("bg-blue-500");
        contrastToggle.classList.add("translate-x-6");
      }

      // Auto-start pembaca teks jika state aktif
      if (textReaderActive) {
        console.log("Auto-starting text reader...");
        setTimeout(() => {
          startTextReader();
        }, 200);
      }
    } else if (retryCount < maxRetries) {
      retryCount++;
      setTimeout(tryUpdateUI, 100);
    }
  }

  setTimeout(tryUpdateUI, 100);

  // Cancel speech synthesis saat halaman dimuat
  if ("speechSynthesis" in window) {
    speechSynthesis.cancel();
  }
});
