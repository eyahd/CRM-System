<?php
require_once 'db_config.php';
session_start();

$pdo = Database::getInstance()->getConnection();

$email = $_POST['email'] ?? '';
$passwort = $_POST['passwort'] ?? '';

// Basisvalidierung
if (empty($email) || empty($passwort)) {
    die("Bitte E-Mail und Passwort eingeben.");
}

// Nutzer aus der Datenbank laden
$sql = "SELECT id, vorname, rolle, passwort_hash, email_verification_token 
        FROM kunden 
        WHERE email = :email";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':email', $email);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Benutzer nicht gefunden oder Passwort falsch.");
}

// Passwort prüfen
if (!password_verify($passwort, $user['passwort_hash'])) {
    die("Benutzer nicht gefunden oder Passwort falsch.");
}

// Falls noch nicht verifiziert
if (!empty($user['email_verification_token'])) {
    die("Bitte bestätigen Sie zuerst Ihre E-Mail-Adresse.");
}

// Session starten
$_SESSION['user_id'] = $user['id'];
$_SESSION['vorname'] = $user['vorname'];
$_SESSION['rolle'] = $user['rolle'];
$_SESSION['email'] = $user['email'];


// Weiterleitung je nach Rolle
if ($user['rolle'] === 'admin') {
    header("Location: admin_dashboard.php"); // Placeholder
    exit;
} elseif ($user['rolle'] === 'kunde') {
    header("Location: kunden_dashboard.php"); // z. B. für Kundendaten ändern
    exit;
} else {
    die("Unbekannte Benutzerrolle.");
}
