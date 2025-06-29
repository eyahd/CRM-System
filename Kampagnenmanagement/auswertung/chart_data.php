<?php
header('Content-Type: application/json');

try {
    $host = 'localhost';
    $db = 'crm_autovermietung';
    $user = 'root';
    $pass = '';

    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stadt = $_GET['stadt'] ?? '';
    $kampagne = $_GET['kampagne'] ?? '';
    $kategorie = $_GET['kategorie'] ?? '';

    $filters = [];
    $clauses = ["1=1"];

    if ($stadt !== '') {
        $clauses[] = "stadt = :stadt";
        $filters[':stadt'] = $stadt;
    }
    if ($kampagne !== '') {
        $clauses[] = "kampagne = :kampagne";
        $filters[':kampagne'] = $kampagne;
    }
    if ($kategorie !== '') {
        $clauses[] = "fahrzeugkategorie = :kategorie";
        $filters[':kategorie'] = $kategorie;
    }

    $where = implode(' AND ', $clauses);

   $sql = "
  SELECT fahrzeugkategorie AS category, COUNT(*) AS conversions
  FROM mietvertraege
  WHERE $where
  GROUP BY fahrzeugkategorie
";

    $stmt = $conn->prepare($sql);
    $stmt->execute($filters);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

