<?php
	//$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
    //$db = new SQLite3("Y:\\Database\\nevada.db");
    $db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
    $db->exec('PRAGMA foreign_keys = ON;');
	$db->busyTimeout(100);

	if(isset($_GET["tagid"])) { $tagid = $_GET["tagid"]; } else { $tagid = -1; };
    if(isset($_GET["parent"])) { $parent = $_GET["parent"]; } else { $parent = ""; };
    if(isset($_GET["cat"])) { $category = $_GET["cat"]; } else { $category = -1; };
	$output = "";
	
    try{
        if($parent != "" and $parent != " ") 
        {
            $sql = $db->prepare("select tagid from tags where tag_name = :parent COLLATE NOCASE");
            $sql->bindValue(':parent', str_replace("_", " ", $parent) , SQLITE3_TEXT);
            $parentid = $sql->execute()->fetchArray()[0] ?? -1;

            $db->exec('BEGIN;');
            $db->enableExceptions(true);

            if($parentid == -1){    // If Parent Tag is New
                $sql = $db->prepare("INSERT INTO tags (tag_name, category, tag_display, tag_count, alias, preferred, child, parent) VALUES (:tag, :category, :tag_display, 0, 0, 0, 0, 0);");
                $sql->bindValue(':tag', trim(str_replace("_", " ", $parent)), SQLITE3_TEXT);
                $sql->bindValue(':category', $category, SQLITE3_INTEGER);
                $sql->bindValue(':tag_display', trim(str_replace("_", " ", $parent)), SQLITE3_TEXT);
                $sql->execute();
            
                $parentid = $db->lastInsertRowid();         
            }
            
            if($parentid != -1){
                $sql = $db->prepare("INSERT INTO parents (child, parent, retro) VALUES (:child, :parent, 0)");
                $sql->bindValue(':child', $tagid, SQLITE3_INTEGER);
                $sql->bindValue(':parent', $parentid, SQLITE3_INTEGER);
                $result = $sql->execute();	

                // $sql = $db->prepare("Update tags set child = 1 where tagid = :child");
                // $sql->bindValue(':child', $tagid, SQLITE3_INTEGER);
                // $result = $sql->execute();

                // $sql = $db->prepare("Update tags set parent = 1 where tagid = :parent");
                // $sql->bindValue(':parent', $parentid, SQLITE3_INTEGER);
                // $result = $sql->execute();
                
                $db->exec('COMMIT;');
                $output = "<i>" . $parent . "</i> Added";
            }
            else{
                $db->exec('ROLLBACK;');
                $output = "Error adding parent";
            }
        }	
    }catch(Excpetion $e){
        $output = $db->lastErrorMsg();
        $db->exec('ROLLBACK;');
    }
	
	echo json_encode($output);			
	
	$db = null;
?>