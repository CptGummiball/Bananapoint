// public/app.js
let deferredPrompt = null;

// Hilfsfunktionen
const basePath = (() => {
  // Pfad des aktuellen Verzeichnisses, z. B. /companies/acme/public/
  const url = new URL('./', window.location.href);
  return url.pathname.endsWith('/') ? url.pathname : url.pathname + '/';
})();

function asset(path) {
  // Baut eine unterpfad-sichere URL ohne führenden Slash
  return basePath + path.replace(/^\/+/, '');
}

// PWA-Install Prompt abfangen
window.addEventListener('beforeinstallprompt', (e) => {
  e.preventDefault();
  deferredPrompt = e;
  const btn = document.getElementById('btn-install');
  if (btn) {
    btn.style.display = 'inline-flex';
    btn.disabled = false;
  }
});

// Installationsstatus beobachten
window.addEventListener('appinstalled', () => {
  deferredPrompt = null;
  const btn = document.getElementById('btn-install');
  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Installiert';
  }
});

document.addEventListener('DOMContentLoaded', () => {
  // PWA installieren (Button)
  const btnInstall = document.getElementById('btn-install');
  if (btnInstall) {
    // Erstmal verstecken; wird durch beforeinstallprompt sichtbar gemacht
    btnInstall.style.display = 'none';
    btnInstall.addEventListener('click', async () => {
      if (!deferredPrompt) {
        alert('Installation derzeit nicht verfügbar.');
        return;
      }
      deferredPrompt.prompt();
      const { outcome } = await deferredPrompt.userChoice;
      deferredPrompt = null;
      btnInstall.disabled = true;
      if (outcome === 'accepted') {
        console.log('PWA installiert');
      } else {
        console.log('PWA-Installation abgelehnt');
      }
    });
  }

  // Desktop-Verknüpfung (.url) erzeugen
  const btnShortcut = document.getElementById('btn-shortcut');
  if (btnShortcut) {
    btnShortcut.addEventListener('click', () => {
      const url = window.location.origin + asset('index.php?route=dashboard');
      const iconUrl = window.location.origin + asset('assets/icon-192.png');
      const content =
        `[InternetShortcut]\nURL=${url}\nIconFile=${iconUrl}\nIconIndex=0\n`;
      const blob = new Blob([content], { type: 'application/octet-stream' });
      const a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = 'Bananapoint.url';
      document.body.appendChild(a);
      a.click();
      URL.revokeObjectURL(a.href);
      a.remove();
    });
  }

  // Service Worker nur in sicherem Kontext registrieren (HTTPS oder localhost)
  const isLocalhost = ['localhost', '127.0.0.1'].includes(location.hostname);
  if (('serviceWorker' in navigator) && (window.isSecureContext || isLocalhost) && location.protocol === 'https:') {
    const swUrl = asset('sw.js');
    const scope = basePath; // z.B. "/", oder "/kundeA/"
    navigator.serviceWorker.register(swUrl, { scope })
      .catch(console.error);
  }
});

