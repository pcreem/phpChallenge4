<?php
namespace App\Core\Database;
use PDO;

class ConnectDB {
  private static $instance = null;
  private $conn;
  
  private function __construct($host, $user, $pass, $name)
  {
    $this->conn = new PDO("mysql:host={$host};
    dbname={$name}", $user,$pass,
    array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
  }
  
  public static function getInstance($host, $user, $pass, $name)
  {
    if(!self::$instance)
    {
      self::$instance = new ConnectDB($host, $user, $pass, $name);
    }
    return self::$instance;
  }
  
  public function getConnection()
  {
    return $this->conn;
  }
}

  