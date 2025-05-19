<?php
require_once 'db_config.php'; // Datenbankkonfiguration einfügen
require_once 'utils.php';
$pdo = Database::getInstance()->getConnection(); // Datenbankverbindung herstellen

// Formularwerte auslesen & vorbereiten
$typ              = $_POST['typ'] ?? 'privat';
$vorname          = $_POST['vorname'] ?? '';
$nachname         = $_POST['nachname'] ?? '';
$email            = $_POST['email'] ?? '';
$passwort         = $_POST['passwort'] ?? '';
$passwort2        = $_POST['passwort2'] ?? '';
$telefon          = $_POST['telefon'] ?? '';
$strasse          = $_POST['strasse'] ?? '';
$plz              = $_POST['plz'] ?? '';
$ort              = $_POST['ort'] ?? '';
$land             = $_POST['land'] ?? '';
$firmenname       = $_POST['firmenname'] ?? null;
$ust_id           = $_POST['ust_id'] ?? null;
$bonitaet_score   = $_POST['bonitaet_score'] ?? 0;
$iban             = $_POST['iban'] ?? null;
$bic              = $_POST['bic'] ?? null;

// E-Mail validieren 
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Fehler: Ungültige E-Mail-Adresse.");
}

// Passwörter vergleichen und auf mindestens 8 Zeichen prüfen im Backend
validierePasswoerter($passwort, $passwort2);

// Passwort sicher hashen 
$passwort_hash = password_hash($passwort, PASSWORD_DEFAULT);
$email_verification_token = bin2hex(random_bytes(16)); // 32 Zeichen langer hexadezimaler String
$email_verification_expires = date('Y-m-d H:i:s', strtotime('+1 hour')); // Ablaufdatum in einer Stunde

// SQL einfügen
$sql = "INSERT INTO kunden (
            typ, vorname, nachname, email, passwort_hash, telefon, strasse, plz, ort, land,
            firmenname, ust_id, bonitaet_score, iban, bic, email_verification_token, email_verification_expires
        ) VALUES (
            :typ, :vorname, :nachname, :email, :passwort_hash, :telefon, :strasse, :plz, :ort, :land,
            :firmenname, :ust_id, :bonitaet_score, :iban, :bic, :email_verification_token, :email_verification_expires
        )";

// SQL-Statement vorbereiten
$stmt = $pdo->prepare($sql);
$params = [
    ':typ'             => $typ,
    ':vorname'         => $vorname,
    ':nachname'        => $nachname,
    ':email'           => $email,
    ':passwort_hash'   => $passwort_hash,
    ':telefon'         => $telefon,
    ':strasse'         => $strasse,
    ':plz'             => $plz,
    ':ort'             => $ort,
    ':land'            => $land,
    ':firmenname'      => ($typ === 'geschaeft') ? $firmenname : null,
    ':ust_id'          => ($typ === 'geschaeft') ? $ust_id : null,
    ':bonitaet_score'  => (int)$bonitaet_score,
    ':iban'            => $iban,
    ':bic'             => $bic,
    ':email_verification_token' => $email_verification_token,
    ':email_verification_expires' => $email_verification_expires
];

// Transaktion starten
$pdo->beginTransaction();

try {
    // Registrierung in der Datenbank durchführen
    if (!$stmt->execute($params)) {
        throw new Exception("Fehler beim Einfügen in die Datenbank.");
    }

    // E-Mail-Bestätigung senden
    require 'mailer.php';
    $verification_link = "http://localhost/CRM-Workspace/verify_email.php?token=$email_verification_token";
    sendeEmail(
        typ: 'email_verification',
        empfaenger_email: $email,
        empfaenger_name: "$vorname $nachname",
        daten: ['verification_link' => $verification_link]
    );

    // Wenn alles gut geht, Transaktion committen
    $pdo->commit();

    echo "Registrierung erfolgreich! Bitte bestätigen Sie Ihre E-Mail-Adresse.";
} catch (Exception $e) {
    // Bei Fehlern die Transaktion zurückrollen
    $pdo->rollBack();
    echo "Fehler: Registrierung fehlgeschlagen. " . $e->getMessage();
    file_put_contents(__DIR__ . '/logs/login_errors.log', date('Y-m-d H:i:s') . " - Fehler: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
}
