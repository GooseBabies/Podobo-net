<?php
	$db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
	$db->exec('PRAGMA foreign_keys = ON;');
	$db->busyTimeout(100);
	$db->enableExceptions(true);
	
	if(isset($_GET["tag"])) { $tag = html_entity_decode($_GET["tag"]); } else { $tag = ""; };
	if(isset($_GET["parents"])) { $parents = html_entity_decode($_GET["parents"]); } else { $parents = ""; };
	if(isset($_GET["siblings"])) { $siblings = html_entity_decode($_GET["siblings"]); } else { $siblings = ""; };
	if(isset($_GET["bt"])) { $bt = $_GET["bt"]; } else { $bt = ""; };
	if(isset($_GET["namespace"])) { $namespace = html_entity_decode($_GET["namespace"]); } else { $namespace = ""; };
	if(isset($_GET["bs"])) { $bs = $_GET["bs"]; } else { $bs = ""; };
	if(isset($_GET["cat"])) { $category = $_GET["cat"]; } else { $category = 0; };
	$ids = [];

	try{
		$tag = trim(str_replace("_", " ", $tag));
	
		if($tag != "")
		{
			$siblings_array = explode(" ", $siblings);
			$parents_array = explode(" ", $parents);

			$db->exec('BEGIN;');
			$db->enableExceptions(true);
					
			//get all image ids that are for the booru_tag and booru_source
			$sql = $db->prepare("select media_id from booru_proc where booru_tag = :bt and booru_source = :bs and namespace = :namespace");
			$sql->bindValue(':bt', $bt, SQLITE3_TEXT);
			$sql->bindValue(':namespace', $namespace, SQLITE3_TEXT);
			$sql->bindValue(':bs', $bs, SQLITE3_INTEGER);
			$result = $sql->execute();
			while ($row = $result->fetchArray()) {
				array_push($ids, $row);
			}

			//sometimes the booru tag stored in the database is stored as the special character or the html code for the special character
			//so if we don't find it when looking for the code of the special character check again for the special character

			if(empty($ids)){
				$sql = $db->prepare("select media_id from booru_proc where booru_tag = :bt and booru_source = :bs and namespace = :namespace");
				$sql->bindValue(':bt', html_entity_decode($bt), SQLITE3_TEXT);
				$sql->bindValue(':namespace', $namespace, SQLITE3_TEXT);
				$sql->bindValue(':bs', $bs, SQLITE3_INTEGER);
				$result = $sql->execute();
				while ($row = $result->fetchArray()) {
					array_push($ids, $row);
				}
			}
			
			$sql = $db->prepare("select tagid from tags where tag_name = :tag COLLATE NOCASE");
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
				if(count($ids) > 0)
				{
					for ($i = 0; $i <= count($ids) - 1; $i++)
					{
							if (!CheckDuplicateTags($tagid, $ids[$i][0]))
							{
								AddNewTag($tagid, $ids[$i][0]);
								CheckForParentTags($tagid, $ids[$i][0]);
							}
					}
				}
			}
			
			//add row to tagmap
			$sql = $db->prepare("insert into tag_map (namespace, booru_tag, booru_source, tagid) values (:namespace, :bt, :bs, :tagid)");
			$sql->bindValue(':namespace', $namespace, SQLITE3_TEXT);
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
						$sql = $db->prepare("select tagid from tags where tag_name = :parent COLLATE NOCASE");
						$sql->bindValue(':parent', str_replace("_", " ", $parents_array[$i]) , SQLITE3_TEXT);
						$parentid = $sql->execute()->fetchArray()[0] ?? -1;
						
						if($parentid != -1){
							$sql = $db->prepare("INSERT INTO parents (child, parent, retro) VALUES (:child, :parent, 0)");
							$sql->bindValue(':child', $tagid, SQLITE3_INTEGER);
							$sql->bindValue(':parent', $parentid, SQLITE3_INTEGER);
							$result = $sql->execute();
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
						$sql = $db->prepare("select tagid from tags where tag_name = :alias COLLATE NOCASE");
						$sql->bindValue(':alias', str_replace("_", " ", $siblings_array[$i]) , SQLITE3_TEXT);
						$siblingid = $sql->execute()->fetchArray()[0] ?? -1;

						if($siblingid == -1)  //If alias is new
						{
							$sql = $db->prepare("INSERT INTO tags (tag_name, category, tag_display, tag_count, alias, preferred, child, parent) VALUES (:tag, :category, :tag_display, 0, 0, 0, 0, 0);");
							$sql->bindValue(':tag', str_replace("_", " ", $siblings_array[$i]), SQLITE3_TEXT);
							$sql->bindValue(':category', $category, SQLITE3_INTEGER);
							$sql->bindValue(':tag_display', str_replace("_", " ", $siblings_array[$i]) . " Displays as: " . str_replace("_", " ", $tag), SQLITE3_TEXT);
							$result = $sql->execute();

							$siblingid = $db->lastInsertRowid();
							$retro = 1;
						}
						else{
							$retro = 0;
						}

						$sql = $db->prepare("INSERT INTO siblings (alias, preferred, retro) VALUES (:alias, :preferred, :retro)");
						$sql->bindValue(':alias', $siblingid, SQLITE3_INTEGER);
						$sql->bindValue(':preferred', $tagid, SQLITE3_INTEGER);
						$sql->bindValue(':retro', $retro, SQLITE3_INTEGER);
						$result = $sql->execute();
					}			
				}
			}
			
			//add tag to each image
			//output image ids
			for ($i = 0; $i <= count($ids) - 1; $i++)
			{
				$sql = $db->prepare("update booru_proc set processed=1 where booru_tag = :bt and namespace = :namespace and booru_source = :bs and media_id = :id");
				$sql->bindValue(':namespace', $namespace, SQLITE3_TEXT);
				$sql->bindValue(':bt', $bt, SQLITE3_TEXT);
				$sql->bindValue(':bs', $bs, SQLITE3_INTEGER);
				$sql->bindValue(':id', $ids[$i][0], SQLITE3_TEXT);
				$result = $sql->execute();

				if (!CheckDuplicateTags($tagid, $ids[$i][0]))
				{
					AddNewTag($tagid, $ids[$i][0]);					
				}
				CheckForParentTags($tagid, $ids[$i][0]);
			}

			array_push($ids, $tagid);

			$db->exec('COMMIT;');
			echo json_encode($ids);
		}
	}
	catch (Exception $e){	
		$output = json_encode($db->lastErrorMsg());	
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