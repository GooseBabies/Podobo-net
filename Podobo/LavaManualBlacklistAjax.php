<?php

    $db = new SQLite3("Y:\\LavaPool\\Iowa.db");
	$db->exec('PRAGMA foreign_keys = ON;');
	$db->busyTimeout(100);
	
	if(isset($_GET["item"])) { $item = $_GET["item"]; } else { $item = ""; };
    if(isset($_GET["source"])) { $source = $_GET["source"]; } else { $source = "unknown"; };

    if($item != ""){
        $sql = $db->prepare("INSERT INTO blacklist (item, acc_count, rej_count, source, processed, moved, ignored) VALUES (:item, :acc_count, :rej_count, :source, :processed, :blacklisted, :whitelisted)");
        $sql->bindValue(':item', $item, SQLITE3_TEXT);
        $sql->bindValue(':acc_count', 0, SQLITE3_INTEGER);
        $sql->bindValue(':rej_count', 1, SQLITE3_INTEGER);
        $sql->bindValue(':source', $source, SQLITE3_TEXT);
        $sql->bindValue(':processed', 0, SQLITE3_INTEGER);
        $sql->bindValue(':blacklisted', 0, SQLITE3_INTEGER);
        $sql->bindValue(':whitelisted', 0, SQLITE3_INTEGER);
        $result = $sql->execute();

        echo(json_encode("Added!"));
    }
    else{
        echo(json_encode("Error"));
    }
    
	$db = null;
?>