<?php

namespace App\Database;

use PDO;
use PDOException;

class Database
{
    private PDO $connection;

    public function __construct(
        string $host,
        string $database,
        string $username,
        string $password
    ) {
        $dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";

        $this->connection = new PDO(
            $dsn,
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}