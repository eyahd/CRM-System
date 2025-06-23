<?php
require_once 'db_config.php';
require_once 'mailer.php';

$pdo = Database::getInstance()->getConnection();
$token = $_GET['token'] ?? '';

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
    $update = $pdo->prepare("
        UPDATE kunden 
        SET email_verification_token = NULL, email_verification_expires = NULL 
        WHERE id = :id
    ");

    if ($update->execute([':id' => $kunde['id']])) {
        sendeEmail(
            typ: 'registrierung',
            empfaenger_email: $kunde['email'],
            empfaenger_name: $kunde['vorname'] . ' ' . $kunde['nachname'],
            daten: ['vorname' => $kunde['vorname']]
        );
        // Erfolgreiche Bestätigung: HTML Ausgabe mit Redirect
?>
        <!DOCTYPE html>
        <html lang="de">

        <head>
            <meta charset="UTF-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1" />
            <title>E-Mail bestätigt</title>
            <link rel="stylesheet" href="styles.css" />
            <meta http-equiv="refresh" content="5;url=startseite.html" />
        </head>

        <body>
            <main role="main" aria-live="polite">
                <div class="confirmation-container">
                    <h1>E-Mail bestätigt!</h1>
                    <div class="spinner" aria-hidden="true"></div>
                    <p>Danke, <?= htmlspecialchars($kunde['vorname']) ?>, Ihre E-Mail-Adresse wurde erfolgreich bestätigt.</p>
                    <p>Sie werden in 5 Sekunden automatisch zur Startseite weitergeleitet.</p>
                    <p>Falls nicht, klicken Sie <a href="startseite.html">hier</a>.</p>
                </div>
            </main>
        </body>

        </html>
<?php
        exit;
    } else {
        http_response_code(500);
        echo "Fehler: Bestätigung konnte nicht gespeichert werden.";
        exit;
    }
} else {
    http_response_code(400);
    echo "Fehler: Ungültiger oder abgelaufener Token.";
    exit;
}
