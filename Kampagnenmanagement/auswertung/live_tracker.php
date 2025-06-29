<?php
// live_tracker.php

// --- CONFIG & DB CONNECTION (same as your working code) ---
$host = 'localhost';
$db = 'crm_autovermietung';
$user = 'root';
$pass = '';

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (Exception $e) {
    if (isset($_GET['action']) && $_GET['action'] === 'fetch') {
        http_response_code(500);
        echo json_encode(['error' => 'DB connection failed', 'details' => $e->getMessage()]);
    } else {
        die("Database connection failed: " . $e->getMessage());
    }
    exit;
}

// --- API endpoint: serve JSON data when ?action=fetch ---
if (isset($_GET['action']) && $_GET['action'] === 'fetch') {
    header('Content-Type: application/json');

    // Sanitize inputs from GET filters
    $stadt = isset($_GET['stadt']) ? trim($_GET['stadt']) : '';
    $kampagne = isset($_GET['kampagne']) ? trim($_GET['kampagne']) : '';
    $kategorie = isset($_GET['kategorie']) ? trim($_GET['kategorie']) : '';

    // Build WHERE clause dynamically
    $whereClauses = [];
    $params = [];

    if ($stadt !== '') {
        $whereClauses[] = 'stadt = :stadt';
        $params[':stadt'] = $stadt;
    }
    if ($kampagne !== '') {
        $whereClauses[] = 'kampagne = :kampagne';
        $params[':kampagne'] = $kampagne;
    }
    if ($kategorie !== '') {
        $whereClauses[] = 'fahrzeugkategorie = :kategorie';
        $params[':kategorie'] = $kategorie;
    }

    $whereSQL = '';
    if (count($whereClauses) > 0) {
        $whereSQL = 'WHERE ' . implode(' AND ', $whereClauses);
    }

    // Fetch data
   $sql = "SELECT kundenID, name, email, stadt, fahrzeugkategorie, startdatum, enddatum, miettage, tagessatz, gesamtpreis, kampagne 
        FROM mietvertraege 
        $whereSQL 
        ORDER BY startdatum DESC";



    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();

    // KPIs
    $totalRentals = count($results);
    $totalRevenue = 0;
    $totalDays = 0;
    foreach ($results as $row) {
        $totalRevenue += floatval($row['gesamtpreis']);
        $totalDays += intval($row['miettage']);
    }
    $avgDays = $totalRentals > 0 ? round($totalDays / $totalRentals, 2) : 0;

    // Extra data for charts (e.g., revenue per category)
    $revenuePerCategory = [];
    foreach ($results as $row) {
        $cat = $row['fahrzeugkategorie'];
        if (!isset($revenuePerCategory[$cat])) $revenuePerCategory[$cat] = 0;
        $revenuePerCategory[$cat] += floatval($row['gesamtpreis']);
    }

    echo json_encode([
        'data' => $results,
        'kpis' => [
            'totalRentals' => $totalRentals,
            'totalRevenue' => round($totalRevenue, 2),
            'avgRentalDays' => $avgDays,
        ],
        'charts' => [
            'revenuePerCategory' => $revenuePerCategory
        ]
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="refresh" content="15">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Live Rental Tracker Dashboard</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

    <!-- Google Fonts - Montserrat -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: #f0f2f5;
            color: #333;
        }
        .container {
            margin-top: 40px;
            margin-bottom: 60px;
        }
        .kpi-card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgb(0 0 0 / 0.1);
            padding: 20px;
            background: #fff;
            transition: transform 0.3s ease;
        }
        .kpi-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 20px rgb(0 0 0 / 0.15);
        }
        .kpi-value {
            font-size: 2.8rem;
            font-weight: 700;
            color: #0d6efd;
        }
        .filter-label {
            font-weight: 600;
            margin-bottom: 6px;
        }
        .data-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgb(0 0 0 / 0.1);
            margin-top: 30px;
            overflow-x: auto;
        }
        table {
            border-collapse: separate !important;
            border-spacing: 0 15px !important;
        }
        thead tr th {
            border-bottom: none;
            font-weight: 600;
            color: #444;
            text-align: left;
            padding: 15px 20px;
        }
        tbody tr {
            background: #f9fafb;
            transition: background 0.3s ease;
            cursor: pointer;
        }
        tbody tr:hover {
            background: #e7f1ff;
        }
        tbody tr td {
            padding: 15px 20px;
            vertical-align: middle;
            border: none !important;
        }
        .spinner-container {
            display: flex;
            justify-content: center;
            margin-top: 50px;
        }
        .error-msg {
            text-align: center;
            margin-top: 40px;
            color: #dc3545;
            font-weight: 600;
        }
    </style>
