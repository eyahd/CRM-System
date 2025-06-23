<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
  die("Nicht eingeloggt.");
}

$pdo = Database::getInstance()->getConnection();
$id = $_SESSION['user_id'];

// Kundendaten laden
$stmt = $pdo->prepare("SELECT vorname FROM kunden WHERE id = :id");
$stmt->execute([':id' => $id]);
$kunde = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kunde) {
  die("Kunde nicht gefunden.");
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>Konto löschen</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <form class="account-delete-form" action="konto_loeschen_confirm.php" method="POST">
    <h2>Konto unwiderruflich löschen</h2>
    <p>Hallo <?= htmlspecialchars($kunde['vorname']) ?>,<br>bitte bestätigen Sie die Löschung mit Ihrem aktuellen Passwort.</p>

    <input type="password" name="passwort" placeholder="Passwort eingeben" required />

    <button type="submit">Konto löschen</button>
    <a href="kunden_dashboard.php">Abbrechen</a>
  </form>
  <script src="js/script.js"></script>
</body>

</html>
