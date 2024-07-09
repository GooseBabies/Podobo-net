<?php

    $db = new SQLite3("Y:\\LavaPool\\Iowa.db");
	$db->exec('PRAGMA foreign_keys = ON;');
	$db->busyTimeout(100);
	
	if(isset($_GET["kept"])) { $kept = $_GET["kept"]; } else { $kept = -1; }; 
    if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = -1; }; 

    if($kept == 1){
        //permanatly keep whitelist
        $sql = $db->prepare("update blacklist set permawhitelisted = 1 where id=:id");
        $sql->bindValue(":id", $id, SQLITE3_INTEGER);
        $sql->execute();
    }
    elseif($kept == 0){
        //remove whitelist
        $sql = $db->prepare("select item from blacklist where id=:id");
        $sql->bindValue(":id", $id, SQLITE3_INTEGER);
        $blacklistitem = $sql->execute()->fetchArray()[0] ?? 0;

        $sql = $db->prepare("delete from whitelist where item=:item");
        $sql->bindValue(":item", $blacklistitem, SQLITE3_TEXT);
        $sql->execute();

        $sql = $db->prepare("update blacklist set processed = 0, blacklisted = 0, whitelisted = 0 where id=:id");
        $sql->bindValue(":id", $id, SQLITE3_INTEGER);
        $sql->execute();
    }
    elseif($kept = 2){
        //reset count
        $sql = $db->prepare("update blacklist set rej_count = 0 where id=:id");
        $sql->bindValue(":id", $id, SQLITE3_INTEGER);
        $sql->execute();
    }

    //check if were still on dupes
    $sql = $db->prepare("select id from blacklist where rej_count > 30 and whitelisted = 1 and permawhitelisted = 0 limit 1");
	$whitelistid = $sql->execute()->fetchArray();

    //cases middle of dupes, end of dupes, middle of decide, end of decide

    if($whitelistid){
        //middle of dupes
        echo json_encode($whitelistid[0]);
    }
    else{
        echo json_encode(-1);
    }
    
	$db = null;
?>