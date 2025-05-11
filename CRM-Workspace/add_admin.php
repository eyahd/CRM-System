<?php
require_once 'db_config.php'; 
// Müssen für jeden Admin manuelll eingegeben werden!
$vorname = "admin"; // Vorname des Admins
$passwort_klar = "NichtErmittelbar!"; // Passwort des Admins (klartext)
$rolle = "admin"; 
$email = "admin@kundenverwaltung.de"; // E-Mail des Admins

// Passwort hashen
$passwort_hash = password_hash($passwort_klar, PASSWORD_DEFAULT);

try {
    $db = Database::getInstance()->getConnection();

    $sql = "INSERT INTO kunden (vorname, passwort_hash, rolle, email) 
            VALUES (:vorname, :passwort_hash, :rolle, :email)";
    
    $stmt = $db->prepare($sql);
    
    $stmt->bindParam(':vorname', $vorname);
    $stmt->bindParam(':passwort_hash', $passwort_hash);
    $stmt->bindParam(':rolle', $rolle);
    $stmt->bindParam(':email', $email);

    $stmt->execute();

    echo "Admin erfolgreich angelegt!";
} catch (PDOException $e) {
    echo "Fehler bei der Admin-Erstellung: " . $e->getMessage();
}
