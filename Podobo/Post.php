<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<?php
	session_start();
		
	$TagColors=array("#FFFFFF", "#6495ED", "#FF4500", "#FF4500", "#FF8C00", "#7FFFD4", "#BA55D3", "#228B22", "#8A2BE2", "#FFFFFF", "#FFFFFF", "#FFFFFF", "#FFFFFF", "#FFFFFF", "#DAA520", "#8FBC8F", "#FFFFFF");
	$TagOrder=array(14,4,0,1,2,3,8,6,7,9,10,12,13,11,16,5,15);
	$TagCategoryTitle=array("General", "IP/Series", "Individual", "", "Artist", "Studio/Network", "Sex", "Afilliation/Group", "Race/Species/Ethnicity", "Body Part", "Clothing/Accessory", "Position", "Setting", "Action", "Meta", "Title", "Release Date");
	
	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");		
	
	if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = 1; };

	$files = [];
	if(isset($_SESSION["filtered_data"]) && count($_SESSION["filtered_data"]) > 0){
		$files = $_SESSION["filtered_data"];
		$idcount = count($files)-1;
		$filtered = true;
		
	}
	else{
		if(isset($_SESSION["image_data"])){
			$files = $_SESSION["image_data"];				
			$filtered = false;
		}
		else{		
			$result = $db->query("SELECT ID, name, overall_rating, video, sound, tag_list FROM files order by id desc");				
			while ($row = $result->fetchArray()) {
				array_push($files, $row);
			}
			$_SESSION["search"] = "";
			$_SESSION["image_data"] = $files;
			$filtered = false;
		}
		$idcount = count($files)-1;
	}

	$r = rand(0, $idcount);

	$index = array_search($id, array_column($files, 0));
	echo "<!-- " . $index . "-->";
	echo "<!-- " . $files[$index][0] . "-->";	
				
	$sql = "SELECT path, tag_list, overall_rating, media_rating, individual_rating, sexual_rating, height, width, sources FROM files where ID='" . $id . "'";
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

	$sql = "SELECT message FROM status where id=1";
	$result = $db->query($sql);
	$status = $result->fetchArray()[0] ?? '';

	$sql = "SELECT iqdb_progress FROM status where id=1";
	$result = $db->query($sql);
	$iqdbcount = $result->fetchArray()[0] ?? '';
			