</head>
<body>
<div class="container">
    <h1 class="mb-4 text-center">🚗 Live Rental Tracker Dashboard</h1>

    <!-- Filters -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="filterStadt" class="filter-label">Stadt</label>
            <select id="filterStadt" class="form-select">
                <option value="">Alle Städte</option>
                <!-- Dynamically loaded options -->
            </select>
        </div>
        <div class="col-md-4">
            <label for="filterKampagne" class="filter-label">Kampagne</label>
            <select id="filterKampagne" class="form-select">
                <option value="">Alle Kampagnen</option>
                <!-- Dynamically loaded options -->
            </select>
        </div>
        <div class="col-md-4">
            <label for="filterKategorie" class="filter-label">Fahrzeugkategorie</label>
            <select id="filterKategorie" class="form-select">
                <option value="">Alle Kategorien</option>
                <!-- Dynamically loaded options -->
            </select>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="kpi-card text-center">
                <div class="kpi-value" id="totalRentals">-</div>
                <div>Gesamtvermietungen</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kpi-card text-center">
                <div class="kpi-value" id="totalRevenue">- €</div>
                <div>Gesamter Umsatz</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kpi-card text-center">
                <div class="kpi-value" id="avgRentalDays">- Tage</div>
                <div>Durchschnittliche Mietdauer</div>
            </div>
        </div>
    </div>

<!-- Chart: Revenue per Category -->
<div class="mb-5 text-center">
  <div style="max-width: 800px; margin: 0 auto;">
    <canvas id="revenueChart" height="120"></canvas>
  </div>
</div>


    <!-- Data Table -->
    <div class="data-table">
        <table class="table table-borderless" id="rentalsTable">
            <thead>
            <tr>
                <th>KundenID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Stadt</th>
                <th>Fahrzeugkategorie</th>
                <th>Startdatum</th>
                <th>Enddatum</th>
                <th>Miettage</th>
                <th>Tagessatz (€)</th>
                <th>Gesamtpreis (€)</th>
                <th>Kampagne</th>
            </tr>
            </thead>
            <tbody>
            <!-- Filled by JS -->
            </tbody>
        </table>
    </div>

    <div class="spinner-container" id="loadingSpinner" style="display:none;">
        <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
        <span class="ms-2">Lädt Daten...</span>
    </div>

    <div class="error-msg" id="errorMsg" style="display:none;"></div>
</div>

