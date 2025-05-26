<?php
require 'db.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=kampagnen.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Titel', 'Startdatum', 'Enddatum', 'Status', 'Kategorie']);

$result = $conn->query("SELECT * FROM kampagnen");
while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}
fclose($output);
exit;
?>
