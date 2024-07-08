<?php
	$db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
	$db->exec('PRAGMA foreign_keys = ON;');
	$db->busyTimeout(100);
	
	if(isset($_GET["tag"])) { $tag = html_entity_decode($_GET["tag"]); } else { $tag = ""; };
	if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = -1; }; 
	if(isset($_GET["cat"])) { $category = $_GET["cat"]; } else { $category = 0; };

	$tag = str_replace("_", " ", $tag);
	
	try{
		if($tag != "" and $id != -1)
		{
			$db->exec('BEGIN;');
            $db->enableExceptions(true);

			if($category == 17){
				if(!CheckIfSourceExists($id, $tag)){
					$sources = GetSources($id);
					array_push($sources, $tag);
					AddSource($id, implode(" ", $sources));
					UpdateAutoBooruFlag($id);
				}
			}
			else{
				// $sql = $db->prepare("select hash from files where id = :id");
				// $sql->bindValue(':id', $id , SQLITE3_INTEGER);
				// $hash = $sql->execute()->fetchArray()[0] ?? '';

				$sql = $db->prepare("select tagid from tags where tag_name = :tag COLLATE NOCASE");
				$sql->bindValue(':tag', $tag, SQLITE3_TEXT);
				$tagid = $sql->execute()->fetchArray()[0] ?? -1;

				if($tagid == -1 and $id != -1)
				{
					$sql = $db->prepare("INSERT INTO tags (tag_name, category, tag_display, tag_count, alias, preferred, child, parent) VALUES (:tag, :category, :tag_display, 0, 0, 0, 0, 0);");
					$sql->bindValue(':tag', $tag, SQLITE3_TEXT);
					$sql->bindValue(':category', $category, SQLITE3_INTEGER);
					$sql->bindValue(':tag_display', $tag, SQLITE3_TEXT);
					$result = $sql->execute();
					
					$tagid = $db->lastInsertRowid();

					if (!CheckDuplicateTags($tagid, $id))
					{
						AddNewTag($tagid, $id);
						CheckForParentTags($tagid, $id);
					}
				}
				else if ($tagid > -1)
				{
					if (!CheckDuplicateTags($tagid, $id))
					{
						AddNewTag($tagid, $id);
						CheckForParentTags($tagid, $id);
					}
				}
			}
			$db->exec('COMMIT;');
			echo json_encode($tag);
		}
	}catch(exception $e){
		$output = $db->lastErrorMsg();
		$db->exec('ROLLBACK;');
	}
	
	$db = null;
	
	function CheckDuplicateTags($tagid, $id)
	{
		try{
			global $db;
			$sql = $db->prepare("SELECT COUNT(*) FROM media_tags WHERE media_id = :id AND tag_id = :tag_id");
			$sql->bindValue(':id', $id, SQLITE3_INTEGER);
			$sql->bindValue(':tag_id', $tagid, SQLITE3_INTEGER);
			$tagcount = $sql->execute()->fetchArray()[0] ?? 1;
			if($tagcount > 0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		catch(Exception $ex){
			
		}
	}
	
	function AddNewTag($tagid, $id)
	{
		try{
			global $db;
			// $taglist = GetTagList($hash);

			// if ($taglist == "" or $taglist == ";")
			// {
			// 	$taglist = ";" . strval($tagid) . ";";
			// }
			// else
			// {
			// 	$taglist = $taglist . strval($tagid) . ";";
			// }

			// UpdateTagList($hash, $taglist);

			$sql = $db->prepare("INSERT INTO media_tags (media_id, tag_id) VALUES (:media_id, :tag_id)");
			$sql->bindValue(':media_id', $id, SQLITE3_INTEGER);
			$sql->bindValue(':tag_id', $tagid, SQLITE3_INTEGER);
			$sql->execute();

			IncrementTagCount($tagid);
		}
		catch(Exception $e){

		}
	}
	
	function CheckForParentTags($childid, $id)
	{
		try{
			$parentagids = GetParentTagsIDs($childid);

			if (count($parentagids) > 0)
			{
				for ($i = 0; $i <= count($parentagids) - 1; $i++)
				{
					if (!CheckDuplicateTags($parentagids[$i][0], $id))
					{
						AddNewTag($parentagids[$i][0], $id);
					}
					CheckForParentTags($parentagids[$i][0], $id);
				}
			}
		}
		catch (Exception $e){

		}
	}
	
	function GetParentTagsIDs($childid)
	{
		try{
			global $db;
			$parentids = [];
			$sql = $db->prepare("SELECT parent FROM parents WHERE child = :child");
			$sql->bindValue(':child', $childid, SQLITE3_INTEGER);
			$result = $sql->execute();
			while ($row = $result->fetchArray()) {
				array_push($parentids, $row);
			}
			return $parentids;
		}
		catch(Exception $ex){

		}
	}
	
	// function GetTagList($hash)
	// {
	// 	global $db;
	// 	$sql = $db->prepare("select tag_list from files where hash = :hash");
	// 	$sql->bindValue(':hash', $hash, SQLITE3_TEXT);
	// 	$taglist = $sql->execute()->fetchArray()[0] ?? '';
	// 	return $taglist;
	// }
	
	// function UpdateTagList($hash, $taglist)
	// {
	// 	global $db;
	// 	$sql = $db->prepare("update files set tag_list = :tag_list where hash = :hash");
	// 	$sql->bindValue(':tag_list', $taglist, SQLITE3_TEXT);
	// 	$sql->bindValue(':hash', $hash, SQLITE3_TEXT);
	// 	$result = $sql->execute();
	// }
	
	function IncrementTagCount($tagid)
	{
		global $db;
		$sql = $db->prepare("update tags set tag_count = tag_count + 1 where tagid = :tagid");
		$sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
		$sql->execute();
	}

	function CheckIfSourceExists($id, $source)
	{
		global $db;
		$sql = $db->prepare("select count(*) from files where id = :id and sources like :source");
		$sql->bindValue(':source', "%" . $source . "%", SQLITE3_TEXT);
		$sql->bindValue(':id', $id, SQLITE3_INTEGER);
		$result = $sql->execute()->fetchArray()[0] ?? 0;

		if($result == 0){
			return false;
		}
		else{
			return true;
		}
	}

	function GetSources($id){
		global $db;
		$sql = $db->prepare("select sources from files where id = :id");
		$sql->bindValue(':id', $id, SQLITE3_INTEGER);
		$result = $sql->execute()->fetchArray()[0] ?? '';

		return array_filter(explode(" ", $result));
	}

	function AddSource($id, $sources){
		global $db;
		$sql = $db->prepare("update files set sources = :sources where id = :id");
		$sql->bindValue(':sources', $sources, SQLITE3_TEXT);
		$sql->bindValue(':id', $id, SQLITE3_INTEGER);
		$sql->execute();
	}

	function UpdateAutoBooruFlag($id){
		global $db;
		$sql = $db->prepare("update files set booru_tagged = 0 where id = :id");
		$sql->bindValue(':id', $id, SQLITE3_INTEGER);
		$sql->execute();
	}
?>