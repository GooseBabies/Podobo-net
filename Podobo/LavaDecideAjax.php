<?php

    $db = new SQLite3("Y:\\LavaPool\\Iowa.db");
	$db->exec('PRAGMA foreign_keys = ON;');
	$db->busyTimeout(100);
	
	if(isset($_GET["kept"])) { $kept = $_GET["kept"]; } else { $kept = -1; }; 
    if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = -1; }; 

    $checkwhitelist = false;
    $blacklistid = -1;

    // $sql = $db->prepare("select name from media where id=:id");
    // $sql->bindValue(":id", $id, SQLITE3_INTEGER);
    // $name = $sql->execute()->fetchArray()[0] ?? 0; 

    // $artists = explode("_", $name);
    // $artists1 = str_replace(" ", "_", $artists[0]);
    // $artist = explode("+", $artists1);

    if($kept == 1){
        //Don't trash
        $sql = $db->prepare("update media set kept=:kept where id=:id;");
        $sql->bindValue(":kept", $kept, SQLITE3_INTEGER);
        $sql->bindValue(":id", $id, SQLITE3_INTEGER);
        $sql->execute();
    }
    else{
        //trash
        $sql = $db->prepare("update media set kept=:kept where id=:id;");
        $sql->bindValue(":kept", $kept, SQLITE3_INTEGER);
        $sql->bindValue(":id", $id, SQLITE3_INTEGER);
        $sql->execute();
    }

    // for($s = 0; $s <= count($artist) - 1; $s++){
    //     $sql = $db->prepare("select count(*) from whitelist where item=:item");
    //     $sql->bindValue(":item", $artist[$s], SQLITE3_TEXT);
    //     $whitelistcount = $sql->execute()->fetchArray()[0] ?? 0;

    //     $sql = $db->prepare("select count(*) from blacklist where item=:item");
    //     $sql->bindValue(":item", $artist[$s], SQLITE3_TEXT);
    //     $blacklistcount = $sql->execute()->fetchArray()[0] ?? 0;

    //     $sql = $db->prepare("select rej_count from blacklist where item=:item");
    //     $sql->bindValue(":item", $artist[$s], SQLITE3_TEXT);
    //     $rejectcount = $sql->execute()->fetchArray()[0] ?? 0;

    //     if($blacklistcount == 0 && $whitelistcount == 0){
    //         $sql = $db->prepare("INSERT INTO blacklist (item, acc_count, rej_count, source, processed, blacklisted, whitelisted) VALUES (:item, :acc_count, :rej_count, :source, :processed, :blacklisted, :whitelisted)");
    //         $sql->bindValue(':item', $artist[$s], SQLITE3_TEXT);
    //         if($kept == 1){
    //             $sql->bindValue(':acc_count', 1, SQLITE3_INTEGER);
    //             $sql->bindValue(':rej_count', 0, SQLITE3_INTEGER);
    //         }
    //         else{
    //             $sql->bindValue(':acc_count', 0, SQLITE3_INTEGER);
    //             $sql->bindValue(':rej_count', 1, SQLITE3_INTEGER);
    //         }            
    //         $sql->bindValue(':source', $artists[2], SQLITE3_TEXT);
    //         $sql->bindValue(':processed', 0, SQLITE3_INTEGER);
    //         $sql->bindValue(':blacklisted', 0, SQLITE3_INTEGER);
    //         $sql->bindValue(':whitelisted', 0, SQLITE3_INTEGER);
    //         $result = $sql->execute();
    //     }
    //     else{
    //         //if blacklist count is over 30 and under 999 mark check whitelist flag
    //         if($rejectcount > 30 && $rejectcount < 999 && $whitelistcount > 0){
    //             $checkwhitelist = true;
    //             $sql = $db->prepare("select id from blacklist where item=:item");
    //             $sql->bindValue(":item", $artist[$s], SQLITE3_TEXT);
    //             $blacklistid = $sql->execute()->fetchArray()[0] ?? "";
    //         }

    //         if($blacklistcount > 0){
    //             if($whitelistcount < 1){
    //                 //if we've seen this item before and it's not on the whitelist (was ignored), mark for reprocessing
    //                 $sql = $db->prepare("update blacklist set processed = 0 where item = :item");
    //                 $sql->bindValue(':item', $artist[$s], SQLITE3_TEXT);
    //                 $result = $sql->execute();
    //             }

    //             if($kept == 1){
    //                 $sql = $db->prepare("update blacklist set acc_count = acc_count + 1 where item = :item");
    //                 $sql->bindValue(':item', $artist[$s], SQLITE3_TEXT);
    //                 $result = $sql->execute();
    //             }
    //             else{
    //                 $sql = $db->prepare("update blacklist set rej_count = rej_count + 1 where item = :item");
    //                 $sql->bindValue(':item', $artist[$s], SQLITE3_TEXT);
    //                 $result = $sql->execute();
    //             }            

    //             $sql = $db->prepare("select source from blacklist where item = :item");
    //             $sql->bindValue(":item", $artist[$s], SQLITE3_TEXT);
    //             $blacklistsource = $sql->execute()->fetchArray()[0] ?? "";

    //             if($blacklistsource == "unknown"){
    //                 $sql = $db->prepare("update blacklist set source = :source where item = :item");
    //                 $sql->bindValue(':source', $artists[2], SQLITE3_TEXT);
    //                 $sql->bindValue(":item", $artist[$s], SQLITE3_TEXT);
    //                 $result = $sql->execute();
    //             }
    //         }
    //     }
    // }

    $sql = $db->prepare("update media set processed=:processed where id=:id;");
    $sql->bindValue(":processed", 1, SQLITE3_INTEGER);
    $sql->bindValue(":id", $id, SQLITE3_INTEGER);
    $sql->execute();

    //select next dupe to process
    $sql = $db->prepare("select * from media where processed = 0 limit 1");
	$mediaid = $sql->execute()->fetchArray();

    if($mediaid){
        echo json_encode($mediaid[0]);
    }
    else{
        $sql = $db->prepare("update processed set state = 4");
        $sql->execute(); 

        echo json_encode(-1);
    }
    
	$db = null;
?>