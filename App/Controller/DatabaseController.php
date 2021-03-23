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

        function show_status($done, $total, $size=30) {
        
            static $start_time;
         
            // if we go over our bound, just ignore it
            if($done > $total) return;
         
            if(empty($start_time)) $start_time=time();
            $now = time();
         
            $perc=(double)($done/$total);
            $bar=(int)floor($perc*$size);
            $status_bar="\r[";
            $status_bar.=str_repeat("=", $bar);
    
            if($bar<$size){
                $status_bar.=">";
                $status_bar.=str_repeat(" ", $size-$bar);
            } else {
                $status_bar.="=";
            }
         
            $disp=number_format($perc*100, 0);
         
            $status_bar.="] $disp%  $done/$total";
         
            $rate = ($now-$start_time)/$done;
            $left = $total - $done;
            $eta = round($rate * $left, 2);
         
            $elapsed = $now - $start_time;
         
            $status_bar.= " remaining: ".number_format($eta)." sec.  elapsed: ".number_format($elapsed)." sec.";
         
            echo "$status_bar  ";
         
            flush();
         
            // when done, send a newline
            if($done == $total) {
                echo "\n";
            }
         
        }
        
        try {
            $this->pdo->query('TRUNCATE TABLE Districts');
            $this->pdo->query('TRUNCATE TABLE Rainfalls');
    
            $sql = 'INSERT INTO Districts VALUES(:id, :name)';
            $stmt = $this->pdo->prepare($sql);
    
            foreach(CollectData::BASE_DISTRICTS as $id=>$name){
                $stmt->execute(['id' => $id, 'name' => $name]);
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
                    $loadingProgress = floor($loading / $filelength * 100); 
                    $loadingProgress = $loadingProgress > 0 ? $loadingProgress : 1;
                    show_status($loadingProgress, 100);
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