<?php
	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	
	if(isset($_GET["tagid"])) { $tagid = $_GET["tagid"]; } else { $tagid = ""; };
    if(isset($_GET["newtag"])) { $newtag = $_GET["newtag"]; } else { $newtag = ""; };
    if(isset($_GET["cat"])) { $category = $_GET["cat"]; } else { $category = 0; };
	$output = "";
    $hashes = [];
	
	$sql = $db->prepare("select category from tags where tagid = :tagid");
	$sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
	$currentcat = $sql->execute()->fetchArray()[0] ?? -1;
	
    //update category
	if($category != $currentcat){
        $sql = $db->prepare("UPDATE tags SET category = :cat WHERE tagid = :tagid");
        $sql->bindValue(':cat', $category, SQLITE3_INTEGER);
        $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
        $result = $sql->execute();
        $output = "Category updated";
    }

    //Update Tag

    $sql = $db->prepare("select tagid from tags where tag_name = :tag;");
    $sql->bindValue(':tag', str_replace("_", " ", $newtag), SQLITE3_TEXT);
    $newtagid = $sql->execute()->fetchArray()[0] ?? -1;

    if($newtagid != -1 && $newtagid != $tagid){
        //update child relationships of old tag to new tag 
        $sql = $db->prepare("Update parents set child = :newtagid where child = :tagid;");
        $sql->bindValue(':newtagid', $newtagid, SQLITE3_INTEGER);
        $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
        $sql->execute();
    
        //update parent relationships of old tag to new tag    
        $sql = $db->prepare("Update parents set parent = :newtagid where parent = :tagid;");
        $sql->bindValue(':newtagid', $newtagid, SQLITE3_INTEGER);
        $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
        $sql->execute();
    
        //update alias relationships of old tag to new tag    
        $sql = $db->prepare("Update siblings set alias = :newtagid where alias = :tagid;");
        $sql->bindValue(':newtagid', $newtagid, SQLITE3_INTEGER);
        $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
        $sql->execute();
    
        //update preferred relationships of old tag to new tag    
        $sql = $db->prepare("Update siblings set preferred = :newtagid where preferred = :tagid;");
        $sql->bindValue(':newtagid', $newtagid, SQLITE3_INTEGER);
        $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
        $sql->execute();

        $sql = $db->prepare("Update tag_map set tagid = :newtagid where tagid = :tagid;");
        $sql->bindValue(':newtagid', $newtagid, SQLITE3_INTEGER);
        $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
        $sql->execute();
    
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
        }
    
        //Delete old tag
        $sql = $db->prepare("Delete from tags where tagid = :tagid;");
        $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
        $sql->execute();

    }
    else{
        //update Tag Name
        if($newtag != "" && $newtagid != $tagid){

            //check for preferred sibling
            $sql = $db->prepare("select preferred from siblings where alias = :tagid");
            $sql->bindValue(':tagid', $tagid , SQLITE3_INTEGER);
            $prefid = $sql->execute()->fetchArray()[0] ?? -1;

            //Check if This tag has a preferred siblings so we can update the tag_display field properly
            if($prefid != -1){
                $sql = $db->prepare("select tag_name from tags where tagid=:tagid");
                $sql->bindValue(':tagid', $prefid , SQLITE3_INTEGER);
                $preftag = $sql->execute()->fetchArray()[0] ?? '';

                $sql = $db->prepare("UPDATE tags SET tag_name = :newtag WHERE tagid = :tagid");
                $sql->bindValue(':newtag', str_replace("_", " ", $newtag), SQLITE3_TEXT);
                $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
                $result = $sql->execute();

                $sql = $db->prepare("UPDATE tags SET tag_display = :newtagdisplay WHERE tagid = :tagid");
                $sql->bindValue(':newtagdisplay', str_replace("_", " ", $newtag) . " Displays as: " . str_replace("_", " ", $pref), SQLITE3_TEXT);
                $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
                $result = $sql->execute();
            }
            else{
                $sql = $db->prepare("UPDATE tags SET tag_name = :newtag WHERE tagid = :tagid");
                $sql->bindValue(':newtag', str_replace("_", " ", $newtag), SQLITE3_TEXT);
                $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
                $result = $sql->execute();

                $sql = $db->prepare("UPDATE tags SET tag_display = :newtagdisplay WHERE tagid = :tagid");
                $sql->bindValue(':newtagdisplay', str_replace("_", " ", $newtag), SQLITE3_TEXT);
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
	
	echo json_encode($output);		
    
    function GetTagList($hash)
	{
		global $db;
		$sql = $db->prepare("select tag_list from files where hash = :hash");
		$sql->bindValue(':hash', $hash, SQLITE3_TEXT);
		$taglist = $sql->execute()->fetchArray()[0] ?? '';
        //print_r($taglist);
		return $taglist;
	}
	
	function UpdateTagList($hash, $taglist)
	{
		global $db;
		$sql = $db->prepare("update files set tag_list = :tag_list where hash = :hash");
		$sql->bindValue(':tag_list', $taglist, SQLITE3_TEXT);
		$sql->bindValue(':hash', $hash, SQLITE3_TEXT);
		$result = $sql->execute();
	}
	
	function IncrementTagCount($tagid)
	{
		global $db;
		$sql = $db->prepare("update tags set tag_count = tag_count + 1 where tagid = :tagid");
		$sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
		$result = $sql->execute();
	}
	
	$db = null;
?>