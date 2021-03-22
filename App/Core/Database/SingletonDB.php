<?php


namespace App\Core\Database;

use PDO;
 
abstract class SingletonDB
{
    private static $instance = null;

    abstract  function __construct();

    public static function init()
    {
        if (!self::$instance) {
            self::$instance = new static;
        }
        return self::$instance;
    }

    abstract function pdo(string $dsn, string $user, string $password): PDO;
}