<?php
/**
 * Class Database
 * Singleton pattern untuk koneksi PDO ke MySQL.
 *
 * Kredensial dibaca dari environment variable (dipakai oleh Docker):
 *   DB_HOST, DB_NAME, DB_USER, DB_PASS
 * Jika tidak ada (misal jalan di XAMPP), dipakai nilai default localhost/root.
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private string $host;
    private string $dbname;
    private string $username;
    private string $password;

    private function __construct()
    {
        $this->host     = getenv('DB_HOST') ?: 'localhost';
        $this->dbname   = getenv('DB_NAME') ?: 'si_mahasiswa';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';

        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
            $this->pdo = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            die("Koneksi gagal: " . $e->getMessage());
        }
    }

    public static function getInstance(): Database
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
