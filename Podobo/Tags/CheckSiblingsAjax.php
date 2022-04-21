<?php
	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");	
	
	if(isset($_GET["siblings"])) { $siblings = html_entity_decode($_GET["siblings"]); } else { $siblings = ""; };	

    $sibling_found = 0;
    
    $siblings_array = explode(" ", $siblings);

    //add new alias siblings to tag
    if(!empty($siblings_array))
    {
        for ($i = 0; $i <= count($siblings_array) - 1; $i++)
        {
            if($siblings_array[$i] != "" and $siblings_array[$i] != " ")
            {	
                $sql = $db->prepare("select tagid from tags where tag_name = :alias COLLATE NOCASE");
                $sql->bindValue(':alias', str_replace("_", " ", $siblings_array[$i]) , SQLITE3_TEXT);
                $siblingid = $sql->execute()->fetchArray()[0] ?? -1;

                if($siblingid != -1)
                {
                    $sibling_found = 1;
                }
            }			
        }
    }
	
	echo json_encode($sibling_found);
	$db = null;	
	
?>