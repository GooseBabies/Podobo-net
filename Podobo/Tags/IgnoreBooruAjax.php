<?php
	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	
	if(isset($_GET["bt"])) { $bt = $_GET["bt"]; } else { $bt = ""; };
	if(isset($_GET["bs"])) { $bs = $_GET["bs"]; } else { $bs = ""; };

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
	
	$db = null;
?>