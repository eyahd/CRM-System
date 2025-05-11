<?php
class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        $dsn = 'mysql:host=localhost;dbname=kundenverwaltung;charset=utf8mb4';
        $user = 'root';
        $pass = '';

        try {
            $this->pdo = new PDO(dsn: $dsn, username: $user, password: $pass);
            $this->pdo->setAttribute(attribute: PDO::ATTR_ERRMODE, value: PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
        }
    }

    public static function getInstance(): mixed
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}
