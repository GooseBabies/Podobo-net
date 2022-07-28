<?php
	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	$db->busyTimeout(100);
	
	if(isset($_GET["txt"])) { $txt = html_entity_decode($_GET["txt"]); } else { $txt = ""; };
	
	$sql = $db->prepare("select category from tags where tag_name = :tag");
	$sql->bindValue(':tag', str_replace("_", " ", $txt), SQLITE3_TEXT);
	$category = $sql->execute()->fetchArray()[0] ?? -1;
	
	echo json_encode($category);			
	
	$db = null;
?>