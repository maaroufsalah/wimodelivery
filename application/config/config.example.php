<?php

// Copier ce fichier en config.php et remplir les valeurs
// cp application/config/config.example.php application/config/config.php

final class Database {
    private static ?Database $instance = null;
    private ?PDO $connection = null;

    private string $DBHOST = "localhost";       // Ex: 127.0.0.1 ou nom du host MariaDB
    private string $DBUSER = "root";            // Utilisateur MariaDB
    private string $DBNAME = "wimodeli_data";   // Nom de la base de données
    private string $DBPASSWORD = "";            // Mot de passe MariaDB

    // جعل الـ constructor غير قابل للوصول من الخارج
    private function __construct() {
        $dsn = "mysql:host={$this->DBHOST};dbname={$this->DBNAME};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        ];

        try {
            $this->connection = new PDO($dsn, $this->DBUSER, $this->DBPASSWORD, $options);
        } catch (PDOException $e) {
            die('فشل الاتصال بقاعدة البيانات: ' . $e->getMessage());
        }
    }

    // الحصول على الكائن الوحيد
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database(); // إنشاء الكائن لأول مرة
        }
        return self::$instance;
    }

    // الحصول على الاتصال
    public function getConnection(): ?PDO {
        return $this->connection;
    }
}



$con = Database::getInstance()->getConnection();
if ($con === null) {
    die('فشل الاتصال بقاعدة البيانات.');
}
