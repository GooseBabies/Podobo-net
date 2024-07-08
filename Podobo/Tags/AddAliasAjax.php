<?php
    $db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
    $db->exec('PRAGMA foreign_keys = ON;');
    $db->busyTimeout(100);    
	
	if(isset($_GET["tagid"])) { $tagid = $_GET["tagid"]; } else { $tagid = -1; };
    if(isset($_GET["alias"])) { $alias = Html_entity_decode($_GET["alias"]); } else { $alias = ""; };

    try{
        if($alias != "" and $alias != " ")
        {	
            $db->exec('BEGIN;');
            $db->enableExceptions(true);

            $sql = $db->prepare("select category from tags where tagid = :tagid");
            $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
            $category = $sql->execute()->fetchArray()[0] ?? -1;
            
            $sql = $db->prepare("select tagid from tags where tag_name = :alias COLLATE NOCASE");
            $sql->bindValue(':alias', str_replace("_", " ", $alias) , SQLITE3_TEXT);
            $aliasid = $sql->execute()->fetchArray()[0] ?? -1;

            $sql = $db->prepare("select tag_count from tags where tag_name = :alias COLLATE NOCASE");
            $sql->bindValue(':alias', str_replace("_", " ", $alias) , SQLITE3_TEXT);
            $aliascount = $sql->execute()->fetchArray()[0] ?? -1;

            $sql = $db->prepare("select tag_name from tags where tagid = :tagid");
            $sql->bindValue(':tagid', $tagid , SQLITE3_INTEGER);
            $tag = $sql->execute()->fetchArray()[0] ?? '';

            if($aliasid == -1)  //If alias is a new tag
            {
                $sql = $db->prepare("INSERT INTO tags (tag_name, category, tag_display, tag_count, alias, preferred, child, parent) VALUES (:tag, :category, :tag_display, 0, 0, 0, 0, 0);");
                $sql->bindValue(':tag', trim(str_replace("_", " ", $alias)), SQLITE3_TEXT);
                $sql->bindValue(':category', $category, SQLITE3_INTEGER);
                $sql->bindValue(':tag_display', trim(str_replace("_", " ", $alias)) . " Displays as: " . trim(str_replace("_", " ", $tag)), SQLITE3_TEXT);
                $result = $sql->execute();

                $aliasid = $db->lastInsertRowid();
                $retro = 1;
            }
            else{
                $retro = 0;
            }			
            
            $sql = $db->prepare("INSERT INTO siblings (alias, preferred, retro) VALUES (:alias, :preferred, :retro)");
            $sql->bindValue(':alias', $aliasid, SQLITE3_INTEGER);
            $sql->bindValue(':preferred', $tagid, SQLITE3_INTEGER);
            $sql->bindValue(':retro', $retro, SQLITE3_INTEGER);
            $result = $sql->execute();

            $db->exec('COMMIT;');
            echo json_encode("<i>" . $alias . "</i> added");
            
        }
        else{
            echo json_encode("Error");
        }
    }
    catch (Exception $e){
        echo json_encode($db->lastErrorMsg());
        $db->exec('ROLLBACK;');
    }
    
	$db = null;
?>