<?php
	//$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	//$db = new SQLite3("Y:\\Database\\nevada.db");
	$db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
	$db->exec('PRAGMA foreign_keys = ON;');
	$db->busyTimeout(100);
	
	if(isset($_GET["txt"])) { $txt = html_entity_decode($_GET["txt"]); } else { $txt = ""; };
	
	$sql = $db->prepare("select category from tags where tag_name = :tag COLLATE NOCASE");
	$sql->bindValue(':tag', str_replace("_", " ", $txt), SQLITE3_TEXT);
	$category = $sql->execute()->fetchArray()[0] ?? -1;
	
	echo json_encode($category);			
	
	$db = null;
?>