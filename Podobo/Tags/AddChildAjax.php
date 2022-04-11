<?php
	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	
	if(isset($_GET["tagid"])) { $tagid = $_GET["tagid"]; } else { $tagid = ""; };
    if(isset($_GET["child"])) { $child = $_GET["child"]; } else { $child = ""; };
	$output = "";
	
	if($child != "" and $child != " ") 
    {
        $sql = $db->prepare("select tagid from tags where tag_name = :child");
        $sql->bindValue(':child', str_replace("_", " ", $child) , SQLITE3_TEXT);
        $childid = $sql->execute()->fetchArray()[0] ?? -1;

        if($childid == -1){    // If Parent Tag is New
            $output = "Error - Cannot add a child tag that is new";         
        }
        
        if($childid != -1){
            $sql = $db->prepare("SELECT COUNT(*) FROM parents WHERE (child = :child AND parent = :parent) or (child = :parent and parent = :child)");
            $sql->bindValue(':parent', $tagid, SQLITE3_INTEGER);
            $sql->bindValue(':child', $childid, SQLITE3_INTEGER);
            $childcount = $sql->execute()->fetchArray()[0] ?? -1;
            if($childcount == 0)
            {
                $sql = $db->prepare("INSERT INTO parents (child, parent, retro) VALUES (:child, :parent, 0)");
                $sql->bindValue(':child', $childid, SQLITE3_INTEGER);
                $sql->bindValue(':parent', $tagid, SQLITE3_INTEGER);
                $result = $sql->execute();
            }	
     
            $output = "<i>" . $child . "</i> Added";
        }
        else{
            $output = "Error";
        }
    }	
	
	echo json_encode($output);			
	
	$db = null;
?>