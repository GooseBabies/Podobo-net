<?php

    $db = new SQLite3("Y:\\LavaPool\\Iowa.db");
	$db->exec('PRAGMA foreign_keys = ON;');
	$db->busyTimeout(100);
	
    if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = -1; }; 

    $sql = $db->prepare("SELECT item FROM blacklist WHERE id = :id");
    $sql->bindValue(':id', $id, SQLITE3_INTEGER);
    $blacklistitem = $sql->execute()->fetchArray()[0] ?? "";

    $sql = $db->prepare("SELECT COUNT(*) FROM whitelist WHERE item = :item");
    $sql->bindValue(':item', $blacklistitem, SQLITE3_TEXT);
    $whitelistcount = $sql->execute()->fetchArray()[0] ?? 0;

    if($whitelistcount < 1){
        $sql = $db->prepare("update blacklist set processed = 1, whitelisted = 1 where id=:id");
        $sql->bindValue(":id", $id, SQLITE3_INTEGER);
        $sql->execute();

        $sql = $db->prepare("INSERT INTO whitelist (item) VALUES (:item)");
        $sql->bindValue(':item', $blacklistitem, SQLITE3_TEXT);
        $result = $sql->execute();
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