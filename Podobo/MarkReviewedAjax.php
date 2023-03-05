<?php
	//$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");	
	//$db = new SQLite3("Y:\\Database\\nevada.db");
	$db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
	$db->exec('PRAGMA foreign_keys = ON;');
	$db->busyTimeout(100);
	
	if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = 0; };

	try{
        $db->exec('BEGIN;');
        $db->enableExceptions(true);

		$sql = $db->prepare("update files set review=:review where id=:id;");
		$sql->bindValue(":review", false, SQLITE3_INTEGER);
		$sql->bindValue(":id", $id, SQLITE3_INTEGER);
		$sql->execute();

		$db->exec('COMMIT;');
		
		echo json_encode("Marked");
    }
    catch(Exception $e){
		$output = $db->lastErrorMsg();
        $db->exec('ROLLBACK;');
    }
	
	$db = null;
?>