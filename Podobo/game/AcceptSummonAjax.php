<?php
	//$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
    //$db = new SQLite3("Y:\\Database\\nevada.db");
    $db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
    $db->exec('PRAGMA foreign_keys = ON;');
	$db->busyTimeout(100);
	
    if(isset($_GET["tagid"])) { $tagid = $_GET["tagid"]; } else { $tagid = -1; };
	if(isset($_GET["tier1"])) { $tier1 = $_GET["tier1"]; } else { $tier1 = ""; };
    if(isset($_GET["tier2"])) { $tier2 = $_GET["tier2"]; } else { $tier2 = ""; };
    if(isset($_GET["tier3"])) { $tier3 = $_GET["tier3"]; } else { $tier3 = ""; };
    if(isset($_GET["tier4"])) { $tier4 = $_GET["tier4"]; } else { $tier4 = ""; };
    $tier1 = explode(",", $tier1);
    $tier2 = explode(",", $tier2);
    $tier3 = explode(",", $tier3);
    $tier4 = explode(",", $tier4);
	
	$sql = $db->prepare("select tag_name from tags where tagid = :tagid");
	$sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
	$tag_name = $sql->execute()->fetchArray()[0] ?? "";

    try{
        $db->exec('BEGIN;');
        $db->enableExceptions(true);

        if($tier1[2] == 0){
            $tier1[2] = 7;
        }
        if($tier1[3] == 0){
            $tier1[3] = 7;
        }
        if($tier1[4] == 0){
            $tier1[4] = 7;
        }

        //determine rarity
        if($tier4[0] != -1){
            $rarity = 4;
        }
        else if($tier3[1] == 10){
            $rarity = 3;
        }
        else if(($tier1[2] + $tier2[2] + $tier3[2])/3 > 7.5){
            $rarity = 2;
        }
        else{
            $rarity = 1;
        }

        $class1 = 0;
        $class2 = 0;

        $sql = $db->prepare("insert into summons (name, title, tier1, tier2, tier3, tier4, rarity, class1, class2, tier1_encounter_count, tier2_encounter_count, tier3_encounter_count, tier4_encounter_count, tier1_summon_count, tier2_evolve_count, tier3_evolve_count, tier4_evolve_count) values (:name, :title, :tier1, :tier2, :tier3, :tier4, :rarity, :class1, :class2, 0, 0, 0, 0, 0, 0, 0, 0)");
        $sql->bindValue(':name', $tag_name, SQLITE3_TEXT);
        $sql->bindValue(':title', '', SQLITE3_TEXT);
        $sql->bindValue(':tier1', $tier1[0], SQLITE3_INTEGER);
        $sql->bindValue(':tier2', $tier2[0], SQLITE3_INTEGER);
        $sql->bindValue(':tier3', $tier3[0], SQLITE3_INTEGER);
        $sql->bindValue(':tier4', $tier4[0], SQLITE3_INTEGER);
        $sql->bindValue(':rarity', $rarity, SQLITE3_INTEGER);
        $sql->bindValue(':class1', $class1, SQLITE3_INTEGER);
        $sql->bindValue(':class2', $class2, SQLITE3_INTEGER);
        $result = $sql->execute();

        $summonid = $db->lastInsertRowid();

        $sql = $db->prepare("insert into summonlist (file_id) values (:file_id)");
        $sql->bindValue(':file_id', $tier1[0], SQLITE3_INTEGER);
        $result = $sql->execute();

        $sql = $db->prepare("insert into summonlist (file_id) values (:file_id)");
        $sql->bindValue(':file_id', $tier2[0], SQLITE3_INTEGER);
        $result = $sql->execute();

        $sql = $db->prepare("insert into summonlist (file_id) values (:file_id)");
        $sql->bindValue(':file_id', $tier3[0], SQLITE3_INTEGER);
        $result = $sql->execute();

        if($tier4[0] != "-1"){
            $sql = $db->prepare("insert into summonlist (file_id) values (:file_id)");
            $sql->bindValue(':file_id', $tier4[0], SQLITE3_INTEGER);
            $result = $sql->execute();
        }

        

        // $stamina = 6 * $tier1[2] * (rand(85, 115)/100);
        // $endurance = $tier1[3] * (rand(85, 115)/100);
        // $attack = $tier1[4] * (rand(85, 115)/100);

        // $sql = $db->prepare("insert into harem (summon_id, level, exp, attack, endurance, stamina) values (:summon_id, 1, 0, :attack, :endurance, :stamina)");
        // $sql->bindValue(':summon_id', $summonid, SQLITE3_INTEGER);
        // $sql->bindValue(':attack', $attack, SQLITE3_FLOAT);
        // $sql->bindValue(':endurance', $endurance, SQLITE3_FLOAT);
        // $sql->bindValue(':stamina', $stamina, SQLITE3_FLOAT);
        // $result = $sql->execute();

        $db->exec('COMMIT;');
    }
    catch(Exception $e){
        echo json_encode($db->lastErrorMsg());
       $db->exec('ROLLBACK;');
       
   }

	echo json_encode("Added");		
	
	$db = null;
?>