<?php

    $db = new SQLite3("Y:\\LavaPool\\Iowa.db");
	$db->exec('PRAGMA foreign_keys = ON;');
	$db->busyTimeout(100);
	
	if(isset($_GET["kept"])) { $kept = $_GET["kept"]; } else { $kept = -1; }; 
    if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = -1; }; 

    if($kept == 1){
        //Ignore
        $sql = $db->prepare("update blacklist set processed = 1 where id=:id");
        $sql->bindValue(":id", $id, SQLITE3_INTEGER);
        $sql->execute();
    }
    else{
        //Blacklist
        $sql = $db->prepare("UPDATE blacklist set processed = 1, blacklisted = 2 where id=:id;");
        $sql->bindValue(":id", $id, SQLITE3_INTEGER);
        $sql->execute();
    }

    //select next dupe to process
    $sql = $db->prepare("select * from blacklist where processed = 0 and rej_count > 0 limit 1");
	$mediaid = $sql->execute()->fetchArray();

    if($mediaid){
        echo $mediaid[0];
    }
    else{
        $sql = $db->prepare("update processed set blacklist=1");
        $sql->execute();
        echo -1;
    }
    
	$db = null;
?>