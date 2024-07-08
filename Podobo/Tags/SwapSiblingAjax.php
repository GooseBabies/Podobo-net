<?php
    $db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
    $db->exec('PRAGMA foreign_keys = ON;');
	
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
            $newaliastag = $sql->execute()->fetchArray()[0];
    
            $sql = $db->prepare("select tag_name from tags where tagid = :alias");
            $sql->bindValue(':alias', $aliasid, SQLITE3_INTEGER);
            $newpreferredtag = $sql->execute()->fetchArray()[0];
    
            //update preferred to temp name
            $sql = $db->prepare("UPDATE tags SET tag_name = 'tempswap' where tagid = :preferred");
            $sql->bindValue(':preferred', $preferredid, SQLITE3_INTEGER);
            $result = $sql->execute();
    
            //update alias tag name to be name that was preferred
            $sql = $db->prepare("UPDATE tags SET tag_name = :newaliastag where tagid = :alias");
            $sql->bindValue(':newaliastag', $newaliastag, SQLITE3_TEXT);
            $sql->bindValue(':alias', $aliasid, SQLITE3_INTEGER);
            $result = $sql->execute();
    
            //update preferred tag name to name that was in alias
            $sql = $db->prepare("UPDATE tags SET tag_name = :newpreferredtag where tagid = :preferred");
            $sql->bindValue(':newpreferredtag', $newpreferredtag, SQLITE3_TEXT);
            $sql->bindValue(':preferred', $preferredid, SQLITE3_INTEGER);
            $result = $sql->execute();

            //update new preferred tag_display to = tag_name
            $sql = $db->prepare("UPDATE tags SET tag_display = tag_name where tagid = :preferred");
            $sql->bindValue(':preferred', $preferredid, SQLITE3_INTEGER);
            $result = $sql->execute();

            $aliases = [];
            //get all alias of preferred
            $sql = $db->prepare("select alias from siblings where preferred = :preferred");
			$sql->bindValue(':preferred', $preferredid, SQLITE3_INTEGER);
			$result = $sql->execute();
			while ($row = $result->fetchArray()) {
				array_push($aliases, $row);
			}            

            //foreach alias update tag_display = tag_name + Displays as: preferred tag_name
            if(count($aliases) > 0){
                for ($i = 0; $i <= count($aliases) - 1; $i++)
				{
                    //echo $aliases[$i][0];
                    $sql = $db->prepare("UPDATE tags SET tag_display = tag_name || ' Displays as: ' || :display where tagid = :alias");
                    $sql->bindValue(':alias', $aliases[$i][0], SQLITE3_INTEGER);
                    $sql->bindValue(':display', $newpreferredtag, SQLITE3_TEXT);
                    $result = $sql->execute();
                }
            }

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