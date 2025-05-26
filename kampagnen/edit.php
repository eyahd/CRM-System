<?php
require 'db.php';

$id = $_GET['id'] ?? null;

if (!$id) {
  die("Ungültige ID.");
}

$stmt = $conn->prepare("SELECT * FROM kampagnen WHERE id = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$campaign = $result->fetch_assoc();

if (!$campaign) {
  die("Kampagne nicht gefunden.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = $_POST['title'];
  $start = $_POST['start_date'];
  $end = $_POST['end_date'];
  $status = $_POST['status'];
  $category = $_POST['category'];

  $stmt = $conn->prepare("UPDATE campaigns SET title=?, start_date=?, end_date=?, status=?, category=? WHERE id=?");
  $stmt->bind_param("sssssi", $title, $start, $end, $status, $category, $id);
  $stmt->execute();

  header("Location: track.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>Kampagne bearbeiten</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      background: linear-gradient(-45deg, #f6d365, #fda085, #fbc2eb, #a6c1ee);
      background-size: 400% 400%;
      animation: gradientBG 10s ease infinite;
    }

    @keyframes gradientBG {
      0% {background-position: 0% 50%;}
      50% {background-position: 100% 50%;}
      100% {background-position: 0% 50%;}
    }

    .card {
      background: white;
      padding: 2rem 2.5rem;
      border-radius: 20px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 500px;
    }

    h1 {
      text-align: center;
      color: #333;
      margin-bottom: 1.5rem;
    }

    label {
      display: block;
      margin-bottom: 1rem;
      color: #444;
    }

    input, select {
      width: 100%;
      padding: 0.7rem;
      margin-top: 0.3rem;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 1rem;
      transition: border 0.3s;
    }

    input:focus, select:focus {
      border-color: #7f5af0;
      outline: none;
    }

    button {
      display: block;
      width: 100%;
      padding: 0.9rem;
      font-size: 1rem;
      background: #7f5af0;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      margin-top: 1.2rem;
      transition: background 0.3s ease;
    }

    button:hover {
      background: #5e3ed7;
    }

    .back-link {
      position: fixed;
      bottom: 30px;
      left: 30px;
      background: #fef08a;
      padding: 0.6rem 1rem;
      border-radius: 50px;
      font-size: 0.9rem;
      color: #444;
      text-decoration: none;
      box-shadow: 0 4px 14px rgba(0,0,0,0.15);
      transition: transform 0.3s ease;
    }

    .back-link:hover {
      transform: translateY(-3px);
    }

  </style>
</head>
<body>
  <div class="card">
    <h1>Kampagne bearbeiten</h1>
    <form method="POST">
      <label>Titel:
        <input type="text" name="title" value="<?= htmlspecialchars($campaign['title'] ?? '') ?>">
      </label>
      <label>Startdatum:
        <input type="date" name="start_date" value="<?= htmlspecialchars($campaign['start_date'] ?? '') ?>">
      </label>
      <label>Enddatum:
        <input type="date" name="end_date" value="<?= htmlspecialchars($campaign['end_date'] ?? '') ?>">
      </label>
      <label>Status:
        <select name="status">
          <option value="Aktiv" <?= ($campaign['status'] ?? '') == 'Aktiv' ? 'selected' : '' ?>>Aktiv</option>
          <option value="Geplant" <?= ($campaign['status'] ?? '') == 'Geplant' ? 'selected' : '' ?>>Geplant</option>
          <option value="Abgelaufen" <?= ($campaign['status'] ?? '') == 'Abgelaufen' ? 'selected' : '' ?>>Abgelaufen</option>
        </select>
      </label>
      <label>Kategorie:
        <select name="category">
          <option value="rabatt" <?= ($campaign['category'] ?? '') == 'rabatt' ? 'selected' : '' ?>>Rabatt</option>
          <option value="werbung" <?= ($campaign['category'] ?? '') == 'werbung' ? 'selected' : '' ?>>Werbung</option>
          <option value="event" <?= ($campaign['category'] ?? '') == 'event' ? 'selected' : '' ?>>Event</option>
        </select>
      </label>
      <button type="submit">💾 Speichern</button>
    </form>
  </div>

  <a class="back-link" href="track.php">← Zurück</a>
</body>
</html>
