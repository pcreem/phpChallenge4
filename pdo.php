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
    
    try {
        $sql = 'INSERT INTO Districts VALUES(:id, :name)';
        $stmt = $pdo->prepare($sql);

        // foreach(BASE_DISTRICTS as $id=>$name){
        //     $stmt->execute(['id' => $id, 'name' => $name]);
        //     echo "$id => $name \n";
        // };
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }

?>