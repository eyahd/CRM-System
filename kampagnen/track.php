<?php
$mysqli = new mysqli("localhost", "root", "", "kampagnen_db");

if ($mysqli->connect_error) {
    die("Verbindungsfehler: " . $mysqli->connect_error);
}

$category = $_GET['category'] ?? '';
$startdate = $_GET['startdate'] ?? '';
$enddate = $_GET['enddate'] ?? '';

$query = "SELECT id, kampagnentyp AS title, startdatum AS start_date, enddatum AS end_date, 
                 kategorie AS type,
                 CASE 
                   WHEN enddatum < CURDATE() THEN 'expired'
                   WHEN startdatum > CURDATE() THEN 'planned'
                   ELSE 'active'
                 END AS status
          FROM kampagnen WHERE 1";

if (!empty($category)) $query .= " AND kategorie = '" . $mysqli->real_escape_string($category) . "'";
if (!empty($startdate)) $query .= " AND startdatum >= '" . $mysqli->real_escape_string($startdate) . "'";
if (!empty($enddate)) $query .= " AND enddatum <= '" . $mysqli->real_escape_string($enddate) . "'";

$result = $mysqli->query($query);
?>


<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kampagnenübersicht</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      padding: 0;
      background: #f4f6f8;
      color: #333;
    }
    .container {
      width: 90%;
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }
    header {
      text-align: center;
      padding: 20px 0;
    }
    header h1 {
      margin: 0;
    }
    .filters, .search-export {
      display: flex;
      justify-content: space-between;
      gap: 20px;
      flex-wrap: wrap;
      margin-bottom: 20px;
    }
    .filter-item, .search-box {
      flex: 1;
      min-width: 150px;
    }
    .campaigns table {
      width: 100%;
      border-collapse: collapse;
      background: white;
    }
    .campaigns th, .campaigns td {
      border: 1px solid #ddd;
      padding: 10px;
      text-align: left;
    }
    .campaigns th {
      background: #f0f0f0;
    }
    .btn {
      padding: 6px 10px;
      text-decoration: none;
      border-radius: 4px;
      background: #3498db;
      color: white;
      margin-right: 5px;
    }
    .btn.delete {
      background: #e74c3c;
    }
      
    .status.active {   
      color: rgb(0, 0, 0);
     }
    .status.planned {
      color: rgb(0, 0, 0);
    }
    .status.expired {
      color: rgb(0, 0, 0);
    }
    .pagination {
      margin-top: 20px;
      text-align: center;
    }
    canvas {
      max-width: 100%;
      margin-top: 30px;
    }
    .export-button {
      background-color: #2ecc71;
      color: white;
      border: none;
      padding: 8px 12px;
      cursor: pointer;
      border-radius: 4px;
    }
  </style>
