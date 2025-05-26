<?php
require 'db.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    die("Keine ID angegeben.");
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
    die("Kampagne mit dieser ID wurde nicht gefunden.");
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>Kampagne löschen</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Roboto', sans-serif;
      background: linear-gradient(135deg, #f9f9f9, #ffe0e9);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .container {
      background: white;
      padding: 2rem 3rem;
      border-radius: 16px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      text-align: center;
      max-width: 400px;
    }

    h1 {
      font-size: 1.5rem;
      color: #d00000;
      margin-bottom: 1rem;
    }

    p {
      font-size: 1rem;
      margin-bottom: 2rem;
    }

    .button-group {
      display: flex;
      justify-content: space-between;
      gap: 1rem;
    }

    button, a {
      padding: 0.6rem 1.2rem;
      border: none;
      border-radius: 8px;
      text-decoration: none;
      font-weight: bold;
      cursor: pointer;
      font-size: 1rem;
    }

    .delete-btn {
      background-color: #ff4d4f;
      color: white;
    }

    .cancel-btn {
      background-color: #e0e0e0;
      color: #333;
    }

    .delete-btn:hover {
      background-color: #d9363e;
    }

    .cancel-btn:hover {
      background-color: #ccc;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Kampagne löschen?</h1>
    <p>Bist du sicher, dass du die Kampagne  löschen möchtest?</p>
    
    <form method="POST" class="button-group">
      <button type="submit" class="delete-btn">Ja, löschen</button>
      <a href="track.php" class="cancel-btn">Abbrechen</a>
    </form>
  </div>
</body>
</html>


<?php
// Delete if confirmed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['confirm'] === 'yes') {
    $deleteStmt = $conn->prepare("DELETE FROM kampagnen WHERE id = ?");
    $deleteStmt->bind_param("i", $id);
    $deleteStmt->execute();

    header("Location: track.php");
    exit;
}
?>
