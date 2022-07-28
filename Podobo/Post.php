<?php
	session_start();
	
	$TagColors=array("#FFFFFF", "#6495ED", "#FF4500", "#DAA520", "#FF8C00", "#7FFFD4", "#BA55D3", "#228B22", "#8A2BE2", "#FFFFFF", "#FFFFFF", "#FFFFFF", "#FFFFFF", "#FFFFFF", "#DAA520", "#8FBC8F", "#FFFFFF");
	$TagOrder=array(13,3,0,15,1,2,7,5,6,8,9,11,12,10,16,4,14);
	$TagCategoryTitle=array("General", "IP/Series", "Individual", "Rating", "Artist", "Studio/Network", "Sex", "Afilliation/Group", "Race/Species/Ethnicity", "Body Part", "Clothing/Accessory", "Position", "Setting", "Action", "Meta", "Title", "Release Date");
	
	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");		
	
	if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = 1; };

	$files = [];
	if(isset($_SESSION["filtered_ids"]) && count($_SESSION["filtered_ids"]) > 0){
		$files = $_SESSION["filtered_ids"];
		$idcount = count($files)-1;
		$filtered = true;
	}
	else{
		if(isset($_SESSION["all_ids"])){
			$files = $_SESSION["all_ids"];				
			$filtered = false;
		}
		else{		
			$result = $db->query("SELECT ID FROM files order by id desc");				
			while ($row = $result->fetchArray()) {
				array_push($files, $row);
			}			
			$_SESSION["all_ids"] = $files;
			$filtered = false;
		}
		$_SESSION["search"] = "";
		$idcount = count($files)-1;
	}

	$r = rand(0, $idcount);

	$index = array_search($id, array_column($files, 0));
	//echo "<!-- " . $index . "-->";
	//echo "<!-- " . $files[$index][0] . "-->";	
				
	$sql = "SELECT path, tag_list, overall_rating, media_rating, individual_rating, sexual_rating, height, width, sources, review, favorite FROM files where ID='" . $id . "'";
	$result = $db->query($sql);
	$row = $result->fetchArray();
	
	$file_link = $row[0];
	$overall_rating = $row[2];
	$media_rating = $row[3];
	$individual_rating = $row[4];
	$sexual_rating = $row[5];
	$tagids = array_filter(explode(";", $row[1]));
	$sources = array_filter(explode(" ", $row[8]));
				
	$tags=array();
	foreach($tagids as $tagid)
	{
		$tag = $db->query("select tag_name, category from tags where tagid=" . $tagid)->fetchArray();
		if(!empty($tag)){					
			$tags[] = array($tag[0], $tag[1], $TagOrder[$tag[1]], $tagid);
		}
		else{
			//tagid doesn't exists anymore
		}
	}
	
	$order = array_column($tags, 2);
	array_multisort($order, SORT_ASC, $tags);
	
	$lastcat = -1;
	
	$PageTitle = "Podobo - " . $id;
	$review = $row[9];
	//echo "<!-- review: " . $review . "-->";	

	function customPageHeader(){?>
		<script>              
			function hidetags(){
				var content = document.getElementById("mr");
				if (content.style.display === "block") {
					content.style.display = "none";
					} else {
					content.style.display = "block";
					}

				var content2 = document.getElementById("ir");
				if (content2.style.display === "block") {
					content2.style.display = "none";
					} else {
					content2.style.display = "block";
					}

				var content3 = document.getElementById("sr");
				if (content3.style.display === "block") {
					content3.style.display = "none";
					} else {
					content3.style.display = "block";
					}
			}

			function showEdit()
			{
				var content = document.getElementsByClassName("rem-button");
				for (var i=0; i < content.length; i++) {
					if (content[i].style.display === "inline") 
					{
						content[i].style.display = "none";					
					} 
					else 
					{
						content[i].style.display = "inline";
					}
				}

				var destry = document.getElementsByTagName("del");
				while (destry.length > 0) {
					destry[0].remove();
				}
			}

			$(document).ready(function()
			{				
				var HeaderButton = document.getElementById("posts");
				HeaderButton.className = "w3-bar-item w3-button w3-theme-l1";

				document.getElementById("separator").style.display = "block";
                document.getElementById("edit-tags").style.display = "block";
                document.getElementById("search-indicator").style.display = "block";
			});
		</script>
		<style type="text/css">
		.fa-star {
			color: gold;
			margin: 3px;
		}

		.container_footer{
			padding-top: 5px;
		}
		</style>
	<?php }

	include_once('header.php');
