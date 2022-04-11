<?php
	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	
	if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = -1; };
    if(isset($_GET["tagid"])) { $tagid = $_GET["tagid"]; } else { $tagid = -1; };
	$output = "";
	
	if($id != -1 and $tagid != -1) 
    {
        $taglist = GetTagList($id);
        $taglist = str_replace(";" . $tagid . ";", ";", $taglist);
        UpdateTagList($id, $taglist);
        DecrementTagCount($tagid);
        echo json_encode("a" . $tagid);
    }
    else
    {
        echo json_encode("Error");
    }
    
	$db = null;

    function GetTagList($id)
	{
		global $db;
		$sql = $db->prepare("select tag_list from files where id = :id");
		$sql->bindValue(':id', $id, SQLITE3_INTEGER);
		$taglist = $sql->execute()->fetchArray()[0] ?? '';
		return $taglist;
	}
	
	function UpdateTagList($id, $taglist)
	{
		global $db;
		$sql = $db->prepare("update files set tag_list = :tag_list where id = :id");
		$sql->bindValue(':tag_list', $taglist, SQLITE3_TEXT);
		$sql->bindValue(':id', $id, SQLITE3_INTEGER);
		$result = $sql->execute();
	}

    function DecrementTagCount($tagid)
	{
		global $db;
		$sql = $db->prepare("update tags set tag_count = tag_count - 1 where tagid = :tagid");
		$sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
		$result = $sql->execute();
	}
?>