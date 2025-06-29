<?php
require_once '../db.php';

$kampagne = $_GET['kampagne'] ?? '';
if (!$kampagne) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing campaign']);
    exit;
}

$sql = "
    SELECT DATE(b.verleihbeginn) as datum, COUNT(*) as anzahl, SUM(b.gesamtpreis) as umsatz
    FROM buchung b
    JOIN fahrzeug f ON b.fahrzeugID = f.fahrzeugID
    WHERE f.kampagne = ?
    GROUP BY datum
    ORDER BY datum ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$kampagne]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($data);
