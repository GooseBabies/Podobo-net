<?php
	//$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
    //$db = new SQLite3("Y:\\Database\\nevada.db");
    $db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
    $db->exec('PRAGMA foreign_keys = ON;');
	$db->busyTimeout(100);
	
	if(isset($_GET["tagid"])) { $tagid = $_GET["tagid"]; } else { $tagid = -1; };
    $tier1 = array(-1, -1, -1, -1, -1);
    $tier2 = array(-1, -1, -1, -1, -1);
    $tier3 = array(-1, -1, -1, -1, -1);
    $tier4 = array(-1, -1, -1, -1, -1);
	
	$sql = $db->prepare("select id, overall_rating, media_rating, individual_rating, sexual_rating from files where tag_list like :tagid and overall_rating > 7 and duration = 0 and id not in (select file_id from summonlist) order by random() limit 1");
	$sql->bindValue(':tagid', '%;' . $tagid . ';%' , SQLITE3_TEXT);
	$tier3 = $sql->execute()->fetchArray() ?? array(-1, -1, -1, -1, -1);

    if($tier3 == false){
        $tier3 = array(-1, -1, -1, -1, -1);
    }

    if($tier3[0] == -1){
        $sql = $db->prepare("select id, overall_rating, media_rating, individual_rating, sexual_rating from files where tag_list like :tagid and overall_rating > 6.9 and duration = 0 and id not in (select file_id from summonlist) order by random() limit 1");
        $sql->bindValue(':tagid', "%;" . $tagid . ";%", SQLITE3_TEXT);
        $tier3 = $sql->execute()->fetchArray() ?? array(-1, -1, -1, -1, -1);
    }

    if($tier3 == false){
        $tier3 = array(-1, -1, -1, -1, -1);
    }
   
    $sql = $db->prepare("select id, overall_rating, media_rating, individual_rating, sexual_rating from files where tag_list like :tagid and (overall_rating between 6.5 and :upper or overall_rating = 0) and duration = 0 and id not in (select file_id from summonlist) and id != :tier3 order by random() limit 1");
    $sql->bindValue(':tagid', "%;" . $tagid . ";%", SQLITE3_TEXT);
    $sql->bindValue(':upper', $tier3[1], SQLITE3_INTEGER);
    $sql->bindValue(':tier3', $tier3[0], SQLITE3_INTEGER);
    $tier2 = $sql->execute()->fetchArray() ?? array(-1, -1, -1, -1, -1);

    if($tier2 == false){
        $tier2 = array(-1, -1, -1, -1, -1);
    }

    $sql = $db->prepare("select id, overall_rating, media_rating, individual_rating, sexual_rating from files where tag_list like :tagid and (overall_rating <= :upper or overall_rating = 0) and duration = 0 and id not in (select file_id from summonlist) and (id != :tier2 and id != :tier3) order by random() limit 1");
    $sql->bindValue(':tagid', "%;" . $tagid . ";%", SQLITE3_TEXT);
    $sql->bindValue(':upper', $tier2[1], SQLITE3_INTEGER);
    $sql->bindValue(':tier2', $tier2[0], SQLITE3_INTEGER);
    $sql->bindValue(':tier3', $tier3[0], SQLITE3_INTEGER);
    $tier1 = $sql->execute()->fetchArray() ?? array(-1, -1, -1, -1, -1);

    if($tier1 == false){
        $tier1 = array(-1, -1, -1, -1, -1);
    }

    $sql = $db->prepare("select id, overall_rating, media_rating, individual_rating, sexual_rating from files where tag_list like :tagid and overall_rating >= 7 and duration between 1 and 5 and id not in (select file_id from summonlist) order by random() limit 1");
    $sql->bindValue(':tagid', "%;" . $tagid . ";%", SQLITE3_TEXT);
    $tier4 = $sql->execute()->fetchArray() ?? array(-1, -1, -1, -1, -1);

    if($tier4 == false){
        $tier4 = array(-1, -1, -1, -1, -1);
    }

    if($tier4[0] == -1 and rand(1, 5) != 3){
        $tier4 = array(-1, -1, -1, -1, -1);
    }

    //echo json_encode($tier4);

    if($tier4[0] == -1){
        $output = array($tier1, $tier2, $tier3);
    }
    else{
        $output = array($tier1, $tier2, $tier3, $tier4);
    }

	echo json_encode($output);		
	
	$db = null;
?>