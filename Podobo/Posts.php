<?php
// Start the session
session_start();
$thumbs_source = "thumbs/";
$columncount = 15;
$rowcount = 7;
$TagColors=array("#FFFFFF", "#6495ED", "#FF4500", "#FF4500", "#FF8C00", "#7FFFD4", "#BA55D3", "#228B22", "#8A2BE2", "#FFFFFF", "#FFFFFF", "#FFFFFF", "#FFFFFF", "#FFFFFF", "#DAA520", "#8FBC8F", "#FFFFFF");
$rows = [];
$tags = [];

$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");

if(isset($_GET["search"])) { $search = html_entity_decode($_GET["search"]); } else { $search = ""; }
if(isset($_GET["page"])) { $page = $_GET["page"]; } else { $page = 1; }
if(isset($_SESSION["image_data"]) and $search == "")
{
	$_SESSION["search"] = $search;
	$rows = $_SESSION["image_data"];
	unset($_SESSION["filtered_data"]);
	$filtered = false;
}
else
{
	if($search == "")
	{
		$sql = "SELECT ID, name, overall_rating, video, sound, tag_list FROM files order by ID desc";
		$result = $db->query($sql);
		while ($row = $result->fetchArray()) {
			array_push($rows, $row);
		}
		
		$_SESSION["search"] = $search;
		$_SESSION["image_data"] = $rows; //only store session data for full sql call
		unset($_SESSION["filtered_data"]);
		$filtered = false;
	}
	else
	{	
		$_SESSION["search"] = $search;
		// echo $search;
		$searchtags = array_filter(explode(" ", $search));
		//echo "<!-- " . print_r($searchtags) . " -->";
		$sql = "select ID, name, overall_rating, video, sound, tag_list from files where";
		for($tag_index = 0; $tag_index<count($searchtags); $tag_index++){
			$searchtags[$tag_index] = str_replace("_", " ", $searchtags[$tag_index]);
			if(str_contains($searchtags[$tag_index], "||")){
				$ortags = array_filter(explode("||", $searchtags[$tag_index]));
				if($tag_index < 1){
					$sql .= " (";
				}
				else{
					$sql .= " and (";
				}

				for($or_index = 0; $or_index<count($ortags); $or_index++){
					if(str_starts_with($ortags[$or_index], "!")){	
						$sqla = $db->prepare("select tagid from tags where tag_name=:tag COLLATE NOCASE");
						$sqla->bindValue(':tag', substr($ortags[$or_index], 1), SQLITE3_TEXT);
						$searchtagid = $sqla->execute()->fetchArray()[0] ?? -1;	
											
						if(!empty($searchtagid)){
							if($or_index < 1){
								$sql .= "tag_list not like '%;" . $searchtagid . ";%'";
							}
							else{
								$sql .= " or tag_list not like '%;" . $searchtagid . ";%'";
							}
						}
						else{
							if($or_index < 1){
								$sql .= "tag_list not like '%;%" . ";%'";
							}
							else{
								$sql .= " or tag_list not like '%;%" . ";%'";
							}
						}
					}
					else if(str_starts_with($ortags[$or_index], '$ext:')){
						if($or_index < 1){
									$sql .= "ext ='" . substr($ortags[$or_index],5) . "'";
							}
							else{
									$sql .= " or ext ='" . substr($ortags[$or_index],5) . "'";
							}
					}
					else if(str_starts_with($ortags[$or_index], '$dur>')){
						if($or_index < 1){
									$sql .= "duration > " . substr($ortags[$or_index],5) . "";
							}
							else{
									$sql .= " or duration > " . substr($ortags[$or_index],5) . "";
							}
					}						
					else if(str_starts_with($ortags[$or_index], '$dur<')){
						if($or_index < 1){
									$sql .= "duration < " . substr($ortags[$or_index],5) . "";
							}
							else{
									$sql .= " or duration < " . substr($ortags[$or_index],5) . "";
							}
					}
					else if(str_starts_with($ortags[$or_index], '$name:')){
						if($or_index < 1){
									$sql .= "name like '%" . str_replace('\'', '_', substr($ortags[$or_index],6)) . "%'";
							}
							else{
									$sql .= " or name like '%" . str_replace('\'', '_', substr($ortags[$or_index],6)) . "%'";
							}
					}
					else if(str_starts_with($ortags[$or_index], '$!name:')){
						if($or_index < 1){
									$sql .= "name not like '%" . str_replace('\'', '_', substr($ortags[$or_index],7)) . "%'";
							}
							else{
									$sql .= " or name not like '%" . str_replace('\'', '_', substr($ortags[$or_index],7)) . "%'";
							}
					}
					else if(str_starts_with($ortags[$or_index], '$rating>')){
						if($or_index < 1){
									$sql .= "overall_rating > " . substr($ortags[$or_index],8) . "";
							}
							else{
									$sql .= " or overall_rating > " . substr($ortags[$or_index],8) . "";
							}
					}						
					else if(str_starts_with($ortags[$or_index], '$rating<')){
						if($or_index < 1){
									$sql .= "overall_rating < " . substr($ortags[$or_index],8) . "";
							}
							else{
									$sql .= " or overall_rating < " . substr($ortags[$or_index],8) . "";
							}
					}
					else if(str_starts_with($ortags[$or_index], '$height>')){
						if($or_index < 1){
									$sql .= "height > " . substr($ortags[$or_index],8) . "";
							}
							else{
									$sql .= " or height > " . substr($ortags[$or_index],8) . "";
							}
					}						
					else if(str_starts_with($ortags[$or_index], '$height<')){
						if($or_index < 1){
									$sql .= "height < " . substr($ortags[$or_index],8) . "";
							}
							else{
									$sql .= " or height < " . substr($ortags[$or_index],8) . "";
							}
					}
					else if(str_starts_with($ortags[$or_index], '$sound')){
						if($or_index < 1){
									$sql .= "sound = 1";
							}
							else{
									$sql .= " or sound = 1";
							}
					}
					else if(str_starts_with($ortags[$or_index], '$video')){
						if($or_index < 1){
									$sql .= "video = 1";
							}
							else{
									$sql .= " or video = 1";
							}
					}
					else if(str_starts_with($ortags[$or_index], '$rev')){
						if($or_index < 1){
									$sql .= "review = 1";
							}
							else{
									$sql .= " or review = 1";
							}
					}							
					else{
						if(str_starts_with($ortags[$or_index], '~')){
							$sqla = $db->prepare("select tagid from tags where tag_name=:tag COLLATE NOCASE");
							$sqla->bindValue(':tag', substr($ortags[$or_index], 1), SQLITE3_TEXT);
							$searchtagid = $sqla->execute()->fetchArray()[0] ?? -1;	
							
							if($or_index < 1){
										$sql .= "tag_list like '%;" . $searchtagid . ";%' or name like '%" . str_replace('\'', '_', substr($ortags[$or_index], 1)) . "%'";
								}
								else{
										$sql .= " or tag_list like '%;" . $searchtagid . ";%' or name like '%" . str_replace('\'', '_', substr($ortags[$or_index], 1)) . "%'";
								}
						}
						else{
							$sqla = $db->prepare("select tagid from tags where tag_name=:tag COLLATE NOCASE");
							$sqla->bindValue(':tag', $ortags[$or_index], SQLITE3_TEXT);
							$searchtagid = $sqla->execute()->fetchArray()[0] ?? -1;	
							
							if(!empty($searchtagid)){
								if($or_index < 1){
									$sql .= "tag_list like '%;" . $searchtagid . ";%'";
								}
								else{
									$sql .= " or tag_list like '%;" . $searchtagid . ";%'";
								}								
							}
							else{
								if($or_index < 1){
									$sql .= "name like '%" . str_replace('\'', '_', $ortags[$or_index]) . "%'";
								}
								else{
									$sql .= " or name like '%" . str_replace('\'', '_', $ortags[$or_index]) . "%'";
								}
							}
						}
					}
				}

				$sql .= ")";
			}
			else if(str_starts_with($searchtags[$tag_index], "!")){		
				$sqla = $db->prepare("select tagid from tags where tag_name=:tag COLLATE NOCASE");
				$sqla->bindValue(':tag', substr($searchtags[$tag_index], 1), SQLITE3_TEXT);
				$searchtagid = $sqla->execute()->fetchArray()[0] ?? -1;	

				if(!empty($searchtagid)){
					if($tag_index < 1){
						$sql .= " tag_list not like '%;" . $searchtagid . ";%'";
					}
					else{
						$sql .= " and tag_list not like '%;" . $searchtagid . ";%'";
					}
				}
				else{
					if($tag_index < 1){
						$sql .= " tag_list not like '%;%" . ";%'";
					}
					else{
						$sql .= " and tag_list not like '%;%" . ";%'";
					}
				}
			}
			else if(str_starts_with($searchtags[$tag_index], '$ext:')){
				if($tag_index < 1){
							$sql .= " ext ='" . substr($searchtags[$tag_index],5) . "'";
					}
					else{
							$sql .= " and ext ='" . substr($searchtags[$tag_index],5) . "'";
					}
			}
			else if(str_starts_with($searchtags[$tag_index], '$dur>')){
				if($tag_index < 1){
							$sql .= " duration > " . substr($searchtags[$tag_index],5) . "";
					}
					else{
							$sql .= " and duration > " . substr($searchtags[$tag_index],5) . "";
					}
			}						
			else if(str_starts_with($searchtags[$tag_index], '$dur<')){
				if($tag_index < 1){
							$sql .= " duration < " . substr($searchtags[$tag_index],5) . "";
					}
					else{
							$sql .= " and duration < " . substr($searchtags[$tag_index],5) . "";
					}
			}
			else if(str_starts_with($searchtags[$tag_index], '$name:')){
				if($tag_index < 1){
							$sql .= " name like '%" . str_replace('\'', '_', substr($searchtags[$tag_index],6)) . "%'";
					}
					else{
							$sql .= " and name like '%" . str_replace('\'', '_', substr($searchtags[$tag_index],6)) . "%'";
					}
			}
			else if(str_starts_with($searchtags[$tag_index], '$!name:')){
				if($tag_index < 1){
							$sql .= " name not like '%" . str_replace('\'', '_', substr($searchtags[$tag_index],7)) . "%'";
					}
					else{
							$sql .= " and name not like '%" . str_replace('\'', '_', substr($searchtags[$tag_index],7)) . "%'";
					}
			}
			else if(str_starts_with($searchtags[$tag_index], '$rating>')){
				if($tag_index < 1){
							$sql .= " overall_rating > " . substr($searchtags[$tag_index],8) . "";
					}
					else{
							$sql .= " and overall_rating > " . substr($searchtags[$tag_index],8) . "";
					}
			}						
			else if(str_starts_with($searchtags[$tag_index], '$rating<')){
				if($tag_index < 1){
							$sql .= " overall_rating < " . substr($searchtags[$tag_index],8) . "";
					}
					else{
							$sql .= " and overall_rating < " . substr($searchtags[$tag_index],8) . "";
					}
			}
			else if(str_starts_with($searchtags[$tag_index], '$height>')){
				if($tag_index < 1){
							$sql .= " height > " . substr($searchtags[$tag_index],8) . "";
					}
					else{
							$sql .= " and height > " . substr($searchtags[$tag_index],8) . "";
					}
			}						
			else if(str_starts_with($searchtags[$tag_index], '$height<')){
				if($tag_index < 1){
							$sql .= " height < " . substr($searchtags[$tag_index],8) . "";
					}
					else{
							$sql .= " and height < " . substr($searchtags[$tag_index],8) . "";
					}
			}
			else if(str_starts_with($searchtags[$tag_index], '$sound')){
				if($tag_index < 1){
							$sql .= " sound = 1";
					}
					else{
							$sql .= " and sound = 1";
					}
			}
			else if(str_starts_with($searchtags[$tag_index], '$video')){
				if($tag_index < 1){
							$sql .= " video = 1";
					}
					else{
							$sql .= " and video = 1";
					}
			}
			else if(str_starts_with($searchtags[$tag_index], '$rev')){
				if($tag_index < 1){
							$sql .= " review = 1";
					}
					else{
							$sql .= " and review = 1";
					}
			}
			else{
				if(str_starts_with($searchtags[$tag_index], '~')){
					$sqla = $db->prepare("select tagid from tags where tag_name=:tag COLLATE NOCASE");
					$sqla->bindValue(':tag', substr($searchtags[$tag_index], 1), SQLITE3_TEXT);
					$searchtagid = $sqla->execute()->fetchArray()[0] ?? -1;

					if($tag_index < 1){
								$sql .= "(tag_list like '%;" . $searchtagid . ";%' or name like '%" . str_replace('\'', '_', substr($searchtags[$tag_index], 1)) . "%')";
						}
						else{
								$sql .= " and (tag_list like '%;" . $searchtagid . ";%' or name like '%" . str_replace('\'', '_', substr($searchtags[$tag_index], 1)) . "%')";
						}
				}
				else{
					$sqla = $db->prepare("select tagid from tags where tag_name=:tag COLLATE NOCASE");
					$sqla->bindValue(':tag', $searchtags[$tag_index], SQLITE3_TEXT);
					$searchtagid = $sqla->execute()->fetchArray()[0] ?? -1;

					if(!empty($searchtagid)){
						if($tag_index < 1){
							$sql .= " tag_list like '%;" . $searchtagid . ";%'";
						}
						else{
							$sql .= " and tag_list like '%;" . $searchtagid . ";%'";
						}								
					}
					else{
						if($tag_index < 1){
							$sql .= " name like '%" . str_replace('\'', '_', $searchtags[$tag_index]) . "%'";
						}
						else{
							$sql .= " and name like '%" . str_replace('\'', '_', $searchtags[$tag_index]) . "%'";
						}
					}
				}
			}						
		}	
		$sql .=  " order by ID desc";					
		
		echo "<!--" . $sql . "-->";
		
		$result = $db->query($sql);
		while ($row = $result->fetchArray()) {
			array_push($rows, $row);
		}
		
		$_SESSION["filtered_data"] = $rows;
		$filtered = true;
	}
}

