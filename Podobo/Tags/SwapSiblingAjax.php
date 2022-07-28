<?php
	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	
	if(isset($_GET["aliasid"])) { $aliasid = $_GET["aliasid"]; } else { $aliasid = -1; };
    if(isset($_GET["preferredid"])) { $preferredid = $_GET["preferredid"]; } else { $preferredid = -1; };
    $db->busyTimeout(100);
	$output = "";
	
	if($aliasid != -1 and $preferredid != -1) 
    {
        $db->exec('BEGIN;');
        $db->enableExceptions(true);

        try
        {
            $sql = $db->prepare("select tag_name from tags where tagid = :preferred");
            $sql->bindValue(':preferred', $preferredid, SQLITE3_INTEGER);
            $preferredtag = $sql->execute()->fetchArray()[0];
    
            $sql = $db->prepare("select tag_name from tags where tagid = :alias");
            $sql->bindValue(':alias', $aliasid, SQLITE3_INTEGER);
            $aliastag = $sql->execute()->fetchArray()[0];
    
            //update preferred to temp name
            $sql = $db->prepare("UPDATE tags SET tag_name = 'tempswap' where tagid = :preferred");
            $sql->bindValue(':preferred', $preferredid, SQLITE3_INTEGER);
            $result = $sql->execute();
    
            //update alias tag name to be name that was preferred
            $sql = $db->prepare("UPDATE tags SET tag_name = :preferredtag where tagid = :alias");
            $sql->bindValue(':preferredtag', $preferredtag, SQLITE3_TEXT);
            $sql->bindValue(':alias', $aliasid, SQLITE3_INTEGER);
            $result = $sql->execute();
    
            //update preferred tag name to name that was in alias
            $sql = $db->prepare("UPDATE tags SET tag_name = :aliastag where tagid = :preferred");
            $sql->bindValue(':aliastag', $aliastag, SQLITE3_TEXT);
            $sql->bindValue(':preferred', $preferredid, SQLITE3_INTEGER);
            $result = $sql->execute();

            $db->exec('COMMIT;');
        }
        catch (Exception $e)
        {
            $output = $db->lastErrorMsg();
            $db->exec('ROLLBACK;');
        }

        echo json_encode("Swapped this tag with it's alias Please refresh");
    }
    else
    {
        echo json_encode("Error");
    }

	$db = null;
?>