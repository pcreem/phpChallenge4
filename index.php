<?php 
declare(strict_types=1);
require_once __DIR__.'/vendor/autoload.php';

use App\Controller\DatabaseController;
use App\Core\Database\ConnectDB;
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

$pdo = ConnectDB::getInstance($host, $user, $password, $dbname)->getConnection();
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

$databaseController = new DatabaseController($pdo);

do {
    $command = strtolower(readline('Import data to database?[y/n/quit]: '));
    switch ($command) {
        case 'y':
        case 'yes':
            $askAgain = false;
            try {
                $databaseController->importData();
            } catch (Exception $e) {
                echo $e->getMessage();
                die();
            }
            break;
        case 'n':
        case 'no':
            $askAgain = false;
            break;
        case 'q':
        case 'quit':
            die();
        default:
            $askAgain = true;
            break;
    }
} while ($askAgain);

statistics:
echo '=================================='.PHP_EOL;
do {
    try {
        $districts = $databaseController->showDistricts();
    } catch (Exception $e) {
        die("DB error!!");
    }
    $command = strtolower(readline('Choose a data to display?'.PHP_EOL.'1:Show Districts 2:Rainfall statistics [1/2/quit]: '));
    switch ($command) {
        case '1':
            $askAgain = true;
            var_export($districts);
            echo PHP_EOL.'=================================='.PHP_EOL;
            break;
        case '2':
            goto totalRainfall;
            break;
        case 'q':
        case 'quit':
            die();
        default:
            $askAgain = true;
            break;
    }
} while ($askAgain);


totalRainfall:
echo '=================================='.PHP_EOL;
do {
    $command = strtolower(readline('Choose a data to display:'.PHP_EOL.'1:Yearly Rainfall 2:Monthly Rainfall [1/2/back/quit]: '));
    switch ($command) {
        case '1':
            $sumBy = 'year';
            goto selectArea;
            break;
        case '2':
            $sumBy = 'month';
            goto selectArea;
            break;
        case 'b':
        case 'back':
            goto statistics;
            break;
        case 'q':
        case 'quit':
            die();
        default:
            $askAgain = true;
            break;
    }
} while ($askAgain);

selectArea:
echo '=================================='.PHP_EOL;
do {
    $command = strtolower(readline('Choose Districts:'.PHP_EOL.'1:All Districts 2:Specific Districts [1/2/back/quit]: '));
    switch ($command) {
        case '1':
            if ($sumBy === 'year') var_export($databaseController->sumByYear());
            if ($sumBy === 'month') var_export($databaseController->sumByMonth());
            goto totalRainfall;
            break;
        case '2':
            goto selectDistricts;
            break;
        case 'b':
        case 'back':
            goto statistics;
            break;
        case 'q':
        case 'quit':
            die();
        default:
            $askAgain = true;
            break;
    }
} while ($askAgain);

selectDistricts:
echo '=================================='.PHP_EOL;
var_export($districts);
echo PHP_EOL;
do {
    $command = strtolower(readline('Choose a specific district? [0~'.(count($districts) - 1).'/back/quit]: '));
    switch ($command) {
        case 'b':
        case 'back':
            goto selectArea;
            break;
        case 'q':
        case 'quit':
            die();
        default:
            if (!in_array($command, array_keys($districts))) {
                $askAgain = true;
                break;
            }
            if ($sumBy === 'year') var_export($databaseController->sumByYear($command));
            if ($sumBy === 'month') var_export($databaseController->sumByMonth($command));
            goto totalRainfall;
            break;
    }
} while ($askAgain);
