<?php
	//$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	//$db = new SQLite3("Y:\\Database\\nevada.db");
	$db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
	$db->exec('PRAGMA foreign_keys = ON;');
	$db->busyTimeout(100);
	
	if(isset($_GET["hash1"])) { $hash1 = $_GET["hash1"]; } else { $hash1 = ""; };
	if(isset($_GET["hash2"])) { $hash2 = $_GET["hash2"]; } else { $hash2 = ""; };
	
	try{
		$db->exec('BEGIN;');
		$db->enableExceptions(true);

		if($hash1 != "" and $hash2 != "")
		{
			$sql = $db->prepare("UPDATE Dupes set processed = 1 WHERE hash_1 = :hash1 AND hash_2 = :hash2");
			$sql->bindValue(':hash1', $hash1, SQLITE3_TEXT);
			$sql->bindValue(':hash2', $hash2, SQLITE3_TEXT);
			$sql->execute();
		}

		$db->exec('COMMIT;');
		echo json_encode("");
	}
	catch(Exception $e){
		$output = $db->lastErrorMsg();
		$db->exec('ROLLBACK;');
	}
	
	$db = null;
?>