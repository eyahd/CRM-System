<?php
header('Content-Type: application/json');

// Datenbankverbindung
$pdo = new PDO("mysql:host=localhost;dbname=crm_autovermietung", "root", "");

// SQL-Abfrage: Umsatz pro Stadt summieren
$sql = "SELECT ort AS stadt, SUM(umsatz) AS gesamt_umsatz 
        FROM kundendatenbank 
        GROUP BY ort 
        ORDER BY gesamt_umsatz DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$daten = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ausgabe als JSON
echo json_encode($daten);
?>