<script>
    // Global variables
    let allData = [];
    let revenueChart;

    // Load filters options dynamically from data (we will first fetch all data and then extract options)
    async function loadFiltersOptions() {
        try {
            const response = await fetch('live_tracker.php?action=fetch');
            const json = await response.json();
            if (json.error) {
                throw new Error(json.error);
            }
            allData = json.data;

            // Extract unique filter options
            const uniqueStadt = new Set();
            const uniqueKampagne = new Set();
            const uniqueKategorie = new Set();

            allData.forEach(item => {
                if (item.stadt) uniqueStadt.add(item.stadt);
                if (item.kampagne) uniqueKampagne.add(item.kampagne);
                if (item.fahrzeugkategorie) uniqueKategorie.add(item.fahrzeugkategorie);
            });

            populateSelect('filterStadt', Array.from(uniqueStadt));
            populateSelect('filterKampagne', Array.from(uniqueKampagne));
            populateSelect('filterKategorie', Array.from(uniqueKategorie));

            // After loading filters, load table and KPIs with no filter
            await loadData();

        } catch (error) {
            showError(error.message);
        }
    }

    function populateSelect(selectId, options) {
        const select = document.getElementById(selectId);
        options.sort();
        options.forEach(opt => {
            const optionEl = document.createElement('option');
            optionEl.value = opt;
            optionEl.textContent = opt;
            select.appendChild(optionEl);
        });
    }

    // Load data with current filters
    async function loadData() {
        showLoading(true);
        hideError();

        // Gather filter values
        const stadt = document.getElementById('filterStadt').value;
        const kampagne = document.getElementById('filterKampagne').value;
        const kategorie = document.getElementById('filterKategorie').value;

        // Build URL params
        const params = new URLSearchParams();
        params.append('action', 'fetch');
        if (stadt) params.append('stadt', stadt);
        if (kampagne) params.append('kampagne', kampagne);
        if (kategorie) params.append('kategorie', kategorie);

        try {
            const response = await fetch('live_tracker.php?' + params.toString());
            const json = await response.json();

            if (json.error) {
                throw new Error(json.error);
            }

            // Update KPIs
            document.getElementById('totalRentals').textContent = json.kpis.totalRentals;
            document.getElementById('totalRevenue').textContent = json.kpis.totalRevenue.toLocaleString('de-DE', {minimumFractionDigits: 2}) + ' €';
            document.getElementById('avgRentalDays').textContent = json.kpis.avgRentalDays + ' Tage';

            // Update table
            renderTable(json.data);

            // Update chart
            renderChart(json.charts.revenuePerCategory);

        } catch (error) {
            showError(error.message);
        }
        showLoading(false);
    }

    function renderTable(data) {
        const tbody = document.querySelector('#rentalsTable tbody');
        tbody.innerHTML = '';

        if (data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="11" class="text-center text-muted">Keine Daten gefunden.</td></tr>`;
            return;
        }

        data.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${row.kundenID}</td>
                <td>${row.name}</td>
                <td>${row.email}</td>
                <td>${row.stadt}</td>
                <td>${row.fahrzeugkategorie}</td>
                <td>${formatDate(row.startdatum)}</td>
                <td>${formatDate(row.enddatum)}</td>
                <td>${row.miettage}</td>
                <td>${Number(row.tagessatz).toFixed(2)} €</td>
                <td>${Number(row.gesamtpreis).toFixed(2)} €</td>
                <td>${row.kampagne}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function formatDate(dateStr) {
        const d = new Date(dateStr);
        if (isNaN(d)) return '-';
        return d.toLocaleDateString('de-DE');
    }

    function renderChart(data) {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const labels = Object.keys(data);
        const values = Object.values(data).map(v => Math.round(v));

        if (revenueChart) {
            revenueChart.data.labels = labels;
            revenueChart.data.datasets[0].data = values;
            revenueChart.update();
            return;
        }

        revenueChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Umsatz pro Kategorie (€)',
                    data: values,
                    backgroundColor: '#0d6efd',
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: context => context.parsed.y.toLocaleString('de-DE') + ' €'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: val => val.toLocaleString('de-DE') + ' €'
                        }
                    }
                }
            }
        });
    }

    function showLoading(show) {
        document.getElementById('loadingSpinner').style.display = show ? 'flex' : 'none';
    }
    function showError(msg) {
        const el = document.getElementById('errorMsg');
        el.textContent = msg;
        el.style.display = 'block';
    }
    function hideError() {
        document.getElementById('errorMsg').style.display = 'none';
    }

    // Event listeners on filters
    document.getElementById('filterStadt').addEventListener('change', loadData);
    document.getElementById('filterKampagne').addEventListener('change', loadData);
    document.getElementById('filterKategorie').addEventListener('change', loadData);

    // Initial load
    loadFiltersOptions();
</script>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
