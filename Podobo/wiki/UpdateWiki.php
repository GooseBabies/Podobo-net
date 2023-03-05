<?php
	//$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
    //$db = new SQLite3("Y:\\Database\\nevada.db");
    $db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
    $db->exec('PRAGMA foreign_keys = ON;');
    $db->busyTimeout(100);    
	
	if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = -1; };
    if(isset($_GET["key"])) { $key = $_GET["key"]; } else { $key = ""; };
    if(isset($_GET["title"])) { $title = Html_entity_decode($_GET["title"]); } else { $title = ""; };
    if(isset($_GET["body"])) { $body = Html_entity_decode($_GET["body"]); } else { $body = ""; };

    //echo $body;

    try{
        $db->exec('BEGIN;');
        $db->enableExceptions(true);

        if($id == -1)
        {	
            $sql = $db->prepare("insert into wiki (key, title, creation_date, last_update, body) values (:key, :title, datetime('now'), datetime('now'), :body)");
            $sql->bindValue(':key', $key, SQLITE3_TEXT);
            $sql->bindValue(':title', $title, SQLITE3_TEXT);
            $sql->bindValue(':body', $body, SQLITE3_TEXT);
            $result = $sql->execute();

            $id = $db->lastInsertRowid();
            
        }
        else{
            $sql = $db->prepare("update wiki set key = :key, title = :title, body = :body, last_update = datetime('now') where id = :id");
            $sql->bindValue(':id', $id, SQLITE3_INTEGER);
            $sql->bindValue(':key', $key, SQLITE3_TEXT);
            $sql->bindValue(':title', $title, SQLITE3_TEXT);
            $sql->bindValue(':body', $body, SQLITE3_TEXT);
            $result = $sql->execute();
        }
        $db->exec('COMMIT;');

        echo $id;
    }
    catch (Exception $e){
        echo json_encode($db->lastErrorMsg());
        $db->exec('ROLLBACK;');
    }
    
	$db = null;
?>