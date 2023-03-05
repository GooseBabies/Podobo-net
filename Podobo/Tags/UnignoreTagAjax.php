<?php
	//$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
    //$db = new SQLite3("Y:\\Database\\nevada.db");
    $db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
    $db->exec('PRAGMA foreign_keys = ON;');
    $db->busyTimeout(100);
	
	if(isset($_GET["boorutag"])) { $boorutag = $_GET["boorutag"]; } else { $boorutag = ''; };
    if(isset($_GET["bs"])) { $bs = $_GET["bs"]; } else { $bs = -1; };
	$output = "";
	
    try{
        if($boorutag != "" and $boorutag != " " and $bs != -1) 
        {
            $db->exec('BEGIN;');
            $db->enableExceptions(true);

            $sql = $db->prepare("Update booru_proc set processed = 0, ignored = 0 WHERE booru_tag = :boorutag and booru_source = :bs");
            $sql->bindValue(':boorutag', $boorutag, SQLITE3_TEXT);
            $sql->bindValue(':bs', $bs, SQLITE3_INTEGER);
            $result = $sql->execute();

            if($db->changes() < 1){
                $sql = $db->prepare("Update booru_proc set processed = 0, ignored = 0 WHERE booru_tag = :boorutag and booru_source = :bs");
                $sql->bindValue(':boorutag', html_entity_decode($boorutag), SQLITE3_TEXT);
                $sql->bindValue(':bs', $bs, SQLITE3_INTEGER);
                $result = $sql->execute();
            }

            $db->exec('COMMIT;');
            echo json_encode($boorutag . "|" . $bs);
        }
        else
        {
            echo json_encode("Error");
        }
    }catch(Exception $e){
        $output = $db->lastErrorMsg();
        $db->exec('ROLLBACK;');
    }

	$db = null;
?>