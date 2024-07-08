<?php
	$db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
	$db->exec('PRAGMA foreign_keys = ON;');
	$db->busyTimeout(100);
	
	if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = 0; };
	if(isset($_GET["rarity"])) { $rarity = $_GET["rarity"]; } else { $rarity = 0; };

	try{
        $db->exec('BEGIN;');
        $db->enableExceptions(true);

		$sql = $db->prepare("update files set rarity = :rarity where id=:id;");
		$sql->bindValue(":rarity", $rarity, SQLITE3_FLOAT);
		$sql->bindValue(":id", $id, SQLITE3_INTEGER);
		$sql->execute();

		$db->exec('COMMIT;');
        $output = $rarity;
    }
    catch(Exception $e){
		$output = $db->lastErrorMsg();
        $db->exec('ROLLBACK;');
    }

    echo json_encode($output);
	
	$db = null;
?>