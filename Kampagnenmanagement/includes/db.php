<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "crm_autovermietung";
require_once "includes/mail.php";

$conn = new mysqli($host, $user, $pass, $dbname);

// Verbindung prüfen
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}
?>