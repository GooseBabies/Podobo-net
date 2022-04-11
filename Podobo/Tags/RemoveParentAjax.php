<?php
	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	
	if(isset($_GET["childid"])) { $childid = $_GET["childid"]; } else { $childid = -1; };
    if(isset($_GET["parentid"])) { $parentid = $_GET["parentid"]; } else { $parentid = -1; };
	$output = "";
	
	if($childid != -1 and $parentid != -1) 
    {
        $sql = $db->prepare("DELETE FROM parents WHERE child = :child AND parent = :parent");
        $sql->bindValue(':child', $childid, SQLITE3_INTEGER);
        $sql->bindValue(':parent', $parentid, SQLITE3_INTEGER);
        $result = $sql->execute();	
        echo json_encode(array($childid, $parentid));
    }
    else
    {
        echo json_encode("Error");
    }

	$db = null;
?>