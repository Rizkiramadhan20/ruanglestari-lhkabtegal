// Simple Accessibility System
class Accessibility {
  constructor() {
    this.isActive = false;
    this.fontSize = 100;
    this.highContrast = false;
    this.init();
  }

  init() {
    this.loadSettings();
    this.createMenu();
  }

  loadSettings() {
    try {
      const saved = localStorage.getItem("accessibility");
      if (saved) {
        const settings = JSON.parse(saved);
        this.isActive = settings.isActive || false;
        this.fontSize = settings.fontSize || 100;
        this.highContrast = settings.highContrast || false;
      }
    } catch (error) {}
  }

  saveSettings() {
    try {
      localStorage.setItem("accessibility", JSON.stringify({
        isActive: this.isActive,
        fontSize: this.fontSize,
        highContrast: this.highContrast
      }));
    } catch (error) {}
  }

  createMenu() {
    const menuHTML = `
      <button id="accessBtn" class="fixed bottom-4 right-4 z-50 bg-blue-500 text-white p-3 rounded-full shadow-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
        </svg>
      </button>
      
      <div id="accessMenu" class="fixed bottom-20 right-4 z-50 bg-white rounded-lg shadow-xl p-4 w-80 hidden">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold">Aksesibilitas</h3>
          <button id="closeMenu" class="text-gray-500">✕</button>
        </div>
        
        <div class="space-y-4">
          <div>
            <label class="flex items-center">
              <input type="checkbox" id="textReaderToggle" class="mr-2">
              Pembaca Teks Otomatis
            </label>
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-2">Ukuran Font</label>
            <div class="flex gap-2">
              <button id="decreaseFont" class="flex-1 bg-gray-100 px-3 py-2 rounded">A-</button>
              <button id="resetFont" class="flex-1 bg-gray-100 px-3 py-2 rounded">Reset</button>
              <button id="increaseFont" class="flex-1 bg-gray-100 px-3 py-2 rounded">A+</button>
            </div>
          </div>
          
          <div>
            <label class="flex items-center">
              <input type="checkbox" id="contrastToggle" class="mr-2">
              Kontras Tinggi
            </label>
          </div>
          
          <button id="readContent" class="w-full bg-blue-500 text-white px-4 py-2 rounded">
            Baca Konten
          </button>
        </div>
      </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', menuHTML);
    this.setupEvents();
  }

  setupEvents() {
    document.getElementById('accessBtn').onclick = () => this.toggleMenu();
    document.getElementById('closeMenu').onclick = () => this.toggleMenu();
    document.getElementById('textReaderToggle').onclick = () => this.toggleTextReader();
    document.getElementById('contrastToggle').onclick = () => this.toggleContrast();
    document.getElementById('decreaseFont').onclick = () => this.changeFontSize('decrease');
    document.getElementById('resetFont').onclick = () => this.changeFontSize('reset');
    document.getElementById('increaseFont').onclick = () => this.changeFontSize('increase');
    document.getElementById('readContent').onclick = () => this.readPageContent();
    
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') this.hideMenu();
    });
  }

  toggleMenu() {
    document.getElementById('accessMenu').classList.toggle('hidden');
  }

  hideMenu() {
    document.getElementById('accessMenu').classList.add('hidden');
  }

  toggleTextReader() {
    this.isActive = !this.isActive;
    document.getElementById('textReaderToggle').checked = this.isActive;
    this.saveSettings();
    
    if (this.isActive) {
      this.showNotification('Pembaca teks diaktifkan');
      setTimeout(() => this.readPageContent(), 1000);
    } else {
      this.showNotification('Pembaca teks dinonaktifkan');
      this.stopReading();
    }
  }

  toggleContrast() {
    this.highContrast = !this.highContrast;
    document.getElementById('contrastToggle').checked = this.highContrast;
    
    if (this.highContrast) {
      document.body.classList.add('high-contrast');
    } else {
      document.body.classList.remove('high-contrast');
    }
    
    this.saveSettings();
    this.showNotification(`Kontras tinggi ${this.highContrast ? 'diaktifkan' : 'dinonaktifkan'}`);
  }

  changeFontSize(action) {
    switch (action) {
      case 'increase': this.fontSize = Math.min(200, this.fontSize + 10); break;
      case 'decrease': this.fontSize = Math.max(80, this.fontSize - 10); break;
      case 'reset': this.fontSize = 100; break;
    }
    
    document.body.style.fontSize = this.fontSize + '%';
    this.saveSettings();
    this.showNotification(`Ukuran font: ${this.fontSize}%`);
  }

  getPageContent() {
    const content = [];
    
    const title = document.querySelector('h1, h2') || document.title;
    if (title) content.push(title.textContent || title);
    
    const cards = document.querySelectorAll('.bg-white.shadow-md');
    if (cards.length > 0) {
      content.push(`Terdapat ${cards.length} ruangan yang tersedia`);
      cards.forEach((card, index) => {
        const name = card.querySelector('h1, h2, h3');
        if (name) content.push(`Ruangan ${index + 1}: ${name.textContent.trim()}`);
      });
    }
    
    return content.join('. ') || 'Tidak ada konten yang dapat dibaca';
  }

  readPageContent() {
    if (!('speechSynthesis' in window)) {
      this.showNotification('Pembaca teks tidak didukung', 'error');
      return;
    }

    this.stopReading();
    const content = this.getPageContent();
    
    const utterance = new SpeechSynthesisUtterance(content);
    utterance.lang = 'id-ID';
    utterance.rate = 0.8;
    
    utterance.onstart = () => this.showNotification('Sedang membaca...');
    utterance.onend = () => this.showNotification('Pembacaan selesai');
    
    speechSynthesis.speak(utterance);
  }

  stopReading() {
    if ('speechSynthesis' in window) {
      speechSynthesis.cancel();
    }
  }

  showNotification(message, type = 'info') {
    const existing = document.querySelector('.accessibility-notification');
    if (existing) existing.remove();

    const notification = document.createElement('div');
    notification.className = `accessibility-notification fixed top-4 right-4 z-50 px-4 py-2 rounded text-white text-sm`;
    
    const colors = { success: 'bg-green-500', error: 'bg-red-500', info: 'bg-blue-500' };
    notification.classList.add(colors[type] || colors.info);
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
      if (notification.parentNode) notification.remove();
    }, 3000);
  }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
  window.accessibility = new Accessibility();
});

// Add CSS
const style = document.createElement('style');
style.textContent = `
  .high-contrast {
    filter: contrast(150%) brightness(110%);
  }
  .high-contrast .bg-white {
    background-color: #000 !important;
    color: #fff !important;
  }
`;
document.head.appendChild(style);