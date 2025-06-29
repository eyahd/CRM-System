# 🎯 ProWin Kampagnenmanagement (SS 2025)

Willkommen zum Kampagnenmanagement-Tool für ProWin, entwickelt im Rahmen des Praxisprojekts SS 2025.  
Dieses System wurde speziell zur automatisierten Durchführung und detaillierten Auswertung von Marketingkampagnen gebaut.

---

## ⚙️ Funktionsübersicht

### 🏠 Einstieg über die Homepage
- Die Anwendung startet auf einer **Home-Seite**, über die sich Mitarbeitende einloggen können.
- Gleichzeitig enthält diese Seite einen **direkten Einstiegspunkt in die Kampagnenplanung**.

### 🔐 Login & Weiterleitung
- Nach erfolgreichem Login gelangen Mitarbeitende automatisch zum **Kampagnenformular**.
- Hier beginnt die konkrete Kampagnenkonfiguration.

---

## ✍️ Phase 1 – Planung der Kampagne (Formular)

Im Formular wählen die Nutzer:
- Kampagnentyp (z. B. Rabattaktion, Event-Einladung)
- Zeitraum
- Zielsegment (z. B. Neukunden, Vielfahrer)

✅ Die Eingaben werden validiert und anschließend in der Datenbank gespeichert.  
➡️ Nach dem Absenden erfolgt die automatische Weiterleitung zur Versandphase.

---

## 📬 Phase 2 – Durchführung: Kampagne starten

Nach dem Absenden startet der **E-Mail-Versand automatisch**.

🔧 Technische Umsetzung:
- Verwendung von **PHPMailer**
- Versand erfolgt **sequenziell**, um Serverlast zu reduzieren & Spamfilter zu umgehen
- Anzeige eines **Live-Trackers** auf der Website:  
  Zeigt in Echtzeit, welcher Kunde die E-Mail gerade erhält
- Nach erfolgreichem Versand erfolgt automatische Weiterleitung zur **Kampagnen-Übersicht**

---

## 📈 Phase 3 – Übersicht & Auswertung

### 👁️ Kampagnen-Übersicht
Alle Kampagnen auf einen Blick:
- Titel, Zeitraum, Typ, Status (geplant, läuft, abgeschlossen)
- Filter- und Suchfunktion

---

### 📊 Click & Convert Dashboard
- Anzahl versendeter & geklickter Mails
- Berechnung der Klickrate je Kampagne
- Export als PDF
- Kampagnenvergleich & Verlauf

---

### 📈 Rental KPIs
Live-Statistiken zum Unternehmen:
- Flottenauslastung 🚗  
- Ø Mietdauer 🕒  
- Tagesumsatz 💰  
- Gesamtanzahl Buchungen 📦  
Visualisierung durch:
- Balkendiagramme nach Städten
- Kreisdiagramme nach Fahrzeugkategorie
- Doughnut-Charts zu laufenden Kampagnen

---

### 🧭 Live Tracker
- Aktualisiert sich **alle 30 Sekunden**
- Zeigt:
  - Echtzeitdaten zu vermieteten Fahrzeugen
  - Umsatz pro Kategorie
  - Ø Mietdauer je Segment

---

### 🗺️ Umsatz Bubble Map
- Interaktive Karte mit dynamischen Bubbles
- Farbe & Größe der Punkte spiegeln Umsatzhöhe je Stadt
- Live-Datenanbindung via REST-API

---

### 💡 Datenbasierte Empfehlungen
- System analysiert vergangene Kampagnen
- Liefert **konkrete Vorschläge** für neue Aktionen:
  - Beste Zielgruppen
  - Effektivste Kampagnentypen
- Direkt-Link zur **vorbefüllten Planung**

---

## 🗃️ Datenbank & Testdaten
- Die Anwendung greift auf eine strukturierte MySQL-Datenbank zu.
- Datenmodell & Testdaten sind dokumentiert in **Notion**.
- Beispiel-Datensätze ermöglichen das sofortige Testen aller Funktionen.

---

## 🛠️ Lokale Installation

### Voraussetzungen
- PHP >= 8.x  
- Composer  
- Xampp
- VS code
- PHP Mailer

### Setup
```bash
git clone https://github.com/DEIN-USERNAME/prowin-kampagnen-tool.git
cd prowin-kampagnen-tool
composer install
