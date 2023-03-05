<?php
	//$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
    //$db = new SQLite3("Y:\\Database\\nevada.db");
    $db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
    $db->exec('PRAGMA foreign_keys = ON;');
    $db->busyTimeout(100);
	
	if(isset($_GET["tagid"])) { $tagid = $_GET["tagid"]; } else { $tagid = -1; };
    if(isset($_GET["newtag"])) { $newtag = html_entity_decode($_GET["newtag"]); } else { $newtag = ""; };
    if(isset($_GET["cat"])) { $category = $_GET["cat"]; } else { $category = 0; };
	$output = "";
    $hashes = [];
    $aliases = [];

    try{
        $db->exec('BEGIN;');
        $db->enableExceptions(true);

        $sql = $db->prepare("select category from tags where tagid = :tagid");
        $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
        $currentcat = $sql->execute()->fetchArray()[0] ?? -1;
        
        //update category
        if($category != $currentcat){
            
            $sql = $db->prepare("UPDATE tags SET category = :cat WHERE tagid = :tagid");
            $sql->bindValue(':cat', $category, SQLITE3_INTEGER);
            $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
            $result = $sql->execute();
        }

        //Update Tag

        $sql = $db->prepare("select tagid from tags where tag_name = :tag");
        $sql->bindValue(':tag', str_replace("_", " ", $newtag), SQLITE3_TEXT);
        $newtagid = $sql->execute()->fetchArray()[0] ?? -1;

        //if new tag is one that already exists (Make old tag an alias of the new tag)
        if($newtagid != -1 && $tagid != $newtagid){
            $sql = $db->prepare("INSERT INTO siblings (alias, preferred, retro) VALUES (:alias, :preferred, 0)");
            $sql->bindValue(':alias', $tagid, SQLITE3_INTEGER);
            $sql->bindValue(':preferred', $newtagid, SQLITE3_INTEGER);
            $result = $sql->execute();

            $sql = $db->prepare("Update tags set alias = 1 where tagid = :alias");
            $sql->bindValue(':alias', $tagid, SQLITE3_INTEGER);
            $result = $sql->execute();

            $sql = $db->prepare("Update tags set preferred = 1 where tagid = :preferred");
            $sql->bindValue(':preferred', $newtagid, SQLITE3_INTEGER);
            $result = $sql->execute();
        
            //Upadte each images tag_list with new tag replacing old tag    
            $sql = $db->prepare("Select hash from files where tag_list like :tagid;");
            $sql->bindValue(':tagid', '%;' . strval($tagid) . ';%', SQLITE3_TEXT);
            $result = $sql->execute();
            while ($row = $result->fetchArray()){
                array_push($hashes, $row);
            }
        
            for($i = 0; $i <= count($hashes) - 1; $i++){
                $taglist = GetTagList($hashes[$i][0]); // Copy from submit post processing line 251
                $taglist = str_replace(';' . strval($tagid) . ';', ';' . strval($newtagid) . ';', $taglist);
                UpdateTagList($hashes[$i][0], $taglist); // Copy from submit post processing line 260
                IncrementTagCount($newtagid); // Copy from submit post processing line 269
                DecrementTagCount($tagid);
            }
        }
        else{
            //update Tag Name
            if($newtag != "" && $tagid != $newtagid){

                $sql = $db->prepare("UPDATE tags SET tag_name = :newtag WHERE tagid = :tagid");
                $sql->bindValue(':newtag', str_replace("_", " ", $newtag), SQLITE3_TEXT);
                $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
                $result = $sql->execute();

                //check if tag is an alias
                $sql = $db->prepare("select preferred from siblings where alias = :alias;");
                $sql->bindValue(':alias', $tagid, SQLITE3_TEXT);
                $preferredid = $sql->execute()->fetchArray()[0] ?? -1;

                if($preferredid = -1){    //not an alias
                    $sql = $db->prepare("UPDATE tags SET tag_display = :newtag WHERE tagid = :tagid");
                    $sql->bindValue(':newtag', str_replace("_", " ", $newtag), SQLITE3_TEXT);
                    $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
                    $result = $sql->execute();
                }
                else{
                    $sql = $db->prepare("select tag_name from tags where tagid = :tagid;");
                    $sql->bindValue(':tagid', $preferredid, SQLITE3_INTEGER);
                    $preferred = $sql->execute()->fetchArray()[0] ?? "";

                    $sql = $db->prepare("UPDATE tags SET tag_display = :newtag WHERE tagid = :tagid");
                    $sql->bindValue(':newtag', str_replace("_", " ", $newtag) . " Displays as: " . $preferred, SQLITE3_TEXT);
                    $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
                    $result = $sql->execute();
                }

                if($output != ""){
                    $output .= " & Tag Name updated";
                }
                else{
                    $output = "Tag Name updated";
                }
            }
        }

        $db->exec('COMMIT;');
        echo json_encode($output);
    }
    catch(Exception $e){
        $output = $db->lastErrorMsg();
        $db->exec('ROLLBACK;');
    }

    $db = null;

    function GetTagList($hash)
	{
        try{
            global $db;
            $sql = $db->prepare("select tag_list from files where hash = :hash");
            $sql->bindValue(':hash', $hash, SQLITE3_TEXT);
            $taglist = $sql->execute()->fetchArray()[0] ?? '';
            return $taglist;
        }catch(Exception $e){

        }
	}
	
	function UpdateTagList($hash, $taglist)
	{
        try{
            global $db;
            $sql = $db->prepare("update files set tag_list = :tag_list where hash = :hash");
            $sql->bindValue(':tag_list', $taglist, SQLITE3_TEXT);
            $sql->bindValue(':hash', $hash, SQLITE3_TEXT);
            $result = $sql->execute();
        }catch(Exception $e){

        }
	}
	
	function IncrementTagCount($tagid)
	{
        try{
            global $db;
            $sql = $db->prepare("update tags set tag_count = tag_count + 1 where tagid = :tagid");
            $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
            $result = $sql->execute();
        }catch(Exception $e){

        }
	}

    function DecrementTagCount($tagid)
	{
        try{
            global $db;
            $sql = $db->prepare("update tags set tag_count = tag_count - 1 where tagid = :tagid");
            $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
            $result = $sql->execute();
        }catch(Exception $e){

        }
	}
?>