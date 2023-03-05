<?php
    $db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
    $db->exec('PRAGMA foreign_keys = ON;');

    if(isset($_GET["tagid"])) { $tagid = $_GET["tagid"]; } else { $tagid = -1; };
	$output = "";

    try{
        $db->exec('BEGIN;');
        $db->enableExceptions(true);

        if($tagid != -1) 
        {
            $sql = $db->prepare("DELETE FROM tags WHERE tagid = :tagid");
            $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
            $result = $sql->execute();

            //remove tags from tag_lists

            echo json_encode("Tag Deleted");
        }
        else
        {
            echo json_encode("Error with Deleting Tag");
        }

        $db->exec('COMMIT;');
    }catch(Exception $e){
        $output = $db->lastErrorMsg();
        $db->exec('ROLLBACK;');
    }
	$db = null;
?>