$rownum = count($rows);

$r = rand(0, $rownum);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html" />
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
	    <title>Podobo - Posts [<?php echo $rownum; ?>]</title>
	    <link rel="stylesheet" type="text/css" href="../style/PodoboStyle.css" />
		<link rel="stylesheet" href="../style/w3.css" />
		<link rel="stylesheet" href="../awesomplete/awesomplete.css">
		<link rel="icon" type="image/x-icon" href="../imgs/favicon.ico">
		<!-- <script type = "text/javascript" src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script> -->
		<script type = "text/javascript" src = "../js/jquery-3.6.0.min.js"></script>
		<script type = "text/javascript" src="../awesomplete/awesomplete.js"></script>
		<style type="text/css">
			input[type=text] {
			width: 240px;
			}
		</style>
	<script>
		
		function ListView() {
			var elements = document.getElementsByClassName("posts");
			for (i = 0; i < elements.length; i++) {
				elements[i].style.width = "100%";
				elements[i].style.height = "151px";
				//elements[i].classList.add("w3-center");
			}

			var nametext = document.getElementsByClassName("nametext");
			while(nametext.length){
				nametext[0].className = "nametext-list";
			}

			var vidandsoundmarker = document.getElementsByClassName(" vidandsoundmarker");
			while( vidandsoundmarker.length){
				vidandsoundmarker[0].className = " vidandsoundmarker-list";
			}

			var vidmarker = document.getElementsByClassName(" vidmarker");
			while( vidmarker.length){
				vidmarker[0].className = " vidmarker-list";
			}

			var nowrap = document.getElementsByClassName("nowrap");
			while(nowrap.length) {
				nowrap[0].classList.remove("nowrap");
			}
		}
	</script>

	</head>
	<body>
		<?php
			//echo "<div class='container'>";
			echo "<div class='w3-bar w3-theme w3-left-align w3-medium container_header'>";
				echo "<a class='w3-bar-item w3-button w3-theme-l1' href='Posts.php'>Posts</a>";
				echo "<a class='w3-bar-item w3-button w3-hide-small w3-hover-blue-grey' href='Tags/TagList.php'>Tags</a>";
				echo "<a class='w3-bar-item w3-button w3-hide-small w3-hover-blue-grey' href='Wiki.php'>Wiki</a>";
				echo "<a class='w3-bar-item w3-button w3-hide-small w3-hover-blue-grey' href='Slideshow.php'>Slideshow</a>";
				echo "<a class='w3-bar-item w3-button w3-hide-small w3-hover-blue-grey' href='Tools.php'>Tools</a>";
			echo "</div>";
			echo "<div class='w3-bar w3-theme-l1 w3-left-align w3-small container_subheader'>";
				echo "<a class='w3-bar-item w3-button w3-theme-l1' href='Posts.php'>Recent</a>";	
				echo "<a class='w3-bar-item w3-button w3-theme-l1' href='Post.php?id=" . $r . "'>Random</a>";
				echo "<a class='w3-bar-item w3-button w3-theme-l1' href='Posts.php?page=" . ceil($rownum/105) . "'>Oldest</a>";
				echo "<a class='w3-bar-item w3-button w3-theme-l1' href='Posts.php?search=%24video'>Videos</a>";
				echo "<a class='w3-bar-item w3-button w3-theme-l1' href='Posts.php?search=%24dur>600'>Studio Videos</a>";
				echo "<a class='w3-bar-item w3-button w3-theme-l1' onclick='ListView()'>List</a>";
			echo "</div>";
			echo "<main class='row'>";
			echo "<div class='col-1 w3-theme main-left'>";
                        echo "<div>";
			echo "<h4 class='w3-bar-item'><b>Tags</b></h4>";
			
			$sql2 = "select tag_name, category, tag_count from tags order by tag_count desc limit 40";
			$result2 = $db->query($sql2);
			while ($row = $result2->fetchArray()) {
				array_push($tags, $row);
			}
			
			$db = null;
			//echo session_save_path();
			
			//echo "<form class='w3-center' autocomplete='off'>"; /* action='main.php?page='" . $page . "' method='get' */
				//echo "<div id='searchbar' class='autocomplete' onkeyup='search_keypress'>";
				//echo"<div>";
				echo "<form class='tagadd' action='Posts.php' method='GET'>";
					//echo "<input type='text' id='search' name='search' oninput='TagSuggestions(this.value)' value='".$search."' />";
					echo "<input type='text' id='tags' oninput='TagSuggestions(this.value)' name='search' value='" . htmlspecialchars($search, ENT_QUOTES) . "'  data-multiple/>";
					echo "<input type='submit' hidden />";
				echo"</form>";
				//echo "</div>";
			//echo "</form>";
			
			echo "<hr />";
                        echo "</div>";
                        echo "<div>";
                        
			echo "<ul class='search-tag-list'>";
			foreach($tags as $tag)
			{
				echo "<li>";
				echo "<a href ='Posts.php?search=!" . $tag[0] = str_replace(" ", "_", $tag[0]) ."&page=" . $page . "'>! </a><a style='color:" . $TagColors[$tag[1]] . "' href ='Posts.php?search=" . $tag[0] = str_replace(" ", "_", $tag[0]) ."&page=" . $page . "'>" . $tag[0] . " - [" . $tag[2] . "]</a>";
				echo "</li>";
				//echo $i;
			}
			echo "</ul>";
			echo "</div>";
                        echo "</div>";
			
			echo "<div class='col-9'><div class='posts-wrapper'>";
			
			//$page = 1;			
			
			$maxpage = ceil($rownum/($columncount*$rowcount));
			$pagestart = ($page - 1) * ($columncount * $rowcount);
			
			for($i = $pagestart; $i<$pagestart+($rowcount*$columncount) and $i<$rownum; $i++)
			{
				if($i % 15 == 0){
					echo "<div class='row-posts'>";
				}
				if(strlen($rows[$i][5]) > 1) {$thumbclass = "class='thumbs-tagged'";} else  {$thumbclass = "class='thumbs-untagged'";} 
				echo "<div class='posts'>";
				echo "<a href ='Post.php?id=" . $rows[$i][0] . "'>";				
				
				echo "<img " . $thumbclass . " src ='" . $thumbs_source . pathinfo(rawurlencode($rows[$i][1]), PATHINFO_FILENAME) . ".jpg" . "' onerror='this.src =\"" . $thumbs_source . "MissingThumb.jpg\"" . "' alt='N/A'/></a><a href='Post.php?id=" . $rows[$i][0] . "'>" . $rows[$i][0] . "</a>";
				echo "<div id='nametext' class='nametext'><p id='nowrap' class='nowrap'>" . $rows[$i][1] . "</p></div>";
				if($rows[$i][3] == 1 && $rows[$i][4] == 1){
					echo "<p class='vidandsoundmarker'>\u{25B6}\u{1F56A}</p>";
				}
				else if($rows[$i][3] == 1 && $rows[$i][4] == 0){
					echo "<p class='vidmarker'>\u{25B6}</p>";
				}
				else if($rows[$i][3] == 0 && $rows[$i][4] == 1){
					echo "<p class='vidmarker'>\u{1F56A}</p>";
				}
				echo "</div>";

				if($i % 15 == 14){
					echo "</div>";
				}
			}
			
			echo "</div>";
			
			echo "<div class='container_footer' align='center'>";
			
			
			$pagelimit = 5;
			if($page < $pagelimit) { $start_page = 1; } else { $start_page = $page - ($maxpage < $pagelimit ? $maxpage + 1 : $pagelimit - 1);}
			if($page > $maxpage - $pagelimit) { $end_page = $maxpage; } else { $end_page = $page + ($maxpage < $pagelimit ? $maxpage : $pagelimit); }
			
			if($page != 1) 
			{
				echo "<a href='Posts.php?page=1&search=" . $search . "'>&lt;&lt;</a>";
				echo "<a href='Posts.php?page=".($page - 1)."&search=" . $search . "'>&lt;</a>";	
			}
			
			for($k = $start_page; $k <= $end_page; $k++)
			{
				echo "<a href='Posts.php?page=".$k."&search=" . $search . "'>";
				if($k==$page) { echo "<strong> ".$k." </strong>"; } else { echo $k;}
				echo "</a>";
			}
			
			if($page != $maxpage) 
			{
				echo "<a href='Posts.php?page=".($page + 1)."&search=" . $search . "'>&gt;</a>";
				echo "<a href='Posts.php?page=".$maxpage."&search=" . $search . "'>&gt;&gt;</a>";
			}
			
			echo "</div></div></main>";
			
		?>
		<script type="text/javascript">
			var awesomplete;
			var input;
			$(document).ready(function()
			{
				input = document.getElementById("tags");
				awesomplete = new Awesomplete(input, { sort: false, tabSelect: true , filter: function(text, input) {
						//console.log(input.match(/[^ ]*$/));
						//return Awesomplete.FILTER_CONTAINS(text.value, input.match(/[^ ]*$/)[0]); 
						return Awesomplete.FILTER_CONTAINS(text.value, input.match(/[^( |\|\||\~|\!)]*$/)[0]);
					},

					item: function(text, input) {
						//console.log(input.match(/[^ ]*$/)[0]);
						//return Awesomplete.ITEM(text, input.match(/[^ ]*$/)[0]);
						return Awesomplete.ITEM(text, input.match(/[^( |\|\||\~|\!)]*$/)[0]);
					},

					replace: function(text) {						
						//var before = this.input.value.match(/^.+ \s*|/)[0];
						//var before = this.input.value.match(/^.+( |\|\|\!|\~)\s*|/)[0]; //matches everything before the last space or || ^.+( |\|\||\!|\~)\s*|^(\~|\!)
						var before = this.input.value.match(/^.+( |\|\||\!|\~)\s*|^(\~|\!)|/)[0];  //matches everything before and including the last space, ~, !, or ||
						console.log(before);
						this.input.value = before + text.value;
				}  } );
			});			
			
			function TagSuggestions(data)
			{
				input.value = input.value.replace(/ $/g, "_");
				data = data.replace(/ $/g, "_");				
				
				input.value = input.value.replace(/__/g, " ");
				data = data.replace(/__/g, " ");
				
				//data = data.replace(/^.+ \s*|/g, "");
				data = data.replace(/^.+( |\|\|)\s*|/g, "");
				data = data.replace(/^(\!|\~)/g, "");
				
				console.log(data);
				
				$.ajax({
					url: 'Tags/TagSuggestionsAjax.php?txt=' + data,
					type: 'get',
					dataType: 'JSON',
					success: function(response){	
						//console.log(response);					
						awesomplete.list = response;
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
