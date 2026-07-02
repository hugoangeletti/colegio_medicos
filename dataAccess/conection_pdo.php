<?php
class Database {
    private static $instancia = null;
    private $pdo;

    private function __construct() {
        //$host    = '192.168.2.50';
        //$db      = 'pruebas_colmed1';
        //$user    = 'hugo';
        //$pass    = 'hugo';
        $host    = 'localhost';
        $db      = 'colmed1';
        $user    = 'root';
        $pass    = '';
        $charset = 'utf8';
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
            $this->pdo->exec("SET SESSION group_concat_max_len = 1000000"); // <-- una sola vez
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    // Método estático para obtener la conexión
    public static function getConnection() {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia->pdo;
    }
}