</head>
<body>
  <div class="container">
    <header>
      <h1>Marketing Kampagnen <i class="fa-solid fa-bullhorn"></i></h1>
      <p>Verwalte deine Kampagnen und erhalte wertvolle Einblicke.</p>
    </header>

    <section class="filters">
      <form method="GET" style="display: flex; flex-wrap: wrap; gap: 20px;">
        <div class="filter-item">
          <label for="category">Kategorie:</label>
          <select name="category" id="category">
            <option value="">Alle</option>
            <option value="rabatt">Rabatt</option>
            <option value="werbung">Werbung</option>
            <option value="event">Event</option>
          </select>
        </div>

        <div class="filter-item">
          <label for="startdate">Startdatum:</label>
          <input type="date" name="startdate" id="startdate">
        </div>

        <div class="filter-item">
          <label for="enddate">Enddatum:</label>
          <input type="date" name="enddate" id="enddate">
        </div>

        <button type="submit" class="btn">Filtern</button>
      </form>
    </section>

    <section class="search-export">
      <div class="search-box">
        <input type="text" id="searchInput" placeholder="Suchen..." style="width: 100%; padding: 8px;">
      </div>
      <button class="export-button" onclick="exportTableToCSV('kampagnen.csv')">
        <i class="fa-solid fa-file-csv"></i> Export CSV
      </button>
    </section>

    <section class="campaigns">
      <table id="kampagnenTable">
        <thead>
          <tr>
            <th>Titel</th>
            <th>Startdatum</th>
            <th>Enddatum</th>
            <th>Status</th>
            <th>Kategorie</th>
            <th>Aktionen</th>
          </tr>
        </thead>
        <tbody>
  <?php while ($row = mysqli_fetch_assoc($result)) { ?>
      <tr>
        <td><?= htmlspecialchars($row['title']) ?></td>
        <td><?= $row['start_date'] ?></td>
        <td><?= $row['end_date'] ?></td>
        <td class="status <?= htmlspecialchars($row['status']) ?>">
          <?= ucfirst($row['status']) === 'Active' ? 'Aktiv' : (ucfirst($row['status']) === 'Planned' ? 'Geplant' : 'Abgelaufen') ?>
        </td>
        <td><?= htmlspecialchars($row['type']) ?></td>
        <td>
          <a href="edit.php?id=<?= $row['id'] ?>" class="btn"><i class="fa-solid fa-pen"></i> Bearbeiten</a>
          <a href="delete.php?id=<?= $row['id'] ?>" class="btn delete"><i class="fa-solid fa-trash"></i> Löschen</a>
        </td>
      </tr>
    <?php } ?>
        </tbody>
      </table>
    </section>

    <canvas id="kampagnenChart"></canvas>

    <section class="pagination">
      <button class="btn">&lt; Zurück</button>
      <span>Seite 1 von 5</span>
      <button class="btn">Weiter &gt;</button>
    </section>
  </div>

  <script>
    // Chart visualization
    const ctx = document.getElementById('kampagnenChart').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Rabatt', 'Werbung', 'Event'],
        datasets: [{
          label: 'Kampagnen pro Kategorie',
          data: [1, 1, 1],
          backgroundColor: ['#3498db', '#f39c12', '#9b59b6']
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: false
          },
          title: {
            display: true,
            text: 'Kampagnen Statistik'
          }
        }
      }
    });

    // CSV Export
    function exportTableToCSV(filename) {
      const rows = document.querySelectorAll("table tr");
      let csv = [];
      rows.forEach(row => {
        const cols = row.querySelectorAll("td, th");
        const rowData = [];
        cols.forEach(col => rowData.push('"' + col.innerText.replace(/\"/g, '""') + '"'));
        csv.push(rowData.join(","));
      });
      const csvFile = new Blob([csv.join("\n")], { type: "text/csv" });
      const downloadLink = document.createElement("a");
      downloadLink.download = filename;
      downloadLink.href = window.URL.createObjectURL(csvFile);
      downloadLink.style.display = "none";
      document.body.appendChild(downloadLink);
      downloadLink.click();
    }

    // Table search
    document.getElementById('searchInput').addEventListener('keyup', function () {
      const value = this.value.toLowerCase();
      const rows = document.querySelectorAll('#kampagnenTable tbody tr');
      rows.forEach(row => {
        row.style.display = [...row.children].some(td => td.innerText.toLowerCase().includes(value)) ? '' : 'none';
      });
    });
  </script>
</body>
</html>


  <!-- PHP Insert Logic -->
  <?php
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $titel = $_POST['titel'];
      $startdatum = $_POST['startdatum'];
      $enddatum = $_POST['enddatum'];
      $kategorie = $_POST['kategorie'];
      $status = $_POST['status'];

      $stmt = $conn->prepare("INSERT INTO kampagnen (titel, startdatum, enddatum, status, kategorie) VALUES (?, ?, ?, ?, ?)");
      $stmt->bind_param("sssss", $titel, $startdatum, $enddatum, $status, $kategorie);
      $stmt->execute();
      echo "<div class='alert alert-success'>Kampagne hinzugefügt!</div>";
  }
  ?>

  <!-- Search -->
  <form method="GET" class="mb-3">
    <input type="text" name="search" class="form-control" placeholder="🔍 Nach Titel oder Kategorie suchen" value="<?= $_GET['search'] ?? '' ?>">
  </form>

  <!-- Export Button -->
  <form method="POST" action="export_csv.php" class="mb-3">
    <button type="submit" class="btn btn-outline-primary">⬇️ Als CSV exportieren</button>
  </form>

  <!-- Table Display -->
  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Titel</th>
        <th>Startdatum</th>
        <th>Enddatum</th>
        <th>Status</th>
        <th>Kategorie</th>
      </tr>
    </thead>
    <tbody>
      <?php
$search = $_GET['search'] ?? '';

$conn = new mysqli("localhost", "root", "", "kampagnen_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT * FROM track WHERE titel LIKE ? OR kategorie LIKE ?");
if (!$stmt) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}

$like = "%$search%";
$stmt->bind_param("ss", $like, $like);

        $stmt->execute();
        $result = $stmt->get_result();

        $statusCounts = ['Geplant' => 0, 'Aktiv' => 0, 'Abgelaufen' => 0];

        while ($row = $result->fetch_assoc()):
          $statusCounts[$row['status']]++;
      ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['titel']) ?></td>
          <td><?= $row['startdatum'] ?></td>
          <td><?= $row['enddatum'] ?></td>
          <td><?= $row['status'] ?></td>
          <td><?= $row['kategorie'] ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>


</div>

</body>
</html>
