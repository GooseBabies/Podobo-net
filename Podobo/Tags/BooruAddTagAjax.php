<?php
	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	$db->busyTimeout(100);
	$db->enableExceptions(true);
	
	if(isset($_GET["tag"])) { $tag = html_entity_decode($_GET["tag"]); } else { $tag = ""; };
	if(isset($_GET["parents"])) { $parents = html_entity_decode($_GET["parents"]); } else { $parents = ""; };
	if(isset($_GET["siblings"])) { $siblings = html_entity_decode($_GET["siblings"]); } else { $siblings = ""; };
	if(isset($_GET["bt"])) { $bt = $_GET["bt"]; } else { $bt = ""; };
	if(isset($_GET["bs"])) { $bs = $_GET["bs"]; } else { $bs = ""; };
	if(isset($_GET["cat"])) { $category = $_GET["cat"]; } else { $category = 0; };
	$hashes = [];
	$ids = [];

	try{
		$tag = trim(str_replace("_", " ", $tag));
	
		if($tag != "")
		{
			$siblings_array = explode(" ", $siblings);
			$parents_array = explode(" ", $parents);

			$db->exec('BEGIN;');
			$db->enableExceptions(true);
			
			//get all image hashes that are for the booru_tag and booru_source
			$sql = $db->prepare("select hash from booru_proc where booru_tag = :bt and booru_source = :bs");
			$sql->bindValue(':bt', $bt, SQLITE3_TEXT);
			$sql->bindValue(':bs', $bs, SQLITE3_INTEGER);
			$result = $sql->execute();
			while ($row = $result->fetchArray()) {
				array_push($hashes, $row);
			}

			//sometimes the booru tag stored in the database is stored as the special character or the html code for the special character
			//so if we don't find it when looking for the code of the special character check again for the special character

			if(empty($hashes)){
				$sql = $db->prepare("select hash from booru_proc where booru_tag = :bt and booru_source = :bs");
				$sql->bindValue(':bt', html_entity_decode($bt), SQLITE3_TEXT);
				$sql->bindValue(':bs', $bs, SQLITE3_INTEGER);
				$result = $sql->execute();
				while ($row = $result->fetchArray()) {
					array_push($hashes, $row);
				}
			}
			
			$sql = $db->prepare("select tagid from tags where tag_name = :tag");
			$sql->bindValue(':tag', $tag, SQLITE3_TEXT);
			$tagid = $sql->execute()->fetchArray()[0] ?? '';
			if($tagid == '')
			{
				$sql = $db->prepare("INSERT INTO tags (tag_name, category, tag_display, tag_count, alias, preferred, child, parent) VALUES (:tag, :category, :tag_display, 0, 0, 0, 0, 0)");
				$sql->bindValue(':tag', $tag, SQLITE3_TEXT);
				$sql->bindValue(':category', $category, SQLITE3_INTEGER);
				$sql->bindValue(':tag_display', $tag, SQLITE3_TEXT);
				$result = $sql->execute();

				$tagid = $db->lastInsertRowid();
				if(count($hashes) > 0)
				{
					for ($i = 0; $i <= count($hashes) - 1; $i++)
					{
							if (!CheckDuplicateTags($tagid, $hashes[$i][0]))
							{
								AddNewTag($tagid, $hashes[$i][0]);
								CheckForParentTags($tagid, $hashes[$i][0]);
							}
					}
				}
			}
			
			//add row to tagmap
			$sql = $db->prepare("insert into tag_map (booru_tag, booru_source, tagid) values (:bt, :bs, :tagid)");
			$sql->bindValue(':bt', $bt, SQLITE3_TEXT);
			$sql->bindValue(':bs', $bs, SQLITE3_INTEGER);
			$sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
			$result = $sql->execute();
			
			//add new parents to tags
			if(!empty($parents_array))
			{
				for ($i = 0; $i <= count($parents_array) - 1; $i++)
				{
					if($parents_array[$i] != "" and $parents_array[$i] != " ") 
					{
						$sql = $db->prepare("select tagid from tags where tag_name = :parent");
						$sql->bindValue(':parent', str_replace("_", " ", $parents_array[$i]) , SQLITE3_TEXT);
						$parentid = $sql->execute()->fetchArray()[0] ?? -1;
						
						if($parentid != -1){
							$sql = $db->prepare("INSERT INTO parents (child, parent, retro) VALUES (:child, :parent, 0)");
							$sql->bindValue(':child', $tagid, SQLITE3_INTEGER);
							$sql->bindValue(':parent', $parentid, SQLITE3_INTEGER);
							$result = $sql->execute();

							// $sql = $db->prepare("Update tags set child = 1 where tagid = :child");
							// $sql->bindValue(':child', $tagid, SQLITE3_INTEGER);
							// $result = $sql->execute();

							// $sql = $db->prepare("Update tags set parent = 1 where tagid = :parent");
							// $sql->bindValue(':parent', $parentid, SQLITE3_INTEGER);
							// $result = $sql->execute();
						}
					}			
				}
			}
			
			//add new alias siblings to tag
			if(!empty($siblings_array))
			{
				for ($i = 0; $i <= count($siblings_array) - 1; $i++)
				{
					if($siblings_array[$i] != "" and $siblings_array[$i] != " ")
					{	
						$sql = $db->prepare("select tagid from tags where tag_name = :alias");
						$sql->bindValue(':alias', str_replace("_", " ", $siblings_array[$i]) , SQLITE3_TEXT);
						$siblingid = $sql->execute()->fetchArray()[0] ?? -1;

						if($siblingid == -1)
						{
							$sql = $db->prepare("INSERT INTO tags (tag_name, category, tag_display, tag_count, alias, preferred, child, parent) VALUES (:tag, :category, :tag_display, 0, 0, 0, 0, 0);");
							$sql->bindValue(':tag', str_replace("_", " ", $siblings_array[$i]), SQLITE3_TEXT);
							$sql->bindValue(':category', $category, SQLITE3_INTEGER);
							$sql->bindValue(':tag_display', str_replace("_", " ", $siblings_array[$i]) . " Displays as: " . str_replace("_", " ", $tag), SQLITE3_TEXT);
							$result = $sql->execute();

							$siblingid = $db->lastInsertRowid();
						}			
						
						$sql = $db->prepare("INSERT INTO siblings (alias, preferred, retro) VALUES (:alias, :preferred, 0)");
						$sql->bindValue(':alias', $siblingid, SQLITE3_INTEGER);
						$sql->bindValue(':preferred', $tagid, SQLITE3_INTEGER);
						$result = $sql->execute();

						// $sql = $db->prepare("Update tags set alias = 1 where tagid = :alias");
						// $sql->bindValue(':alias', $siblingid, SQLITE3_INTEGER);
						// $result = $sql->execute();

						// $sql = $db->prepare("Update tags set preferred = 1 where tagid = :preferred");
						// $sql->bindValue(':preferred', $tagid, SQLITE3_INTEGER);
						// $result = $sql->execute();
					}			
				}
			}
			
			//add tag to each image
			
			if(count($hashes) > 0)
			{
				for ($i = 0; $i <= count($hashes) - 1; $i++)
				{
					if (!CheckDuplicateTags($tagid, $hashes[$i][0]))
					{
						AddNewTag($tagid, $hashes[$i][0]);
						CheckForParentTags($tagid, $hashes[$i][0]);
					}
					$sql = $db->prepare("update booru_proc set processed=1 where booru_tag = :bt and booru_source = :bs and hash = :hash");	
					$sql->bindValue(':bt', $bt, SQLITE3_TEXT);
					$sql->bindValue(':bs', $bs, SQLITE3_INTEGER);
					$sql->bindValue(':hash', $hashes[$i][0], SQLITE3_TEXT);
					$result = $sql->execute();
				}
			}

			array_push($ids, $tagid);
			
			//output image ids
			for ($i = 0; $i <= count($hashes) - 1; $i++)
			{
				$sql = $db->prepare("select id from files where hash = :hash");
				$sql->bindValue(':hash', $hashes[$i][0] , SQLITE3_TEXT);
				$id = $sql->execute()->fetchArray()[0] ?? -1;
				array_push($ids, $id);
			}

			$db->exec('COMMIT;');
			echo json_encode($ids);
		}
	}
	catch (Exception $e){	
		$output = json_encode($db->lastErrorMsg());	
		$db->exec('ROLLBACK;');
	}
	
	$db = null;
	
	function CheckDuplicateTags($tagid, $hash)
	{
		try{
			global $db;
			$sql = $db->prepare("SELECT COUNT(*) FROM files WHERE hash = :hash AND tag_list like :tag_list");
			$sql->bindValue(':hash', $hash, SQLITE3_TEXT);
			$sql->bindValue(':tag_list', "%;" . strval($tagid) . ";%", SQLITE3_TEXT);
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
	
	function AddNewTag($tagid, $hash)
	{
		try{
			$taglist = GetTagList($hash);

		if ($taglist == "" or $taglist == ";")
		{
			$taglist = ";" . strval($tagid) . ";";
		}
		else
		{
			$taglist = $taglist . strval($tagid) . ";";
		}

		UpdateTagList($hash, $taglist);

		IncrementTagCount($tagid);
		}
		catch(Exception $e){

		}
	}
	
	function CheckForParentTags($childid, $hash)
	{
		try{
			$parentagids = GetParentTagsIDs($childid);

			if (count($parentagids) > 0)
			{
				for ($i = 0; $i <= count($parentagids) - 1; $i++)
				{
					if (!CheckDuplicateTags($parentagids[$i][0], $hash))
					{
						AddNewTag($parentagids[$i][0], $hash);
					}
					CheckForParentTags($parentagids[$i][0], $hash);
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
	
	function GetTagList($hash)
	{
		try{
			global $db;
			$sql = $db->prepare("select tag_list from files where hash = :hash");
			$sql->bindValue(':hash', $hash, SQLITE3_TEXT);
			$taglist = $sql->execute()->fetchArray()[0] ?? '';
			return $taglist;
		}
		catch(Exception $e){

		}
	}
	
	function UpdateTagList($hash, $taglist)
	{
		try{
			global $db;
			$sql = $db->prepare("update files set tag_list = :tag_list where hash = :hash");
			$sql->bindValue(':tag_list', $taglist, SQLITE3_TEXT);
			$sql->bindValue(':hash', $hash, SQLITE3_TEXT);
			$result = $sql->execute();
		}
		catch(Exception $e){

		}
	}
	
	function IncrementTagCount($tagid)
	{
		try{
			global $db;
			$sql = $db->prepare("update tags set tag_count = tag_count + 1 where tagid = :tagid");
			$sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
			$result = $sql->execute();
		}
		catch(Exception $e){

		}
	}
?>