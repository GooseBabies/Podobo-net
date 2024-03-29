<?php
	//$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	//$db = new SQLite3("Y:\\Database\\nevada.db");
	$db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
	$db->exec('PRAGMA foreign_keys = ON;');
	$db->busyTimeout(100);

	if(isset($_GET["bt"])) { $bt = $_GET["bt"]; } else { $bt = ""; };
	if(isset($_GET["bs"])) { $bs = $_GET["bs"]; } else { $bs = -1; };

	try{
		$db->exec('BEGIN;');
		$db->enableExceptions(true);

		$sql = $db->prepare("select count(*) from booru_proc where booru_tag = :bt and booru_source = :bs");
		$sql->bindValue(':bt', $bt, SQLITE3_TEXT);
		$sql->bindValue(':bs', $bs, SQLITE3_INTEGER);
		$booru_count = $sql->execute()->fetchArray()[0] ?? -1;

		if($booru_count < 1){
			$bt = html_entity_decode($bt);
		}
		
		$sql = $db->prepare("update booru_proc set processed=1, ignored=1 where booru_tag=:bt and booru_source=:bs");
		$sql->bindValue(':bt', $bt, SQLITE3_TEXT);
		$sql->bindValue(':bs', $bs, SQLITE3_INTEGER);
		$result = $sql->execute();

		$db->exec('COMMIT;');

	} catch (Exception $e){
		$output = $db->lastErrorMsg();
		$db->exec('ROLLBACK;');
	}

	$db = null;
?>