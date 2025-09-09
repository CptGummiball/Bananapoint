Bananapoint (Dienstplan) – PHP 8.2 + MySQL 8

SCHNELLSTART
1) Erstellen Sie eine MySQL-Datenbank und einen Benutzer.
2) Importieren Sie schema.sql.
3) Kopieren Sie .env.example zu .env (eine Ebene über /public) und tragen Sie DB-Zugangsdaten ein.
4) Stellen Sie sicher, dass Ihr Webserver den Ordner /public als Document Root verwendet.
5) Erstellen Sie den ersten Admin:
   php scripts/init_admin.php admin@example.com "Admin" "SicheresPasswort"
   oder über init_admin_web.php (Dannach die php Datei löschen!)
6) Rufen Sie die Seite auf: /index.php?route=login

SICHERHEIT
- DB-Zugangsdaten in .env außerhalb von /public lagern.
- Passwörter: Passwort-Hashing mit password_hash()/password_verify().
- CSRF-Schutz für alle POST-Formulare.
- Prepared Statements (PDO) für SQL.
- Session-Cookies: HttpOnly, SameSite=Lax, Secure (sofern HTTPS).

FUNKTIONEN
- Rollen: employee (Mitarbeiter), manager (Berechtigt), admin.
- Mitarbeiter sehen nur eigene Dienste/Fehlzeiten.
- Manager/Admin: Dienste/Fehlzeiten für Mitarbeiter anlegen/löschen,
  Mitarbeiter und Tätigkeiten verwalten.
- Dienst kopieren über Zeitraum (Wochentage wählbar).
- Fehlzeiten: Urlaub, Krankheit, Sonderurlaub, Sonstiges.
- Tätigkeiten mit Icon (lokales png) in separatem Menü.
- Einfache Ansicht + Kalenderansicht.
- PWA: "Als App installieren" (mobil/desktop), Service Worker, Manifest.
- Desktop: Button erzeugt .url-Datei, die als Verknüpfung auf den Desktop gelegt werden kann.

HINWEISE
- Es werden keine externen CDNs verwendet; alles lokal hostbar.
- Für Nginx/Apache: richten Sie /public als Root ein, um src/ zu schützen.
- Für HTTPS (empfohlen) Zertifikate einrichten, damit PWA-Install ohne Warnungen funktioniert.
