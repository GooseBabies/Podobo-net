<?php
	//$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
    //$db = new SQLite3("Y:\\Database\\nevada.db");
    $db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
    $db->exec('PRAGMA foreign_keys = ON;');
	$db->busyTimeout(100);

    //rarity random
    //SR: 1/200
    //R: 4/200
    //UC: 45/200
    //C: 150/200

    $roll = rand(1, 200);
    $rarity = 1;

    if($roll == 200){
        $rarity = 4;
    }
    else if($roll < 200 and $roll > 195){
        $rarity = 3;
    }
    else if($roll < 196 and $roll > 149){
        $rarity = 2;
    }
    else{
        $rarity = 1;
    }
    
    $sql = $db->prepare("select id, tier1, name from summons where rarity = :rarity order by random() limit 1");
	$sql->bindValue(':rarity', $rarity, SQLITE3_INTEGER);
	$summon = $sql->execute()->fetchArray() ?? "";

    $sql = $db->prepare("select path, media_rating, individual_rating, sexual_rating from files where id = :id");
	$sql->bindValue(':id', $summon[1], SQLITE3_INTEGER);
	$ratings = $sql->execute()->fetchArray() ?? "";

    try{
        $db->exec('BEGIN;');
        $db->enableExceptions(true);

        // for ($i = 1; $i < count($ratings); $i++)
        // {
        //     if($ratings[$i] == 0){
        //         $ratings[$i] = 7;
        //     }
        // }

        $stamina = 6 * ($ratings[1]==0 ? 7 : $ratings[1]) * (rand(85, 115)/100);
        $endurance = ($ratings[2]==0 ? 7 : $ratings[2]) * (rand(85, 115)/100);
        $attack = ($ratings[3]==0 ? 7 : $ratings[3]) * (rand(85, 115)/100);

        $sql = $db->prepare("insert into harem (summon_id, level, exp, attack, endurance, stamina) values (:summon_id, 1, 0, :attack, :endurance, :stamina)");
        $sql->bindValue(':summon_id', $summon[0], SQLITE3_INTEGER);
        $sql->bindValue(':attack', $attack, SQLITE3_FLOAT);
        $sql->bindValue(':endurance', $endurance, SQLITE3_FLOAT);
        $sql->bindValue(':stamina', $stamina, SQLITE3_FLOAT);
        $result = $sql->execute();

        $sql = $db->prepare("update save set summon_pts = summon_pts - 10");
        $result = $sql->execute();

        $db->exec('COMMIT;');
    }
    catch(Exception $e){
        echo json_encode($db->lastErrorMsg());
        $db->exec('ROLLBACK;');
    }
    $path = strtolower(substr(pathinfo($ratings[0], PATHINFO_DIRNAME), 3)) . "\\" . rawurlencode(basename($ratings[0]));

	echo json_encode(array($summon[2], $rarity, $path, $stamina, $endurance, $attack));		//, 
	
	$db = null;
?>