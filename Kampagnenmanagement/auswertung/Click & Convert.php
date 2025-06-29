<?php
$host = 'localhost';
$db = 'crm_autovermietung';
$user = 'root';
$pass = '';

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch campaigns
$sql = "SELECT * FROM kampagnen";
$result = $conn->query($sql);

// Fetch campaign stats
$sql_stats = "SELECT kampagne_id, SUM(klicks) AS klicks, SUM(conversions) AS oeffnungen FROM kampagnen_stats GROUP BY kampagne_id";
$result_stats = $conn->query($sql_stats);

$kampagnen_stats = [];
while ($row = $result_stats->fetch_assoc()) {
    $kampagnen_stats[$row['kampagne_id']] = $row;
}

if (isset($_GET['stop'])) {
    $kampagne_id = (int)$_GET['stop'];
    $conn->query("UPDATE kampagnen SET aktiv = 0 WHERE id = $kampagne_id");

    if ($conn->affected_rows > 0) {
        echo "Kampagne $kampagne_id gestoppt.";
    } else {
        echo "Fehler beim Stoppen oder bereits gestoppt.";
    }

    // header("Location: übersicht.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <title>Kampagnen-Dashboard</title>
<style>
  /* Body & Layout */
  body {
    background-color: #f0f4f8;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 20px;
    color: #333;
  }

  h1 {
    text-align: center;
    margin-bottom: 1rem;
    color: #1e3a8a;
    font-weight: 700;
  }

  #filterBar {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 15px;
    margin-bottom: 20px;
  }

  #filterBar input, #filterBar select {
    padding: 10px 15px;
    font-size: 15px;
    border-radius: 6px;
    border: 1.8px solid #cbd5e1;
    min-width: 180px;
    transition: border-color 0.3s ease;
  }

  #filterBar input:focus, #filterBar select:focus {
    border-color: #2563eb;
    outline: none;
  }

  #filterBar button {
    padding: 12px 22px;
    background-color: #2563eb;
    border: none;
    color: white;
    font-weight: 600;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.25s ease;
  }
  #filterBar button:hover {
    background-color: #1e40af;
  }

  /* Campaign Cards Container: grid */
  #campaignList {
    display: grid;
    grid-template-columns: repeat(auto-fill,minmax(320px,1fr));
    gap: 20px;
    padding: 0 10px;
  }

  /* Individual Card */
  .kampagnen-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 16px rgba(37, 99, 235, 0.15);
    padding: 20px;
    position: relative;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  .kampagnen-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 15px 30px rgba(37, 99, 235, 0.3);
  }

  .kampagnen-card h3 {
    font-size: 20px;
    margin-bottom: 10px;
    color: #2563eb;
  }

  .kampagnen-card p {
    font-size: 14px;
    line-height: 1.4;
    margin: 5px 0;
    color: #475569;
  }

  /* Status Badge */
  .status-badge {
    position: absolute;
    top: 20px;
    right: 20px;
    padding: 6px 14px;
    font-weight: 700;
    border-radius: 20px;
    font-size: 13px;
    color: white;
    user-select: none;
    text-transform: uppercase;
  }
  .status-badge.active {
    background-color: #22c55e; /* green */
  }
  .status-badge.upcoming {
    background-color: #fbbf24; /* amber */
    color: #92400e;
  }
  .status-badge.expired {
    background-color: #ef4444; /* red */
  }

  /* Buttons */
  a.btn-danger {
    display: inline-block;
    padding: 10px 18px;
    background-color: #dc2626;
    color: white;
    font-weight: 600;
    border-radius: 6px;
    text-decoration: none;
    transition: background-color 0.3s ease, transform 0.15s ease;
    user-select: none;
  }
  a.btn-danger:hover {
    background-color: #b91c1c;
    transform: scale(1.05);
  }

  span.btn-disabled {
    display: inline-block;
    padding: 10px 18px;
    background-color: #94a3b8;
    border-radius: 6px;
    color: white;
    font-weight: 600;
  }

  /* Pagination Controls */
  #paginationControls {
    margin: 20px auto;
    text-align: center;
  }
  #paginationControls button {
    margin: 0 8px;
    padding: 10px 20px;
    font-weight: 600;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    background-color: #2563eb;
    color: white;
    transition: background-color 0.3s ease;
  }
  #paginationControls button:disabled {
    background-color: #94a3b8;
    cursor: default;
  }
  #paginationControls button:hover:not(:disabled) {
    background-color: #1e40af;
  }
  #pageInfo {
    font-weight: 700;
    font-size: 16px;
    color: #1e40af;
  }

  /* Chart Container */
  #chartContainer {
    max-width: 960px;
    margin: 40px auto 60px;
  }
