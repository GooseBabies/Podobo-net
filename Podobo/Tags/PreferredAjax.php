<?php
	$db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
	$db->exec('PRAGMA foreign_keys = ON;');
	$db->busyTimeout(100);
	
	if(isset($_GET["txt"])) { $txt = html_entity_decode($_GET["txt"]); } else { $txt = ""; };
	$rows = [];
	$outrows = [];
	
	$sql = $db->prepare("select tagid from tags where tag_name = :tag COLLATE NOCASE");
	$sql->bindValue(':tag', str_replace("_", " ", $txt), SQLITE3_TEXT);
	$tagid = $sql->execute()->fetchArray()[0] ?? -1;
	
	$sql = $db->prepare("select preferred from siblings where alias = :tagid");
	$sql->bindValue(':tagid', $tagid , SQLITE3_INTEGER);
	$result = $sql->execute();
	while ($row = $result->fetchArray()) {
		array_push($rows, $row);
	}
	
	for ($i = 0; $i <= count($rows) - 1; $i++)
	{
		$sql = $db->prepare("select tag_name from tags where tagid=:tagid");
		$sql->bindValue(':tagid', $rows[$i][0] , SQLITE3_INTEGER);
		$parenttag = $sql->execute()->fetchArray()[0] ?? '';
		array_push($outrows, str_replace(" ", "_", $parenttag));		
	}
	
	echo json_encode($outrows);			
	
	$db = null;
?>