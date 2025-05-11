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

// Passwort-Hash laden
$stmt = $pdo->prepare("SELECT passwort_hash, vorname, email FROM kunden WHERE id = :id");
$stmt->execute([':id' => $id]);
$kunde = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kunde || !password_verify($pass, $kunde['passwort_hash'])) {
  die("Passwort falsch. <a href='kunden_dashboard.php'>Zurück</a>");
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

echo "Ihr Konto wurde erfolgreich gelöscht. <a href='startseite.html'>Zur Startseite</a>";
exit;
