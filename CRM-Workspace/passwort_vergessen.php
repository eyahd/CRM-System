<?php
require_once 'db_config.php';
require_once 'mailer.php';

$pdo = Database::getInstance()->getConnection();
$email = $_POST['email'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: passwort_vergessen.html?status=invalid");
    exit;
}

// Nutzer holen
$sql = "SELECT id, vorname, passwort_reset_expires FROM kunden WHERE email = :email";
$stmt = $pdo->prepare($sql);
$stmt->execute([':email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Wenn Nutzer existiert
if ($user) {
    // Rate-Limiting: nur blockieren, wenn letztes Reset <60 Sekunden her
    $expiresTimestamp = !empty($user['passwort_reset_expires']) ? strtotime($user['passwort_reset_expires']) : 0;
    $letzteAnfrage = $expiresTimestamp - 3600; // weil in DB "+1 Stunde" gespeichert wird

    if ($letzteAnfrage > time() - 60) {
        header("Location: passwort_vergessen.html?status=limit");
        exit;
    }

    // Token & Ablaufzeit erzeugen
    $token = bin2hex(random_bytes(16));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // In DB speichern
    $update = $pdo->prepare("
        UPDATE kunden 
        SET passwort_reset_token = :token, 
            passwort_reset_expires = :expires 
        WHERE email = :email
    ");
    $update->execute([
        ':token' => $token,
        ':expires' => $expires,
        ':email' => $email
    ]);

    // Reset-Link bauen
    $link = "http://localhost/CRM-Workspace/passwort_zuruecksetzen.php?token=$token";

    // E-Mail senden
    sendeEmail(
        typ: 'passwort_reset',
        empfaenger_email: $email,
        empfaenger_name: $user['vorname'],
        daten: [
            'vorname' => $user['vorname'],
            'reset_link' => $link
        ]
    );
}

// Immer Weiterleitung (auch wenn E-Mail nicht gefunden – aus Sicherheitsgründen)
header("Location: startseite.html?status=reset_sent");
exit;
