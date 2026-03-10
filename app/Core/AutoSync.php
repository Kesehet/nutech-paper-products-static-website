<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use Throwable;

final class AutoSync
{
    private static bool $checked = false;

    public static function runIfRequired(): void
    {
        if (self::$checked) {
            return;
        }
        self::$checked = true;

        if (!config_bool('AUTO_SYNC_ON_BOOT', true)) {
            return;
        }

        if ((string) env('DB_CONNECTION', 'mysql') !== 'mysql') {
            return;
        }

        try {
            self::ensureDatabaseExists();
            $pdo = Database::connection();

            if (!self::shouldSync($pdo)) {
                return;
            }

            self::runSqlFile($pdo, BASE_PATH . '/database/schema.sql');
            self::runSqlFile($pdo, BASE_PATH . '/database/seeders/seed.sql');
        } catch (Throwable $exception) {
            self::log('AutoSync failed: ' . $exception->getMessage());
        }
    }

    private static function ensureDatabaseExists(): void
    {
        $database = (string) env('DB_DATABASE', '');
        if ($database === '') {
            throw new \RuntimeException('DB_DATABASE is empty.');
        }

        $serverPdo = Database::serverConnection();
        $quotedDb = str_replace('`', '``', $database);
        $charset = (string) env('DB_CHARSET', 'utf8mb4');

        $serverPdo->exec(sprintf(
            'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET %s COLLATE %s_unicode_ci',
            $quotedDb,
            $charset,
            $charset
        ));
    }

    private static function shouldSync(PDO $pdo): bool
    {
        $database = (string) env('DB_DATABASE', '');
        if ($database === '') {
            return false;
        }

        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema = :db_name AND table_name = :table_name'
        );
        $stmt->execute([
            'db_name' => $database,
            'table_name' => 'users',
        ]);
        $exists = (int) $stmt->fetchColumn() > 0;

        if (!$exists) {
            return true;
        }

        $userCountStmt = $pdo->query('SELECT COUNT(*) FROM users');
        $userCount = (int) $userCountStmt->fetchColumn();
        return $userCount === 0;
    }

    private static function runSqlFile(PDO $pdo, string $filePath): void
    {
        if (!is_file($filePath)) {
            throw new \RuntimeException('SQL file not found: ' . $filePath);
        }

        $sql = file_get_contents($filePath);
        if ($sql === false) {
            throw new \RuntimeException('Unable to read SQL file: ' . $filePath);
        }

        foreach (self::splitStatements($sql) as $statement) {
            $pdo->exec($statement);
        }
    }

    private static function splitStatements(string $sql): array
    {
        $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql) ?? $sql;
        $lines = preg_split('/\R/', $sql) ?: [];
        $cleaned = [];

        foreach ($lines as $line) {
            $trimmed = ltrim($line);
            if ($trimmed === '') {
                continue;
            }

            if (str_starts_with($trimmed, '--') || str_starts_with($trimmed, '#')) {
                continue;
            }

            $cleaned[] = $line;
        }

        $payload = implode("\n", $cleaned);
        $parts = explode(';', $payload);
        $statements = [];

        foreach ($parts as $part) {
            $statement = trim($part);
            if ($statement !== '') {
                $statements[] = $statement;
            }
        }

        return $statements;
    }

    private static function log(string $message): void
    {
        $logDir = BASE_PATH . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }

        $line = sprintf("[%s] %s\n", date('Y-m-d H:i:s'), $message);
        @file_put_contents($logDir . '/auto_sync.log', $line, FILE_APPEND);
    }
}

