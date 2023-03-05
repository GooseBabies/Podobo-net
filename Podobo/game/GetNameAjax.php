<?php
	//$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	//$db = new SQLite3("Y:\\Database\\nevada.db");
	$db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
	$db->exec('PRAGMA foreign_keys = ON;');
	$db->busyTimeout(100);
	
	if(isset($_GET["tagid"])) { $tagid = $_GET["tagid"]; } else { $tagid = ""; };
	
	$sql = $db->prepare("select tag_name from tags where tagid = :tagid");
	$sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
	$tag = $sql->execute()->fetchArray()[0] ?? "non";
	
	echo json_encode($tag);		
	
	$db = null;
?>