?>
            <main class="row">
                <div class="col-2 w3-theme main-left">
		
			<?php
			
			//echo "<div class='tagarea' >";
			echo "<div class='rating-wrapper'><strong id='rating-number'onclick='hidetags()'>Rating - " . $overall_rating . "/10 | </Strong>";  //overall rating
			echo "<div id='or'>";
			for($q = 1; $q <= 10; $q++)
			{
				if($q <= $overall_rating)
				{
					//echo "<img onclick='updaterating(" . $q . ")' class='star' src='img/starFull.png' />";
					//echo "<i class='fas fa-star fa-2x' onclick='updateOverallRating(" . $q . ")'></i>";
					echo "<svg class='svg-inline--fa fa-star fa-2x' focusable='false' viewBox='0 0 576 512' height='30' width='30' onclick='updateOverallRating(" . $q . ")'><path class='or-star-item' fill='currentColor' d='M381.2 150.3L524.9 171.5C536.8 173.2 546.8 181.6 550.6 193.1C554.4 204.7 551.3 217.3 542.7 225.9L438.5 328.1L463.1 474.7C465.1 486.7 460.2 498.9 450.2 506C440.3 513.1 427.2 514 416.5 508.3L288.1 439.8L159.8 508.3C149 514 135.9 513.1 126 506C116.1 498.9 111.1 486.7 113.2 474.7L137.8 328.1L33.58 225.9C24.97 217.3 21.91 204.7 25.69 193.1C29.46 181.6 39.43 173.2 51.42 171.5L195 150.3L259.4 17.97C264.7 6.954 275.9-.0391 288.1-.0391C300.4-.0391 311.6 6.954 316.9 17.97L381.2 150.3z'></path></svg></a>";
				}				
				else
				{
					//echo "<img onclick='updaterating(" . $q . ")' class='star' src='img/starEmpty.png' />";
					//echo "<i class='far fa-star fa-2x' onclick='updateOverallRating(" . $q . ")'></i>";
					echo "<svg class='svg-inline--fa fa-star fa-2x' focusable='false' viewBox='0 0 576 512' height='30' width='30' onclick='updateOverallRating(" . $q . ")'><path class='or-star-item' fill='currentColor' d='M287.9 0C297.1 0 305.5 5.25 309.5 13.52L378.1 154.8L531.4 177.5C540.4 178.8 547.8 185.1 550.7 193.7C553.5 202.4 551.2 211.9 544.8 218.2L433.6 328.4L459.9 483.9C461.4 492.9 457.7 502.1 450.2 507.4C442.8 512.7 432.1 513.4 424.9 509.1L287.9 435.9L150.1 509.1C142.9 513.4 133.1 512.7 125.6 507.4C118.2 502.1 114.5 492.9 115.1 483.9L142.2 328.4L31.11 218.2C24.65 211.9 22.36 202.4 25.2 193.7C28.03 185.1 35.5 178.8 44.49 177.5L197.7 154.8L266.3 13.52C270.4 5.249 278.7 0 287.9 0L287.9 0zM287.9 78.95L235.4 187.2C231.9 194.3 225.1 199.3 217.3 200.5L98.98 217.9L184.9 303C190.4 308.5 192.9 316.4 191.6 324.1L171.4 443.7L276.6 387.5C283.7 383.7 292.2 383.7 299.2 387.5L404.4 443.7L384.2 324.1C382.9 316.4 385.5 308.5 391 303L476.9 217.9L358.6 200.5C350.7 199.3 343.9 194.3 340.5 187.2L287.9 78.95z'></path></svg></a>";
				}
			}
			//echo "<strong id='rating-number'>" . $rating . "/10</strong>";
			echo "</div>";
			
			echo "<div id='mr'>";	//media rating
			echo "<i id='media-rating-number'>Media - " . $media_rating . "/10 | </i>";
			for($q = 1; $q <= 10; $q++)
			{
				if($q <= $media_rating)
				{
					//echo "<img onclick='updaterating(" . $q . ")' class='star' src='img/starFull.png' />";
					//echo "<i class='fas fa-star fa-2x' onclick='updateMediaRating(" . $q . ")'></i>";
					echo "<svg class='svg-inline--fa fa-star fa-2x' focusable='false' viewBox='0 0 576 512' height='30' width='30' onclick='updateMediaRating(" . $q . ")'><path class='mr-star-item' fill='currentColor' d='M381.2 150.3L524.9 171.5C536.8 173.2 546.8 181.6 550.6 193.1C554.4 204.7 551.3 217.3 542.7 225.9L438.5 328.1L463.1 474.7C465.1 486.7 460.2 498.9 450.2 506C440.3 513.1 427.2 514 416.5 508.3L288.1 439.8L159.8 508.3C149 514 135.9 513.1 126 506C116.1 498.9 111.1 486.7 113.2 474.7L137.8 328.1L33.58 225.9C24.97 217.3 21.91 204.7 25.69 193.1C29.46 181.6 39.43 173.2 51.42 171.5L195 150.3L259.4 17.97C264.7 6.954 275.9-.0391 288.1-.0391C300.4-.0391 311.6 6.954 316.9 17.97L381.2 150.3z'></path></svg></a>";
				}				
				else
				{
					//echo "<img onclick='updaterating(" . $q . ")' class='star' src='img/starEmpty.png' />";
					//echo "<i class='far fa-star fa-2x' onclick='updateMediaRating(" . $q . ")'></i>";
					echo "<svg class='svg-inline--fa fa-star fa-2x' focusable='false' viewBox='0 0 576 512' height='30' width='30' onclick='updateMediaRating(" . $q . ")'><path class='mr-star-item' fill='currentColor' d='M287.9 0C297.1 0 305.5 5.25 309.5 13.52L378.1 154.8L531.4 177.5C540.4 178.8 547.8 185.1 550.7 193.7C553.5 202.4 551.2 211.9 544.8 218.2L433.6 328.4L459.9 483.9C461.4 492.9 457.7 502.1 450.2 507.4C442.8 512.7 432.1 513.4 424.9 509.1L287.9 435.9L150.1 509.1C142.9 513.4 133.1 512.7 125.6 507.4C118.2 502.1 114.5 492.9 115.1 483.9L142.2 328.4L31.11 218.2C24.65 211.9 22.36 202.4 25.2 193.7C28.03 185.1 35.5 178.8 44.49 177.5L197.7 154.8L266.3 13.52C270.4 5.249 278.7 0 287.9 0L287.9 0zM287.9 78.95L235.4 187.2C231.9 194.3 225.1 199.3 217.3 200.5L98.98 217.9L184.9 303C190.4 308.5 192.9 316.4 191.6 324.1L171.4 443.7L276.6 387.5C283.7 383.7 292.2 383.7 299.2 387.5L404.4 443.7L384.2 324.1C382.9 316.4 385.5 308.5 391 303L476.9 217.9L358.6 200.5C350.7 199.3 343.9 194.3 340.5 187.2L287.9 78.95z'></path></svg></a>";
				}
			}
			echo "</div>";
			
			echo "<div id='ir'>";  //individual rating
			echo "<i id='individual-rating-number'>Individual - " . $individual_rating . "/10 | </i>";
			for($q = 1; $q <= 10; $q++)
			{
				if($q <= $individual_rating)
				{
					//echo "<img onclick='updaterating(" . $q . ")' class='star' src='img/starFull.png' />";
					//echo "<i class='fas fa-star fa-2x' onclick='updateIndividualRating(" . $q . ")'></i>";
					echo "<svg class='svg-inline--fa fa-star fa-2x' focusable='false' viewBox='0 0 576 512' height='30' width='30' onclick='updateIndividualRating(" . $q . ")'><path class='ir-star-item' fill='currentColor' d='M381.2 150.3L524.9 171.5C536.8 173.2 546.8 181.6 550.6 193.1C554.4 204.7 551.3 217.3 542.7 225.9L438.5 328.1L463.1 474.7C465.1 486.7 460.2 498.9 450.2 506C440.3 513.1 427.2 514 416.5 508.3L288.1 439.8L159.8 508.3C149 514 135.9 513.1 126 506C116.1 498.9 111.1 486.7 113.2 474.7L137.8 328.1L33.58 225.9C24.97 217.3 21.91 204.7 25.69 193.1C29.46 181.6 39.43 173.2 51.42 171.5L195 150.3L259.4 17.97C264.7 6.954 275.9-.0391 288.1-.0391C300.4-.0391 311.6 6.954 316.9 17.97L381.2 150.3z'></path></svg></a>";
				}				
				else
				{
					//echo "<img onclick='updaterating(" . $q . ")' class='star' src='img/starEmpty.png' />";
					//echo "<i class='far fa-star fa-2x' onclick='updateIndividualRating(" . $q . ")'></i>";
					echo "<svg class='svg-inline--fa fa-star fa-2x' focusable='false' viewBox='0 0 576 512' height='30' width='30' onclick='updateIndividualRating(" . $q . ")'><path class='ir-star-item' fill='currentColor' d='M287.9 0C297.1 0 305.5 5.25 309.5 13.52L378.1 154.8L531.4 177.5C540.4 178.8 547.8 185.1 550.7 193.7C553.5 202.4 551.2 211.9 544.8 218.2L433.6 328.4L459.9 483.9C461.4 492.9 457.7 502.1 450.2 507.4C442.8 512.7 432.1 513.4 424.9 509.1L287.9 435.9L150.1 509.1C142.9 513.4 133.1 512.7 125.6 507.4C118.2 502.1 114.5 492.9 115.1 483.9L142.2 328.4L31.11 218.2C24.65 211.9 22.36 202.4 25.2 193.7C28.03 185.1 35.5 178.8 44.49 177.5L197.7 154.8L266.3 13.52C270.4 5.249 278.7 0 287.9 0L287.9 0zM287.9 78.95L235.4 187.2C231.9 194.3 225.1 199.3 217.3 200.5L98.98 217.9L184.9 303C190.4 308.5 192.9 316.4 191.6 324.1L171.4 443.7L276.6 387.5C283.7 383.7 292.2 383.7 299.2 387.5L404.4 443.7L384.2 324.1C382.9 316.4 385.5 308.5 391 303L476.9 217.9L358.6 200.5C350.7 199.3 343.9 194.3 340.5 187.2L287.9 78.95z'></path></svg></a>";
				}
			}
			//echo "<strong id='rating-number'>" . $rating . "/10</strong>";
			echo "</div>";
			
			echo "<div id='sr'>";  //sexual rating
			echo "<i id='sexual-rating-number'>Sexual - " . $sexual_rating . "/10 | </i>";
			for($q = 1; $q <= 10; $q++)
			{
				if($q <= $sexual_rating)
				{
					//echo "<img onclick='updaterating(" . $q . ")' class='star' src='img/starFull.png' />";
					//echo "<i class='fas fa-star fa-2x' onclick='updateSexualRating(" . $q . ")'></i>";
					echo "<svg class='svg-inline--fa fa-star fa-2x' focusable='false' viewBox='0 0 576 512' height='30' width='30' onclick='updateSexualRating(" . $q . ")'><path class='sr-star-item' fill='currentColor' d='M381.2 150.3L524.9 171.5C536.8 173.2 546.8 181.6 550.6 193.1C554.4 204.7 551.3 217.3 542.7 225.9L438.5 328.1L463.1 474.7C465.1 486.7 460.2 498.9 450.2 506C440.3 513.1 427.2 514 416.5 508.3L288.1 439.8L159.8 508.3C149 514 135.9 513.1 126 506C116.1 498.9 111.1 486.7 113.2 474.7L137.8 328.1L33.58 225.9C24.97 217.3 21.91 204.7 25.69 193.1C29.46 181.6 39.43 173.2 51.42 171.5L195 150.3L259.4 17.97C264.7 6.954 275.9-.0391 288.1-.0391C300.4-.0391 311.6 6.954 316.9 17.97L381.2 150.3z'></path></svg></a>";
				}				
				else
				{
					//echo "<img onclick='updaterating(" . $q . ")' class='star' src='img/starEmpty.png' />";
					//echo "<i class='far fa-star fa-2x' onclick='updateSexualRating(" . $q . ")'></i>";
					echo "<svg class='svg-inline--fa fa-star fa-2x' focusable='false' viewBox='0 0 576 512' height='30' width='30' onclick='updateSexualRating(" . $q . ")'><path class='sr-star-item' fill='currentColor' d='M287.9 0C297.1 0 305.5 5.25 309.5 13.52L378.1 154.8L531.4 177.5C540.4 178.8 547.8 185.1 550.7 193.7C553.5 202.4 551.2 211.9 544.8 218.2L433.6 328.4L459.9 483.9C461.4 492.9 457.7 502.1 450.2 507.4C442.8 512.7 432.1 513.4 424.9 509.1L287.9 435.9L150.1 509.1C142.9 513.4 133.1 512.7 125.6 507.4C118.2 502.1 114.5 492.9 115.1 483.9L142.2 328.4L31.11 218.2C24.65 211.9 22.36 202.4 25.2 193.7C28.03 185.1 35.5 178.8 44.49 177.5L197.7 154.8L266.3 13.52C270.4 5.249 278.7 0 287.9 0L287.9 0zM287.9 78.95L235.4 187.2C231.9 194.3 225.1 199.3 217.3 200.5L98.98 217.9L184.9 303C190.4 308.5 192.9 316.4 191.6 324.1L171.4 443.7L276.6 387.5C283.7 383.7 292.2 383.7 299.2 387.5L404.4 443.7L384.2 324.1C382.9 316.4 385.5 308.5 391 303L476.9 217.9L358.6 200.5C350.7 199.3 343.9 194.3 340.5 187.2L287.9 78.95z'></path></svg></a>";
				}
			}
			//echo "<strong id='rating-number'>" . $rating . "/10</strong>";
			echo "</div>";

			echo "</div>";

			echo "<hr />";

			echo "<div class='tagadd'>";
				//echo "<form>";
				echo "<input type='text' id='tag-input' oninput='TagSuggestions(this.value)' onpaste='textPaste()'/>";
				//echo "<input type='hidden' onsubmit='AddTag()'/>";
				//echo "</form>";

				echo "<select name='categories' id='category'>";
					echo "<option value='0'>General</option>";
					echo "<option value='1'>IP</option>";
					echo "<option value='2'>Individual</option>";
					echo "<option value='3'>Rating</option>";
					echo "<option value='4'>Artist</option>";
					echo "<option value='5'>Studio</option>";
					echo "<option value='6'>Sex</option>";
					echo "<option value='7'>Afilliation</option>";
					echo "<option value='8'>Race</option>";
					echo "<option value='9'>Body Part</option>";
					echo "<option value='10'>Clothing</option>";
					echo "<option value='11'>Position</option>";
					echo "<option value='12'>Setting</option>";
					echo "<option value='13'>Action</option>";
					echo "<option value='14'>Meta</option>";
					echo "<option value='15'>Title</option>";
					echo "<option value='16'>Date</option>";
					echo "<option value='17'>Source</option>";
				echo "</select>";

				echo "<input type='button' value='Add Tag' onclick='AddTag()' />";

				//echo"</form>";

			
			echo "</div>";

			echo "<hr />";
			
			echo "<div class='tagarea' >";
			echo "<div id='tagarea'>";
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
				echo "<a href='Tags/Tag.php?tagid=".$tag[3]."'>";//<i style='color:" . $TagColors[$tag[1]] . "' class='fa-solid fa-pen-to-square fa-xl'></i>";
				echo "<svg class='svg-inline--fa fa-pen-to-square fa-xl' focusable='false' height='18' width='18' viewBox='0 0 512 512'><path fill='" . $TagColors[$tag[1]] . "' d='M490.3 40.4C512.2 62.27 512.2 97.73 490.3 119.6L460.3 149.7L362.3 51.72L392.4 21.66C414.3-.2135 449.7-.2135 471.6 21.66L490.3 40.4zM172.4 241.7L339.7 74.34L437.7 172.3L270.3 339.6C264.2 345.8 256.7 350.4 248.4 353.2L159.6 382.8C150.1 385.6 141.5 383.4 135 376.1C128.6 370.5 126.4 361 129.2 352.4L158.8 263.6C161.6 255.3 166.2 247.8 172.4 241.7V241.7zM192 63.1C209.7 63.1 224 78.33 224 95.1C224 113.7 209.7 127.1 192 127.1H96C78.33 127.1 64 142.3 64 159.1V416C64 433.7 78.33 448 96 448H352C369.7 448 384 433.7 384 416V319.1C384 302.3 398.3 287.1 416 287.1C433.7 287.1 448 302.3 448 319.1V416C448 469 405 512 352 512H96C42.98 512 0 469 0 416V159.1C0 106.1 42.98 63.1 96 63.1H192z'></path></svg></a>";
				if($tag[1] == 15){
					if(str_contains($tag[0], " (Title)")){
						echo "</a> <a style='color:" . $TagColors[$tag[1]] . "' href ='Posts.php?search=" . str_replace(" ", "_", $tag[0]) ."&page=1'>" . str_replace(" (Title)", "", $tag[0]) . "</a>";
					}
					else{
						echo "</a> <a style='color:" . $TagColors[$tag[1]] . "' href ='Posts.php?search=" . str_replace(" ", "_", $tag[0]) ."&page=1'>" . $tag[0] . "</a>";
					}
				}
				else{
					echo "</a> <a style='color:" . $TagColors[$tag[1]] . "' href ='Posts.php?search=" . str_replace(" ", "_", $tag[0]) ."&page=1'>" . $tag[0] . "</a>";
				}
				echo "<input type='Button' value='x' class='rem-button' onclick='RemoveTag(" . $tag[3] . ", " . $id . ")' />";
				echo "</div>";
				echo "</dd>";
			}
			
			echo "</dl>";
			echo "</div>";
			

			echo "</div>";

			echo "<hr />";

			//echo "<div class='imageinfo'>";

			//echo "<input type='Button' class='w3-center' value='Edit Tags' onclick='showEdit()' />";
			$filename = basename($file_link);
			if(str_contains($filename, "]")){
				$filename_contents = preg_split('/(\]|\[| \- |\(|\))/', $filename, -1, PREG_SPLIT_NO_EMPTY);
				for($i = 0; $i <= count($filename_contents) - 1; $i++){
					echo "<p onclick='copyText(this.innerHTML)'>" . trim($filename_contents[$i]) . "</p>";
				}
			}
			else{
				echo "<p>" . basename($file_link) . "</p>";
			}
			echo "<p>" . $row[6] . " x " . $row[7] . "</p>";
			echo "<div id='sources'>";
			//print_r($sources);
			if(!empty($sources)){
				foreach ($sources as $source)
				{
					if($source != "" and $source != " "){
						echo "<div id='" . json_encode($source) . "'>";
						echo "<a href='" . $source ."' target='_blank'>" . $source . "</a>";
						echo "<input type='Button' value='x' class='rem-button' onclick='RemoveSource(" . json_encode($source) . ", " . $id . ")' />";
						echo "</div>";
					}
				}
			}

			echo "</div></div>";
			
			$db = null;
			
			
			
			echo "<div class='col-8'>";
			
			//echo pathinfo($file_link, PATHINFO_DIRNAME);
			
			switch(pathinfo(basename($file_link), PATHINFO_EXTENSION))
			{
				case "jpg":
					display_image($file_link);
					break;
				case "jpeg":
					display_image($file_link);
					break;
				case "png":
					display_image($file_link);
					break;
				case "gif":
					display_image($file_link);
					break;
				case "webm":
					display_video($file_link);
					break;
				case "mp4":
					display_video($file_link);
					break;
				default:
					echo "<p> the file type is not supported.</p>";
					break;
			}
			
			
			//echo "<div class='viewer_container_right'>";
			//echo "</div>";
			
			
			echo "<div class='container_footer'>";
			
			$idlimit = 5;
			
			if($index < $idlimit + 1) { $start_page = 0; } else { $start_page = $index - ($idcount < $idlimit ? $idcount + 1 : $idlimit);};
			if($index > $idcount - $idlimit) { $end_page = $idcount; } else { $end_page = $index + ($idcount < $idlimit ? $idcount : $idlimit); };
			
			if($index != 0) 
			{
				echo "<a href='Post.php?id=" . $files[0][0] . "'>";//<i class='fas fa-angles-left fa-2x'></i></a>";  data-prefix='fas' data-icon='angles-left' role='img' xmlns='http://www.w3.org/2000/svg' data-fa-i2svg=''aria-hidden='true' 
				echo "<svg class='svg-inline--fa fa-angles-left fa-2x' focusable='false' height='20' width='20' viewBox='0 0 448 512'><path fill='currentColor' d='M77.25 256l137.4-137.4c12.5-12.5 12.5-32.75 0-45.25s-32.75-12.5-45.25 0l-160 160c-12.5 12.5-12.5 32.75 0 45.25l160 160C175.6 444.9 183.8 448 192 448s16.38-3.125 22.62-9.375c12.5-12.5 12.5-32.75 0-45.25L77.25 256zM269.3 256l137.4-137.4c12.5-12.5 12.5-32.75 0-45.25s-32.75-12.5-45.25 0l-160 160c-12.5 12.5-12.5 32.75 0 45.25l160 160C367.6 444.9 375.8 448 384 448s16.38-3.125 22.62-9.375c12.5-12.5 12.5-32.75 0-45.25L269.3 256z'></path></svg></a>";
				echo "<a href='Post.php?id=" . $files[$index - 1][0] . "'>";//<i class='fas fa-angle-left fa-2x'></i></a>";	
				echo "<svg class='svg-inline--fa fa-angle-left fa-2x' focusable='false' height='20' width='20' viewBox='0 0 448 512'><path fill='currentColor' d='M192 448c-8.188 0-16.38-3.125-22.62-9.375l-160-160c-12.5-12.5-12.5-32.75 0-45.25l160-160c12.5-12.5 32.75-12.5 45.25 0s12.5 32.75 0 45.25L77.25 256l137.4 137.4c12.5 12.5 12.5 32.75 0 45.25C208.4 444.9 200.2 448 192 448z'></path></svg></a>";
			};
			
			for($k = $start_page; $k <= $end_page; $k++)
			{
				if($k == $index){
					echo "<a class='current-page' href='Post.php?id=".$files[$k][0]."'>";
					echo ($files[$k][0]);
					echo "</a>";
				}
				else{
					echo "<a class='other-page' href='Post.php?id=".$files[$k][0]."'>";
					echo ($files[$k][0]);
					echo "</a>";
				}
			}
			
			if($index != $idcount) 
			{
				echo "<a href='Post.php?id=".$files[$index + 1][0]."'>";//<i class='fas fa-angle-right fa-2x'></i></a>";
				echo "<svg class='svg-inline--fa fa-angle-right fa-2x' focusable='false' height='20' width='20' viewBox='0 0 448 512'><path fill='currentColor' d='M64 448c-8.188 0-16.38-3.125-22.62-9.375c-12.5-12.5-12.5-32.75 0-45.25L178.8 256L41.38 118.6c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0l160 160c12.5 12.5 12.5 32.75 0 45.25l-160 160C80.38 444.9 72.19 448 64 448z'></path></svg></a>";
				echo "<a href='Post.php?id=".$files[$idcount][0]."'>";//<i class='fas fa-angles-right fa-2x'></i></a>";
				echo "<svg class='svg-inline--fa fa-angle-right fa-2x' focusable='false' height='20' width='20' viewBox='0 0 448 512'><path fill='currentColor' d='M246.6 233.4l-160-160c-12.5-12.5-32.75-12.5-45.25 0s-12.5 32.75 0 45.25L178.8 256l-137.4 137.4c-12.5 12.5-12.5 32.75 0 45.25C47.63 444.9 55.81 448 64 448s16.38-3.125 22.62-9.375l160-160C259.1 266.1 259.1 245.9 246.6 233.4zM438.6 233.4l-160-160c-12.5-12.5-32.75-12.5-45.25 0s-12.5 32.75 0 45.25L370.8 256l-137.4 137.4c-12.5 12.5-12.5 32.75 0 45.25C239.6 444.9 247.8 448 256 448s16.38-3.125 22.62-9.375l160-160C451.1 266.1 451.1 245.9 438.6 233.4z'></path></svg></a>";
			};
			
			echo "</div>"; //center col div
			echo "</div>"; //footer div
			echo "</main>";
			
			function display_image($file_item)
			{
				echo "<div><img class='center-fit' src='" . strtolower(substr(pathinfo($file_item, PATHINFO_DIRNAME), 3)) . "\\" . rawurlencode(basename($file_item)) . "' />"; /* height='".$img_height."' width='".$img_width."' */
				//echo "<p text-align='center'>" . basename($file_item) . "</p></div>";
			}
			
			function display_video($file_item)
			{
				echo "<div><video class='center-fit' loop controls src='" . strtolower(substr(pathinfo($file_item, PATHINFO_DIRNAME), 3)) . "\\"  . rawurlencode(basename($file_item)) . "' poster='thumbs/" . pathinfo(rawurlencode(basename($file_item)), PATHINFO_FILENAME) . ".jpg'></video>";
				//echo "<p text-align'center'>" . basename($file_item) . "</p></div>";
			}	
			
			function searchForId($id, $array) {
			   foreach ($array as $key => $val) {
				   if ($val['id'] === $id) {
					   return $key;
				   }
			   }
			   return null;
			}
		?>

	<script>
		var awesomplete;
		var input;
		var id = <?php echo $id; ?>;
		$(document).ready(function()
		{
			input = document.getElementById("tag-input");
			awesomplete = new Awesomplete(input, { sort: false } );
			if(jQuery.browser.mobile == false){
				input.focus();
			}

			input.addEventListener("awesomplete-select", (event) => {
				GetCategory(event.text.value);
			});	

			input.addEventListener("awesomplete-selectcomplete", (event) => {
				AddTag();
			});
		});

		function copyText(data){
			input.value = data.replace(/ /g, "_");
			category.value = 15;
		}

		function updateOverallRating(data){	
			  $.ajax({
				url: 'OverallRatingAjax.php?id=' + <?php echo $id; ?> + "&rating=" + data,
				type: 'get',
				dataType: 'JSON',
				success: function(response){					
					updateRatings(response);
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					console.log(xhr.responseText);
				}
			});	
		}

		function updateMediaRating(data){	
			  $.ajax({
				url: 'MediaRatingAjax.php?id=' + <?php echo $id; ?> + "&rating=" + data,
				type: 'get',
				dataType: 'JSON',
				success: function(response){					
					updateRatings(response);
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					console.log(xhr.responseText);
				}
			});	
		}

		function updateIndividualRating(data){		
			  $.ajax({
				url: 'IndividualRatingAjax.php?id=' + <?php echo $id; ?> + "&rating=" + data,
				type: 'get',
				dataType: 'JSON',
				success: function(response){					
					updateRatings(response);
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					console.log(xhr.responseText);
				}
			});	
		}

		function updateSexualRating(data){		
			  $.ajax({
				url: 'SexualRatingAjax.php?id=' + <?php echo $id; ?> + "&rating=" + data,
				type: 'get',
				dataType: 'JSON',
				success: function(response){					
					updateRatings(response);
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					console.log(xhr.responseText);
				}
			});	
		}

		function updateRatings(ratings){
			var stars = document.getElementsByClassName("or-star-item");
			for(var i = 0; i < stars.length; i++){
				if(i < ratings[0]){
					stars[i].setAttribute('d', 'M381.2 150.3L524.9 171.5C536.8 173.2 546.8 181.6 550.6 193.1C554.4 204.7 551.3 217.3 542.7 225.9L438.5 328.1L463.1 474.7C465.1 486.7 460.2 498.9 450.2 506C440.3 513.1 427.2 514 416.5 508.3L288.1 439.8L159.8 508.3C149 514 135.9 513.1 126 506C116.1 498.9 111.1 486.7 113.2 474.7L137.8 328.1L33.58 225.9C24.97 217.3 21.91 204.7 25.69 193.1C29.46 181.6 39.43 173.2 51.42 171.5L195 150.3L259.4 17.97C264.7 6.954 275.9-.0391 288.1-.0391C300.4-.0391 311.6 6.954 316.9 17.97L381.2 150.3z');
				}
				else{
					stars[i].setAttribute('d', 'M287.9 0C297.1 0 305.5 5.25 309.5 13.52L378.1 154.8L531.4 177.5C540.4 178.8 547.8 185.1 550.7 193.7C553.5 202.4 551.2 211.9 544.8 218.2L433.6 328.4L459.9 483.9C461.4 492.9 457.7 502.1 450.2 507.4C442.8 512.7 432.1 513.4 424.9 509.1L287.9 435.9L150.1 509.1C142.9 513.4 133.1 512.7 125.6 507.4C118.2 502.1 114.5 492.9 115.1 483.9L142.2 328.4L31.11 218.2C24.65 211.9 22.36 202.4 25.2 193.7C28.03 185.1 35.5 178.8 44.49 177.5L197.7 154.8L266.3 13.52C270.4 5.249 278.7 0 287.9 0L287.9 0zM287.9 78.95L235.4 187.2C231.9 194.3 225.1 199.3 217.3 200.5L98.98 217.9L184.9 303C190.4 308.5 192.9 316.4 191.6 324.1L171.4 443.7L276.6 387.5C283.7 383.7 292.2 383.7 299.2 387.5L404.4 443.7L384.2 324.1C382.9 316.4 385.5 308.5 391 303L476.9 217.9L358.6 200.5C350.7 199.3 343.9 194.3 340.5 187.2L287.9 78.95z');
				}
			}

			var stars = document.getElementsByClassName("mr-star-item");
			for(var i = 0; i < stars.length; i++){
				if(i < ratings[1]){
					stars[i].setAttribute('d', 'M381.2 150.3L524.9 171.5C536.8 173.2 546.8 181.6 550.6 193.1C554.4 204.7 551.3 217.3 542.7 225.9L438.5 328.1L463.1 474.7C465.1 486.7 460.2 498.9 450.2 506C440.3 513.1 427.2 514 416.5 508.3L288.1 439.8L159.8 508.3C149 514 135.9 513.1 126 506C116.1 498.9 111.1 486.7 113.2 474.7L137.8 328.1L33.58 225.9C24.97 217.3 21.91 204.7 25.69 193.1C29.46 181.6 39.43 173.2 51.42 171.5L195 150.3L259.4 17.97C264.7 6.954 275.9-.0391 288.1-.0391C300.4-.0391 311.6 6.954 316.9 17.97L381.2 150.3z');
				}
				else{
					stars[i].setAttribute('d', 'M287.9 0C297.1 0 305.5 5.25 309.5 13.52L378.1 154.8L531.4 177.5C540.4 178.8 547.8 185.1 550.7 193.7C553.5 202.4 551.2 211.9 544.8 218.2L433.6 328.4L459.9 483.9C461.4 492.9 457.7 502.1 450.2 507.4C442.8 512.7 432.1 513.4 424.9 509.1L287.9 435.9L150.1 509.1C142.9 513.4 133.1 512.7 125.6 507.4C118.2 502.1 114.5 492.9 115.1 483.9L142.2 328.4L31.11 218.2C24.65 211.9 22.36 202.4 25.2 193.7C28.03 185.1 35.5 178.8 44.49 177.5L197.7 154.8L266.3 13.52C270.4 5.249 278.7 0 287.9 0L287.9 0zM287.9 78.95L235.4 187.2C231.9 194.3 225.1 199.3 217.3 200.5L98.98 217.9L184.9 303C190.4 308.5 192.9 316.4 191.6 324.1L171.4 443.7L276.6 387.5C283.7 383.7 292.2 383.7 299.2 387.5L404.4 443.7L384.2 324.1C382.9 316.4 385.5 308.5 391 303L476.9 217.9L358.6 200.5C350.7 199.3 343.9 194.3 340.5 187.2L287.9 78.95z');
				}
			}

			var stars = document.getElementsByClassName("ir-star-item");
			for(var i = 0; i < stars.length; i++){
				if(i < ratings[2]){
					stars[i].setAttribute('d', 'M381.2 150.3L524.9 171.5C536.8 173.2 546.8 181.6 550.6 193.1C554.4 204.7 551.3 217.3 542.7 225.9L438.5 328.1L463.1 474.7C465.1 486.7 460.2 498.9 450.2 506C440.3 513.1 427.2 514 416.5 508.3L288.1 439.8L159.8 508.3C149 514 135.9 513.1 126 506C116.1 498.9 111.1 486.7 113.2 474.7L137.8 328.1L33.58 225.9C24.97 217.3 21.91 204.7 25.69 193.1C29.46 181.6 39.43 173.2 51.42 171.5L195 150.3L259.4 17.97C264.7 6.954 275.9-.0391 288.1-.0391C300.4-.0391 311.6 6.954 316.9 17.97L381.2 150.3z');
				}
				else{
					stars[i].setAttribute('d', 'M287.9 0C297.1 0 305.5 5.25 309.5 13.52L378.1 154.8L531.4 177.5C540.4 178.8 547.8 185.1 550.7 193.7C553.5 202.4 551.2 211.9 544.8 218.2L433.6 328.4L459.9 483.9C461.4 492.9 457.7 502.1 450.2 507.4C442.8 512.7 432.1 513.4 424.9 509.1L287.9 435.9L150.1 509.1C142.9 513.4 133.1 512.7 125.6 507.4C118.2 502.1 114.5 492.9 115.1 483.9L142.2 328.4L31.11 218.2C24.65 211.9 22.36 202.4 25.2 193.7C28.03 185.1 35.5 178.8 44.49 177.5L197.7 154.8L266.3 13.52C270.4 5.249 278.7 0 287.9 0L287.9 0zM287.9 78.95L235.4 187.2C231.9 194.3 225.1 199.3 217.3 200.5L98.98 217.9L184.9 303C190.4 308.5 192.9 316.4 191.6 324.1L171.4 443.7L276.6 387.5C283.7 383.7 292.2 383.7 299.2 387.5L404.4 443.7L384.2 324.1C382.9 316.4 385.5 308.5 391 303L476.9 217.9L358.6 200.5C350.7 199.3 343.9 194.3 340.5 187.2L287.9 78.95z');
				}
			}

			var stars = document.getElementsByClassName("sr-star-item");
			for(var i = 0; i < stars.length; i++){
				if(i < ratings[3]){
					stars[i].setAttribute('d', 'M381.2 150.3L524.9 171.5C536.8 173.2 546.8 181.6 550.6 193.1C554.4 204.7 551.3 217.3 542.7 225.9L438.5 328.1L463.1 474.7C465.1 486.7 460.2 498.9 450.2 506C440.3 513.1 427.2 514 416.5 508.3L288.1 439.8L159.8 508.3C149 514 135.9 513.1 126 506C116.1 498.9 111.1 486.7 113.2 474.7L137.8 328.1L33.58 225.9C24.97 217.3 21.91 204.7 25.69 193.1C29.46 181.6 39.43 173.2 51.42 171.5L195 150.3L259.4 17.97C264.7 6.954 275.9-.0391 288.1-.0391C300.4-.0391 311.6 6.954 316.9 17.97L381.2 150.3z');
				}
				else{
					stars[i].setAttribute('d', 'M287.9 0C297.1 0 305.5 5.25 309.5 13.52L378.1 154.8L531.4 177.5C540.4 178.8 547.8 185.1 550.7 193.7C553.5 202.4 551.2 211.9 544.8 218.2L433.6 328.4L459.9 483.9C461.4 492.9 457.7 502.1 450.2 507.4C442.8 512.7 432.1 513.4 424.9 509.1L287.9 435.9L150.1 509.1C142.9 513.4 133.1 512.7 125.6 507.4C118.2 502.1 114.5 492.9 115.1 483.9L142.2 328.4L31.11 218.2C24.65 211.9 22.36 202.4 25.2 193.7C28.03 185.1 35.5 178.8 44.49 177.5L197.7 154.8L266.3 13.52C270.4 5.249 278.7 0 287.9 0L287.9 0zM287.9 78.95L235.4 187.2C231.9 194.3 225.1 199.3 217.3 200.5L98.98 217.9L184.9 303C190.4 308.5 192.9 316.4 191.6 324.1L171.4 443.7L276.6 387.5C283.7 383.7 292.2 383.7 299.2 387.5L404.4 443.7L384.2 324.1C382.9 316.4 385.5 308.5 391 303L476.9 217.9L358.6 200.5C350.7 199.3 343.9 194.3 340.5 187.2L287.9 78.95z');
				}
			}

			var ratnum = document.getElementById("rating-number");
			ratnum.innerHTML = "Rating - " + ratings[0] + "/10 | ";

			var mratnum = document.getElementById("media-rating-number");
			mratnum.innerHTML = "Media - " + ratings[1] + "/10 | ";

			var iratnum = document.getElementById("individual-rating-number");
			iratnum.innerHTML = "Individual - " + ratings[2] + "/10 | ";

			var sratnum = document.getElementById("sexual-rating-number");
			sratnum.innerHTML = "Sexual - " + ratings[3] + "/10 | ";
		}

		function handleTouchStart(evt) {
			const firstTouch = getTouches(evt)[0];                                      
			xDown = firstTouch.clientX;                                      
			yDown = firstTouch.clientY;                                      
		};    

		//document.addEventListener('touchstart', handleTouchStart, false);        
		//document.addEventListener('touchmove', handleTouchMove, false);

		var xDown = null;                                                        
		var yDown = null;

		function getTouches(evt) 
		{
			return evt.touches ||             // browser API
			evt.originalEvent.touches; // jQuery
		}  		
																				 
		function handleTouchMove(evt) {
			if ( ! xDown || ! yDown ) {
				return;
			}

			var xUp = evt.touches[0].clientX;                                    
			var yUp = evt.touches[0].clientY;

			var xDiff = xDown - xUp;
			var yDiff = yDown - yUp;
																				 
			if ( Math.abs( xDiff ) > Math.abs( yDiff ) ) {/*most significant*/
				if ( xDiff > 0 ) {
					//window.location.href = 'viewer.php?id=<?php echo ($id - 1) ?>';
					//document.title = xDiff;
				} else {
					//window.location.href = 'viewer.php?id=<?php echo ($id + 1) ?>';
				}                       
			} else {
				if ( yDiff > 0 ) {
					//window.location.href = 'viewer.php?id=69420';
				} else { 
					//window.location.href = 'viewer.php?id=<?php echo $r; ?>';
				}                                                                 
			}
			/* reset values */
			xDown = null;
			yDown = null;                                             
		};

		$(document).keydown(function(e) {
			///console.log(e);
			if(e.keyCode == 33) {
				window.location.href = 'Post.php?id=<?php if ($index > 0) { echo $files[$index - 1][0]; } else { echo $files[0][0]; } ?>';
			}
			else if(e.keyCode == 34)
			{
				window.location.href = 'Post.php?id=<?php if ($index < $idcount) { echo $files[$index + 1][0]; } else { echo $files[$idcount][0]; } ?>';
			}
			else if(e.keyCode == 35)
			{
				window.location.href = 'Post.php?id=<?php echo $files[$idcount][0] ?>';
			}
			else if(e.keyCode == 36)
			{
				window.location.href = 'Post.php?id=<?php echo $files[0][0] ?>';
			}
		});

		function TagSuggestions(data)
		{
			if(input.value.includes("http")){
				category.value = 17;
			}
			else{
				input.value = input.value.replace(/ $/g, "_");
			data = data.replace(/ $/g, "_");
			
			$.ajax({
				url: 'Tags/TagSuggestionsAjax.php?txt=' + data,
				type: 'get',
				dataType: 'JSON',
				success: function(response){					
					awesomplete.list = response;
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					console.log(xhr.responseText);
				}
			});
			}
		}

		function GetCategory(data)
		{
			$.ajax({
				url: 'Tags/CategoryAjax.php?txt=' + data,
				type: 'get',
				dataType: 'JSON',
				success: function(response){
					//console.log(response);
					category.value = response;
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					console.log(xhr.responseText);
				}
			});
		}

		function AddTag()
		{
			//e.preventDefault();
			var tag = input.value;
			//console.log(tag);
			cat = category.value;
			//console.log(cat);
			
			$.ajax({
				url: 'ImgTagAddAjax.php?tag=' + encodeURIComponent(tag) + '&cat=' + cat + '&id=' + id,
				type: 'get',
				dataType: 'JSON',
				success: function(response){
					if(cat != 17){
						GetNewTags();
					}
					else{
						//input.value = "";
						//category.selectedIndex = 0;
						GetNewSources();
					}
					
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					//console.log(url);
					console.log(xhr.responseText);
					console.log(thrownError);
					//resdiv.innerHTML += "<div><p style='color:red;'>Error</p></div>";
				}
			});

			if(jQuery.browser.mobile == false){
				input.focus();
			}

			return false;
		}

		function GetNewTags()
		{
			input.value = "";
			
			$.ajax({ 
				url: 'ImgTagListAjax.php?id=' + id,
				type: 'get',
				success: function(response){
					UpdateTagList(response);
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					//console.log(url);
					console.log(xhr.responseText);
					console.log(thrownError);
					//resdiv.innerHTML += "<div><p style='color:red;'>Error</p></div>";
				}
			});
		}

		function GetNewSources()
		{
			input.value = "";
			
			$.ajax({ 
				url: 'SourceListAjax.php?id=' + id,
				type: 'get',
				success: function(response){
					UpdateSourcesList(response);
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					//console.log(url);
					console.log(xhr.responseText);
					console.log(thrownError);
					//resdiv.innerHTML += "<div><p style='color:red;'>Error</p></div>";
				}
			});
		}

		function UpdateTagList(taghtml)
		{
			tagarea = document.getElementById("tagarea");
			tagarea.innerHTML = taghtml;
			category.selectedIndex = 0;
		}

		function UpdateSourcesList(sourcehtml)
		{
			sourcearea = document.getElementById("sources");
			sourcearea.innerHTML = sourcehtml;
			category.selectedIndex = 0;
		}

		function RemoveTag(tagid, imageid)
		{			
			$.ajax({ 
				url: 'Tags/RemoveTagAjax.php?id=' + imageid + "&tagid=" + tagid,
				type: 'get',
				success: function(response){
					//console.log(response);
					if(response == "Error"){
						console.log("Error with Remove Tag");
					}	
					else{
						var remy = document.getElementById("a" + tagid);
						//console.log(tagid);
						var inner = remy.outerHTML;
						remy.outerHTML = "<del style='color:red;'>" + inner + "</del>";
					}	
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					console.log(xhr.responseText);
				}
			});
		}

		function RemoveSource(source, imageid)
		{			
			$.ajax({ 
				url: 'Tags/RemoveSourceAjax.php?id=' + imageid + "&source=" + encodeURIComponent(source),
				type: 'get',
				success: function(response){
					if(response == "Error"){
						console.log("Error with Remove Source");
					}	
					else{
						//console.log(response);
						var remy = document.getElementById(response);
						//console.log(tagid);
						var inner = remy.outerHTML;
						remy.outerHTML = "<del style='color:red;'>" + inner + "</del>";
					}	
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					console.log(xhr.responseText);
				}
			});
		}

		function textPaste(){
			setTimeout(function(){
				console.log(input.value);
				input.value = input.value.replace(/ /g, "_");
			}, 0);

			category.value = 15;
		}

		function review(){
			$.ajax({ 
				url: 'MarkReviewedAjax.php?id=' + id,
				type: 'get',
				success: function(response){
					if(response == "Error"){
						console.log("Error Marking as Reviewd");
					}	
					else{
						var revv = document.getElementById("mark-review");
						revv.innerHTML = "Reviewed!";
					}	
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					console.log(xhr.responseText);
				}
			});
		}
	</script>		
	</body>
</html>