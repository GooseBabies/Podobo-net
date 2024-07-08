<?php
	$db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
	$db->exec('PRAGMA foreign_keys = ON;');
	$db->busyTimeout(100);
	$SpecialTags=array("\$ext:", "\$dur>", "\$dur<", "\$name:", "\$!name:", "\$start:", "\$rating>", "\$rating<", "\$height>", "\$height<", "\$sound", "\$video", "\$rev", "\$rev", "\$tags=", "\$!tagged", "\$tags>", "\$tags<", "\$source", "\$!source", "\$set:", "\$event");
	
	if(isset($_GET["txt"])) { $txt = html_entity_decode($_GET["txt"]); } else { $txt = ""; };
	$rows = [];
	$outrows = [];
	$limit = 8;

	$txt = str_replace("_", " ", $txt);

	if(str_starts_with($txt, "$")){
		$outrows = array_filter($SpecialTags, function($k) {
			return substr($k, 0, 1) === '$';
		});
	}
	else{

		$sql = $db->prepare("select tag_name, tagid, tag_count from tags where tag_name = :txt COLLATE NOCASE and category != 15");
		$sql->bindValue(":txt", $txt, SQLITE3_TEXT);
		$exactsearch = $sql->execute()->fetchArray() ?? Array();
		
		if(!empty($exactsearch))
		{
			array_push($rows, $exactsearch);
			$limit--;
		}

		$sql = $db->prepare("select tag_name, tagid, tag_count from tags where tag_name like :txt COLLATE NOCASE and category != 15 order by tag_count desc limit :limit");
		$sql->bindValue(":txt", "%" . $txt . "%", SQLITE3_TEXT);
		$sql->bindValue(":limit", $limit, SQLITE3_INTEGER);
		$result = $sql->execute();
		while ($row = $result->fetchArray()) {
			if($exactsearch != $row)
			{			
				array_push($rows, $row);
			}
		}
		
		for ($i = 0; $i <= count($rows) - 1; $i++)
		{
			$sql = $db->prepare("select preferred from siblings where alias = :alias");
			$sql->bindValue(":alias", $rows[$i][1], SQLITE3_INTEGER);
			$preferred = $sql->execute()->fetchArray()[0] ?? -1;
			if($preferred > 0)
			{		
				$sql = $db->prepare("select tag_name from tags where tagid= :preferred ");
				$sql->bindValue(":preferred", $preferred, SQLITE3_INTEGER);
				$newtag = $sql->execute()->fetchArray()[0] ?? '';
				array_push($outrows, array("label"=>str_replace(" ", "_", $rows[$i][0]) . " [" . $rows[$i][2] . "] » " . str_replace(" ", "_", $newtag), "value"=>str_replace(" ", "_", $newtag)));
			}
			else
			{
				array_push($outrows, array("label"=>str_replace(" ", "_", $rows[$i][0]) . " [" . $rows[$i][2] . "] ", "value"=>str_replace(" ", "_", $rows[$i][0])));
			}		
		}
	}
	
	echo json_encode($outrows);			
	
	$db = null;
?>