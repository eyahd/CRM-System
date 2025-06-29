<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rolle'] !== 'admin') {
    die("Zugriff verweigert.");
}

$pdo = Database::getInstance()->getConnection();
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;
if (!$id) die("Keine gültige Benutzer-ID angegeben.");

// Benutzer abrufen
$stmt = $pdo->prepare("SELECT * FROM kunden WHERE id = :id");
$stmt->execute([':id' => $id]);
$benutzer = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$benutzer) die("Benutzer nicht gefunden.");

// Benutzer löschen
if (isset($_POST['benutzer_loeschen'])) {
    $stmt = $pdo->prepare("DELETE FROM kunden WHERE id = :id");
    $stmt->execute([':id' => $id]);
    header("Location: admin_dashboard.php?status=benutzer_geloescht");
    exit;
}

// Benutzer aktualisieren
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['benutzer_loeschen'])) {
    $rolle = $_POST['rolle'] === 'admin' ? 'admin' : 'kunde';
    $typ = ($rolle === 'kunde') ? $_POST['typ'] ?? null : null;

    $daten = [
        'vorname' => $_POST['vorname'],
        'nachname' => $_POST['nachname'],
        'email' => $_POST['email'],
        'telefon' => $_POST['telefon'] ?? null,
        'strasse' => $_POST['strasse'] ?? null,
        'plz' => $_POST['plz'] ?? null,
        'ort' => $_POST['ort'] ?? null,
        'land' => $_POST['land'] ?? null,
        'firmenname' => $_POST['firmenname'] ?? null,
        'ust_id' => $_POST['ust_id'] ?? null,
        'bonitaet_score' => $_POST['bonitaet_score'] ?? 0,
        'iban' => $_POST['iban'] ?? null,
        'bic' => $_POST['bic'] ?? null,
        'rolle' => $rolle,
        'typ' => $typ,
        'offene_rechnungen' => isset($_POST['offene_rechnungen']) ? 1 : 0,
        'id' => $id
    ];

    $update = "UPDATE kunden SET
        vorname = :vorname,
        nachname = :nachname,
        email = :email,
        telefon = :telefon,
        strasse = :strasse,
        plz = :plz,
        ort = :ort,
        land = :land,
        firmenname = :firmenname,
        ust_id = :ust_id,
        bonitaet_score = :bonitaet_score,
        iban = :iban,
        bic = :bic,
        rolle = :rolle,
        typ = :typ,
        offene_rechnungen = :offene_rechnungen
        WHERE id = :id";

    $stmt = $pdo->prepare($update);
    $stmt->execute($daten);

    header("Location: admin_dashboard.php?status=kunden_update");
    exit;
}
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>Benutzer bearbeiten</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <h2>Benutzer bearbeiten</h2>
    <form method="POST" class="edit-user-form">
        <label>Vorname:</label>
        <input type="text" name="vorname" value="<?= htmlspecialchars($benutzer['vorname']) ?>" required><br>

        <label>Nachname:</label>
        <input type="text" name="nachname" value="<?= htmlspecialchars($benutzer['nachname']) ?>" required><br>

        <label>E-Mail:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($benutzer['email']) ?>" required><br>

        <div class="kunde-feld">
            <label>Telefon:</label>
            <input type="text" name="telefon" value="<?= htmlspecialchars($benutzer['telefon']) ?>"><br>

            <label>Straße:</label>
            <input type="text" name="strasse" value="<?= htmlspecialchars($benutzer['strasse']) ?>"><br>

            <label>PLZ:</label>
            <input type="text" name="plz" value="<?= htmlspecialchars($benutzer['plz']) ?>"><br>

            <label>Ort:</label>
            <input type="text" name="ort" value="<?= htmlspecialchars($benutzer['ort']) ?>"><br>

            <label>Land:</label>
            <input type="text" name="land" value="<?= htmlspecialchars($benutzer['land']) ?>"><br>

            <fieldset id="firmenfelder">
                <label for="firmenname">Firmenname</label>
                <input type="text" name="firmenname" id="firmenname"
                    value="<?= htmlspecialchars($benutzer['firmenname'] ?? '') ?>" />
                <label for="ust_id">USt-ID</label>
                <input type="text" name="ust_id" id="ust_id"
                    value="<?= htmlspecialchars($benutzer['ust_id'] ?? '') ?>" />
            </fieldset>

            <label for="iban">IBAN:</label>
            <input type="text" id="iban" name="iban" placeholder="DE..." required
                value="<?= htmlspecialchars($benutzer['iban'] ?? '') ?>" />
            <small id="iban-fehler" class="fehler-text"></small>

            <label for="bic">BIC:</label>
            <input type="text" name="bic" id="bic" required
                value="<?= htmlspecialchars($benutzer['bic'] ?? '') ?>" />

            <label for="bonitaet_score">Bonitätsscore (0–255)</label>
            <input type="number" name="bonitaet_score" id="bonitaet_score" min="0" max="255" required
                value="<?= htmlspecialchars($benutzer['bonitaet_score'] ?? 0) ?>" />

            <label>Offene Rechnungen:</label>
            <input type="checkbox" name="offene_rechnungen" <?= $benutzer['offene_rechnungen'] ? 'checked' : '' ?>><br>
        </div>

        <label>Rolle:</label>
        <select name="rolle" id="rolle" onchange="toggleKundentyp(this.value); toggleKundenfelder(this.value)">
            <option value="kunde" <?= $benutzer['rolle'] === 'kunde' ? 'selected' : '' ?>>Kunde</option>
            <option value="admin" <?= $benutzer['rolle'] === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select><br>

        <div id="kundentyp-container" style="display: <?= $benutzer['rolle'] === 'kunde' ? 'block' : 'none' ?>;">
            <label>Kundentyp:</label>
            <select name="typ" id="typ" onchange="toggleFirmenfelder()">
                <option value="privat" <?= $benutzer['typ'] === 'privat' ? 'selected' : '' ?>>Privatkunde</option>
                <option value="geschaeft" <?= $benutzer['typ'] === 'geschaeft' ? 'selected' : '' ?>>Geschäftskunde</option>
            </select><br>
        </div>

        
            <button type="submit">Speichern</button>
        </div>
    </form>

    <form method="POST" onsubmit="return confirm('Möchten Sie diesen Benutzer wirklich löschen?');" style="margin-top: 1rem;" class="center-button">
        <input type="hidden" name="benutzer_loeschen" value="1">
        <button type="submit" class="button button-danger">Benutzer löschen</button>
    </form>

    <script src="js/iban.js"></script>
    <script src="js/script.js"></script>
</body>

</html>
