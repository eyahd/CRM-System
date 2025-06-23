<?php
require_once 'db_config.php';
session_start();

header('Content-Type: application/json');

$pdo = Database::getInstance()->getConnection();

// Rohdaten einlesen und JSON dekodieren
$dataRaw = file_get_contents("php://input");
$data = json_decode($dataRaw, true);

// Ungültige JSON-Daten abfangen
if (!is_array($data)) {
    echo json_encode(["success" => false, "message" => "Ungültige Anmeldedaten."]);
    exit;
}

// Eingaben vorbereiten
$email = trim($data['email'] ?? '');
$passwort = $data['passwort'] ?? '';

// Leere Felder prüfen
if (empty($email) || empty($passwort)) {
    echo json_encode(["success" => false, "message" => "Bitte E-Mail und Passwort eingeben."]);
    exit;
}

// Nutzer mit passender E-Mail abrufen
$stmt = $pdo->prepare("
    SELECT id, vorname, rolle, email, passwort_hash, email_verification_token 
    FROM kunden 
    WHERE email = :email
");
$stmt->bindParam(':email', $email);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Existenz und Passwort überprüfen
if (!$user || !password_verify($passwort, $user['passwort_hash'])) {
    echo json_encode(["success" => false, "message" => "Benutzer nicht gefunden oder Passwort falsch."]);
    exit;
}

// E-Mail-Verifikation prüfen
if (!empty($user['email_verification_token'])) {
    echo json_encode(["success" => false, "message" => "Bitte bestätigen Sie zuerst Ihre E-Mail-Adresse."]);
    exit;
}

// Erfolgreich: Session starten
$_SESSION['user_id'] = $user['id'];
$_SESSION['vorname'] = $user['vorname'];
$_SESSION['rolle']   = $user['rolle'];
$_SESSION['email']   = $user['email'];

// Erfolgsantwort mit Weiterleitung
echo json_encode([
    "success" => true,
    "redirect" => $user['rolle'] === 'admin' ? 'admin_dashboard.php' : 'kunden_dashboard.php'
]);
exit;
