<?php
header('Content-Type: application/json');

$host = 'localhost';
$db = 'crm_autovermietung';
$user = 'root';
$pass = '';

$conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
;

function fetchDistinctValues(PDO $conn, string $column): array {
    $allowed = ['stadt', 'kampagne', 'fahrzeugkategorie'];
    if (!in_array($column, $allowed)) return [];

    $stmt = $conn->prepare("
        SELECT DISTINCT $column 
        FROM mietvertraege
        WHERE $column IS NOT NULL AND TRIM($column) <> ''
        ORDER BY $column ASC
    ");

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

echo json_encode([
    'stadt'     => fetchDistinctValues($conn, 'stadt'),
    'kampagne'  => fetchDistinctValues($conn, 'kampagne'),
    'kategorie' => fetchDistinctValues($conn, 'fahrzeugkategorie'),
]);
