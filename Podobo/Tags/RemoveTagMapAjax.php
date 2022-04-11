<?php
	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	
	if(isset($_GET["boorutag"])) { $boorutag = $_GET["boorutag"]; } else { $boorutag = ''; };
    if(isset($_GET["bs"])) { $bs = $_GET["bs"]; } else { $bs = ''; };
    if(isset($_GET["tagid"])) { $tagid = $_GET["tagid"]; } else { $tagid = -1; };
	$output = "";
    //echo $boorutag;
	
	if($tagid != -1 and $boorutag != "" and $boorutag != " ") 
    {
        $sql = $db->prepare("DELETE FROM tag_map WHERE booru_tag = :boorutag AND tagid = :tagid and booru_source = :bs");
        $sql->bindValue(':boorutag', $boorutag, SQLITE3_TEXT);
        $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
        $sql->bindValue(':bs', $bs, SQLITE3_INTEGER);
        $result = $sql->execute();	

        $sql = $db->prepare("Update booru_proc set processed = 0 and ignored = 0 WHERE booru_tag = :boorutag and booru_source = :bs");
        $sql->bindValue(':boorutag', $boorutag, SQLITE3_TEXT);
        $sql->bindValue(':bs', $bs, SQLITE3_INTEGER);
        $result = $sql->execute();

        echo json_encode($boorutag);
    }
    else
    {
        echo json_encode("Error");
    }

	$db = null;
?>