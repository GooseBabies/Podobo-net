<?php
    $db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
    $db->exec('PRAGMA foreign_keys = ON;');
    $db->busyTimeout(100);
	
	if(isset($_GET["tag"])) { $tag = html_entity_decode($_GET["tag"]); } else { $tag = ""; };	

    $tag_found = 0;

    if($tag != "")
    {
        $sql = $db->prepare("select tagid from tags where tag_name = :tag COLLATE NOCASE");
        $sql->bindValue(':tag', str_replace("_", " ", $tag) , SQLITE3_TEXT);
        $tagid = $sql->execute()->fetchArray()[0] ?? -1;

        if($tagid != -1)
        {
            $tag_found = 1;
        }
    }
	
	echo json_encode($tag_found);
	$db = null;	
	
?>