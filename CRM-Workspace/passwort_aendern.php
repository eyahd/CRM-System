<?php
session_start();
require_once 'db_config.php';
require_once 'mailer.php';
require_once 'utils.php';

if (!isset($_SESSION['user_id'])) {
    die("Nicht angemeldet.");
}

$pdo = Database::getInstance()->getConnection();

$kundeId = $_SESSION['user_id'];
$altesPasswort = $_POST['altes_passwort'] ?? '';
$neuesPasswort = $_POST['passwort'] ?? '';
$neuesPasswort2 = $_POST['passwort2'] ?? '';

// 2te Validierung
if (empty($altesPasswort) || empty($neuesPasswort) || empty($neuesPasswort2)) {
    die("Bitte alle Felder ausfüllen.");
}

$fehler = validierePasswoerter(pw1: $neuesPasswort, pw2: $neuesPasswort2);

if ($fehler !== null) {
    // Fehler gefunden – dem Nutzer anzeigen und Skript beenden
    echo '<p style="color:red;">' . htmlspecialchars($fehler) . '</p>';
    exit;
}

// Altes Passwort prüfen
$sql = "SELECT vorname, email, passwort_hash FROM kunden WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $kundeId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($altesPasswort, $user['passwort_hash'])) {
    header("Location: kunden_dashboard.php?status=passwort_falsch");
    exit;
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
    daten: ['vorname' => $user['vorname']]
);

// Weiterleitung mit Erfolgsmeldung
header("Location: kunden_dashboard.php?status=passwort_geaendert");
exit;

