<?php
namespace App\Core\Database;

use PDO;

class DB extends SingletonDB
{
    // Hold the class instance.
    private static $instance = null;
    private $conn;
    
    // The db connection is established in the private constructor.
    private function __construct($host, $user, $pass, $name)
    {
      $this->conn = new PDO("mysql:host={$host};
      dbname={$name}", $user,$pass,
      array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    }
    
    public static function init()
    {
      if(!self::$instance)
      {
        self::$instance = new DB;
      }
     
      return self::$instance;
    }

    public function pdo(): PDO{
        return $this->conn;
    }        
}