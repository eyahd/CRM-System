<?php
header('Content-Type: application/json');
$mysqli = new mysqli("localhost", "root", "", "crm_autovermietung"); // change db name

if ($mysqli->connect_errno) {
    echo json_encode(["error" => "DB-Verbindung fehlgeschlagen."]);
    exit();
}

// 1. Gesamtumsatz heute
$umsatz_stmt = $mysqli->prepare("SELECT SUM(gesamtpreis) AS umsatz FROM mietvertraege WHERE startdatum = CURDATE()");
$umsatz_stmt->execute();
$umsatz_result = $umsatz_stmt->get_result()->fetch_assoc();
$gesamt_umsatz_heute = $umsatz_result['umsatz'] ?? 0;

// 2. Aktive Vermietungen
$active_stmt = $mysqli->prepare("SELECT COUNT(*) AS aktive FROM mietvertraege WHERE CURDATE() BETWEEN startdatum AND enddatum");
$active_stmt->execute();
$active_result = $active_stmt->get_result()->fetch_assoc();
$aktive_vermietungen = $active_result['aktive'] ?? 0;

// 3. Beliebteste Stadt
$stadt_stmt = $mysqli->prepare("SELECT stadt, COUNT(*) AS anzahl FROM mietvertraege GROUP BY stadt ORDER BY anzahl DESC LIMIT 1");
$stadt_stmt->execute();
$stadt_result = $stadt_stmt->get_result()->fetch_assoc();
$beliebteste_stadt = $stadt_result['stadt'] ?? null;

// 4. Beliebteste Fahrzeugkategorie
$kategorie_stmt = $mysqli->prepare("SELECT fahrzeugkategorie, COUNT(*) AS anzahl FROM mietvertraege GROUP BY fahrzeugkategorie ORDER BY anzahl DESC LIMIT 1");
$kategorie_stmt->execute();
$kategorie_result = $kategorie_stmt->get_result()->fetch_assoc();
$beliebteste_kategorie = $kategorie_result['fahrzeugkategorie'] ?? null;

// 5. Anomalien (fehlende Daten)
$anomalien_result = $mysqli->query("SELECT kundenID, name FROM mietvertraege WHERE startdatum = '0000-00-00' OR enddatum = '0000-00-00'");
$anomalien = [];
while ($row = $anomalien_result->fetch_assoc()) {
    $anomalien[] = [
        "kundenID" => $row['kundenID'],
        "name" => $row['name'],
        "grund" => "Fehlendes Datum"
    ];
}

echo json_encode([
    "gesamt_umsatz_heute" => (float)$gesamt_umsatz_heute,
    "aktive_vermietungen" => (int)$aktive_vermietungen,
    "beliebteste_stadt" => $beliebteste_stadt,
    "beliebteste_kategorie" => $beliebteste_kategorie,
    "anomalien" => $anomalien
]);
