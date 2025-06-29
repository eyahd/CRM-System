<?php
session_start();
require_once "includes/db.php";
require_once "includes/mail_funktionen.php";
require_once "includes/log_funktionen.php";

// --- Kampagne starten ---
if (isset($_GET['start'])) {
    $kampagne_id = (int) $_GET['start'];
    $conn->query("UPDATE kampagnen SET aktiv = 1 WHERE id = $kampagne_id");

    $result = $conn->query("
        SELECT s.name 
        FROM kampagnen_zielgruppen kz
        JOIN segmente s ON kz.segment_id = s.id
        WHERE kz.kampagne_id = $kampagne_id
    ");

    $log = "Kampagne ID $kampagne_id gesendet an Segmente:\n";
    while ($r = $result->fetch_assoc()) {
        $log .= "- {$r['name']}\n";
    }
    file_put_contents("sendelog.txt", $log, FILE_APPEND);

    $resCamp = $conn->query("SELECT * FROM kampagnen WHERE id = $kampagne_id");
    $kampagne  = $resCamp ? $resCamp->fetch_assoc() : null;

    if ($kampagne) {
        $betreff = "Autovermietung: {$kampagne['titel']}";
        $inhalt  = "<h3>Jetzt buchen und sparen!</h3><p>{$kampagne['beschreibung']}</p>";

        $test_empfaenger = ['eya.hamdi2004@gmail.com', 'test2@example.com'];
        foreach ($test_empfaenger as $mail) {
            sendeKampagnenMail($mail, $betreff, $inhalt);
        }
        $_SESSION['message'] = "✅ Nachricht wurde gesendet!";
    } else {
        $_SESSION['message'] = "❌ Kampagne nicht gefunden.";
    }

    header("Location: durchfuehrung.php");
    exit;
}

$res = $conn->query("SELECT * FROM kampagnen ORDER BY start_date DESC");
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>📤 Kampagnen Übersicht</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background: #f9fafb;
      color: #1f2937;
      padding: 2rem;
      line-height: 1.6;
    }

    h1 {
      font-size: 2rem;
      font-weight: 600;
      margin-bottom: 1rem;
      color: #111827;
    }

    .btn {
      display: inline-block;
      padding: 0.6rem 1.2rem;
      background-color:rgb(21, 70, 175);
      color: white;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 500;
      transition: 0.2s ease;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .btn:hover {
      background-color: #1e3a8a;
    }

    .btn-disabled {
      background-color: #e5e7eb;
      color: #9ca3af;
      cursor: not-allowed;
    }

    .message {
      background-color: #ecfdf5;
      color: #065f46;
      padding: 1rem 1.25rem;
      border-left: 4px solid #10b981;
      border-radius: 6px;
      margin-bottom: 1.5rem;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .actions {
      display: flex;
      gap: 1rem;
      margin-bottom: 1.5rem;
    }

    table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0 0.5rem;
    }

    th, td {
      text-align: left;
      padding: 0.85rem 1rem;
    }

    th {
      background-color: #f3f4f6;
      color: #374151;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.85rem;
    }

    tr {
      background-color: white;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
      border-radius: 10px;
    }

    tr td:first-child {
      border-top-left-radius: 10px;
      border-bottom-left-radius: 10px;
    }

    tr td:last-child {
      border-top-right-radius: 10px;
      border-bottom-right-radius: 10px;
    }

    .status-badge {
      display: inline-block;
      padding: 0.4rem 0.7rem;
      font-size: 0.8rem;
      font-weight: 500;
      border-radius: 999px;
    }

    .status-active {
      background-color: #d1fae5;
      color: #065f46;
    }

    .status-pending {
      background-color: #fef9c3;
      color: #92400e;
    }

    .status-expired {
      background-color: #fee2e2;
      color: #991b1b;
    }

    .status-soon {
      background-color: #ede9fe;
      color: #4c1d95;
    }

    .status-warning {
      background-color: #fef3c7;
      color: #78350f;
    }

    .empty-row {
      text-align: center;
      padding: 1.5rem;
      color: #6b7280;
      font-style: italic;
    }
  </style>
</head>
<body>
  <h1>Kampagnen Übersicht</h1>

  <?php if (!empty($_SESSION['message'])): ?>
      <div class="message">
          <?= htmlspecialchars($_SESSION['message'], ENT_QUOTES) ?>
      </div>
      <?php unset($_SESSION['message']); ?>
  <?php endif; ?>

  <div class="actions">
    <a href="plannung.php" class="btn">Neue Kampagne</a>
    <a href="auswertung/index.php" class="btn">Auswertung</a>
  </div>

  <table>
    <tr>
      <th>Typ</th>
      <th>Zeitraum</th>
      <th>Status</th>
      <th>Aktion</th>
    </tr>

    <?php if ($res && $res->num_rows): ?>
        <?php while ($row = $res->fetch_assoc()):
            $start = new DateTime($row['start_date']);
            $ende  = new DateTime($row['end_date']);
            $heute = new DateTime();

            if ($heute > $ende) {
                $statusText = "Abgelaufen";
                $statusClass = "status-expired";
            } elseif ($heute < $start) {
                $tageBisStart = $heute->diff($start)->days;
                if ($tageBisStart <= 7) {
                    $statusText = "Startet bald";
                    $statusClass = "status-soon";
                } else {
                    $statusText = "Noch nicht gestartet";
                    $statusClass = "status-pending";
                }
            } else {
                $tageBisEnde = $heute->diff($ende)->days;
                if ($tageBisEnde <= 3) {
                    $statusText = "Endet bald";
                    $statusClass = "status-warning";
                } else {
                    $statusText = "Aktiv";
                    $statusClass = "status-active";
                }
            }
        ?>
        <tr>
          <td><?= htmlspecialchars($row['subtype'], ENT_QUOTES) ?></td>
          <td><?= htmlspecialchars($row['start_date'], ENT_QUOTES) ?> &ndash; <?= htmlspecialchars($row['end_date'], ENT_QUOTES) ?></td>
          <td><span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span></td>
          <td>
              <?php if (!$row['aktiv']): ?>
                  <a href="?start=<?= $row['id'] ?>" class="btn">🚀 Starten</a>
              <?php else: ?>
                  <span class="btn btn-disabled">Bereits gestartet</span>
              <?php endif; ?>
          </td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
          <td colspan="4" class="empty-row">Keine Kampagnen gefunden.</td>
        </tr>
    <?php endif; ?>
  </table>
</body>
