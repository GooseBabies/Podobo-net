<?php
	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
    

    if(isset($_GET["aliasid"])) { $aliasid = $_GET["aliasid"]; } else { $aliasid = -1; };
    if(isset($_GET["preferredid"])) { $preferredid = $_GET["preferredid"]; } else { $preferredid = -1; };
	$output = "";

    try{
        $db->exec('BEGIN;');
        $db->enableExceptions(true);

        if($aliasid != -1 and $preferredid != -1) 
        {
            $sql = $db->prepare("DELETE FROM siblings WHERE alias = :alias AND preferred = :preferred");
            $sql->bindValue(':alias', $aliasid, SQLITE3_INTEGER);
            $sql->bindValue(':preferred', $preferredid, SQLITE3_INTEGER);
            $result = $sql->execute();	

            // $sql = $db->prepare("Update tags set alias = 0 where tagid = :alias");
            // $sql->bindValue(':alias', $aliasid, SQLITE3_INTEGER);
            // $result = $sql->execute();

            // $sql = $db->prepare("Update tags set preferred = 0 where tagid = :preferred");
            // $sql->bindValue(':preferred', $preferredid, SQLITE3_INTEGER);
            // $result = $sql->execute();

            echo json_encode($aliasid);
        }
        else
        {
            echo json_encode("Error");
        }

        $db->exec('COMMIT;');
    }catch(Exception $e){
        $output = $db->lastErrorMsg();
        $db->exec('ROLLBACK;');
    }
	$db = null;
?>