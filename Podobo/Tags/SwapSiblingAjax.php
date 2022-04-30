<?php
	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	
	if(isset($_GET["aliasid"])) { $aliasid = $_GET["aliasid"]; } else { $aliasid = -1; };
    if(isset($_GET["preferredid"])) { $preferredid = $_GET["preferredid"]; } else { $preferredid = -1; };
	$output = "";
	
	if($aliasid != -1 and $preferredid != -1) 
    {
        Swap($aliasid, $preferredid);

        echo json_encode("Swapped this tag with it's alias Please refresh");
    }
    else
    {
        echo json_encode("Error");
    }

	$db = null;

    function Swap($newpreferred, $newalias){
        global $db;
        $sql = $db->prepare("DELETE FROM siblings WHERE alias = :alias AND preferred = :preferred");
        $sql->bindValue(':alias', $newpreferred, SQLITE3_INTEGER);
        $sql->bindValue(':preferred', $newalias, SQLITE3_INTEGER);
        $result = $sql->execute();	

        $sql = $db->prepare("INSERT INTO siblings (alias, preferred, retro) VALUES (:alias, :preferred, 0)");
        $sql->bindValue(':alias', $newalias, SQLITE3_INTEGER);
        $sql->bindValue(':preferred', $newpreferred, SQLITE3_INTEGER);
        $result = $sql->execute();

        $sql = $db->prepare("UPDATE Tag_Map SET tagid = :newpreferred where tagid = :oldpreferred");
        $sql->bindValue(':newpreferred', $newpreferred, SQLITE3_INTEGER);
        $sql->bindValue(':oldpreferred', $newalias, SQLITE3_INTEGER);
        $result = $sql->execute();

        $sql = $db->prepare("update siblings set preferred = :newpreffered where preferred = :oldpreferred");
        $sql->bindValue(':newpreferred', $newpreferred, SQLITE3_INTEGER);
        $sql->bindValue(':oldpreferred', $newalias, SQLITE3_INTEGER);
        $result = $sql->execute();
    }
?>