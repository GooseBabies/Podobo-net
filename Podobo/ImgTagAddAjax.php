<?php
	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	
	if(isset($_GET["tag"])) { $tag = html_entity_decode($_GET["tag"]); } else { $tag = ""; };
	if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = -1; }; 
	if(isset($_GET["cat"])) { $category = $_GET["cat"]; } else { $category = 0; };

	$tag = str_replace("_", " ", $tag);
	
	if($tag != "" and $id != -1)
	{
		//echo $tag;
		if($category == 17){
			if(!CheckIfSourceExists($id, $tag)){
				$sources = GetSources($id);
				//echo print_r($sources);
				array_push($sources, $tag);
				//echo print_r($sources);
				AddSource($id, implode(" ", $sources));
				UpdateAutoBooruFlag($id);
			}
		}
		else{
			$sql = $db->prepare("select hash from files where id = :id");
			$sql->bindValue(':id', $id , SQLITE3_INTEGER);
			$hash = $sql->execute()->fetchArray()[0] ?? '';

			$sql = $db->prepare("select tagid from tags where tag_name = :tag");
			$sql->bindValue(':tag', $tag, SQLITE3_TEXT);
			$tagid = $sql->execute()->fetchArray()[0] ?? -1;

			if($tagid == -1 and $hash != '')
			{
				$sql = $db->prepare("INSERT INTO tags (tag_name, category, tag_display, tag_count) VALUES (:tag, :category, :tag_display, 0);");
				$sql->bindValue(':tag', $tag, SQLITE3_TEXT);
				$sql->bindValue(':category', $category, SQLITE3_INTEGER);
				$sql->bindValue(':tag_display', $tag, SQLITE3_TEXT);
				$result = $sql->execute();

				$sql = $db->prepare("select tagid from tags where tag_name = :tag");
				$sql->bindValue(':tag', $tag, SQLITE3_TEXT);
				$tagid = $sql->execute()->fetchArray()[0] ?? '';

				if (!CheckDuplicateTags($tagid, $hash))
				{
					AddNewTag($tagid, $hash);
					CheckForParentTags($tagid, $hash);
				}
			}
			else if ($tagid > -1)
			{
				if (!CheckDuplicateTags($tagid, $hash))
				{
					AddNewTag($tagid, $hash);
					CheckForParentTags($tagid, $hash);
				}
			}
		}
	}

    echo json_encode("");
	$db = null;
	
	function CheckDuplicateTags($tagid, $hash)
	{
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
	
	function AddNewTag($tagid, $hash)
	{
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
	
	function CheckForParentTags($childid, $hash)
	{
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
	
	function GetParentTagsIDs($childid)
	{
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
	
	function GetTagList($hash)
	{
		global $db;
		$sql = $db->prepare("select tag_list from files where hash = :hash");
		$sql->bindValue(':hash', $hash, SQLITE3_TEXT);
		$taglist = $sql->execute()->fetchArray()[0] ?? '';
		return $taglist;
	}
	
	function UpdateTagList($hash, $taglist)
	{
		global $db;
		$sql = $db->prepare("update files set tag_list = :tag_list where hash = :hash");
		$sql->bindValue(':tag_list', $taglist, SQLITE3_TEXT);
		$sql->bindValue(':hash', $hash, SQLITE3_TEXT);
		$result = $sql->execute();
	}
	
	function IncrementTagCount($tagid)
	{
		global $db;
		$sql = $db->prepare("update tags set tag_count = tag_count + 1 where tagid = :tagid");
		$sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
		$result = $sql->execute();
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
		$result = $sql->execute();
	}

	function UpdateAutoBooruFlag($id){
		global $db;
		$sql = $db->prepare("update files set booru_tagged = 0 where id = :id");
		$sql->bindValue(':id', $id, SQLITE3_INTEGER);
		$result = $sql->execute();
	}
?>