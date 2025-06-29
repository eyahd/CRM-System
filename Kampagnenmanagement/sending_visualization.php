 <?php
//require_once "includes/db.php";
//require 'vendor/autoload.php'; // PHPMailer (if used)

// DB connection
$conn = new mysqli("localhost", "root", "", "crm_autovermietung");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Sanitize input
$segment = isset($_GET['segment']) ? trim($_GET['segment']) : '';

if ($segment) {
    $stmt = $conn->prepare("SELECT email FROM kategorie WHERE segment = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $segment);
    $stmt->execute();
    $result = $stmt->get_result();

    $emails = [];
    while ($row = $result->fetch_assoc()) {
        $emails[] = $row['email'];
    }
    $stmt->close();

    if (count($emails) === 0) {
        echo "Keine E-Mails für das Segment gefunden.";
        exit();
    }
} else {
    echo "Bitte wählen Sie ein gültiges Segment aus.";
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <title>Kampagne wird gesendet...</title>
  <style>
    /* your existing CSS */
    body {
      font-family: Arial, sans-serif;
      background: linear-gradient(to right, #f0f2f0, #000c40);
      color: #fff;
      text-align: center;
      padding: 2rem;
    }
    h1 {
      font-size: 2rem;
      margin-bottom: 1rem;
    }
    #emailList {
      max-width: 500px;
      margin: 2rem auto;
      text-align: left;
      background: #1e1e2f;
      padding: 1rem;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.4);
    }
    .email-item {
      padding: 0.5rem;
      border-bottom: 1px solid #333;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .status {
      font-size: 1rem;
      color: #999;
    }
    .tick {
      color: #4caf50;
      font-weight: bold;
      display: none;
    }
    .loading {
      width: 15px;
      height: 15px;
      border: 2px solid #ccc;
      border-top: 2px solid #4caf50;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin-left: auto;
    }
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    #done {
      display: none;
      font-size: 1.3rem;
      margin-top: 2rem;
      color: #00ff99;
    }
  </style>
</head>
<body>

<h1>📤 Kampagne wird versendet...</h1>
<p>Bitte warten Sie, während wir Ihre Kampagne an die Kunden senden.</p>

<?php if (!empty($emails)): ?>
  <div id="emailList">
    <?php foreach ($emails as $index => $email): ?>
      <div class="email-item" id="email-<?= $index ?>">
        <?= htmlspecialchars($email) ?>
        <span class="status">
          <span class="loading"></span>
          <span class="tick">✔️ Gesendet</span>
        </span>
      </div>
    <?php endforeach; ?>
  </div>

  <div id="done">🎉 Alle E-Mails wurden erfolgreich versendet!</div>

  <script>
    const emails = <?= json_encode($emails, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    let current = 0;

    function sendNextEmail() {
      if (current >= emails.length) {
        document.getElementById("done").style.display = "block";
        // Redirect to uebersicht.php (avoid umlaut in URL)
        setTimeout(() => {
          window.location.href = "übersicht.php";
        }, 2000);
        return;
      }

      const row = document.getElementById("email-" + current);
      const loading = row.querySelector(".loading");
      const tick = row.querySelector(".tick");

      setTimeout(() => {
        loading.style.display = "none";
        tick.style.display = "inline";
        current++;
        sendNextEmail();
      }, 1500);
    }

    window.onload = () => {
      sendNextEmail();
    };
  </script>

<?php else: ?>
  <p>Keine E-Mails für das ausgewählte Segment gefunden.</p>
<?php endif; ?>

</body>
</html>