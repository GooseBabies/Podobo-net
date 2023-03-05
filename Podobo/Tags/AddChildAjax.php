<?php
	//$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
    //$db = new SQLite3("Y:\\Database\\nevada.db");
    $db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
    $db->exec('PRAGMA foreign_keys = ON;');
    $db->busyTimeout(100);
	
	if(isset($_GET["tagid"])) { $tagid = $_GET["tagid"]; } else { $tagid = -1; };
    if(isset($_GET["child"])) { $child = $_GET["child"]; } else { $child = ""; };
	$output = "";
	
    try{
        if($child != "" and $child != " ") 
        {
            $sql = $db->prepare("select tagid from tags where tag_name = :child COLLATE NOCASE");
            $sql->bindValue(':child', str_replace("_", " ", $child) , SQLITE3_TEXT);
            $childid = $sql->execute()->fetchArray()[0] ?? -1;

            if($childid == -1){    // If Child Tag is New
                $output = "Error - Cannot add a child tag that is new";         
            }
            else{
                $db->exec('BEGIN;');
                $db->enableExceptions(true);

                    $sql = $db->prepare("INSERT INTO parents (child, parent, retro) VALUES (:child, :parent, 0)");
                    $sql->bindValue(':child', $childid, SQLITE3_INTEGER);
                    $sql->bindValue(':parent', $tagid, SQLITE3_INTEGER);
                    $result = $sql->execute();

                    // $sql = $db->prepare("Update tags set child = 1 where tagid = :child");
                    // $sql->bindValue(':child', $childid, SQLITE3_INTEGER);
                    // $result = $sql->execute();

                    // $sql = $db->prepare("Update tags set parent = 1 where tagid = :parent");
                    // $sql->bindValue(':parent', $tagid, SQLITE3_INTEGER);
                    // $result = $sql->execute();
                    
                    $db->exec('COMMIT;');
                    $output = "<i>" . $child . "</i> Added";
            }
        }
    }catch(exception $e){
        echo json_encode($db->lastErrorMsg());
        $db->exec('ROLLBACK;');
    }
    
	echo json_encode($output);			
	
	$db = null;
?>