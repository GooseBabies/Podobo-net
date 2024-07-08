<?php
    $TagColors=array("#FFFFFF", "#6495ED", "#FF4500", "#DAA520", "#FF8C00", "#7FFFD4", "#BA55D3", "#228B22", "#8A2BE2", "#FFFFFF", "#FFFFFF", "#FFFFFF", "#FFFFFF", "#FFFFFF", "#DAA520", "#8FBC8F", "#FFFFFF");
    $TagOrder=array(13,3,0,15,1,2,7,5,6,8,9,11,12,10,16,4,14);
    $TagCategoryTitle=array("General", "IP/Series", "Individual", "Rating", "Artist", "Studio/Network", "Sex", "Afilliation/Group", "Race/Species/Ethnicity", "Body Part", "Clothing/Accessory", "Action/Position", "Setting", "", "Meta", "Title", "Release Date");

    $db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
    $db->exec('PRAGMA foreign_keys = ON;');
    $db->busyTimeout(100);
    $tags = [];
	
	if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = -1; }; 

	//$tag = str_replace("_", " ", $tag);
	
	// $sql = $db->prepare("SELECT tag_list FROM files where ID=:id");
    // $sql->bindValue(':id', $id, SQLITE3_INTEGER);
	// $taglist = $sql->execute()->fetchArray()[0] ?? '';

    $sql = $db->prepare("SELECT tag_id, tag_name, category FROM media_tags join tags on tag_id = tagid where media_id = :id");
    $sql->bindValue(':id', $id, SQLITE3_INTEGER);
	$result = $sql->execute() ?? '';
    while ($tagitem = $result->fetchArray()) {
		array_push($tagitem, $TagOrder[$tagitem[2]]);
		array_push($tags, $tagitem);
	}
	//$tagids = array_filter(explode(";", $taglist));

    //old: tag name, category, order, tagid
    //new tagid, tag name, category, order
    
				
	// $tags=array();
	// foreach($tagids as $tagid)
	// {
    //     //echo $tagid;
	// 	$sql = $db->prepare("select tag_name, category, tagid from tags where tagid=:tagid");
    //     $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
    //     $tag = $sql->execute()->fetchArray();
	// 	if(!empty($tag)){					
	// 		array_push($tags, array($tag[0], $tag[1], $TagOrder[$tag[1]], $tag[2]));
	// 	}
	// }
	
	$order = array_column($tags, 3);
	array_multisort($order, SORT_ASC, $tags);
	
	$lastcat = -1;

    echo "<dl>";
    foreach($tags as $tag)
    {
        if($tag[2] != $lastcat){
            if($lastcat != -1){
                echo "<br />";
            }
            $lastcat = $tag[2];
            echo "<dt style='color:" . $TagColors[$tag[2]] . "'>" . $TagCategoryTitle[$tag[2]] . "</dt>";
        }
        echo "<dd id='a" . $tag[0] . "'>";
        echo "<div>";
        echo "<a href='Tags/Tag.php?tagid=".$tag[0]."'>";//<i style='color:" . $TagColors[$tag[1]] . "' class='fa-solid fa-pen-to-square fa-xl'></i>";
        echo "<svg class='svg-inline--fa fa-pen-to-square fa-xl' focusable='false' height='18' width='18' viewBox='0 0 512 512'><path fill='" . $TagColors[$tag[2]] . "' d='M490.3 40.4C512.2 62.27 512.2 97.73 490.3 119.6L460.3 149.7L362.3 51.72L392.4 21.66C414.3-.2135 449.7-.2135 471.6 21.66L490.3 40.4zM172.4 241.7L339.7 74.34L437.7 172.3L270.3 339.6C264.2 345.8 256.7 350.4 248.4 353.2L159.6 382.8C150.1 385.6 141.5 383.4 135 376.1C128.6 370.5 126.4 361 129.2 352.4L158.8 263.6C161.6 255.3 166.2 247.8 172.4 241.7V241.7zM192 63.1C209.7 63.1 224 78.33 224 95.1C224 113.7 209.7 127.1 192 127.1H96C78.33 127.1 64 142.3 64 159.1V416C64 433.7 78.33 448 96 448H352C369.7 448 384 433.7 384 416V319.1C384 302.3 398.3 287.1 416 287.1C433.7 287.1 448 302.3 448 319.1V416C448 469 405 512 352 512H96C42.98 512 0 469 0 416V159.1C0 106.1 42.98 63.1 96 63.1H192z'></path></svg></a>";
        if($tag[2] == 15){
            if(str_contains($tag[0], " (Title)")){
                echo "</a> <a style='color:" . $TagColors[$tag[2]] . "' href ='Posts.php?search=" . str_replace(" ", "_", $tag[1]) ."&page=1'>" . str_replace(" (Title)", "", $tag[1]) . "</a>";
            }
            else{
                echo "</a> <a style='color:" . $TagColors[$tag[2]] . "' href ='Posts.php?search=" . str_replace(" ", "_", $tag[1]) ."&page=1'>" . $tag[1] . "</a>";
            }
        }
        else{
            echo "</a> <a style='color:" . $TagColors[$tag[2]] . "' href ='Posts.php?search=" . str_replace(" ", "_", $tag[1]) ."&page=1'>" . $tag[1] . "</a>";
        }
        //add male/female symbols to individual tags
        if($tag[2] == 2){
            
            $sql = $db->prepare("select count(*) from parents where child=:individualid and parent=36383");
            $sql->bindValue(':individualid', $tag[0], SQLITE3_INTEGER);
            $tagcount = $sql->execute()->fetchArray()[0] ?? 0;
            if($tagcount > 0){
                echo "<svg xmlns='http://www.w3.org/2000/svg' class='svg-inline--fa fa-venus fa-xl' focusable='false' height='18' width='18' viewBox='0 0 384 512'><path fill='Pink' d='M64 176a112 112 0 1 1 224 0A112 112 0 1 1 64 176zM208 349.1c81.9-15 144-86.8 144-173.1C352 78.8 273.2 0 176 0S0 78.8 0 176c0 86.3 62.1 158.1 144 173.1V384H112c-17.7 0-32 14.3-32 32s14.3 32 32 32h32v32c0 17.7 14.3 32 32 32s32-14.3 32-32V448h32c17.7 0 32-14.3 32-32s-14.3-32-32-32H208V349.1z'/></svg>";
            }
            else{
                $sql = $db->prepare("select count(*) from parents where child=:individualid and parent=36384");
                $sql->bindValue(':individualid', $tag[0], SQLITE3_INTEGER);
                $tagcount = $sql->execute()->fetchArray()[0] ?? 0;
                if($tagcount > 0){
                    echo "<svg xmlns='http://www.w3.org/2000/svg' class='svg-inline--fa fa-mars fa-xl' focusable='false' height='18' width='18' viewBox='0 0 448 512'><path fill='PowderBlue' d='M289.8 46.8c3.7-9 12.5-14.8 22.2-14.8H424c13.3 0 24 10.7 24 24V168c0 9.7-5.8 18.5-14.8 22.2s-19.3 1.7-26.2-5.2l-33.4-33.4L321 204.2c19.5 28.4 31 62.7 31 99.8c0 97.2-78.8 176-176 176S0 401.2 0 304s78.8-176 176-176c37 0 71.4 11.4 99.8 31l52.6-52.6L295 73c-6.9-6.9-8.9-17.2-5.2-26.2zM400 80l0 0h0v0zM176 416a112 112 0 1 0 0-224 112 112 0 1 0 0 224z'/></svg>";
                }
            }					
        }
        echo "<input type='Button' value='x' class='rem-button' onclick='RemoveTag(" . $tag[0] . ", " . $id . ")' />";
        echo "</div>";
        echo "</dd>";
    }
    
    echo "</dl>";
	$db = null;
?>