<?php
	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
    $db->busyTimeout(100);
	
	if(isset($_GET["boorutag"])) { $boorutag = $_GET["boorutag"]; } else { $boorutag = ''; };
    if(isset($_GET["bs"])) { $bs = $_GET["bs"]; } else { $bs = ''; };
    if(isset($_GET["tagid"])) { $tagid = $_GET["tagid"]; } else { $tagid = -1; };
	$output = "";
	
    try{
        if($tagid != -1 and $boorutag != "" and $boorutag != " ") 
        {
            $db->exec('BEGIN;');
            $db->enableExceptions(true);

            $sql = $db->prepare("DELETE FROM tag_map WHERE booru_tag = :boorutag AND tagid = :tagid and booru_source = :bs");
            $sql->bindValue(':boorutag', $boorutag, SQLITE3_TEXT);
            $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
            $sql->bindValue(':bs', $bs, SQLITE3_INTEGER);
            $result = $sql->execute();

            if($db->changes() < 1){
                $sql = $db->prepare("DELETE FROM tag_map WHERE booru_tag = :boorutag AND tagid = :tagid and booru_source = :bs");
                $sql->bindValue(':boorutag', html_entity_decode($boorutag), SQLITE3_TEXT);
                $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
                $sql->bindValue(':bs', $bs, SQLITE3_INTEGER);
                $result = $sql->execute();
            }

            $db->exec('COMMIT;');
            echo json_encode($boorutag);
        }
        else
        {
            echo json_encode("Error");
        }
    }
    catch(Exception $e){
        $output = $db->lastErrorMsg();
        $db->exec('ROLLBACK;');
    }

	$db = null;
?>