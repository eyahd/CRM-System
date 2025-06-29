<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Car Rental Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet" />
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background: linear-gradient(to right, #f4f7fe, #e9f2ff);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      color: #2c3e50;
      padding: 2rem;
    }

    .dashboard {
      background: white;
      padding: 3rem;
      border-radius: 20px;
      max-width: 1000px;
      width: 100%;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
      text-align: center;
    }

    h1 {
      font-size: 2.5rem;
      font-weight: 600;
      color: #1e3a8a;
      margin-bottom: 2rem;
    }

    .dashboard-links {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 1.5rem;
    }

    .btn-link {
      background-color: #1e3a8a;
      color: white;
      padding: 1.2rem 1rem;
      border-radius: 12px;
      font-weight: 500;
      text-decoration: none;
      transition: all 0.3s ease;
      box-shadow: 0 6px 20px rgba(30, 58, 138, 0.2);
    }

    .btn-link:hover {
      background-color: #3b82f6;
      transform: translateY(-3px);
    }

    footer {
      margin-top: 3rem;
      font-size: 0.85rem;
      color: #777;
    }
  </style>
</head>
<body>

  <div class="dashboard">
    <h1>Car Rental Dashboard</h1>

    <div class="dashboard-links">
      <a href="map_view.php" target="_blank" class="btn-link">Umsatz Bubble Map</a>
      <a href="live_tracker.php" target="_blank" class="btn-link">Live Tracker</a>
      <a href="kpi_dashboard.html" target="_blank" class="btn-link">Rental KPIs</a>
      <a href="Click & Convert.php" target="_blank" class="btn-link">Click & Convert</a>
      <a href="empfehlungen.php" target="_blank" class="btn-link">Empfehlungen</a>
    </div>

    <footer>© 2025 Citycar24 — Klar, Schnell, Intuitiv.</footer>
  </div>

</body>
</html>
