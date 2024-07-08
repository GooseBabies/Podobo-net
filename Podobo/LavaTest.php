<?php

    $db = new SQLite3("Y:\\LavaPool\\Iowa.db");
	$db->exec('PRAGMA foreign_keys = ON;');
	$db->busyTimeout(100);
	
	$sql = $db->prepare("select count(*) from whitelist where item=:item");
    $sql->bindValue(":item", 'anonymous', SQLITE3_TEXT);
    $whitelistcount = $sql->execute()->fetchArray()[0] ?? 0;

    echo $whitelistcount;
    
	$db = null;
?>