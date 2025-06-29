<?php
// Dynamische CTR-Simulation (ersetzt Dummy-Tabelle)
function getCTR($segment, $kampagnenTyp) {
    $basis = 4.0; // Basiswert
    $boost = 0;
    if (strpos($kampagnenTyp, 'Rabatt') !== false) $boost += 1.5;
    if (strpos($kampagnenTyp, 'Gewinnspiel') !== false) $boost += 2.0;
    if ($segment === 'Junge Fahrer') $boost += 1.3;
    if ($segment === 'Touristen') $boost += 1.0;
    return round($basis + $boost, 1);
}

function generateMessageWithGPT($segment, $kampagnenTyp, $begruendung, $ctr = null) {
    $emoji = [
        'Rabattaktion' => '💸',
        'Gewinnspiel' => '🎁',
        'Last-Minute-Angebot' => '⏰',
        'Sonderaktion' => '⭐',
    ][$kampagnenTyp] ?? '📢';

    $message = "$emoji Hallo $segment! Unsere neueste $kampagnenTyp ist da: $begruendung.";
    if ($ctr !== null) {
        $bewertung = $ctr > 6.5 ? " Sehr beliebt!" : " Gute Performance";
        $message .= " Diese Kampagnenart erzielte kürzlich eine CTR von {$ctr}%. $bewertung";
    }
    return $message . " Jetzt entdecken!";
}

// Empfehlungen — könnte später aus einer Datenbank kommen
$recommendations = [
    [
        'segment' => 'Geschäftskunden',
        'kampagnenArt' => 'E-Mail',
        'kampagnenTyp' => 'Rabattaktion',
        'zeitraum' => 'Juli 2025',
        'begründung' => 'Steigende Nachfrage bei Firmenwagen im Sommer'
    ],
    [
        'segment' => 'Junge Fahrer',
        'kampagnenArt' => 'Social Media',
        'kampagnenTyp' => 'Gewinnspiel',
        'zeitraum' => 'August 2025',
        'begründung' => 'Hohe Interaktion auf Instagram im Sommer'
    ],
    [
        'segment' => 'Touristen',
        'kampagnenArt' => 'Online Banner',
        'kampagnenTyp' => 'Last-Minute-Angebot',
        'zeitraum' => 'Juni–August 2025',
        'begründung' => 'Starker Anstieg an Buchungen durch Tourismus'
    ],
    [
        'segment' => 'Senioren',
        'kampagnenArt' => 'Postsendung',
        'kampagnenTyp' => 'Sonderaktion',
        'zeitraum' => 'Mai 2025',
        'begründung' => 'Traditionelle Kanäle erreichen diese Zielgruppe besser'
    ]
];
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <title>📌 Kampagnenempfehlungen</title>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f4f5f7;
      margin: 0;
      padding: 2rem;
      color: #333;
    }
    h1 {
      text-align: center;
      color: rgb(33, 38, 180);
      margin-bottom: 2rem;
    }
    .recommendation-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 2rem;
      max-width: 1200px;
      margin: 0 auto;
    }
    .card {
      background-color: #fff;
      padding: 1.5rem;
      border-radius: 12px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.08);
      transition: transform 0.2s ease;
      position: relative;
    }
    .card:hover {
      transform: translateY(-5px);
    }
    .card h2 {
      color: #1e3a8a;
      font-size: 1.3rem;
      margin-bottom: 0.5rem;
    }
    .card p {
      margin: 0.4rem 0;
    }
    .card .high-ctr {
      color: #28a745;
      font-weight: bold;
    }
    .card .low-ctr {
      color: #e67e22;
    }
    .plan-btn {
      display: inline-block;
      margin-top: 1rem;
      padding: 0.7rem 1.2rem;
      background-color: #1e3a8a;
      color: #fff;
      border: none;
      border-radius: 10px;
      text-decoration: none;
      font-size: 0.95rem;
      transition: background 0.3s ease;
    }
    .plan-btn:hover {
      background-color: #1e3a8a;
    }
  </style>
</head>
<body>

<h1> Kampagnenempfehlungen</h1>

<div class="recommendation-grid">
  <?php foreach ($recommendations as $rec): ?>
    <?php
      $ctr = getCTR($rec['segment'], $rec['kampagnenTyp']);
      $message = generateMessageWithGPT($rec['segment'], $rec['kampagnenTyp'], $rec['begründung'], $ctr);
      $ctrClass = $ctr > 6.5 ? 'high-ctr' : 'low-ctr';
    ?>
    <div class="card">
      <h2><?= htmlspecialchars($rec['segment']) ?></h2>
      <p><strong>Kampagnenart:</strong> <?= htmlspecialchars($rec['kampagnenArt']) ?></p>
      <p><strong>Kampagnentyp:</strong> <?= htmlspecialchars($rec['kampagnenTyp']) ?></p>
      <p><strong>Zeitraum:</strong> <?= htmlspecialchars($rec['zeitraum']) ?></p>
      <p><em> <?= htmlspecialchars($rec['begründung']) ?></em></p>
      <p><strong>CTR:</strong> <span class="<?= $ctrClass ?>"><?= htmlspecialchars($ctr) ?>%</span></p>
      <p><strong>Vorschlag Nachricht:</strong><br><?= htmlspecialchars($message) ?></p>
      <a
  class="plan-btn"
  href="../planung2.php?segment=<?= urlencode($rec['segment']) ?>&kampagnenArt=<?= urlencode($rec['kampagnenArt']) ?>&kampagnen_typ=<?= urlencode($rec['kampagnenTyp']) ?>&zeitraum=<?= urlencode($rec['zeitraum']) ?>&begruendung=<?= urlencode($rec['begründung']) ?>"
>
   Kampagne planen
</a>

    </div>
  <?php endforeach; ?>
</div>

</body>
</html>
