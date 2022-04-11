<?php
	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	
	if(isset($_GET["boorutag"])) { $boorutag = $_GET["boorutag"]; } else { $boorutag = ''; };
    if(isset($_GET["bs"])) { $bs = $_GET["bs"]; } else { $bs = -1; };
	$output = "";
	
	if($boorutag != "" and $boorutag != " " and $bs != -1) 
    {
        $sql = $db->prepare("Update booru_proc set processed = 0, ignored = 0 WHERE booru_tag = :boorutag and booru_source = :bs");
        $sql->bindValue(':boorutag', $boorutag, SQLITE3_TEXT);
        $sql->bindValue(':bs', $bs, SQLITE3_INTEGER);
        $result = $sql->execute();

        echo json_encode($boorutag . "|" . $bs);
    }
    else
    {
        echo json_encode("Error");
    }

	$db = null;
?>