?>
	<head>
		<meta http-equiv="Content-Type" content="text/html" charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
	    <title>Podobo - <?php echo $id; ?></title>
	    <link rel="stylesheet" type="text/css" href="../style/PodoboStyle.css" />		
		<link rel="stylesheet" href="../style/w3.css" />
		<link rel="stylesheet" href="../awesomplete/awesomplete.css">
		<link rel="icon" type="image/x-icon" href="../imgs/favicon.ico">
        <!-- <script type = "text/javascript" src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script> -->
		<script type = "text/javascript" src = "../js/jquery-3.6.0.min.js"></script>
		<script type = "text/javascript" src="../awesomplete/awesomplete.js"></script>
		<!-- <script src="https://kit.fontawesome.com/710df8e4cb.js" crossorigin="anonymous"></script> -->
		<!-- <script src="../js/710df8e4cb-edit.js" crossorigin="anonymous"></script> -->
		<script src="../style/releases/v5.15.4/js/all.min.js" crossorigin="anonymous"></script>
		<base target="_parent" />
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
		</script>
		<style type="text/css">
		.fa-star {
			color: gold;
			margin: 3px;
		}
		</style>
	</head>
	<body>
            <div class="w3-bar w3-theme w3-left-align w3-medium container_header">
                <a class="w3-bar-item w3-button w3-theme-l1" href="Posts.php">Posts</a>		
				<a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="Tags/TagList.php">Tags</a>
				<a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="Wiki.php">Wiki</a>
				<a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="Slideshow.php">Slideshow</a>
				<a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="Tools.php">Tools</a>
				<a class="w3-bar-item w3-theme-l1"> | </a>
				<a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey"><?php echo $status . " - " . $iqdbcount; ?></a>

            </div>
			<div class="w3-bar w3-theme-l1 w3-left-align w3-small container_subheader">
                <a class="w3-bar-item w3-button w3-theme-l1" href="Posts.php">Recent</a>		
				<a class="w3-bar-item w3-button w3-theme-l1" href="Post.php?id=<?php echo $r; ?>">Random</a>
				<a class="w3-bar-item w3-button w3-theme-l1" href="Posts.php?page=<?php echo ceil($idcount/105) ?>">Oldest</a>
				<a class="w3-bar-item w3-button w3-theme-l1" href="Posts.php?search=%24video">Videos</a>
				<a class="w3-bar-item w3-button w3-theme-l1" href="Posts.php?search=%24dur>600">Studio Videos</a>
				<a class="w3-bar-item w3-theme-l1">|</a>
				<a class="w3-bar-item w3-button w3-theme-l1" onclick="showEdit()">Edit Tags</a>
				<a class="w3-bar-item w3-button w3-theme-l1" href='<?php if($_SESSION["search"] != "") { echo "Posts.php?search=" . $_SESSION["search"] . "'>Search: " . $_SESSION["search"]; } else { echo "Posts.php'>Search: All"; }; ?></a>
            </div>
            <main class="row">
                <div class="col-2 w3-theme main-left">
		
			<?php
			
			echo "<div class='tagarea' >";
			echo "<div class='rating-wrapper'><strong id='rating-number'onclick='hidetags()'>Rating - " . $overall_rating . "/10 | </Strong>";  //overall rating
			echo "<div id='or'>";
			for($q = 1; $q <= 10; $q++)
			{
				if($q <= $overall_rating)
				{
					//echo "<img onclick='updaterating(" . $q . ")' class='star' src='img/starFull.png' />";
					echo "<i class='fas fa-star fa-2x' onclick='updateOverallRating(" . $q . ")'></i>";
				}				
				else
				{
					//echo "<img onclick='updaterating(" . $q . ")' class='star' src='img/starEmpty.png' />";
					echo "<i class='far fa-star fa-2x' onclick='updateOverallRating(" . $q . ")'></i>";
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
					echo "<i class='fas fa-star fa-2x' onclick='updateMediaRating(" . $q . ")'></i>";
				}				
				else
				{
					//echo "<img onclick='updaterating(" . $q . ")' class='star' src='img/starEmpty.png' />";
					echo "<i class='far fa-star fa-2x' onclick='updateMediaRating(" . $q . ")'></i>";
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
					echo "<i class='fas fa-star fa-2x' onclick='updateIndividualRating(" . $q . ")'></i>";
				}				
				else
				{
					//echo "<img onclick='updaterating(" . $q . ")' class='star' src='img/starEmpty.png' />";
					echo "<i class='far fa-star fa-2x' onclick='updateIndividualRating(" . $q . ")'></i>";
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
					echo "<i class='fas fa-star fa-2x' onclick='updateSexualRating(" . $q . ")'></i>";
				}				
				else
				{
					//echo "<img onclick='updaterating(" . $q . ")' class='star' src='img/starEmpty.png' />";
					echo "<i class='far fa-star fa-2x' onclick='updateSexualRating(" . $q . ")'></i>";
				}
			}
			//echo "<strong id='rating-number'>" . $rating . "/10</strong>";
			echo "</div>";

			echo "</div>";

			echo "<hr />";

			echo "<div class='tagadd'>";
				//echo "<form>";
				echo "<input type='text' id='tags' oninput='TagSuggestions(this.value)' onpaste='textPaste()'/>";
				//echo "<input type='hidden' onsubmit='AddTag()'/>";
				//echo "</form>";

				echo "<select name='categories' id='category'>";
					echo "<option value='0'>General</option>";
					echo "<option value='1'>IP</option>";
					echo "<option value='2'>Individual</option>";
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
				echo "<a href='Tags/Tag.php?tagid=".$tag[3]."'><i style='color:" . $TagColors[$tag[1]] . "' class='fa-solid fa-pen-to-square fa-xl'></i></a> <a style='color:" . $TagColors[$tag[1]] . "' href ='Posts.php?search=" . str_replace(" ", "_", $tag[0]) ."&page=1'>" . $tag[0] . "</a>";
				echo "<input type='Button' value='x' class='rem-button' onclick='RemoveTag(" . $tag[3] . ", " . $id . ")' />";
				echo "</div>";
				echo "</dd>";
			}
			
			echo "</dl>";
			echo "</div>";
			echo "<hr />";

			//echo "<div class='imageinfo'>";

			//echo "<input type='Button' class='w3-center' value='Edit Tags' onclick='showEdit()' />";
			$filename = basename($file_link);
			if(str_contains($filename, "]")){
				$filename_contents = preg_split('/(\]|\[| \- |\(|\))/', $filename, -1, PREG_SPLIT_NO_EMPTY);
				for($i = 0; $i < count($filename_contents) - 1; $i++){
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
			
			echo "</div>";
			
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
			
			
			echo "<div class='container_footer' align='center'>";
			
			$idlimit = 5;

			echo "<!-- index: " . $index . "-->";
			
			if($index < $idlimit) { $start_page = 0; } else { $start_page = $index - ($idcount < $idlimit ? $idcount + 1 : $idlimit);};
			if($index > $idcount - $idlimit) { $end_page = $idcount; } else { $end_page = $index + ($idcount < $idlimit ? $idcount : $idlimit); };
			
			if($index != 0) 
			{
				echo "<a href='Post.php?id=" . $files[0][0] . "'>&lt;&lt;</a>";
				echo "<a href='Post.php?id=" . $files[$index - 1][0] . "'>&lt;- Previous</a>";	
			};
			
			for($k = $start_page; $k <= $end_page; $k++)
			{
				echo "<a href='Post.php?id=".$files[$k][0]."'>";
				if($k==$index) { echo "<strong> " . ($k + 1) . " </strong>"; } else { echo "<p id='otherpages'>" . ($k + 1) . "</p>";};
				echo "</a>";
			}
			
			if($index != $idcount) 
			{
				echo "<a href='Post.php?id=".$files[$index + 1][0]."'>Next -&gt;</a>";
				echo "<a href='Post.php?id=".$files[$idcount][0]."'>&gt;&gt;</a>";
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
				echo "<div><video class='center-fit' loop controls muted src='" . strtolower(substr(pathinfo($file_item, PATHINFO_DIRNAME), 3)) . "\\"  . rawurlencode(basename($file_item)) . "'></video>";
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
			input = document.getElementById("tags");
			awesomplete = new Awesomplete(input, { sort: false } );
			input.focus();

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
			var stars = document.getElementById("or").getElementsByClassName("fa-star");
			for(var i = 0; i < stars.length; i++){
				if(i < ratings[0]){
					stars[i].classList.remove('far');
					stars[i].classList.add('fas');
				}
				else{
					stars[i].classList.remove('fas');
					stars[i].classList.add('far');
				}
			}

			var stars = document.getElementById("mr").getElementsByClassName("fa-star");
			for(var i = 0; i < stars.length; i++){
				if(i < ratings[1]){
					stars[i].classList.remove('far');
					stars[i].classList.add('fas');
				}
				else{
					stars[i].classList.remove('fas');
					stars[i].classList.add('far');
				}
			}

			var stars = document.getElementById("ir").getElementsByClassName("fa-star");
			for(var i = 0; i < stars.length; i++){
				if(i < ratings[2]){
					stars[i].classList.remove('far');
					stars[i].classList.add('fas');
				}
				else{
					stars[i].classList.remove('fas');
					stars[i].classList.add('far');
				}
			}

			var stars = document.getElementById("sr").getElementsByClassName("fa-star");
			for(var i = 0; i < stars.length; i++){
				if(i < ratings[3]){
					stars[i].classList.remove('far');
					stars[i].classList.add('fas');
				}
				else{
					stars[i].classList.remove('fas');
					stars[i].classList.add('far');
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

			return false;

			input.focus();
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
	</script>		
	</body>
</html>