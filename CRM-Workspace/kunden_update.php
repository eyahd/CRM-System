<?php
session_start();
require_once 'db_config.php';
require_once 'mailer.php';

if (!isset($_SESSION['user_id'])) {
    die("Nicht eingeloggt.");
}

$pdo = Database::getInstance()->getConnection();
$id = $_SESSION['user_id'];

// Alte Daten abrufen
$stmt = $pdo->prepare("SELECT vorname, nachname, email, telefon, strasse, plz, ort, land, iban, bic FROM kunden WHERE id = :id");
$stmt->execute([':id' => $id]);
$alt = $stmt->fetch(PDO::FETCH_ASSOC);

// Neue Daten aus dem Formular
$neu = [
    'vorname' => $_POST['vorname'] ?? '',
    'nachname' => $_POST['nachname'] ?? '',
    'email' => $_POST['email'] ?? '',
    'telefon' => $_POST['telefon'] ?? '',
    'strasse' => $_POST['strasse'] ?? '',
    'plz' => $_POST['plz'] ?? '',
    'ort' => $_POST['ort'] ?? '',
    'land' => $_POST['land'] ?? '',
    'iban' => $_POST['iban'] ?? '',
    'bic' => $_POST['bic'] ?? ''
];

// Änderungen vergleichen
$geaenderte_felder = [];
foreach ($neu as $feld => $wert) {
    if ($wert !== $alt[$feld]) {
        $geaenderte_felder[] = ucfirst($feld);
    }
}

// Daten aktualisieren
$sql = "UPDATE kunden SET 
    vorname = :vorname,
    nachname = :nachname,
    email = :email,
    telefon = :telefon,
    strasse = :strasse,
    plz = :plz,
    ort = :ort,
    land = :land,
    iban = :iban,
    bic = :bic  
WHERE id = :id";


$stmt = $pdo->prepare($sql);
$neu['id'] = $id;
$stmt->execute($neu);

// E-Mail versenden (nur wenn es Änderungen gab)
if (!empty($geaenderte_felder)) {
    sendeEmail(
        typ: 'daten_update',
        empfaenger_email: $neu['email'],
        empfaenger_name: $neu['vorname'],
        daten: [
            'vorname' => $neu['vorname'],
            'felder' => implode(', ', $geaenderte_felder)
        ]
    );
}

header("Location: kunden_dashboard.php?status=kunden_update");
exit;
