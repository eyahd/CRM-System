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
  <h2>Konto unwiderruflich löschen</h2>
  <p>Hallo <?= htmlspecialchars($kunde['vorname']) ?>, bitte bestätigen Sie die Löschung mit Ihrem aktuellen Passwort.</p>

  <form action="konto_loeschen_confirm.php" method="POST">
    <label for="passwort">Passwort:</label>
    <input type="password" name="passwort" required />
    <button type="submit" style="background-color: #c0392b; color: white;">Konto löschen</button>
  </form>

  <p><a href="kunden_dashboard.php">Abbrechen</a></p>
</body>
</html>
