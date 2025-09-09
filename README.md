![bananapointlogo](/project/public/assets/icon-192.png)

# Bananapoint

Bananapoint ist eine webbasierte Progressive Web App zur Verwaltung von Schichten, Nutzern und Lageraktivitäten.  
Die Anwendung bietet eine einfache Benutzerverwaltung, Dashboard-Ansichten und ist als PWA offlinefähig.

## Features

- 📦 Verwaltung von Aktivitäten (z. B. Inventur, Kommissionierung, Transport)
- 👥 Benutzer- und Rollenverwaltung
- 🗓 Schichtplanung und -übersicht
- 📊 Dashboard mit Auswertungen
- 🌐 Progressive Web App (PWA) mit Offline-Unterstützung
- 🔒 Login- und Authentifizierungssystem

## Installation

1. Repository klonen oder herunterladen:
   ```bash
   git clone https://github.com/dein-user/bananapoint.git
   cd bananapoint
   ```

2. Abhängigkeiten und Umgebung einrichten:
   - `.env.example` kopieren nach `.env` und konfigurieren
   - Datenbank mit `schema.sql` importieren

3. Webserver einrichten (z. B. Apache/Nginx, DocumentRoot auf `public/` zeigen lassen).

4. Admin-User initialisieren:
   ```bash
   php scripts/init_admin.php
   ```

## Verzeichnisstruktur

```
├── public/          # Öffentliche Dateien (Index, Assets, Landingpage)
├── src/             # Business-Logik (Auth, DB, Config, Helpers)
├── templates/       # HTML/PHP-Templates (Dashboard, Login, Admin-Bereich)
├── scripts/         # Setup-Skripte
├── schema.sql       # Datenbankschema
└── .env.example     # Beispiel-Umgebungsvariablen
```

## Voraussetzungen

- PHP >= 7.4
- MySQL oder MariaDB
- Apache/Nginx mit mod_rewrite
- Composer (optional, falls erweitert)

## Nutzung

- Weboberfläche im Browser öffnen
- Als PWA installierbar (Offline-Unterstützung aktiviert)

## Lizenz

Dieses Projekt steht unter der [MIT-Lizenz](LICENSE).
