<?php

namespace App;

use PDO;
use PDOException;

/**
 * Database — Singleton PDO connection.
 * Lê credenciais do .env carregado no bootstrap.php.
 */
class Database
{
    private static ?PDO $instance = null;

    public static function connection(): PDO
    {
        if (self::$instance === null) {
            $host    = $_ENV['DB_HOST']     ?? 'localhost';
            $port    = $_ENV['DB_PORT']     ?? '3306';
            $user    = $_ENV['DB_USER']     ?? 'root';
            $pass    = $_ENV['DB_PASSWORD'] ?? '';
            $dbname  = $_ENV['DB_NAME']     ?? 'pesquisaia';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                error_log('[Database] Connection failed: ' . $e->getMessage());
                die(json_encode(['error' => 'Database connection failed.']));
            }
        }

        return self::$instance;
    }

    /** Shorthand para a conexão PDO. */
    public static function pdo(): PDO
    {
        return self::connection();
    }
}
