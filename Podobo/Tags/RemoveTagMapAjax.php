<?php
	//$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
    //$db = new SQLite3("Y:\\Database\\nevada.db");
    $db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
    $db->exec('PRAGMA foreign_keys = ON;');
    $db->busyTimeout(100);
	
	if(isset($_GET["boorutag"])) { $boorutag = urldecode($_GET["boorutag"]); } else { $boorutag = ''; };
    if(isset($_GET["bs"])) { $bs = $_GET["bs"]; } else { $bs = 0; };
    if(isset($_GET["tagid"])) { $tagid = $_GET["tagid"]; } else { $tagid = -1; };
    if(isset($_GET["namespace"])) { $namespace = urldecode($_GET["namespace"]); } else { $namespace = ''; };
	$output = "";
    //echo urldecode($boorutag);

    try{
        if($tagid != -1 and $boorutag != "" and $boorutag != " ") 
        {
            $db->exec('BEGIN;');
            $db->enableExceptions(true);

            $sql = $db->prepare("DELETE FROM tag_map WHERE booru_tag = :boorutag AND tagid = :tagid and booru_source = :bs and namespace = :namespace");
            $sql->bindValue(':boorutag', $boorutag, SQLITE3_TEXT);
            $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
            $sql->bindValue(':bs', $bs, SQLITE3_INTEGER);
            $sql->bindValue(':namespace', $namespace, SQLITE3_TEXT);
            $result = $sql->execute();

            if($db->changes() < 1){
                $sql = $db->prepare("DELETE FROM tag_map WHERE booru_tag = :boorutag AND tagid = :tagid and booru_source = :bs and namespace = :namespace");
                $sql->bindValue(':boorutag', html_entity_decode($boorutag), SQLITE3_TEXT);
                $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
                $sql->bindValue(':bs', $bs, SQLITE3_INTEGER);
                $sql->bindValue(':namespace', $namespace, SQLITE3_TEXT);
                $result = $sql->execute();
            }

            $sql = $db->prepare("update booru_proc set processed=0 where booru_tag = :bt and namespace = :namespace and booru_source = :bs");
            $sql->bindValue(':namespace', $namespace, SQLITE3_TEXT);
            $sql->bindValue(':bt', $boorutag, SQLITE3_TEXT);
            $sql->bindValue(':bs', $bs, SQLITE3_INTEGER);
            $result = $sql->execute();

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