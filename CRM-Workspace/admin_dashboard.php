<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rolle'] !== 'admin') {
    die("Zugriff verweigert.");
}

$pdo = Database::getInstance()->getConnection();

$suche = $_GET['suche'] ?? '';
$rolleFilter = $_GET['rolle'] ?? '';

// SQL mit optionalen Filtern
$sql = "SELECT id, vorname, nachname, email, rolle, offene_rechnungen FROM kunden WHERE 1=1";
$params = [];

// Suche nach Name, E-Mail oder ID
if (!empty($suche)) {
    $sql .= " AND (email LIKE :suche OR vorname LIKE :suche OR nachname LIKE :suche";
    $params[':suche'] = "%$suche%";

    if (ctype_digit($suche)) {
        $sql .= " OR id = :id";
        $params[':id'] = (int)$suche;
    }

    $sql .= ")";
}

// Filter nach Rolle
if ($rolleFilter === 'kunde' || $rolleFilter === 'admin') {
    $sql .= " AND rolle = :rolle";
    $params[':rolle'] = $rolleFilter;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$nutzerListe = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin-Dashboard</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <h1>Admin-Dashboard</h1>

  <!-- Such- und Filterformular -->
  <form method="GET" action="admin_dashboard.php">
    <input type="text" name="suche" placeholder="Suche nach E-Mail, Name, ID..." value="<?= htmlspecialchars($suche) ?>">
    <select name="rolle">
      <option value="">Alle Rollen</option>
      <option value="kunde" <?= $rolleFilter === 'kunde' ? 'selected' : '' ?>>Kunden</option>
      <option value="admin" <?= $rolleFilter === 'admin' ? 'selected' : '' ?>>Admins</option>
    </select>
    <button type="submit">Suchen</button>
  </form>

  <!-- Admin hinzufügen Button -->
  <form action="add_admin.php" method="GET">
    <button type="submit">Admin hinzufügen</button>
  </form>

  <!-- Ergebnisliste -->
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
      <?php if (!empty($nutzerListe)): ?>
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
      <?php else: ?>
        <tr><td colspan="7">Keine Nutzer gefunden.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</body>
</html>
