# Kundenverwaltungs-Website (CRM-Workspace)

Dies ist eine webbasierte Anwendung zur **Verwaltung und Registrierung von Kunden**, entwickelt mit PHP, HTML, CSS, JavaScript und MySQL. Sie dient als Grundlage für ein einfaches CRM-System und wurde lokal mit **XAMPP** und **phpMyAdmin** umgesetzt. Der E-Mail-Versand wird über **Papercut SMTP** realisiert.

##  Funktionen

- Registrierung neuer Kunden mit E-Mail-Bestätigung
- Login-System für Kunden und Administratoren
- Trennung von Admin- und Kunden-Dashboard
- Passwort-Zurücksetzen via E-Mail
- Kunden können ihre Daten selbst aktualisieren und via E-Mail bestätigen
- Administratoren können Benutzerkonten verwalten und löschen

##  Projektstruktur

```
CRM-Workspace/
├── mails/                        # E.Mail Templates je nach Anwendungsfall
├── js/script.js                  # Funktionen zum Steuern des Formularverhaltens in Abhängigkeit des in der Session befindlichen Anwenders (kunde/admin)
├── js/iban.js                    # Skript zur Validierung der Iban auf ISO 13616 IBAN Registry technical specification
├── add_admin.php                 # Admin manuell hinzufügen
├── admin_dashboard.php           # Admin-Bereich
├── config/mail_config.php        # SMTP Daten
├── benutzer_bearbeiten.php       # Kunden durch Admin bearbeiten/Suchen
├── composer.json / composer.lock # Abhängigkeiten zum automatischen einbinden des PHPMailers
├── db_config.php                 # Datenbankverbindung
├── konto_loeschen*.php           # Konto löschen (inkl. Bestätigung)
├── kunden_dashboard.php          # Kundenbereich
├── kunden_update.php             # Kundendaten aktualisieren
├── login.html / login.php        # Login-Logik. Leitet automatisch auf das zugehörige Dashboard in Abhängigkeit des Anwenders admin/kunde
├── logout.php                    # Logout
├── utils.php                     # Funktion zur Passwortvalidierung
├── mailer.php                    # PHPMailer-Integration
├── passwort_vergessen.php        # Passwort zurücksetzen
├── passwort_aendern.php          # Passwort zurücksetzen
├── passwort_zurueksetzen.php     # Passwort zurücksetzen
├── registrierung.html/.php       # Registrierung
├── startseite.html               # Landing Page
├── styles.css                    # Styles 
├── verify_email.php              # E-Mail-Verifizierung
├── PapercutService/              # Lokaler SMTP-Service 
├── vendor/                       # Initialisierter Composer. Enthält PHPMailer Funktionen für den E-Mail versandt.
└── kunden.sql/                   # Zu importierende Tabelle 'kunden' in Datenbank 'kundenverwaltung' ink. eines Admins. Anmeldedaten befinden sich in der "add_admin.php"

 
```

##  Installation und Einrichtung

### Voraussetzungen müssen nur installiert werden falls der PapercutService und Composer-->vendor/ & composer.json & composer.lock aus dem Stammverzeichnis CRM-Workspace nicht verwendet werden!

- [XAMPP](https://www.apachefriends.org/index.html) mit Apache & MySQL
- [Composer](https://getcomposer.org/)
- [Papercut SMTP](https://github.com/ChangemakerStudios/Papercut-SMTP) für E-Mail-Tests
- Ein Editor wie VS Code (optional, empfohlen) 

### Setup

1. **Projekt entpacken und in das XAMPP-Verzeichnis verschieben**, z. B.:

   ```bash
   C:\xampp\htdocs\CRM-Workspace
   ```

2. **Datenbank einrichten** über [http://localhost/phpmyadmin](http://localhost/phpmyadmin)  
   Erstelle eine neue Datenbank `kunden` und importiere das im Repository enthaltene SQL-Schema "kunden.sql"

3. **Abhängigkeiten installieren**:

   ```bash
   cd CRM-Workspace
   composer install
   ```

4. **Papercut SMTP starten** (aus dem Ordner `PapercutService`)  
   Öffne `Papercut.exe` → es läuft auf `localhost:25` (kein Versand nach außen).

5. **Projekt im Browser starten**:

   ```
   http://localhost/CRM-Workspace/startseite.html
   ```

##  Sicherheit & Hinweise

- Der E-Mail-Versand ist aktuell nur für lokale Tests gedacht.
- Bei Einsatz im Live-Betrieb: SMTP-Login-Daten 'mail_config.php' absichern und HTTPS verwenden.

---

© 2025 – Kundenverwaltungs-Website | Entwickelt mit VS Code
