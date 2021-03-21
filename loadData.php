<?php declare(strict_types=1);

    const BASE_DISTRICTS = [
        '南區', '北區', '安平區', '左鎮區', '仁德區', '關廟區', '官田區', '麻豆區', '佳里區', '西港區', '七股區', '將軍區', '學甲區',
        '北門區', '新營區', '後壁區', '白河區', '東山區', '下營區', '柳營區', '鹽水區', '山上區', '安定區',
    ];

    $host = '127.0.0.1';
    $dbname   = 'challenge';
    $user = 'paper';
    $password = 'pass';
    $port = "3306";
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset;port=$port";

    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);   
    
    function arrayFindKey(string $str, array $array):int {
        $expr = '/' . $str . '/';
        foreach ($array as $key => $value) {
          if (preg_match($expr, $value)) {
            return $key;
          }
        }
    }

    function convertDatetime(string $str, int $a, $b = null) :int{
        return intval(substr($str, $a, $b));
    }
    
    try {
        $pdo->query('TRUNCATE TABLE Districts');
        $pdo->query('TRUNCATE TABLE Rainfalls');

        $sql = 'INSERT INTO Districts VALUES(:id, :name)';
        $stmt = $pdo->prepare($sql);

        foreach(BASE_DISTRICTS as $id=>$name){
            $stmt->execute(['id' => $id, 'name' => $name]);
            // echo "$id => $name \n";
        };

        $sql = 'INSERT INTO Rainfalls(year, month, day, time, rainfall, districtsID) VALUES(:year, :month, :day, :time, :rainfall, :districtsID)';
        $stmt = $pdo->prepare($sql);

        foreach (glob("./rainfallData/*.json") as $filepath) {
            $filecontentStr = file_get_contents($filepath);
            $filecontentArr = json_decode($filecontentStr,true);
            $filelength = count($filecontentArr);
            $loading = 0;
            
            $filename = basename($filepath, ".json");
            $districtname = substr($filename,-6);
            $districtID = arrayFindKey($districtname, BASE_DISTRICTS);

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

?>