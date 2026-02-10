<?php
/**
 * Database Connection Manager
 */

declare(strict_types=1);

namespace System;

use PDO;
use PDOStatement;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private static ?Database $remoteInstance = null;
    private ?PDO $pdo = null;

    private function __construct(string $type = 'local')
    {
        $this->connect($type);
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self('local');
        }
        return self::$instance;
    }

    public static function getRemoteInstance(): Database
    {
        if (self::$remoteInstance === null) {
            self::$remoteInstance = new self('remote');
        }
        return self::$remoteInstance;
    }

    private function connect(string $type = 'local'): void
    {
        if ($type === 'remote') {
            $host = $_ENV['REMOTE_DB_HOST'] ?? '';
            $port = $_ENV['REMOTE_DB_PORT'] ?? '3306';
            $database = $_ENV['REMOTE_DB_DATABASE'] ?? '';
            $username = $_ENV['REMOTE_DB_USERNAME'] ?? '';
            $password = $_ENV['REMOTE_DB_PASSWORD'] ?? '';

            // Debug logging for remote connection
            Logger::info("Remote DB Connection Attempt", [
                'host' => $host,
                'port' => $port,
                'database' => $database,
                'username' => $username,
                'password_length' => strlen($password),
                'password_last_3' => substr($password, -3)
            ]);
        } else {
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $port = $_ENV['DB_PORT'] ?? '3306';
            $database = $_ENV['DB_DATABASE'] ?? '';
            $username = $_ENV['DB_USERNAME'] ?? '';
            $password = $_ENV['DB_PASSWORD'] ?? '';
        }

        if (empty($database))
            return;

        try {
            $dsn = sprintf(
                '%s:host=%s;port=%s;dbname=%s;charset=%s',
                $_ENV['DB_DRIVER'] ?? 'mysql',
                $host,
                $port,
                $database,
                $_ENV['DB_CHARSET'] ?? 'utf8mb4'
            );

            $this->pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true, // Enable persistent connections
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                PDO::ATTR_TIMEOUT => 5, // 5 second timeout
            ]);
        } catch (PDOException $e) {
            Logger::error("Database connection ($type) failed", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getPdo(): ?PDO
    {
        return $this->pdo;
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params)->fetch();
        return $result ?: null;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetchColumn(string $sql, array $params = [], int $column = 0): mixed
    {
        return $this->query($sql, $params)->fetchColumn($column);
    }

    public function insert(string $table, array $data): int|string
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = sprintf(
            'INSERT INTO `%s` (`%s`) VALUES (%s)',
            $table,
            implode('`, `', $columns),
            implode(', ', $placeholders)
        );

        $this->query($sql, array_values($data));
        return $this->pdo->lastInsertId();
    }

    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $setParts = [];
        $values = [];

        foreach ($data as $column => $value) {
            $setParts[] = "`$column` = ?";
            $values[] = $value;
        }

        $sql = sprintf('UPDATE `%s` SET %s WHERE %s', $table, implode(', ', $setParts), $where);
        return $this->query($sql, array_merge($values, $whereParams))->rowCount();
    }

    public function delete(string $table, string $where, array $params = []): int
    {
        return $this->query("DELETE FROM `$table` WHERE $where", $params)->rowCount();
    }

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }

    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }

    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
}

function db(): Database
{
    return Database::getInstance();
}
