<?php
	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	
	if(isset($_GET["txt"])) { $txt = html_entity_decode($_GET["txt"]); } else { $txt = ""; };
	
	$category = $db->query("select category from tags where tag_name = '" . str_replace("_", " ", $txt) . "'")->fetchArray()[0] ?? '';
	
	echo json_encode($category);			
	
	$db = null;
?>