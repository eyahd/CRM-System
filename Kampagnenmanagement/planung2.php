<?php  
// DB-Verbindung
$conn = new mysqli("localhost", "root", "", "crm_autovermietung");
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

// Segmentoptionen aus Datenbank laden 
$segments = [];
$segment_result = $conn->query("SELECT DISTINCT segment FROM kategorie");
if ($segment_result) {
    while ($row = $segment_result->fetch_assoc()) {
        $segments[] = $row['segment'];
    }
}

// Fehlerbehandlung und Erfolgsmeldung
$errors = [];
$success = false;

// Initial values for sticky form
$betreff = $inhalt = $segment = "";
$kampagnenArt = $kampagnenTyp = "";
$startdatum = $enddatum = "";
$language = "de";
$rabattCode = "";

// Formularverarbeitung
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $segment = $_POST['segment'] ?? '';
    $kampagnenArt = $_POST['kampagnenArt'] ?? '';
    $kampagnenTyp = $_POST['kampagnen_typ'] ?? '';
    $startdatum = $_POST['startdatum'] ?? '';
    $enddatum = $_POST['enddatum'] ?? '';
    $language = $_POST['language'] ?? 'de';

    if (empty($segment)) {
        $errors[] = "Bitte wählen Sie eine Zielgruppe.";
    }
    if (empty($kampagnenArt)) {
        $errors[] = "Bitte wählen Sie eine Kampagnenart.";
    }
    if (empty($kampagnenTyp)) {
        $errors[] = "Bitte wählen Sie einen Kampagnentyp.";
    }
    if (empty($startdatum)) {
        $errors[] = "Bitte geben Sie ein Startdatum ein.";
    }
    if (empty($enddatum)) {
        $errors[] = "Bitte geben Sie ein Enddatum ein.";
    }

    if (empty($errors)) {
        $params = http_build_query([
            'segment' => $segment,
            'kampagnenArt' => $kampagnenArt,
            'kampagnenTyp' => $kampagnenTyp,
            'startdatum' => $startdatum,
            'enddatum' => $enddatum,
            'language' => $language
        ]);
        header("Location: sending_visualization.php?$params");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <title>Kampagnenplanung</title>
   <style>
body {
  font-family: 'Poppins', sans-serif;
  background: 
    linear-gradient(rgba(245, 247, 250, 0.85), rgba(226, 232, 236, 0.85)),
    url('https://images.unsplash.com/photo-1552519507-da3b142c6e3d'); /* background of cars/highway */
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  color: #333;
  margin: 0;
  padding: 0;
}

.container {
  max-width: 850px;
  margin: 60px auto;
  background-color: rgba(255, 255, 255, 0.95);
  padding: 40px;
  border-radius: 20px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

h1 {
  text-align: center;
  color: #1e3a8a;
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 2.5rem;
}

label {
  display: block;
  margin-top: 20px;
  font-weight: 500;
  color: #2c3e50;
}

input[type="text"],
input[type="date"],
textarea,
select {
  width: 100%;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 10px;
  margin-top: 8px;
  font-size: 1rem;
  background-color: #fefefe;
}

input:focus,
select:focus,
textarea:focus {
  border: 2px solid #1e3a8a;
  outline: none;
}

.button {
  background-color: #1e3a8a;
  color: #fbfdff;
  padding: 14px 22px;
  border: none;
  border-radius: 10px;
  font-size: 1rem;
  margin-top: 30px;
  cursor: pointer;
  font-weight: 600;
  transition: background-color 0.3s ease, transform 0.2s;
}

.button:hover {
  background-color: #1e3a8a;
  transform: scale(1.05);
}

.error {
  color: #e74c3c;
  font-weight: 500;
  margin-bottom: 1rem;
}
</style>

<?php

$segment = $_GET['segment'] ?? '';
$kampagnenArt = $_GET['kampagnenArt'] ?? '';
$kampagnenTyp = $_GET['kampagnen_typ'] ?? '';
$startdatum = date('Y-m-d'); 
$enddatum = date('Y-m-d', strtotime('+7 days')); 
$language = 'de';


$segments = ['Geschäftskunden', 'Junge Fahrer', 'Touristen', 'Senioren'];
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>Kampagne planen</title>
</head>
<body>
  <h1>Neue Kampagne planen</h1>
<div class="container">
  <form method="POST" action="sending_visualization.php">
    <label for="language">Sprache der Nachricht</label>
    <select id="language" name="language" aria-label="Sprache">
      <option value="de" <?= $language === 'de' ? 'selected' : '' ?>>Deutsch</option>
      <option value="en" <?= $language === 'en' ? 'selected' : '' ?>>Englisch</option>
    </select>

    <label for="segment">Zielgruppe</label>
    <select id="segment" name="segment">
      <option value="">-- Bitte wählen --</option>
      <?php foreach ($segments as $seg): ?>
        <option value="<?= htmlspecialchars($seg) ?>" <?= $segment === $seg ? 'selected' : '' ?>>
          <?= htmlspecialchars($seg) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <label for="kampagnenArt">Kampagnenart</label>
    <select id="kampagnenArt" name="kampagnenArt" required>
      <option value="">Bitte wählen</option>
      <option value="E-Mail" <?= $kampagnenArt === 'E-Mail' ? 'selected' : '' ?>>📧 E-Mail</option>
      <option value="Social Media" <?= $kampagnenArt === 'Social Media' ? 'selected' : '' ?>>📱 Social Media</option>
      <option value="Online Banner" <?= $kampagnenArt === 'Online Banner' ? 'selected' : '' ?>>🖥️ Online Banner</option>
      <option value="Postsendung" <?= $kampagnenArt === 'Postsendung' ? 'selected' : '' ?>>📬 Postsendung</option>
    </select>

    <label for="kampagnen_typ">Kampagnen-Typ</label>
    <select id="kampagnen_typ" name="kampagnen_typ" required>
      <option value="">-- Bitte wählen --</option>
      <option value="Rabattaktion" <?= $kampagnenTyp === 'Rabattaktion' ? 'selected' : '' ?>>💸 Rabattaktion</option>
      <option value="Gewinnspiel" <?= $kampagnenTyp === 'Gewinnspiel' ? 'selected' : '' ?>>🎁 Gewinnspiel</option>
      <option value="Last-Minute-Angebot" <?= $kampagnenTyp === 'Last-Minute-Angebot' ? 'selected' : '' ?>>⏰ Last-Minute-Angebot</option>
      <option value="Sonderaktion" <?= $kampagnenTyp === 'Sonderaktion' ? 'selected' : '' ?>>⭐ Sonderaktion</option>
    </select>

    <label for="startdatum">Startdatum</label>
    <input type="date" id="startdatum" name="startdatum" value="<?= $startdatum ?>" required>

    <label for="enddatum">Enddatum</label>
    <input type="date" id="enddatum" name="enddatum" value="<?= $enddatum ?>" required>

    <label for="rabattCode">Rabattcode</label>
    <input type="text" id="rabattCode" name="rabattCode" value="AUTO-<?= strtoupper(substr($segment, 0, 3)) ?>-<?= rand(100, 999) ?>" readonly>

    <label for="betreff">Betreff</label>
    <input type="text" id="betreff" name="betreff" value="Ihre exklusive <?= $kampagnenTyp ?> für <?= $segment ?>" readonly>

    <label for="nachricht">Nachricht</label>
    <textarea id="nachricht" name="nachricht" rows="6" readonly>
<?= generateMessageWithGPT($segment, $kampagnenTyp, 'Automatisch generiert', getCTR($segment, $kampagnenTyp)) ?>
    </textarea>

    <br><br>
    <button type="submit">Speichern</button>
  </form>

</body>
</html>

<?php
// Reuse existing functions
function getCTR($segment, $kampagnenTyp) {
    $basis = 4.0;
    $boost = 0;
    if (strpos($kampagnenTyp, 'Rabatt') !== false) $boost += 1.5;
    if (strpos($kampagnenTyp, 'Gewinnspiel') !== false) $boost += 2.0;
    if ($segment === 'Junge Fahrer') $boost += 1.3;
    if ($segment === 'Touristen') $boost += 1.0;
    return round($basis + $boost, 1);
}

function generateMessageWithGPT($segment, $kampagnenTyp, $begruendung, $ctr = null) {
    [$kampagnenTyp] ?? '📢';

    $message = " Hallo $segment! Unsere neueste $kampagnenTyp ist da: $begruendung.";
    if ($ctr !== null) {
        $bewertung = $ctr > 6.5 ? " Sehr beliebt!" : " Gute Performance";
        $message .= " Diese Kampagnenart erzielte kürzlich eine CTR von {$ctr}%. $bewertung";
    }
    return $message . " Jetzt entdecken!";
}
?>

</body>
</html>
