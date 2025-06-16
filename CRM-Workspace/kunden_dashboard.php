<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
  die("Nicht eingeloggt.");
}

$pdo = Database::getInstance()->getConnection();
$id = $_SESSION['user_id'];

// Aktuelle Kundendaten abrufen
$stmt = $pdo->prepare("SELECT vorname, nachname, email, telefon, strasse, plz, ort, land, iban, bic FROM kunden WHERE id = :id");
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
  <title>Kundendaten aktualisieren</title>
  <link rel="stylesheet" href="styles.css">
</head>

<body>
  <form action="kunden_update.php" method="POST" class="registration-form">
    <fieldset>
      <legend>Persönliche Daten</legend>

    <label for="vorname">Vorname:</label>
    <input type="text" id="vorname" name="vorname" value="<?= htmlspecialchars($kunde['vorname']) ?>" required />

    <label for="nachname">Nachname:</label>
    <input type="text" id="nachname" name="nachname" value="<?= htmlspecialchars($kunde['nachname']) ?>" required />

    <label for="email">E-Mail:</label>
    <input type="email" id="email" name="email" value="<?= htmlspecialchars($kunde['email']) ?>" required />

    <label for="telefon">Telefon:</label>
    <input type="text" id="telefon" name="telefon" required value="<?= htmlspecialchars($kunde['telefon']) ?>" />

    <label for="strasse">Straße:</label>
    <input type="text" id="strasse" name="strasse" required value="<?= htmlspecialchars($kunde['strasse']) ?>" />

    <label for="plz">PLZ:</label>
    <input type="text" id="plz" name="plz" required value="<?= htmlspecialchars($kunde['plz']) ?>" />

    <label for="ort">Ort:</label>
    <input type="text" id="ort" name="ort" required value="<?= htmlspecialchars($kunde['ort']) ?>" />

    <label for="land">Land:</label>
    <input type="text" id="land" name="land" required value="<?= htmlspecialchars($kunde['land']) ?>" />

    <label for="iban">IBAN:</label>
    <input type="text" id="iban" name="iban" required value="<?= htmlspecialchars($kunde['iban']) ?>" />
    <small id="iban-fehler" class="fehlertext"></small>

    <label for="bic">BIC:</label>
    <input type="text" id="bic" name="bic" required value="<?= htmlspecialchars($kunde['bic']) ?>" />

    <button type="submit">Daten speichern</button>
    </fieldset>
  </form>
</body>

</html>

<!-- Formular zur Passwortänderung -->
<h2>Passwort ändern</h2>
<form action="passwort_aendern.php" method="POST" id="passwort-formular" class="registration-form">
  <fieldset>
    <legend>Passwort ändern</legend>

    <label for="altes_passwort">Aktuelles Passwort:</label>
    <input
      type="password"
      id="altes_passwort"
      name="altes_passwort"
      required />

    <label for="passwort">Neues Passwort:</label>
    <input
      type="password"
      id="passwort"
      name="passwort"
      minlength="8"
      required
      aria-describedby="passwort-fehler" />

    <label for="passwort2">Neues Passwort wiederholen:</label>
    <input
      type="password"
      id="passwort2"
      name="passwort2"
      minlength="8"
      required
      aria-describedby="passwort-fehler" />

    <!-- Fehleranzeige -->
    <div id="passwort-fehler" class="fehler-text"></div>

    <button type="submit">Passwort ändern</button>
  </fieldset>
</form>

</fieldset>
</form>
<!-- Konto löschen -->
<form action="konto_loeschen.php" method="GET">
  <button type="submit" style="background-color: #c0392b; color: white;">Konto löschen</button>
</form>
<!-- Abmelden -->
<form action="logout.php" method="POST">
  <button type="submit">Abmelden</button>
</form>
<script src="js/iban.js"></script>
<script src="js/script.js"></script>
</body>
</html>