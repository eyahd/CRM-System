<?php
require_once 'db_config.php';
require_once 'mailer.php';

$pdo = Database::getInstance()->getConnection();
$token = $_GET['token'] ?? '';

// 1. Kundendaten anhand des Tokens holen
$stmt = $pdo->prepare("
    SELECT id, vorname, nachname, email 
    FROM kunden 
    WHERE email_verification_token = :token 
      AND email_verification_token IS NOT NULL 
      AND email_verification_expires > CURRENT_TIMESTAMP 
    LIMIT 1
");
$stmt->execute([':token' => $token]);
$kunde = $stmt->fetch();

if ($kunde) {
    // 2. Bestätigung in der DB aktualisieren
    $update = $pdo->prepare("
        UPDATE kunden 
        SET email_verification_token = NULL, email_verification_expires = NULL 
        WHERE id = :id
    ");

    if ($update->execute([':id' => $kunde['id']])) {
        // 3. Bestätigungsmail senden
        sendeEmail(
            typ: 'registrierung',
            empfaenger_email: $kunde['email'],
            empfaenger_name: $kunde['vorname'] . ' ' . $kunde['nachname'],
            daten: ['vorname' => $kunde['vorname']]
        );

        echo "Ihre E-Mail-Adresse wurde erfolgreich bestätigt. Sie können sich nun anmelden.";
    } else {
        die("Fehler: Bestätigung konnte nicht gespeichert werden.");
    }
} else {
    die("Fehler: Ungültiger oder abgelaufener Token.");
}
