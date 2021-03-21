<?php declare(strict_types=1);
   const BASE_DISTRICTS = [
        '南區', '北區', '安平區', '左鎮區', '仁德區', '關廟區', '官田區', '麻豆區', '佳里區', '西港區', '七股區', '將軍區', '學甲區',
        '北門區', '新營區', '後壁區', '白河區', '東山區', '下營區', '柳營區', '鹽水區', '山上區', '安定區',
    ];

    function arrayFindKey($str, array $array) {
        $expr = '/' . $str . '/';
        foreach ($array as $key => $value) {
          if (preg_match($expr, $value)) {
            return $key;
          }
        }
      }

    $str = file_get_contents("./rainfallData/C0X050_東山.json");
    // var_dump(json_decode($str));
    $path = "./rainfallData/C0X050_東山.json";
    $file1 = basename($path); 
    $file2 = basename($path, ".json"); 

    foreach (glob("./rainfallData/*.json") as $file) {
        $filename = basename($file, ".json");
        $filename = substr($filename,-6);
        
        echo arrayFindKey($filename, BASE_DISTRICTS);
        echo "\n";

    }
?>