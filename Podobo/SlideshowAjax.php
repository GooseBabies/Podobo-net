<?php
	$db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
	$db->exec('PRAGMA foreign_keys = ON;');
	$db->busyTimeout(100);
	
	if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = 0; };	
	
	$img = $db->query("select path from files where id='" . $id . "'")->fetchArray()[0];

	$sql = $db->prepare("update files set viewcount = viewcount + 1, last_viewed = date('now') where id = :id");
	$sql->bindValue(':id', $id, SQLITE3_INTEGER);
	$result = $sql->execute();

	$sql = $db->prepare("INSERT INTO view_history (media_id, viewtime) VALUES (:media_id, datetime('now'))");
	$sql->bindValue(':media_id', $id, SQLITE3_INTEGER);
	$result = $sql->execute();
	
	echo strtolower(substr(pathinfo($img, PATHINFO_DIRNAME), 3)) . "\\" . rawurlencode(basename($img));			
	
	$db = null;
?>
