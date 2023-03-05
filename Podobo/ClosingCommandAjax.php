<?php
	//$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
    //$db = new SQLite3("Y:\\Database\\nevada.db");
    $db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
    $db->exec('PRAGMA foreign_keys = ON;');
    $db->busyTimeout(100);

	try{
        $db->exec('BEGIN;');
        $db->enableExceptions(true);

		$sql = $db->prepare("update settings set command = 2 where id = 1");
    	$result = $sql->execute();	

        $db->exec('COMMIT;');
    }
    catch(Exception $e){
        $output = $db->lastErrorMsg();
        $db->exec('ROLLBACK;');
    }
	
	$db = null;
?>