</style>

</head>
<body>
  <h1>🎯 Click & Convert Dashboard</h1>
<!-- Include necessary libraries -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<div id="filterBar">
  <input id="searchInput" type="text" placeholder="🔍 Suche Kampagne..." />
  <select id="filterStatus">
    <option value="all">Status: Alle</option>
    <option value="active">🟢 Läuft</option>
    <option value="upcoming">⏳ Startet bald</option>
    <option value="expired">❌ Abgelaufen</option>
  </select>

  <select id="filterCategory">
    <option value="all">Kategorie: Alle</option>
    <?php 
      $categories = [];
      $result->data_seek(0);
      while ($row = $result->fetch_assoc()) {
          if (!in_array($row['category'], $categories)) $categories[] = $row['category'];
      }
      foreach ($categories as $cat) {
          echo "<option value='".htmlspecialchars($cat)."'>".htmlspecialchars($cat)."</option>";
      }
      $result->data_seek(0);
    ?>
  </select>

  <select id="sortSelect">
    <option value="start_asc">Startdatum ↑</option>
    <option value="start_desc">Startdatum ↓</option>
    <option value="end_asc">Enddatum ↑</option>
    <option value="end_desc">Enddatum ↓</option>
    <option value="clicks_desc">Klicks ↓</option>
    <option value="clicks_asc">Klicks ↑</option>
  </select>

  <button id="exportPdfBtn">Liste als PDF exportieren</button>
</div>

<div id="paginationControls">
  <button id="prevPageBtn" disabled>Zurück</button>
  <span id="pageInfo">Seite 1</span>
  <button id="nextPageBtn">Weiter</button>
</div>

<div id="campaignList">
  <?php while ($kampagne = $result->fetch_assoc()): ?>
    <?php 
      $stats = $kampagnen_stats[$kampagne['id']] ?? ['klicks' => 0, 'oeffnungen' => 0];
      $mails = max(1, $stats['oeffnungen']);
      $klickrate = round(($stats['klicks'] / $mails) * 100, 1);
      $start = new DateTime($kampagne['start_date']);
      $ende = new DateTime($kampagne['end_date']);
      $heute = new DateTime();
      $tage_left = $heute <= $ende ? $heute->diff($ende)->days : 0;

      $status = ($heute > $ende) ? "expired" : (($heute < $start) ? "upcoming" : "active");
      $showStopButton = in_array($status, ['active', 'upcoming']);
      $chartId = "chart_" . $kampagne['id'];
    ?>
    <div class="kampagnen-card" 
        data-name="<?= htmlspecialchars($kampagne['subtype']) ?>" 
        data-clicks="<?= $stats['klicks'] ?>" 
        data-status="<?= $status ?>"
        data-category="<?= htmlspecialchars($kampagne['category']) ?>"
        data-start="<?= $kampagne['start_date'] ?>"
        data-end="<?= $kampagne['end_date'] ?>">

      <div class="status-badge <?= $status ?>"><?= ucfirst($status) ?></div>
      <h3>🎯 <?= htmlspecialchars($kampagne['subtype']) ?></h3>
      <p>📅 <strong>Start – Ende:</strong> <?= $kampagne['start_date'] ?> – <?= $kampagne['end_date'] ?></p>
      <p>📢 <strong>Typ:</strong> <?= htmlspecialchars($kampagne['category']) ?></p>
      <p>🕓 <strong>Noch Tage:</strong> <?= $tage_left ?></p>
      <p>📬 <strong>Mails gesendet:</strong> <?= $stats['oeffnungen'] ?></p>
      <p>🔗 <strong>Klicks:</strong> <?= $stats['klicks'] ?></p>
      <p>📊 <strong>Klickrate:</strong> <?= $klickrate ?>%</p>

      <?php if ($showStopButton): ?>
        <a href="?stop=<?= $kampagne['id'] ?>" class="btn-danger" onclick="return confirm('Kampagne wirklich stoppen?')">🚩 Kampagne beenden</a>
      <?php else: ?>
        <span class="btn-disabled">❌ Bereits beendet</span>
      <?php endif; ?>

      <div id="chartContainer">
        <canvas id="<?= $chartId ?>"></canvas>
      </div>

      <script>
        (() => {
          const ctx = document.getElementById("<?= $chartId ?>").getContext('2d');
          new Chart(ctx, {
            type: 'bar',
            data: {
              labels: ["<?= htmlspecialchars($kampagne['subtype']) ?>"],
              datasets: [{
                label: "Klicks",
                data: [<?= $stats['klicks'] ?>],
                backgroundColor: "<?= $status === 'active' ? '#27ae60' : ($status === 'upcoming' ? '#f39c12' : '#c0392b') ?>"
              }]
            },
            options: {
              responsive: true,
              scales: { y: { beginAtZero: true } },
              plugins: { legend: { display: false } }
            }
          });
        })();
      </script>
    </div>
  <?php endwhile; ?>
