<?php declare(strict_types=1);

    $str = file_get_contents("./rainfallData/C0X050_東山.json");
    // var_dump(json_decode($str));
    $path = "./rainfallData/C0X050_東山.json";
    $file1 = basename($path); 
    $file2 = basename($path, ".json"); 

    foreach (glob("./rainfallData/*.json") as $file) {
        $filename = basename($file, ".json");
        // echo substr($filename,-6);
        // echo "\n";
    }
?>