<?php
	$db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
	$db->exec('PRAGMA foreign_keys = ON;');
	$db->busyTimeout(100);
	
	if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = -1; };
	if(isset($_GET["set"])) { $set = $_GET["set"]; } else { $set = ""; };
    if(isset($_GET["media_id"])) { $media_id = $_GET["media_id"]; } else { $media_id = -1; };

	try{
        $db->exec('BEGIN;');
        $db->enableExceptions(true);

        if($media_id != -1){
            if($id == -1){
                //create new set
                $sql = $db->prepare("INSERT INTO sets (name, series, set_list) VALUES (:name, :series, :set_list);");
                $sql->bindValue(':name', $set, SQLITE3_TEXT);
                $sql->bindValue(':series', 0, SQLITE3_INTEGER);
                $sql->bindValue(':set_list', "," . $media_id . ",", SQLITE3_TEXT);
                $result = $sql->execute();

                $id = $db->lastInsertRowid();
            }
            else{
                $sql = $db->prepare("SELECT set_list FROM sets where id=:id");
                $sql->bindValue(':id', $id, SQLITE3_INTEGER);
                $setlist = $sql->execute()->fetchArray()[0] ?? '';
    
                $setlist = trim($setlist) . $media_id . ",";
    
                $sql = $db->prepare("update sets set set_list = :set_list where id=:id;");
                $sql->bindValue(":set_list", $setlist, SQLITE3_TEXT);
                $sql->bindValue(":id", $id, SQLITE3_INTEGER);
                $sql->execute();
            }
        }

		$db->exec('COMMIT;');
		
		echo json_encode($id);
    }
    catch(Exception $e){
		$output = $db->lastErrorMsg();
        $db->exec('ROLLBACK;');
    }
	
	$db = null;
?>