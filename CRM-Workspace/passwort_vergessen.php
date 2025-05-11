<?php
// Diese Datei ermöglicht es einem Benutzer, sein Passwort zurückzusetzen, indem er einen Link an seine E-Mail-Adresse sendet.
require_once 'db_config.php';
require_once 'mailer.php';

$pdo = Database::getInstance()->getConnection();
$email = $_POST['email'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Ungültige E-Mail-Adresse.");
}

// Prüfen, ob Nutzer existiert
$sql = "SELECT id, vorname FROM kunden WHERE email = :email";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':email', $email);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("E-Mail-Adresse nicht gefunden.");
}

// Token erzeugen
$token = bin2hex(random_bytes(16));
$expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

// Token speichern
$sql = "UPDATE kunden SET passwort_reset_token = :token, passwort_reset_expires = :expires WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':token' => $token,
    ':expires' => $expires,
    ':id' => $user['id']
]);

$link = "http://localhost/CRM-Workspace/passwort_zuruecksetzen.php?token=$token";

sendeEmail(
    typ: 'passwort_reset',
    empfaenger_email: $email,
    empfaenger_name: $user['vorname'],
    daten: [
        'vorname' => $user['vorname'],
        'reset_link' => $link
    ]
);


echo "Ein Link zum Zurücksetzen wurde an die angegebende E-Mail gesendet.";
