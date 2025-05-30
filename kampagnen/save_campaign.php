<?php
require 'db.php';
require 'vendor/autoload.php'; // include PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Get form values
$kampagnentyp = $_POST['kampagnentyp'];
$beschreibung = $_POST['beschreibung'];
$zielgruppe_typ = $_POST['zielgruppe_typ'];
$kategorie = isset($_POST['kategorie']) ? $_POST['kategorie'] : null;
$kunden_id = isset($_POST['kunden_id']) ? (int)$_POST['kunden_id'] : null;
$email = $_POST['email'] ?? null;
$startdatum = $_POST['startdatum'];
$enddatum = $_POST['enddatum'];
$nachricht = $_POST['nachricht'];
$action = $_POST['action']; // 'save_only' or 'save_send'

$conn = new mysqli("localhost", "root", "", "kampagnen_db");
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

if (strtotime($startdatum) > strtotime($enddatum)) {
    die("Fehler: Enddatum darf nicht vor dem Startdatum liegen.");
}

// 1. Save campaign
$stmt = $conn->prepare("INSERT INTO kampagnen (kampagnentyp, beschreibung, zielgruppe_typ, kategorie, kunden_id, email, startdatum, enddatum, nachricht) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssissss", $kampagnentyp, $beschreibung, $zielgruppe_typ, $kategorie, $kunden_id, $email, $startdatum, $enddatum, $nachricht);
$stmt->execute();
$stmt->close();

// 2. Send campaign if selected
if ($action === 'save_send') {
    if ($zielgruppe_typ === 'gruppe' && $kategorie) {
        $kunden_query = $conn->prepare("SELECT email FROM kunden WHERE kategorie = ?");
        $kunden_query->bind_param("s", $kategorie);
        $kunden_query->execute();
        $result = $kunden_query->get_result();

        while ($row = $result->fetch_assoc()) {
            sendEmailPHPMailer($row['email'], $nachricht);
        }
        $kunden_query->close();

    } elseif ($zielgruppe_typ === 'individuell') {
        if (!empty($email)) {
            sendEmailPHPMailer($email, $nachricht);
        } elseif (!empty($kunden_id)) {
            $kunden_query = $conn->prepare("SELECT email FROM kunden WHERE id = ?");
            $kunden_query->bind_param("s", $kunden_id);
            $kunden_query->execute();
            $result = $kunden_query->get_result();
            if ($row = $result->fetch_assoc()) {
                sendEmailPHPMailer($row['email'], $nachricht);
            }
            $kunden_query->close();
        }
    }
}

$conn->close();

// Redirect to success page
header("Location: sent.html");
exit;


// -- PHPMailer function
function sendEmailPHPMailer($to, $message) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.ethereal.email';  // your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'peyton.johnston45@ethereal.email';  // your email
        $mail->Password = 'm5gHMaC46AsUf2n9X5';    // your password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Email content
        $mail->setFrom('you@example.com', 'Your Company');
        $mail->addAddress($to);
        $mail->Subject = 'Neue Kampagne';
        $mail->Body = $message;

        $mail->send();
        echo "<br>Nachricht erfolgreich gesendet an: $to<br>";
    } catch (Exception $e) {
    // Optional: log errors or send to admin
    header("Location: error.html");
    exit;
}

}
?>
