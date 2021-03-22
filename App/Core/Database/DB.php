<?php declare(strict_types=1);

namespace App\Core\Database;

use PDO;

class DB extends SingletonDB
{
    public function __construct(){}
    public function pdo(string $dsn, string $user, string $password): PDO {
        return new PDO($dsn, $user, $password);
    }
}