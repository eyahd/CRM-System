<?php
require_once 'db_config.php'; // Datenbankkonfiguration einfügen
require_once 'utils.php';
$pdo = Database::getInstance()->getConnection(); // DB-Verbindung holen

// 1. Alte, nicht aktivierte Benutzer mit abgelaufenem Token löschen
$pdo->prepare("
    DELETE FROM kunden 
    WHERE email_verification_token IS NOT NULL 
      AND email_verification_expires < CURRENT_TIMESTAMP
")->execute();

// 2. Formularwerte vorbereiten
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

// 3. E-Mail validieren
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Fehler: Ungültige E-Mail-Adresse.");
}

// 4. Prüfen, ob E-Mail schon existiert
$checkStmt = $pdo->prepare("SELECT id, email, email_verification_token, email_verification_expires FROM kunden WHERE email = :email");
$checkStmt->execute([':email' => $email]);
$existing = $checkStmt->fetch();

if ($existing) {
    if ($existing['email_verification_token'] !== null && strtotime($existing['email_verification_expires']) < time()) {
        // Abgelaufener, nicht aktivierter Account → löschen
        $pdo->prepare("DELETE FROM kunden WHERE id = :id")->execute([':id' => $existing['id']]);
    } else {
        // Bereits registrierte/aktive E-Mail blockieren
        header("Location: registrierung.html?status=email_exists");
    }
}

// 5. Passwort prüfen
$fehler = validierePasswoerter(pw1: $passwort, pw2: $passwort2);
if ($fehler !== null) {
    echo '<p style="color:red;">' . htmlspecialchars($fehler) . '</p>';
    exit;
}

// 6. Passwort hashen & Token vorbereiten
$passwort_hash = password_hash($passwort, PASSWORD_DEFAULT);
$email_verification_token = bin2hex(random_bytes(16));
$email_verification_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

// 7. SQL vorbereiten
$sql = "INSERT INTO kunden (
            typ, vorname, nachname, email, passwort_hash, telefon, strasse, plz, ort, land,
            firmenname, ust_id, bonitaet_score, iban, bic, email_verification_token, email_verification_expires
        ) VALUES (
            :typ, :vorname, :nachname, :email, :passwort_hash, :telefon, :strasse, :plz, :ort, :land,
            :firmenname, :ust_id, :bonitaet_score, :iban, :bic, :email_verification_token, :email_verification_expires
        )";

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

// 8. Transaktion starten
$pdo->beginTransaction();

try {
    if (!$stmt->execute($params)) {
        throw new Exception("Fehler beim Einfügen in die Datenbank.");
    }

    // 9. Bestätigungs-E-Mail senden
    require 'mailer.php';
    $verification_link = "http://localhost/CRM-Workspace/verify_email.php?token=$email_verification_token";
    sendeEmail(
        typ: 'email_verification',
        empfaenger_email: $email,
        empfaenger_name: "$vorname $nachname",
        daten: ['verification_link' => $verification_link]
    );

    $pdo->commit();
     header("Location: startseite.html?status=confirmation_sent");
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Fehler: Registrierung fehlgeschlagen. " . $e->getMessage();
    file_put_contents(__DIR__ . '/logs/login_errors.log', date('Y-m-d H:i:s') . " - Fehler: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
}
