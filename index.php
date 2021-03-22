<?php 
declare(strict_types=1);
require_once __DIR__.'/vendor/autoload.php';

use App\Controller\DatabaseController;
use App\Core\Database\DB;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['DB_HOST'];
$dbname   = $_ENV['DB_DATABASE'];
$user = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];
$port = $_ENV['DB_PORT'];
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset;port=$port";
$filepath = $_ENV['FILE_PATH'];

$pdo = DB::init()->pdo($dsn, $user, $password);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

$databaseController = new DatabaseController($pdo);

