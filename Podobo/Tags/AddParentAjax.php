<?php
	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	
	if(isset($_GET["tagid"])) { $tagid = $_GET["tagid"]; } else { $tagid = ""; };
    if(isset($_GET["parent"])) { $parent = $_GET["parent"]; } else { $parent = ""; };
    if(isset($_GET["cat"])) { $category = $_GET["cat"]; } else { $category = -1; };
	$output = "";
	
	if($parent != "" and $parent != " ") 
    {
        $sql = $db->prepare("select tagid from tags where tag_name = :parent");
        $sql->bindValue(':parent', str_replace("_", " ", $parent) , SQLITE3_TEXT);
        $parentid = $sql->execute()->fetchArray()[0] ?? -1;

        if($parentid == -1){    // If Parent Tag is New
            $sql = $db->prepare("INSERT INTO tags (tag_name, category, tag_display, tag_count) VALUES (:tag, :category, :tag_display, 0);");
            $sql->bindValue(':tag', str_replace("_", " ", $parent), SQLITE3_TEXT);
            $sql->bindValue(':category', $category, SQLITE3_INTEGER);
            $sql->bindValue(':tag_display', str_replace("_", " ", $parent), SQLITE3_TEXT);
            $sql->execute();
         
            $sql = $db->prepare("Select tagid from tags where tag_name = :tag;");
            $sql->bindValue(':tag', str_replace("_", " ", $parent), SQLITE3_TEXT);
            $parentid = $sql->execute()->fetchArray()[0] ?? -1;            
        }
        
        if($parentid != -1){
            $sql = $db->prepare("SELECT COUNT(*) FROM parents WHERE (child = :child AND parent = :parent) or (child = :parent and parent = :child)");
            $sql->bindValue(':child', $tagid, SQLITE3_INTEGER);
            $sql->bindValue(':parent', $parentid, SQLITE3_INTEGER);
            $parentcount = $sql->execute()->fetchArray()[0] ?? -1;
            if($parentcount == 0)
            {
                $sql = $db->prepare("INSERT INTO parents (child, parent, retro) VALUES (:child, :parent, 0)");
                $sql->bindValue(':child', $tagid, SQLITE3_INTEGER);
                $sql->bindValue(':parent', $parentid, SQLITE3_INTEGER);
                $result = $sql->execute();
            }	
     
            $output = "<i>" . $parent . "</i> Added";
        }
        else{
            $output = "Error";
        }
    }	
	
	echo json_encode($output);			
	
	$db = null;
?>