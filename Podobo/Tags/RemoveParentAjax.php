<?php
	//$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
    //$db = new SQLite3("Y:\\Database\\nevada.db");
    $db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
    $db->exec('PRAGMA foreign_keys = ON;');
    $db->busyTimeout(100);
	
	if(isset($_GET["childid"])) { $childid = $_GET["childid"]; } else { $childid = -1; };
    if(isset($_GET["parentid"])) { $parentid = $_GET["parentid"]; } else { $parentid = -1; };
	$output = "";

    try{
        if($childid != -1 and $parentid != -1) 
        {
            $db->exec('BEGIN;');
            $db->enableExceptions(true);

            $sql = $db->prepare("DELETE FROM parents WHERE child = :child AND parent = :parent");
            $sql->bindValue(':child', $childid, SQLITE3_INTEGER);
            $sql->bindValue(':parent', $parentid, SQLITE3_INTEGER);
            $result = $sql->execute();	

            $sql = $db->prepare("Update tags set child = 0 where tagid = :child");
            $sql->bindValue(':child', $childid, SQLITE3_INTEGER);
            $result = $sql->execute();

            $sql = $db->prepare("Update tags set parent = 0 where tagid = :parent");
            $sql->bindValue(':parent', $parentid, SQLITE3_INTEGER);
            $result = $sql->execute();


            echo json_encode(array($childid, $parentid));

            $db->exec('COMMIT;');
        }
        else
        {
            echo json_encode("Error");
        }
    }catch(Exception $e){
        $output = $db->lastErrorMsg();
        $db->exec('ROLLBACK;');
    }

	$db = null;
?>