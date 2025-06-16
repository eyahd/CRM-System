<?php
// link zum Zurücksetzen des Passworts in der E-Mail
require_once 'db_config.php';
require_once 'mailer.php';
require_once 'utils.php';
$pdo = Database::getInstance()->getConnection();

$token = $_GET['token'] ?? '';
if (!$token) die("Ungültiger Link.");

// Prüfen, ob Token gültig ist
$sql = "SELECT id, vorname, email FROM kunden WHERE passwort_reset_token = :token AND passwort_reset_expires > NOW()";
$stmt = $pdo->prepare($sql);
$stmt->execute([':token' => $token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) die("Link ungültig oder abgelaufen.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pw = $_POST['passwort'] ?? '';
    $pw2 = $_POST['passwort2'] ?? '';

    $fehler = validierePasswoerter(pw1: $pw, pw2: $pw2);

    if ($fehler !== null) {
        // Fehler gefunden – dem Nutzer anzeigen und Skript beenden
        echo '<p style="color:red;">' . htmlspecialchars($fehler) . '</p>';
        exit;
    }

    // Passwort aktualisieren
    $hash = password_hash($pw, PASSWORD_DEFAULT);
    $sql = "UPDATE kunden 
            SET passwort_hash = :hash, passwort_reset_token = NULL, passwort_reset_expires = NULL 
            WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':hash' => $hash, ':id' => $user['id']]);

    // Bestätigungsmail versenden
    sendeEmail(
        typ: 'passwort_update',
        empfaenger_email: $user['email'],
        empfaenger_name: $user['vorname'],
        daten: ['vorname' => $user['vorname']]
    );

    // Erfolgsmeldung und Weiterleitung
    echo "<p>Passwort erfolgreich aktualisiert. Sie werden in 5 Sekunden zur Login-Seite weitergeleitet...</p>";
    echo '<meta http-equiv="refresh" content="5; URL=login.html">';
    exit;
}
?>
<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <title>Neues Passwort setzen</title>
    <link rel="stylesheet" href="styles.css"/>
</head>
<body>

<form method="POST" id="passwort-formular">
    <p>Bitte geben Sie Ihr neues Passwort ein.</p>

    <label for="passwort">Neues Passwort:</label>
    <input
        type="password"
        name="passwort"
        id="passwort"
        minlength="8"
        required
        aria-describedby="passwort-fehler" />

    <label for="passwort2">Passwort wiederholen:</label>
    <input
        type="password"
        name="passwort2"
        id="passwort2"
        minlength="8"
        required
        aria-describedby="passwort-fehler" />

    <div id="passwort-fehler" class="fehler-text"></div>

    <button type="submit">Passwort speichern</button>
</form>
<script src="js/script.js"></script>
</body>
</html>