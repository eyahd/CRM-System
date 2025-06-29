<?php
header('Content-Type: application/json');
$pdo = new PDO('mysql:host=localhost;dbname=crm_autovermietung', 'root', '');


$totalUmsatz = (float) $pdo->query("SELECT SUM(umsatz) AS totalUmsatz FROM kundendatenbank")->fetch()['totalUmsatz'];


$avgBonitaet = $pdo->query("SELECT AVG(bonität) AS avgBonitaet FROM kundendatenbank")->fetch()['avgBonitaet'];

$avgMietdauer = $pdo->query("SELECT AVG(mietdauer) AS avgMietdauer FROM kundendatenbank")->fetch()['avgMietdauer'];


$bookings = $pdo->query("SELECT COUNT(*) AS count FROM kundendatenbank")->fetch()['count'];


$topOrte = $pdo->query("
    SELECT ort, SUM(umsatz) AS total
    FROM kundendatenbank
    GROUP BY ort
    ORDER BY total DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);


$topCities = array_map(function($city) {
    return [
        "stadt" => $city['ort'],
        "rentals" => round((float)$city['total'], 2)
    ];
}, $topOrte);


$topCategories = [
    ["fahrzeugkategorie" => "SUV", "revenue" => 2450.50],
    ["fahrzeugkategorie" => "Cabrio", "revenue" => 1880.00],
    ["fahrzeugkategorie" => "Kompakt", "revenue" => 1450.00]
];


$campaigns = [
    ["kampagne" => "Sommer", "rentals" => 120],
    ["kampagne" => "Neueröffnung", "rentals" => 35],
    ["kampagne" => "Weekend Getaway", "rentals" => 50]
];



$fleetUtilization = 56;
$revenuePerDay = round($totalUmsatz / 90, 2);


$response = [
    "fleetUtilization" => $fleetUtilization,
    "avgRentalDuration" => round($avgMietdauer, 1),
    "revenuePerDay" => $revenuePerDay,
    "bookings" => (int)$bookings,
    "topCities" => $topCities,
    "topCategories" => $topCategories,
    "campaigns" => $campaigns
];

echo json_encode($response);
