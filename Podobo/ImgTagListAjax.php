<?php
    $TagColors=array("#FFFFFF", "#6495ED", "#FF4500", "#DAA520", "#FF8C00", "#7FFFD4", "#BA55D3", "#228B22", "#8A2BE2", "#FFFFFF", "#FFFFFF", "#FFFFFF", "#FFFFFF", "#FFFFFF", "#DAA520", "#8FBC8F", "#FFFFFF");
    $TagOrder=array(13,3,0,15,1,2,7,5,6,8,9,11,12,10,16,4,14);
    $TagCategoryTitle=array("General", "IP/Series", "Individual", "Rating", "Artist", "Studio/Network", "Sex", "Afilliation/Group", "Race/Species/Ethnicity", "Body Part", "Clothing/Accessory", "Position", "Setting", "Action", "Meta", "Title", "Release Date");

	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
    $db->busyTimeout(100);
	
	if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = -1; }; 

	//$tag = str_replace("_", " ", $tag);
	
	$sql = $db->prepare("SELECT tag_list FROM files where ID=:id");
    $sql->bindValue(':id', $id, SQLITE3_INTEGER);
	$taglist = $sql->execute()->fetchArray()[0] ?? '';

	$tagids = array_filter(explode(";", $taglist));
    
				
	$tags=array();
	foreach($tagids as $tagid)
	{
        //echo $tagid;
		$sql = $db->prepare("select tag_name, category, tagid from tags where tagid=:tagid");
        $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
        $tag = $sql->execute()->fetchArray();
		if(!empty($tag)){					
			array_push($tags, array($tag[0], $tag[1], $TagOrder[$tag[1]], $tag[2]));
		}
	}
	
	$order = array_column($tags, 2);
	array_multisort($order, SORT_ASC, $tags);
	
	$lastcat = -1;

    echo "<dl>";
    foreach($tags as $tag)
    {
        if($tag[1] != $lastcat){
            if($lastcat != -1){
                echo "<br />";
            }
            $lastcat = $tag[1];
            echo "<dt style='color:" . $TagColors[$tag[1]] . "'>" . $TagCategoryTitle[$tag[1]] . "</dt>";
        }
        echo "<dd id='a" . $tag[3] . "'>";
        echo "<div>";
        echo "<a href='Tags/Tag.php?tagid=".$tag[3]."'><i style='color:" . $TagColors[$tag[1]] . "' class='fa-solid fa-pen-to-square fa-xl'></i></a>";
        //echo "<a style='color:" . $TagColors[$tag[1]] . "' href ='Posts.php?search=" . str_replace(" ", "_", $tag[0]) ."&page=1'>" . $tag[0] . "</a>";
        if($tag[1] == 15){
            if(str_contains($tag[0], " (Title)")){
                echo "<a style='color:" . $TagColors[$tag[1]] . "' href ='Posts.php?search=" . str_replace(" ", "_", $tag[0]) ."&page=1'>" . str_replace(" (Title)", "", $tag[0]) . "</a>";
            }
            else{
                echo "<a style='color:" . $TagColors[$tag[1]] . "' href ='Posts.php?search=" . str_replace(" ", "_", $tag[0]) ."&page=1'>" . $tag[0] . "</a>";
            }
        }
        else{
            echo "<a style='color:" . $TagColors[$tag[1]] . "' href ='Posts.php?search=" . str_replace(" ", "_", $tag[0]) ."&page=1'>" . $tag[0] . "</a>";
        }
        echo "<input type='Button' value='x' class='rem-button' onclick='RemoveTag(" . $tag[3] . ", " . $id . ")' />";
        echo "</div>";
        echo "</dd>";
    }
    
    echo "</dl>";
	$db = null;
?>