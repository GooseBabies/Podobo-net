<?php
// Start the session
session_start();
$thumbs_source = "thumbs/";
$columncount = 12;
$rowcount = 6;
$itemcount = $columncount * $rowcount;
$TagColors=array("#FFFFFF", "#6495ED", "#FF4500", "#FF4500", "#FF8C00", "#7FFFD4", "#BA55D3", "#228B22", "#8A2BE2", "#FFFFFF", "#FFFFFF", "#FFFFFF", "#FFFFFF", "#FFFFFF", "#DAA520", "#8FBC8F", "#FFFFFF");
$files = [];
$file_page_data = [];
$tags = [];

$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");

if(isset($_GET["search"])) { $search = html_entity_decode($_GET["search"]); } else { $search = ""; }
if(isset($_GET["page"])) { $page = $_GET["page"]; } else { $page = 1; }
if(isset($_SESSION["all_ids"]) and $search == "")
{
	$sql = $db->prepare("SELECT name, overall_rating, video, sound, tag_list FROM files order by ID desc limit :limit offset :offset");
	$sql->bindValue(':limit', $itemcount, SQLITE3_INTEGER);
	$sql->bindValue(':offset', $itemcount * ($page - 1), SQLITE3_INTEGER);
	echo "<!--" . $sql->getSQL() . "-->";
	$result = $sql->execute();
	while ($row = $result->fetchArray()) {
		array_push($file_page_data, $row);
	}

	$_SESSION["search"] = $search;
	$files = $_SESSION["all_ids"];
	unset($_SESSION["filtered_ids"]);
	$filtered = false;
}
else
{
	if($search == "")
	{
		$sql = $db->prepare("SELECT ID FROM files order by ID desc");
		$result = $sql->execute();
		while ($row = $result->fetchArray()) {
			array_push($files, $row);
		}

		$sql = $db->prepare("SELECT name, overall_rating, video, sound, tag_list FROM files order by ID desc limit :limit offset :offset");
		$sql->bindValue(':limit', $itemcount, SQLITE3_INTEGER);
		$sql->bindValue(':offset', $itemcount * ($page - 1), SQLITE3_INTEGER);
		echo "<!--" . $sql->getSQL() . "-->";
		$result = $sql->execute();
		while ($row = $result->fetchArray()) {
			array_push($file_page_data, $row);
		}
		
		$_SESSION["search"] = $search;
		$_SESSION["all_ids"] = $files; //only store session data for full sql call
		unset($_SESSION["filtered_ids"]);
		$filtered = false;
	}
	else
	{	
		$_SESSION["search"] = $search;
		// echo $search;
		$searchtags = array_filter(explode(" ", $search));
		//echo "<!-- " . print_r($searchtags) . " -->";
		$sql1 = "select ID, name, overall_rating, video, sound, tag_list from files where";
		$sql2 = "select name, overall_rating, video, sound, tag_list from files where";
		$sql_preposition = "";
		for($tag_index = 0; $tag_index<count($searchtags); $tag_index++){
			$searchtags[$tag_index] = str_replace("_", " ", $searchtags[$tag_index]);
			if(str_contains($searchtags[$tag_index], "||")){
				$ortags = array_filter(explode("||", $searchtags[$tag_index]));
				if($tag_index < 1){
					$sql_preposition .= " (";
				}
				else{
					$sql_preposition .= " and (";
				}

				for($or_index = 0; $or_index<count($ortags); $or_index++){
					if(str_starts_with($ortags[$or_index], "!")){	
						$sqla = $db->prepare("select tagid from tags where tag_name=:tag COLLATE NOCASE");
						$sqla->bindValue(':tag', substr($ortags[$or_index], 1), SQLITE3_TEXT);
						$searchtagid = $sqla->execute()->fetchArray()[0] ?? -1;	
											
						if(!empty($searchtagid)){
							if($or_index < 1){
								$sql_preposition .= "tag_list not like '%;" . $searchtagid . ";%'";
							}
							else{
								$sql_preposition .= " or tag_list not like '%;" . $searchtagid . ";%'";
							}
						}
						else{
							if($or_index < 1){
								$sql_preposition .= "tag_list not like '%;%" . ";%'";
							}
							else{
								$sql_preposition .= " or tag_list not like '%;%" . ";%'";
							}
						}
					}
					else if(str_starts_with($ortags[$or_index], '$ext:')){
						if($or_index < 1){
								$sql_preposition .= "ext ='" . substr($ortags[$or_index],5) . "'";
							}
							else{
								$sql_preposition .= " or ext ='" . substr($ortags[$or_index],5) . "'";
							}
					}
					else if(str_starts_with($ortags[$or_index], '$dur>')){
						if($or_index < 1){
								$sql_preposition .= "duration > " . substr($ortags[$or_index],5) . "";
							}
							else{
								$sql_preposition .= " or duration > " . substr($ortags[$or_index],5) . "";
							}
					}						
					else if(str_starts_with($ortags[$or_index], '$dur<')){
						if($or_index < 1){
								$sql_preposition .= "duration < " . substr($ortags[$or_index],5) . "";
							}
							else{
								$sql_preposition .= " or duration < " . substr($ortags[$or_index],5) . "";
							}
					}
					else if(str_starts_with($ortags[$or_index], '$name:')){
						if($or_index < 1){
								$sql_preposition .= "name like '%" . str_replace('\'', '_', substr($ortags[$or_index],6)) . "%'";
							}
							else{
								$sql_preposition .= " or name like '%" . str_replace('\'', '_', substr($ortags[$or_index],6)) . "%'";
							}
					}
					else if(str_starts_with($ortags[$or_index], '$!name:')){
						if($or_index < 1){
								$sql_preposition .= "name not like '%" . str_replace('\'', '_', substr($ortags[$or_index],7)) . "%'";
							}
							else{
								$sql_preposition .= " or name not like '%" . str_replace('\'', '_', substr($ortags[$or_index],7)) . "%'";
							}
					}
					else if(str_starts_with($ortags[$or_index], '$rating>')){
						if($or_index < 1){
								$sql_preposition .= "overall_rating > " . substr($ortags[$or_index],8) . "";
							}
							else{
								$sql_preposition .= " or overall_rating > " . substr($ortags[$or_index],8) . "";
							}
					}						
					else if(str_starts_with($ortags[$or_index], '$rating<')){
						if($or_index < 1){
								$sql_preposition .= "overall_rating < " . substr($ortags[$or_index],8) . "";
							}
							else{
								$sql_preposition .= " or overall_rating < " . substr($ortags[$or_index],8) . "";
							}
					}
					else if(str_starts_with($ortags[$or_index], '$height>')){
						if($or_index < 1){
								$sql_preposition .= "height > " . substr($ortags[$or_index],8) . "";
							}
							else{
								$sql_preposition .= " or height > " . substr($ortags[$or_index],8) . "";
							}
					}						
					else if(str_starts_with($ortags[$or_index], '$height<')){
						if($or_index < 1){
								$sql_preposition .= "height < " . substr($ortags[$or_index],8) . "";
							}
							else{
								$sql_preposition .= " or height < " . substr($ortags[$or_index],8) . "";
							}
					}
					else if(str_starts_with($ortags[$or_index], '$sound')){
						if($or_index < 1){
								$sql_preposition .= "sound = 1";
							}
							else{
								$sql_preposition .= " or sound = 1";
							}
					}
					else if(str_starts_with($ortags[$or_index], '$video')){
						if($or_index < 1){
									$sql_preposition .= "video = 1";
							}
							else{
									$sql_preposition .= " or video = 1";
							}
					}
					else if(str_starts_with($ortags[$or_index], '$rev')){
						if($or_index < 1){
									$sql_preposition .= "review = 1";
							}
							else{
									$sql_preposition .= " or review = 1";
							}
					}
					else if(str_starts_with($ortags[$or_index], '$tags=0')){
						if($or_index < 1){
									$sql_preposition .= "tag_list is null";
							}
							else{
									$sql_preposition .= " or tag_list is null";
							}
					}
					else if(str_starts_with($ortags[$or_index], '$tags=')){
						if($or_index < 1){
									$sql_preposition .= "(Length(tag_list) - Length(REPLACE(tag_list, ';', '')) - 1) = " . substr($ortags[$or_index],6);
							}
							else{
									$sql_preposition .= " or (Length(tag_list) - Length(REPLACE(tag_list, ';', '')) - 1) = " . substr($ortags[$or_index],6);
							}
					}
					else if(str_starts_with($ortags[$or_index], '$tags>')){
						if($or_index < 1){
									$sql_preposition .= "(Length(tag_list) - Length(REPLACE(tag_list, ';', '')) - 1) > " . substr($ortags[$or_index],6);
							}
							else{
									$sql_preposition .= " or (Length(tag_list) - Length(REPLACE(tag_list, ';', '')) - 1) > " . substr($ortags[$or_index],6);
							}
					}
					else if(str_starts_with($ortags[$or_index], '$tags<')){
						if($or_index < 1){
									$sql_preposition .= "(Length(tag_list) - Length(REPLACE(tag_list, ';', '')) - 1) < " . substr($ortags[$or_index],6);
							}
							else{
									$sql_preposition .= " or (Length(tag_list) - Length(REPLACE(tag_list, ';', '')) - 1) < " . substr($ortags[$or_index],6);
							}
					}				
					else{
						if(str_starts_with($ortags[$or_index], '~')){
							$sqla = $db->prepare("select tagid from tags where tag_name=:tag COLLATE NOCASE");
							$sqla->bindValue(':tag', substr($ortags[$or_index], 1), SQLITE3_TEXT);
							$searchtagid = $sqla->execute()->fetchArray()[0] ?? -1;	
							
							if($or_index < 1){
										$sql_preposition .= "tag_list like '%;" . $searchtagid . ";%' or name like '%" . str_replace('\'', '_', substr($ortags[$or_index], 1)) . "%'";
								}
								else{
										$sql_preposition .= " or tag_list like '%;" . $searchtagid . ";%' or name like '%" . str_replace('\'', '_', substr($ortags[$or_index], 1)) . "%'";
								}
						}
						else{
							$sqla = $db->prepare("select tagid from tags where tag_name=:tag COLLATE NOCASE");
							$sqla->bindValue(':tag', $ortags[$or_index], SQLITE3_TEXT);
							$searchtagid = $sqla->execute()->fetchArray()[0] ?? -1;	
							
							if(!empty($searchtagid)){
								if($or_index < 1){
									$sql_preposition .= "tag_list like '%;" . $searchtagid . ";%'";
								}
								else{
									$sql_preposition .= " or tag_list like '%;" . $searchtagid . ";%'";
								}								
							}
							else{
								if($or_index < 1){
									$sql_preposition .= "name like '%" . str_replace('\'', '_', $ortags[$or_index]) . "%'";
								}
								else{
									$sql_preposition .= " or name like '%" . str_replace('\'', '_', $ortags[$or_index]) . "%'";
								}
							}
						}
					}
				}

				$sql_preposition .= ")";
			}
			else if(str_starts_with($searchtags[$tag_index], "!")){		
				$sqla = $db->prepare("select tagid from tags where tag_name=:tag COLLATE NOCASE");
				$sqla->bindValue(':tag', substr($searchtags[$tag_index], 1), SQLITE3_TEXT);
				$searchtagid = $sqla->execute()->fetchArray()[0] ?? -1;	

				if(!empty($searchtagid)){
					if($tag_index < 1){
						$sql_preposition .= " tag_list not like '%;" . $searchtagid . ";%'";
					}
					else{
						$sql_preposition .= " and tag_list not like '%;" . $searchtagid . ";%'";
					}
				}
				else{
					if($tag_index < 1){
						$sql_preposition .= " tag_list not like '%;%" . ";%'";
					}
					else{
						$sql_preposition .= " and tag_list not like '%;%" . ";%'";
					}
				}
			}
			else if(str_starts_with($searchtags[$tag_index], '$ext:')){
				if($tag_index < 1){
							$sql_preposition .= " ext ='" . substr($searchtags[$tag_index],5) . "'";
					}
					else{
							$sql_preposition .= " and ext ='" . substr($searchtags[$tag_index],5) . "'";
					}
			}
			else if(str_starts_with($searchtags[$tag_index], '$dur>')){
				if($tag_index < 1){
							$sql_preposition .= " duration > " . substr($searchtags[$tag_index],5) . "";
					}
					else{
							$sql_preposition .= " and duration > " . substr($searchtags[$tag_index],5) . "";
					}
			}						
			else if(str_starts_with($searchtags[$tag_index], '$dur<')){
				if($tag_index < 1){
							$sql_preposition .= " duration < " . substr($searchtags[$tag_index],5) . "";
					}
					else{
							$sql_preposition .= " and duration < " . substr($searchtags[$tag_index],5) . "";
					}
			}
			else if(str_starts_with($searchtags[$tag_index], '$name:')){
				if($tag_index < 1){
							$sql_preposition .= " name like '%" . str_replace('\'', '_', substr($searchtags[$tag_index],6)) . "%'";
					}
					else{
							$sql_preposition .= " and name like '%" . str_replace('\'', '_', substr($searchtags[$tag_index],6)) . "%'";
					}
			}
			else if(str_starts_with($searchtags[$tag_index], '$!name:')){
				if($tag_index < 1){
							$sql_preposition .= " name not like '%" . str_replace('\'', '_', substr($searchtags[$tag_index],7)) . "%'";
					}
					else{
							$sql_preposition .= " and name not like '%" . str_replace('\'', '_', substr($searchtags[$tag_index],7)) . "%'";
					}
			}
			else if(str_starts_with($searchtags[$tag_index], '$rating>')){
				if($tag_index < 1){
							$sql_preposition .= " overall_rating > " . substr($searchtags[$tag_index],8) . "";
					}
					else{
							$sql_preposition .= " and overall_rating > " . substr($searchtags[$tag_index],8) . "";
					}
			}						
			else if(str_starts_with($searchtags[$tag_index], '$rating<')){
				if($tag_index < 1){
							$sql_preposition .= " overall_rating < " . substr($searchtags[$tag_index],8) . "";
					}
					else{
							$sql_preposition .= " and overall_rating < " . substr($searchtags[$tag_index],8) . "";
					}
			}
			else if(str_starts_with($searchtags[$tag_index], '$height>')){
				if($tag_index < 1){
							$sql_preposition .= " height > " . substr($searchtags[$tag_index],8) . "";
					}
					else{
							$sql_preposition .= " and height > " . substr($searchtags[$tag_index],8) . "";
					}
			}						
			else if(str_starts_with($searchtags[$tag_index], '$height<')){
				if($tag_index < 1){
							$sql_preposition .= " height < " . substr($searchtags[$tag_index],8) . "";
					}
					else{
							$sql_preposition .= " and height < " . substr($searchtags[$tag_index],8) . "";
					}
			}
			else if(str_starts_with($searchtags[$tag_index], '$sound')){
				if($tag_index < 1){
							$sql_preposition .= " sound = 1";
					}
					else{
							$sql_preposition .= " and sound = 1";
					}
			}
			else if(str_starts_with($searchtags[$tag_index], '$video')){
				if($tag_index < 1){
							$sql_preposition .= " video = 1";
					}
					else{
							$sql_preposition .= " and video = 1";
					}
			}
			else if(str_starts_with($searchtags[$tag_index], '$rev')){
				if($tag_index < 1){
							$sql_preposition .= " review = 1";
					}
					else{
							$sql_preposition .= " and review = 1";
					}
			}
			else if(str_starts_with($searchtags[$tag_index], '$tags=0')){
				if($tag_index < 1){
							$sql_preposition .= "tag_list is null";
					}
					else{
							$sql_preposition .= " and tag_list is null";
					}
			}
			else if(str_starts_with($searchtags[$tag_index], '$tags=')){
				if($tag_index < 1){
							$sql_preposition .= "(Length(tag_list) - Length(REPLACE(tag_list, ';', '')) - 1) = " . substr($searchtags[$tag_index],6);
					}
					else{
							$sql_preposition .= " and (Length(tag_list) - Length(REPLACE(tag_list, ';', '')) - 1) = " . substr($searchtags[$tag_index],6);
					}
			}
			else if(str_starts_with($searchtags[$tag_index], '$tags>')){
				if($tag_index < 1){
							$sql_preposition .= "(Length(tag_list) - Length(REPLACE(tag_list, ';', '')) - 1) > " . substr($searchtags[$tag_index],6);
					}
					else{
							$sql_preposition .= " and (Length(tag_list) - Length(REPLACE(tag_list, ';', '')) - 1) > " . substr($searchtags[$tag_index],6);
					}
			}
			else if(str_starts_with($searchtags[$tag_index], '$tags<')){
				if($tag_index < 1){
							$sql_preposition .= "(Length(tag_list) - Length(REPLACE(tag_list, ';', '')) - 1) < " . substr($searchtags[$tag_index],6);
					}
					else{
							$sql_preposition .= " and (Length(tag_list) - Length(REPLACE(tag_list, ';', '')) - 1) < " . substr($searchtags[$tag_index],6);
					}
			}
			else{
				if(str_starts_with($searchtags[$tag_index], '~')){
					$sqla = $db->prepare("select tagid from tags where tag_name=:tag COLLATE NOCASE");
					$sqla->bindValue(':tag', substr($searchtags[$tag_index], 1), SQLITE3_TEXT);
					$searchtagid = $sqla->execute()->fetchArray()[0] ?? -1;

					if($tag_index < 1){
								$sql_preposition .= "(tag_list like '%;" . $searchtagid . ";%' or name like '%" . str_replace('\'', '_', substr($searchtags[$tag_index], 1)) . "%')";
						}
						else{
								$sql_preposition .= " and (tag_list like '%;" . $searchtagid . ";%' or name like '%" . str_replace('\'', '_', substr($searchtags[$tag_index], 1)) . "%')";
						}
				}
				else{
					$sqla = $db->prepare("select tagid from tags where tag_name=:tag COLLATE NOCASE");
					$sqla->bindValue(':tag', $searchtags[$tag_index], SQLITE3_TEXT);
					$searchtagid = $sqla->execute()->fetchArray()[0] ?? -1;

					if(!empty($searchtagid)){
						if($tag_index < 1){
							$sql_preposition .= " tag_list like '%;" . $searchtagid . ";%'";
						}
						else{
							$sql_preposition .= " and tag_list like '%;" . $searchtagid . ";%'";
						}								
					}
					else{
						if($tag_index < 1){
							$sql_preposition .= " name like '%" . str_replace('\'', '_', $searchtags[$tag_index]) . "%'";
						}
						else{
							$sql_preposition .= " and name like '%" . str_replace('\'', '_', $searchtags[$tag_index]) . "%'";
						}
					}
				}
			}						
		}	
		$sql1 .= $sql_preposition . " order by ID desc";	
		$sql2 .= $sql_preposition . " order by ID desc limit " . strval($itemcount) . " offset " . strval($itemcount * ($page - 1));			
		
		echo "<!--" . $sql1 . "-->";
		echo "<!--" . $sql2 . "-->";
		
		$result = $db->query($sql1);
		while ($row = $result->fetchArray()) {
			array_push($files, $row);
		}

		$result = $db->query($sql2);
		while ($row = $result->fetchArray()) {
			array_push($file_page_data, $row);
		}
		
		$_SESSION["filtered_ids"] = $files;
		$filtered = true;
	}
}

