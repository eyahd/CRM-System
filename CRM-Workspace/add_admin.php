<?php
session_start();
require_once 'db_config.php';
require_once 'mailer.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rolle'] !== 'admin') {
    die("Zugriff verweigert.");
}

$pdo = Database::getInstance()->getConnection();
$fehlermeldung = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vorname = trim($_POST['vorname']);
    $nachname = trim($_POST['nachname']);
    $email = trim($_POST['email']);
    $passwort = $_POST['passwort'];

    // E-Mail prüfen
    $stmt = $pdo->prepare("SELECT id FROM kunden WHERE email = :email");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        $fehlermeldung = "Ein Benutzer mit dieser E-Mail existiert bereits.";
    } else {
        // Passwort hashen
        $hash = password_hash($passwort, PASSWORD_DEFAULT);

        // In DB einfügen
        $stmt = $pdo->prepare("INSERT INTO kunden (vorname, nachname, email, passwort_hash, rolle) 
                               VALUES (:vorname, :nachname, :email, :passwort, 'admin')");
        if ($stmt->execute([
            'vorname' => $vorname,
            'nachname' => $nachname,
            'email' => $email,
            'passwort' => $hash
        ])) {
            // Bestätigung per E-Mail
            sendeEmail(
                typ: 'admin_hinzufuegen',
                empfaenger_email: $email,
                empfaenger_name: "$vorname $nachname",
                daten: [
                    'vorname' => $vorname,
                    'nachname' => $nachname,
                    'email' => $email,
                    'login_link' => 'https://localhost/CRM-Workspace/startseite.html',
                    'passwort' => $passwort
                ]
            );
        } else {
            $fehlermeldung = "Fehler beim Hinzufügen des Admins.";
        }

        echo "Admin wurde erfolgreich hinzugefügt.";
        echo '<meta http-equiv="refresh" content="2; URL=admin_dashboard.php">';
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>Admin hinzufügen</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <h2>Neuen Admin anlegen</h2>
    <?php if ($fehlermeldung): ?>
        <p style="color: red;"><?= htmlspecialchars($fehlermeldung) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Vorname:</label>
        <input type="text" name="vorname" required><br>

        <label>Nachname:</label>
        <input type="text" name="nachname" required><br>

        <label>E-Mail:</label>
        <input type="email" name="email" required><br>

        <label>Passwort:</label>
        <input type="password" name="passwort" minlength="8"><br>

        <button type="submit">Admin erstellen</button>
    </form>

    <div class="center-button">
        <a href="admin_dashboard.php" class="button">Zurück zum Dashboard</a>
    </div>
</body>

</html>