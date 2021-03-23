<?php declare(strict_types=1);

namespace App\Controller;

use App\Core\Database\RainfallSchema;
use App\Core\Database\CollectData;
use PDO;

class DatabaseController implements RainfallSchema, CollectData
{
    public $pdo;

    public function __construct($pdo){
        $this->pdo = $pdo;
        // $this->createDistrictsTable();
        // $this->createRainfallsTable();
        // $this->importData();
    }

    public function createDistrictsTable(){
        $this->pdo->query('
        CREATE TABLE Districts
        (
            id int,
            name varchar(255)
        )');
    }

    public function createRainfallsTable(){
        $this->pdo->query('
        CREATE TABLE Rainfalls (
            id int NOT NULL AUTO_INCREMENT,
            year int NOT NULL,
            month int NOT NULL,
            day int NOT NULL,
            time varchar(255) NOT NULL,
            rainfall float NOT NULL,
            districtsID int NOT NULL,
            PRIMARY KEY (id)
        )
        ');
    }

    public function importData(){
        function arrayFindKey(string $district, array $array):int {
            $expr = '/' . $district . '/';
            foreach ($array as $key => $value) {
              if (preg_match($expr, $value)) {
                return $key;
              }
            }
        }
    
        function convertDatetime(string $date, int $a, $b = null) :int{
            return intval(substr($date, $a, $b));
        }
        
        try {
            $this->pdo->query('TRUNCATE TABLE Districts');
            $this->pdo->query('TRUNCATE TABLE Rainfalls');
    
            $sql = 'INSERT INTO Districts VALUES(:id, :name)';
            $stmt = $this->pdo->prepare($sql);
    
            foreach(CollectData::BASE_DISTRICTS as $id=>$name){
                $stmt->execute(['id' => $id, 'name' => $name]);
                // echo "$id => $name \n";
            };
    
            $sql = 'INSERT INTO Rainfalls(year, month, day, time, rainfall, districtsID) VALUES(:year, :month, :day, :time, :rainfall, :districtsID)';
            $stmt = $this->pdo->prepare($sql);
    
            foreach (glob("./rainfallData/*.json") as $filepath) {
                $filecontentStr = file_get_contents($filepath);
                $filecontentArr = json_decode($filecontentStr,true);
                $filelength = count($filecontentArr);
                $loading = 0;
                
                $filename = basename($filepath, ".json");
                $districtname = substr($filename,-6);
                $districtID = arrayFindKey($districtname, CollectData::BASE_DISTRICTS);
    
                echo "Loading $districtname\n";
     
                foreach($filecontentArr as $datetime => $rainfall){
                    $year = convertDatetime($datetime,0,4);
                    $month = convertDatetime($datetime,5,2);
                    $day = convertDatetime($datetime,8,2);
                    $time = substr($datetime,-8);
    
                    $stmt->execute(['year' => $year,'month' => $month,'day' => $day, 'time' => $time, 'rainfall' => $rainfall, 'districtsID' => $districtID]);
                    
                    $loading++;
                    $loadingProgress = $loading / $filelength;        
                    echo $loadingProgress === 1 ? "$districtname load finished\n" : null;
                }
        
            }
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public function showDistricts(): array { return CollectData::BASE_DISTRICTS; }

    public function sumByYear($district = null): array{ 
        if ($district){
            try{
                $district = (int)$district;
                $sql='
                    SELECT D.id, D.name, R.year as Year, SUM(R.rainfall) as Rainfall                     
                    FROM Districts D                      
                    INNER JOIN Rainfalls R                      
                    ON D.id = R.districtsID 
                    WHERE R.districtsID = :districtsID 
                    GROUP BY D.id, D.name, R.year                     
                    ORDER BY D.id ASC                
                ';
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(['districtsID' => $district]);
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return $result;                
            }
            catch (\PDOException $e) {
                throw new \PDOException($e->getMessage(), (int)$e->getCode());
            }
        }
        else{
            try{
                
                $sql='
                    SELECT D.id, D.name, R.year as Year, SUM(R.rainfall) as Rainfall
                    FROM Districts D 
                    INNER JOIN Rainfalls R 
                    ON D.id = R.districtsID
                    GROUP BY D.id, D.name, R.year
                    ORDER BY D.id ASC
                ';
                $stmt = $this->pdo->query($sql);
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return $result;                
            }
            catch (\PDOException $e) {
                throw new \PDOException($e->getMessage(), (int)$e->getCode());
            }
        }
  
    }

    public function sumByMonth($district = null): array{ 
        if ($district){
            try{
                $district = (int)$district;
                $sql='
                    SELECT D.id, D.name, R.month as Month, SUM(R.rainfall) as Rainfall                     
                    FROM Districts D                      
                    INNER JOIN Rainfalls R                      
                    ON D.id = R.districtsID 
                    WHERE R.districtsID = :districtsID 
                    GROUP BY D.id, D.name, R.month                     
                    ORDER BY D.id ASC                
                ';
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(['districtsID' => $district]);
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return $result;                
            }
            catch (\PDOException $e) {
                throw new \PDOException($e->getMessage(), (int)$e->getCode());
            }
        }
        else{
            try{
                
                $sql='
                    SELECT D.id, D.name, R.month as Month, SUM(R.rainfall) as Rainfall
                    FROM Districts D 
                    INNER JOIN Rainfalls R 
                    ON D.id = R.districtsID
                    GROUP BY D.id, D.name, R.month
                    ORDER BY D.id ASC
                ';
                $stmt = $this->pdo->query($sql);
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return $result;                
            }
            catch (\PDOException $e) {
                throw new \PDOException($e->getMessage(), (int)$e->getCode());
            }
        }
  
    }
}