</div>

<script>
  const searchInput = document.getElementById('searchInput');
  const filterStatus = document.getElementById('filterStatus');
  const filterCategory = document.getElementById('filterCategory');
  const sortSelect = document.getElementById('sortSelect');
  const campaignList = document.getElementById('campaignList');

  function filterAndSort() {
    const searchText = searchInput.value.toLowerCase();
    const status = filterStatus.value;
    const category = filterCategory.value;
    const sort = sortSelect.value;

    let cards = [...campaignList.children];

    cards.forEach(card => {
      const matchesSearch = card.dataset.name.toLowerCase().includes(searchText);
      const matchesStatus = status === 'all' || card.dataset.status === status;
      const matchesCategory = category === 'all' || card.dataset.category === category;
      card.style.display = (matchesSearch && matchesStatus && matchesCategory) ? '' : 'none';
    });

    cards = cards.filter(c => c.style.display !== 'none');

    cards.sort((a, b) => {
      if (sort === 'start_asc') return new Date(a.dataset.start) - new Date(b.dataset.start);
      if (sort === 'start_desc') return new Date(b.dataset.start) - new Date(a.dataset.start);
      if (sort === 'end_asc') return new Date(a.dataset.end) - new Date(b.dataset.end);
      if (sort === 'end_desc') return new Date(b.dataset.end) - new Date(a.dataset.end);
      if (sort === 'clicks_asc') return a.dataset.clicks - b.dataset.clicks;
      if (sort === 'clicks_desc') return b.dataset.clicks - a.dataset.clicks;
      return 0;
    });

    cards.forEach(card => campaignList.appendChild(card));
  }

  searchInput.addEventListener('input', filterAndSort);
  filterStatus.addEventListener('change', filterAndSort);
  filterCategory.addEventListener('change', filterAndSort);
  sortSelect.addEventListener('change', filterAndSort);

  filterAndSort();

  document.getElementById('exportPdfBtn').addEventListener('click', async () => {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    const visibleCards = [...campaignList.children].filter(c => c.style.display !== 'none');
    let y = 10;

    for (const card of visibleCards) {
      const canvas = await html2canvas(card);
      const imgData = canvas.toDataURL('image/png');
      const imgProps = doc.getImageProperties(imgData);
      const pdfWidth = doc.internal.pageSize.getWidth() - 20;
      const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

      if (y + pdfHeight > doc.internal.pageSize.getHeight() - 10) {
        doc.addPage();
        y = 10;
      }

      doc.addImage(imgData, 'PNG', 10, y, pdfWidth, pdfHeight);
      y += pdfHeight + 10;
    }

    doc.save('kampagnen-liste.pdf');
  });
</script>

</body>
</html>
