<?php
	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	$db->busyTimeout(100);
	
	if(isset($_GET["tag"])) { $tag = html_entity_decode($_GET["tag"]); } else { $tag = ""; };
	
	$sql = $db->prepare("select tag_count from tags where tag_name = :tag");
	$sql->bindValue(':tag', str_replace("_", " ", $tag), SQLITE3_TEXT);
	$tagcount = $sql->execute()->fetchArray()[0] ?? -1;
	
	echo json_encode($tagcount);		
	
	$db = null;
?>