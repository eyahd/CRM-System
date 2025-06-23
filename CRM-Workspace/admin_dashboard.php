<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rolle'] !== 'admin') {
    die("Zugriff verweigert.");
}

$pdo = Database::getInstance()->getConnection();

$suche = $_GET['suche'] ?? '';
$rolleFilter = $_GET['rolle'] ?? '';
$seite = isset($_GET['seite']) && is_numeric($_GET['seite']) ? (int)$_GET['seite'] : 1;
$eintraegeProSeite = 10;
$offset = ($seite - 1) * $eintraegeProSeite;

// Zähle die Gesamtanzahl der Nutzer mit den Filtern
$sqlCount = "SELECT COUNT(*) FROM kunden WHERE 1=1";
$paramsCount = [];

if (!empty($suche)) {
    $sqlCount .= " AND (email LIKE :suche OR vorname LIKE :suche OR nachname LIKE :suche";
    $paramsCount[':suche'] = "%$suche%";

    if (ctype_digit($suche)) {
        $sqlCount .= " OR id = :id";
        $paramsCount[':id'] = (int)$suche;
    }
    $sqlCount .= ")";
}

if ($rolleFilter === 'kunde' || $rolleFilter === 'admin') {
    $sqlCount .= " AND rolle = :rolle";
    $paramsCount[':rolle'] = $rolleFilter;
}

$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($paramsCount);
$gesamtEintraege = $stmtCount->fetchColumn();
$gesamtSeiten = ceil($gesamtEintraege / $eintraegeProSeite);

// Daten abfragen
$sql = "SELECT id, vorname, nachname, email, rolle, offene_rechnungen FROM kunden WHERE 1=1";
$params = [];

if (!empty($suche)) {
    $sql .= " AND (email LIKE :suche OR vorname LIKE :suche OR nachname LIKE :suche";
    $params[':suche'] = "%$suche%";

    if (ctype_digit($suche)) {
        $sql .= " OR id = :id";
        $params[':id'] = (int)$suche;
    }

    $sql .= ")";
}

if ($rolleFilter === 'kunde' || $rolleFilter === 'admin') {
    $sql .= " AND rolle = :rolle";
    $params[':rolle'] = $rolleFilter;
}

$sql .= " LIMIT :limit OFFSET :offset";
$params[':limit'] = $eintraegeProSeite;
$params[':offset'] = $offset;

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->execute();
$nutzerListe = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>Admin-Dashboard</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="admindash">
    <h1>Admin-Dashboard</h1>

    <form method="GET" action="admin_dashboard.php">
      <input type="text" name="suche" placeholder="Suche nach E-Mail, Name, ID..." value="<?= htmlspecialchars($suche) ?>">
      <select name="rolle">
        <option value="">Alle Rollen</option>
        <option value="kunde" <?= $rolleFilter === 'kunde' ? 'selected' : '' ?>>Kunden</option>
        <option value="admin" <?= $rolleFilter === 'admin' ? 'selected' : '' ?>>Admins</option>
      </select>
      <button type="submit">Suchen</button>
    </form>

    <form action="add_admin.php" method="GET">
      <button type="submit">Admin hinzufügen</button>
    </form>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Vorname</th>
          <th>Nachname</th>
          <th>E-Mail</th>
          <th>Rolle</th>
          <th>Offene Rechnungen</th>
          <th>Aktion</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($nutzerListe as $nutzer): ?>
          <tr>
            <td><?= htmlspecialchars($nutzer['id']) ?></td>
            <td><?= htmlspecialchars($nutzer['vorname']) ?></td>
            <td><?= htmlspecialchars($nutzer['nachname']) ?></td>
            <td><?= htmlspecialchars($nutzer['email']) ?></td>
            <td><?= htmlspecialchars($nutzer['rolle']) ?></td>
            <td><?= $nutzer['offene_rechnungen'] ? 'Ja' : 'Nein' ?></td>
            <td>
              <a href="benutzer_bearbeiten.php?id=<?= $nutzer['id'] ?>">Bearbeiten</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="pagination">
      <?php for ($i = 1; $i <= $gesamtSeiten; $i++): ?>
        <a href="?seite=<?= $i ?>&suche=<?= urlencode($suche) ?>&rolle=<?= urlencode($rolleFilter) ?>" class="<?= $i == $seite ? 'active' : '' ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>
    </div>
  </div>
  <script src="js/script.js"></script>
</body>
</html>
