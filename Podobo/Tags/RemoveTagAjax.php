<?php
	$db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
	$db->exec('PRAGMA foreign_keys = ON;');
	$db->busyTimeout(100);
	
	if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = -1; };
    if(isset($_GET["tagid"])) { $tagid = $_GET["tagid"]; } else { $tagid = -1; };
	$output = "";
	
	try{
		if($id != -1 and $tagid != -1) 
		{
			$db->exec('BEGIN;');
            $db->enableExceptions(true);

			// $taglist = GetTagList($id);
			// $taglist = str_replace(";" . $tagid . ";", ";", $taglist);
			// UpdateTagList($id, $taglist);
			// DecrementTagCount($tagid);
			$sql = $db->prepare("update tags set tag_count = tag_count - 1 where tagid = :tagid");
			$sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
			$sql->execute();

			$sql = $db->prepare("delete from media_tags where media_id = :media_id and tag_id = :tag_id");
			$sql->bindValue(':media_id', $id, SQLITE3_INTEGER);
			$sql->bindValue(':tag_id', $tagid, SQLITE3_INTEGER);			
			$sql->execute();

			$db->exec('COMMIT;');
			echo json_encode("a" . $tagid);
		}
		else
		{
			echo json_encode("Error");
		}
	}
	catch(exception $e){
		$output = $db->lastErrorMsg();
		echo json_encode($output);
		$db->exec('ROLLBACK;');
	}
	
	$db = null;

    function GetTagList($id)
	{
		try{
			global $db;
			$sql = $db->prepare("select tag_list from files where id = :id");
			$sql->bindValue(':id', $id, SQLITE3_INTEGER);
			$taglist = $sql->execute()->fetchArray()[0] ?? '';
			return $taglist;
		}
		catch(exception $e){
			
		}
	}
	
	function UpdateTagList($id, $taglist)
	{
		try{
			global $db;
			$sql = $db->prepare("update files set tag_list = :tag_list where id = :id");
			$sql->bindValue(':tag_list', $taglist, SQLITE3_TEXT);
			$sql->bindValue(':id', $id, SQLITE3_INTEGER);
			$sql->execute();
		}
		catch(exception $e){
			
		}
	}

    // function DecrementTagCount($tagid)
	// {
	// 	try{
	// 		global $db;
	// 		$sql = $db->prepare("update tags set tag_count = tag_count - 1 where tagid = :tagid");
	// 		$sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
	// 		$sql->execute();
	// 	}
	// 	catch(exception $e){
			
	// 	}
	// }
?>