$rownum = count($files);

$r = rand(0, $rownum);

$PageTitle = "Podobo - Posts [" . $rownum . "]";
$list_view = true;
	
function customPageHeader(){?>
	<script>		
		function ListView() {
			var elements = document.getElementsByClassName("posts");
			for (i = 0; i < elements.length; i++) {
				elements[i].style.width = "100%";
				elements[i].style.height = "151px";
				elements[i].style.margin = "20px 0px 14px 0px";
				elements[i].classList.add("w3-center");
			}

			var nametext = document.getElementsByClassName("nametext");
			while(nametext.length){
				nametext[0].className = "nametext-list";
			}

			var vidandsoundmarker = document.getElementsByClassName(" vidandsoundmarker");
			while( vidandsoundmarker.length){
				vidandsoundmarker[0].className = "vidandsoundmarker-list";
			}

			var vidmarker = document.getElementsByClassName(" vidmarker");
			while( vidmarker.length){
				vidmarker[0].className = "vidmarker-list";
			}

			var nowrap = document.getElementsByClassName("nowrap");
			while(nowrap.length) {
				nowrap[0].classList.remove("nowrap");
			}

			var postid = document.getElementsByClassName("postid");
			while(postid.length) {
				postid[0].className = "postid-list";
			}
		}

		$(document).ready(function()
		{				
			var HeaderButton = document.getElementById("posts");
			HeaderButton.className = "w3-bar-item w3-button w3-theme-l1";
		});
	</script>
	<style type="text/css">
		input[type=text] {
		width: 230px;
		}
	</style>
<?php }

