<?php
	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");	
	
	if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = 0; };	
	
	$img = $db->query("select path from files where id='" . $id . "'")->fetchArray()[0];
	
	echo strtolower(substr(pathinfo($img, PATHINFO_DIRNAME), 3)) . "\\" . rawurlencode(basename($img));			
	
	$db = null;
?>
