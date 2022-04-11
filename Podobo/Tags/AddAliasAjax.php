<?php
	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	
	if(isset($_GET["tagid"])) { $tagid = $_GET["tagid"]; } else { $tagid = ""; };
    if(isset($_GET["alias"])) { $alias = $_GET["alias"]; } else { $alias = ""; };

	$output = "";
	
	if($alias != "" and $alias != " ")
    {	
        $sql = $db->prepare("select category from tags where tagid = :tagid");
        $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
        $category = $sql->execute()->fetchArray()[0] ?? -1;
        
        $sql = $db->prepare("select tagid from tags where tag_name = :alias");
        $sql->bindValue(':alias', str_replace("_", " ", $alias) , SQLITE3_TEXT);
        $aliasid = $sql->execute()->fetchArray()[0] ?? -1;

        $sql = $db->prepare("select tag_name from tags where tagid = :tagid");
        $sql->bindValue(':tagid', $tagid , SQLITE3_INTEGER);
        $tag = $sql->execute()->fetchArray()[0] ?? '';

        if($aliasid == -1)
        {
            $sql = $db->prepare("INSERT INTO tags (tag_name, category, tag_display, tag_count) VALUES (:tag, :category, :tag_display, 0);");
            $sql->bindValue(':tag', str_replace("_", " ", $alias), SQLITE3_TEXT);
            $sql->bindValue(':category', $category, SQLITE3_INTEGER);
            $sql->bindValue(':tag_display', str_replace("_", " ", $alias) . " Displays as: " . str_replace("_", " ", $tag), SQLITE3_TEXT);
            $result = $sql->execute();

            $sql = $db->prepare("select tagid from tags where tag_name = :alias");
            $sql->bindValue(':alias', str_replace("_", " ", $alias) , SQLITE3_TEXT);
            $aliasid = $sql->execute()->fetchArray()[0] ?? -1;
        }			
        
        $sql = $db->prepare("SELECT COUNT(*) FROM siblings WHERE preferred = :preferred AND alias = :alias");
        $sql->bindValue(':alias', $aliasid, SQLITE3_INTEGER);
        $sql->bindValue(':preferred', $tagid, SQLITE3_INTEGER);
        $siblingscount = $sql->execute()->fetchArray()[0] ?? -1;
        if($siblingscount == 0)
        {
            $sql = $db->prepare("INSERT INTO siblings (alias, preferred, retro) VALUES (:alias, :preferred, 0)");
            $sql->bindValue(':alias', $aliasid, SQLITE3_INTEGER);
            $sql->bindValue(':preferred', $tagid, SQLITE3_INTEGER);
            $result = $sql->execute();
        }

        echo json_encode("<i>" . $alias . "</i> added");
    }
    else{
        echo json_encode("Error");
    }
    
	$db = null;
?>