include_once('header.php');
?>
					
			<main class="row">
			<div class="col-1 w3-theme main-left">
                        <div>
			<h4 class="w3-bar-item"><b>Tags</b></h4>
			
			<?php
			$sql2 = "select tag_name, category, tag_count from tags order by tag_count desc limit 40";
			$result2 = $db->query($sql2);
			while ($row = $result2->fetchArray()) {
				array_push($tags, $row);
			}
			
			$db = null;
			echo "<form class='tagadd' action='Posts.php' method='GET'>";
				echo "<input type='text' id='tag-input' oninput='TagSuggestions(this.value)' name='search' value='" . htmlspecialchars($search, ENT_QUOTES) . "'  data-multiple/>";
				echo "<input type='submit' hidden />";
			echo"</form>";
			
			echo "<hr />";
			echo "</div>";
			echo "<div>";
                        
			echo "<ul class='search-tag-list'>";
			foreach($tags as $tag)
			{
				echo "<li>";
				echo "<a href ='Posts.php?search=!" . $tag[0] = str_replace(" ", "_", $tag[0]) ."&page=" . $page . "'>! </a><a style='color:" . $TagColors[$tag[1]] . "' href ='Posts.php?search=" . $tag[0] = str_replace(" ", "_", $tag[0]) ."&page=" . $page . "'>" . $tag[0] . " - [" . $tag[2] . "]</a>";
				echo "</li>";
			}
			echo "</ul>";
			echo "</div>";
			echo "</div>";
			
			echo "<div class='col-9'><div class='posts-wrapper'>";			
			
			$maxpage = ceil($rownum/($columncount*$rowcount));
			$pagestart = ($page - 1) * ($columncount * $rowcount);
			
			//for($i = $pagestart; $i<$pagestart+($rowcount*$columncount) and $i<$rownum; $i++)
			for($i = 0; $i<count($file_page_data); $i++)
			{
				if($i % $columncount == 0){
					echo "<div class='row-posts'>";
				}
				if(strlen($file_page_data[$i][4]) > 1) {$thumbclass = "class='thumbs tagged'";} else  {$thumbclass = "class='thumbs untagged'";} 
				echo "<div class='posts'>";
				echo "<a href ='Post.php?id=" . $files[$pagestart + $i][0] . "'>";		
				
				echo "<img " . $thumbclass . " src ='" . $thumbs_source . pathinfo(rawurlencode($file_page_data[$i][0]), PATHINFO_FILENAME) . ".jpg" . "' onerror='this.src =\"" . $thumbs_source . "MissingThumb.jpg\"" . "' alt='N/A'/></a>"; //<a href='Post.php?id=" . $files[$i][0] . "'>" . $files[$i][0] . "</a>
				//echo "<div id='nametext' class='nametext'><p id='nowrap' class='nowrap'>" . $files[$i][1] . "</p></div>";
				if($file_page_data[$i][2] == 1 && $file_page_data[$i][3] == 1){
					echo "<div class='vidandsoundmarker'><i class='fas fa-play fa-2x'></i><i class='fas fa-volume-off fa-2x'></i></div>"; //<p>\u{25B6}\u{1F56A}</p>
				}
				else if($file_page_data[$i][2] == 1 && $file_page_data[$i][3] == 0){
					echo "<div class='vidmarker'><i class='fas fa-play fa-2x'></i></div>"; //\u{25B6}
				}
				else if($file_page_data[$i][2] == 0 && $file_page_data[$i][3] == 1){
					echo "<p class='vidmarker'>\u{1F56A}</p>";
				}
				echo "</div>";

				if($i % $columncount == ($columncount - 1) || $i == count($files) - 1){
					echo "</div>";
				}
			}
			
			echo "</div>";
			
			echo "<div class='container_footer'>";
			
			
			$pagelimit = 5;
			if($page < $pagelimit + 1) { $start_page = 1; } else { $start_page = $page - ($maxpage < $pagelimit ? $maxpage + 1 : $pagelimit);}
			if($page > $maxpage - $pagelimit) { $end_page = $maxpage; } else { $end_page = $page + ($maxpage < $pagelimit ? $maxpage : $pagelimit); }
			
			if($page != 1) 
			{
				echo "<a href='Posts.php?page=1&search=" . $search . "'><i class='fas fa-angles-left fa-2x'></i></a>";
				echo "<a href='Posts.php?page=".($page - 1)."&search=" . $search . "'><i class='fas fa-angle-left fa-2x'></i></a>";	
			}
			
			for($k = $start_page; $k <= $end_page; $k++)
			{
				if($k == $page){
					echo "<a class='current-page' href='Posts.php?page=".$k."&search=" . $search . "'>";
					echo $k;
					echo "</a>";
				}
				else{
					echo "<a class='other-page' href='Posts.php?page=".$k."&search=" . $search . "'>";
					echo $k;
					echo "</a>";
				}
				
			}
			
			if($page != $maxpage) 
			{
				echo "<a href='Posts.php?page=".($page + 1)."&search=" . $search . "'><i class='fas fa-angle-right fa-2x'></i></a>";
				echo "<a href='Posts.php?page=".$maxpage."&search=" . $search . "'><i class='fas fa-angles-right fa-2x'></i></a>";
			}
			
			echo "</div>";
			echo "</div>";
			echo "</main>";
			
		?>
		<script type="text/javascript">
			var awesomplete;
			var input;
			$(document).ready(function()
			{
				input = document.getElementById("tag-input");
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
