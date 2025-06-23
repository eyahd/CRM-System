<?php
session_start();
require_once 'db_config.php';
require_once 'mailer.php';

if (!isset($_SESSION['user_id'])) {
    die("Nicht eingeloggt.");
}

$pdo = Database::getInstance()->getConnection();
$id = $_SESSION['user_id'];
$pass = $_POST['passwort'] ?? '';

// Kundendaten inkl. Passwort-Hash und Rechnungsstatus laden
$stmt = $pdo->prepare("SELECT passwort_hash, vorname, email, offene_rechnungen FROM kunden WHERE id = :id");
$stmt->execute([':id' => $id]);
$kunde = $stmt->fetch(PDO::FETCH_ASSOC);

// Überprüfung auf Existenz & Passwort
if (!$kunde || !password_verify($pass, $kunde['passwort_hash'])) {
    header("Location: konto_loeschen.php?status=passwort_falsch");
    exit;
}

// Prüfung auf offene Rechnungen
if (!empty($kunde['offene_rechnungen'])) {
    header("Location: konto_loeschen.php?status=offene_rechnung");
    exit;
}

// Konto löschen
$stmt = $pdo->prepare("DELETE FROM kunden WHERE id = :id");
$stmt->execute([':id' => $id]);

// Session beenden
session_destroy();

// Bestätigungsmail senden
sendeEmail(
    typ: 'konto_geloescht',
    empfaenger_email: $kunde['email'],
    empfaenger_name: $kunde['vorname'],
    daten: ['vorname' => $kunde['vorname']]
);

header("Location: startseite.html?status=konto_geloescht");
exit;
