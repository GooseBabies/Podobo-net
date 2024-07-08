<?php
	$db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
	$db->exec('PRAGMA foreign_keys = ON;');
	$db->busyTimeout(100);
	//$SpecialTags=array("\$ext:", "\$dur>", "\$dur<", "\$name:", "\$!name:", "\$start:", "\$rating>", "\$rating<", "\$height>", "\$height<", "\$sound", "\$video", "\$rev", "\$rev", "\$tags=", "\$!tagged", "\$tags>", "\$tags<", "\$source", "\$!source", "\$set:", "\$event");
	
	if(isset($_GET["txt"])) { $txt = html_entity_decode($_GET["txt"]); } else { $txt = ""; };
	$rows = [];
	$outrows = [];
	$limit = 7;

	//$txt = str_replace("_", " ", $txt);

	$sql = $db->prepare("select name, id from sets where name like :txt COLLATE NOCASE order by name asc limit :limit");
    $sql->bindValue(":txt", "%" . $txt . "%", SQLITE3_TEXT);
    $sql->bindValue(":limit", $limit, SQLITE3_INTEGER);
    $result = $sql->execute();
    while ($row = $result->fetchArray()) {
        array_push($outrows, array("label"=>$row[0], "value"=>$row[1]));
    }

	//array_push($outrows, array("label"=>$rows[0], "value"=>$rows[1]));
	
	echo json_encode($outrows);			
	
	$db = null;
?>