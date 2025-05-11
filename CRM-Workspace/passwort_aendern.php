<?php
session_start();
require_once 'db_config.php';
require_once 'mailer.php';

if (!isset($_SESSION['user_id'])) {
    die("Nicht angemeldet.");
}

$pdo = Database::getInstance()->getConnection();

$kundeId = $_SESSION['user_id'];
$altesPasswort = $_POST['altes_passwort'] ?? '';
$neuesPasswort = $_POST['neues_passwort'] ?? '';
$neuesPasswort2 = $_POST['neues_passwort2'] ?? '';

// Validierung
if (empty($altesPasswort) || empty($neuesPasswort) || empty($neuesPasswort2)) {
    die("Bitte alle Felder ausfüllen.");
}

if ($neuesPasswort !== $neuesPasswort2) {
    die("Die neuen Passwörter stimmen nicht überein.");
}

if (strlen($neuesPasswort) < 8) {
    die("Das neue Passwort muss mindestens 8 Zeichen lang sein.");
}

// Altes Passwort prüfen
$sql = "SELECT vorname, email, passwort_hash FROM kunden WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $kundeId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($altesPasswort, $user['passwort_hash'])) {
    die("Das aktuelle Passwort ist falsch.");
}

// Neues Passwort speichern
$neuerHash = password_hash($neuesPasswort, PASSWORD_DEFAULT);
$sql = "UPDATE kunden SET passwort_hash = :hash WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':hash' => $neuerHash,
    ':id' => $kundeId
]);

// Bestätigung per E-Mail
sendeEmail(
    typ: 'passwort_update',
    empfaenger_email: $user['email'],
    empfaenger_name: $user['vorname'],
    daten: []
);

// Weiterleitung mit Erfolgsmeldung
header("Location: kunden_dashboard.html?update=passwort_geaendert");
exit;
