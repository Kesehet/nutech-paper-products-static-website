<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $pdo = null;
    private static ?PDO $serverPdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $driver = (string) env('DB_CONNECTION', 'mysql');
        $host = (string) env('DB_HOST', '127.0.0.1');
        $port = (string) env('DB_PORT', '3306');
        $database = (string) env('DB_DATABASE', '');
        $charset = (string) env('DB_CHARSET', 'utf8mb4');
        $username = (string) env('DB_USERNAME', '');
        $password = (string) env('DB_PASSWORD', '');

        $dsn = self::buildDsn($driver, $host, $port, $charset, $database);

        try {
            self::$pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            throw new PDOException('Database connection failed: ' . $exception->getMessage(), (int) $exception->getCode(), $exception);
        }

        return self::$pdo;
    }

    public static function serverConnection(): PDO
    {
        if (self::$serverPdo instanceof PDO) {
            return self::$serverPdo;
        }

        $driver = (string) env('DB_CONNECTION', 'mysql');
        $host = (string) env('DB_HOST', '127.0.0.1');
        $port = (string) env('DB_PORT', '3306');
        $charset = (string) env('DB_CHARSET', 'utf8mb4');
        $username = (string) env('DB_USERNAME', '');
        $password = (string) env('DB_PASSWORD', '');

        $dsn = self::buildDsn($driver, $host, $port, $charset, null);

        try {
            self::$serverPdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            throw new PDOException('Database server connection failed: ' . $exception->getMessage(), (int) $exception->getCode(), $exception);
        }

        return self::$serverPdo;
    }

    private static function buildDsn(string $driver, string $host, string $port, string $charset, ?string $database): string
    {
        if ($database !== null && $database !== '') {
            return sprintf('%s:host=%s;port=%s;dbname=%s;charset=%s', $driver, $host, $port, $database, $charset);
        }

        return sprintf('%s:host=%s;port=%s;charset=%s', $driver, $host, $port, $charset);
    }
}
