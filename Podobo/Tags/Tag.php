<?php
	session_start();
	//$TagOrder=array(14,4,0,1,2,8,6,7,9,10,12,13,11,16,5,15);
	$TagCategoryTitle=array("General", "IP/Series", "Individual", "Rating", "Artist", "Studio/Network", "Sex", "Afilliation/Group", "Race/Species/Ethnicity", "Body Part", "Clothing/Accessory", "Position", "Setting", "Action", "Meta", "Title", "Release Date");
    $parentids = [];
    $parents = [];
    $children = [];
    $childids = [];
    $aliases = [];
    $aliasids = [];
	$preferred = [];
    $preferredids = [];
	$tagmap = [];
	$tagids = [];

	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");	

    if(isset($_GET["tagid"])) { $tagid = $_GET["tagid"]; } else { $tagid = 1; };

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

	if(isset($_SESSION["filtered_tag_ids"]) && count($_SESSION["filtered_tag_ids"]) > 0){
		$tagids = $_SESSION["filtered_tag_ids"];
		$filtered = true;
	}
	else{
		if(isset($_SESSION["tag_ids"])){
			$tagids = $_SESSION["tag_ids"];				
			$filtered = false;
		}
		else{		
			$result = $db->query("SELECT tagid FROM tags order by tag_name COLLATE NOCASE");				
			while ($row = $result->fetchArray()) {
				array_push($tagids, $row);
			}			
			$_SESSION["tag_ids"] = $tagids;
			$filtered = false;
		}
		$_SESSION["tagsearch"] = "";
	}

	$index = array_search($tagid, array_column($tagids, 0));
	$tagcount = count($tagids) - 1;
	
	$sql = $db->prepare("select tag_name, category, tag_count from tags where tagid = :tagid");
    $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
	$tag = $sql->execute()->fetchArray();

    //Children

    $sql = $db->prepare("select child from parents where parent = :tagid");
    $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
	$result = $sql->execute();
	while ($row = $result->fetchArray()) {
        array_push($childids, $row[0]);
    }

    foreach($childids as $childid){
        $sql = $db->prepare("select tag_name from tags where tagid = :tagid");
        $sql->bindValue(':tagid', $childid, SQLITE3_INTEGER);
	    $childtag = $sql->execute()->fetchArray()[0];
        array_push($children, array($childid, $childtag));
    }

    //Parents

    $sql = $db->prepare("select parent from parents where child = :tagid");
    $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
	$result = $sql->execute();
	while ($row = $result->fetchArray()) {
        array_push($parentids, $row[0]);
    }

    foreach($parentids as $parentid){
        $sql = $db->prepare("select tag_name from tags where tagid = :tagid");
        $sql->bindValue(':tagid', $parentid, SQLITE3_INTEGER);
	    $parenttag = $sql->execute()->fetchArray()[0];
        array_push($parents, array($parentid, $parenttag));
    }

    //Aliases

    $sql = $db->prepare("select alias from siblings where preferred = :tagid");
    $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
	$result = $sql->execute();
	while ($row = $result->fetchArray()) {
        array_push($aliasids, $row[0]);
    }

    foreach($aliasids as $aliasid){
        $sql = $db->prepare("select tag_name from tags where tagid = :tagid");
        $sql->bindValue(':tagid', $aliasid, SQLITE3_INTEGER);
	    $aliastag = $sql->execute()->fetchArray()[0];
        array_push($aliases, array($aliasid, $aliastag));
    }

	//Aliases

    $sql = $db->prepare("select preferred from siblings where alias = :tagid");
    $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
	$result = $sql->execute();
	while ($row = $result->fetchArray()) {
        array_push($preferredids, $row[0]);
    }

    foreach($preferredids as $preferredid){
        $sql = $db->prepare("select tag_name from tags where tagid = :tagid");
        $sql->bindValue(':tagid', $preferredid, SQLITE3_INTEGER);
	    $preferredtag = $sql->execute()->fetchArray()[0];
        array_push($preferred, array($preferredid, $preferredtag));
    }

	//tag Map

    $sql = $db->prepare("select booru_tag, booru_source from tag_map where tagid = :tagid");
    $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
	$result = $sql->execute();
	while ($row = $result->fetchArray()) {
        array_push($tagmap, $row);
    }

	$PageTitle = "Piaz-Online - " . $tag[0] . " (" . $tagid . ")";
	$InTags = true;

	function customPageHeader(){?>
		<script>
			function showEdit()
			{
				var edit_div = document.getElementById("edit-tag");
				var parent_div = document.getElementById("add-parent");
				var child_div = document.getElementById("add-child");
				var alias_div = document.getElementById("add-alias");
				if (edit_div.style.display === "inline") 
				{
					edit_div.style.display = "none";					
				} 
				else 
				{
					edit_div.style.display = "inline";
					if (parent_div.style.display === "inline") 
					{
						parent_div.style.display = "none";					
					}
					if (child_div.style.display === "inline") 
					{
						child_div.style.display = "none";					
					}
					if (alias_div.style.display === "inline") 
					{
						alias_div.style.display = "none";					
					}
					edit_input.focus();
				}
			}

			function showParent(){
				var edit_div = document.getElementById("edit-tag");
				var parent_div = document.getElementById("add-parent");
				var child_div = document.getElementById("add-child");
				var alias_div = document.getElementById("add-alias");
				if (parent_div.style.display === "inline") 
				{
					parent_div.style.display = "none";					
				} 
				else 
				{
					parent_div.style.display = "inline";
					if (edit_div.style.display === "inline") 
					{
						edit_div.style.display = "none";					
					}
					if (child_div.style.display === "inline") 
					{
						child_div.style.display = "none";					
					}
					if (alias_div.style.display === "inline") 
					{
						alias_div.style.display = "none";					
					}
					parent_input.focus();
				}
			}

			function showChild(){
				var edit_div = document.getElementById("edit-tag");
				var parent_div = document.getElementById("add-parent");
				var child_div = document.getElementById("add-child");
				var alias_div = document.getElementById("add-alias");
				if (child_div.style.display === "inline") 
				{
					child_div.style.display = "none";					
				} 
				else 
				{
					child_div.style.display = "inline";
					if (edit_div.style.display === "inline") 
					{
						edit_div.style.display = "none";					
					}
					if (parent_div.style.display === "inline") 
					{
						parent_div.style.display = "none";					
					}
					if (alias_div.style.display === "inline") 
					{
						alias_div.style.display = "none";					
					}
					child_input.focus();
				}
			}

			function showAlias(){
				var edit_div = document.getElementById("edit-tag");
				var parent_div = document.getElementById("add-parent");
				var child_div = document.getElementById("add-child");
				var alias_div = document.getElementById("add-alias");
				if (alias_div.style.display === "inline") 
				{
					alias_div.style.display = "none";					
				} 
				else 
				{
					alias_div.style.display = "inline";
					child_div.style.display = "inline";
					if (edit_div.style.display === "inline") 
					{
						edit_div.style.display = "none";					
					}
					if (parent_div.style.display === "inline") 
					{
						parent_div.style.display = "none";					
					}
					if (child_div.style.display === "inline") 
					{
						child_div.style.display = "none";					
					}
					alias_input.focus();
				}
			}
			$(document).ready(function()
			{				
				var HeaderButton = document.getElementById("tags");
				HeaderButton.className = "w3-bar-item w3-button w3-theme-l1";
			});
		</script>

		<style type="text/css" media="screen">
			div {
                display: block;
            }
		</style>
	<?php }

	include_once('../header.php');
	
	$db = null;

			echo "<div class='container_footer'>";

			$taglimit = 5;
			
			if($index < $taglimit + 1) { $start_page = 0; } else { $start_page = $index - ($tagcount < $taglimit ? $tagcount + 1 : $taglimit);};
			if($index > $tagcount - $taglimit) { $end_page = $tagcount; } else { $end_page = $index + ($tagcount < $taglimit ? $tagcount : $taglimit); };
			
			if($index != 0) 
			{
				echo "<a href='Tag.php?tagid=" . $tagids[0][0] . "'>";//<i class='fas fa-angles-left fa-2x'></i></a>";  data-prefix='fas' data-icon='angles-left' role='img' xmlns='http://www.w3.org/2000/svg' data-fa-i2svg=''aria-hidden='true' 
				echo "<svg class='svg-inline--fa fa-angles-left fa-2x' focusable='false' height='20' width='20' viewBox='0 0 448 512'><path fill='currentColor' d='M77.25 256l137.4-137.4c12.5-12.5 12.5-32.75 0-45.25s-32.75-12.5-45.25 0l-160 160c-12.5 12.5-12.5 32.75 0 45.25l160 160C175.6 444.9 183.8 448 192 448s16.38-3.125 22.62-9.375c12.5-12.5 12.5-32.75 0-45.25L77.25 256zM269.3 256l137.4-137.4c12.5-12.5 12.5-32.75 0-45.25s-32.75-12.5-45.25 0l-160 160c-12.5 12.5-12.5 32.75 0 45.25l160 160C367.6 444.9 375.8 448 384 448s16.38-3.125 22.62-9.375c12.5-12.5 12.5-32.75 0-45.25L269.3 256z'></path></svg></a>";
				echo "<a href='Tag.php?tagid=" . $tagids[$index - 1][0] . "'>";//<i class='fas fa-angle-left fa-2x'></i></a>";	
				echo "<svg class='svg-inline--fa fa-angle-left fa-2x' focusable='false' height='20' width='20' viewBox='0 0 448 512'><path fill='currentColor' d='M192 448c-8.188 0-16.38-3.125-22.62-9.375l-160-160c-12.5-12.5-12.5-32.75 0-45.25l160-160c12.5-12.5 32.75-12.5 45.25 0s12.5 32.75 0 45.25L77.25 256l137.4 137.4c12.5 12.5 12.5 32.75 0 45.25C208.4 444.9 200.2 448 192 448z'></path></svg></a>";
			};
			
			for($k = $start_page; $k <= $end_page; $k++)
			{
				if($k == $index){
					echo "<a class='current-page' href='Tag.php?tgaid=".$tagids[$k][0]."'>";
					echo ($tagids[$k][0]);
					echo "</a>";
				}
				else{
					echo "<a class='other-page' href='Tag.php?tagid=".$tagids[$k][0]."'>";
					echo ($tagids[$k][0]);
					echo "</a>";
				}
			}
			
			if($index != $tagcount) 
			{
				echo "<a href='Tag.php?tagid=".$tagids[$index + 1][0]."'>";//<i class='fas fa-angle-right fa-2x'></i></a>";
				echo "<svg class='svg-inline--fa fa-angle-right fa-2x' focusable='false' height='20' width='20' viewBox='0 0 448 512'><path fill='currentColor' d='M64 448c-8.188 0-16.38-3.125-22.62-9.375c-12.5-12.5-12.5-32.75 0-45.25L178.8 256L41.38 118.6c-12.5-12.5-12.5-32.75 0-45.25s32.75-12.5 45.25 0l160 160c12.5 12.5 12.5 32.75 0 45.25l-160 160C80.38 444.9 72.19 448 64 448z'></path></svg></a>";
				echo "<a href='Tag.php?tagid=".$tagids[$tagcount][0]."'>";//<i class='fas fa-angles-right fa-2x'></i></a>";
				echo "<svg class='svg-inline--fa fa-angle-right fa-2x' focusable='false' height='20' width='20' viewBox='0 0 448 512'><path fill='currentColor' d='M246.6 233.4l-160-160c-12.5-12.5-32.75-12.5-45.25 0s-12.5 32.75 0 45.25L178.8 256l-137.4 137.4c-12.5 12.5-12.5 32.75 0 45.25C47.63 444.9 55.81 448 64 448s16.38-3.125 22.62-9.375l160-160C259.1 266.1 259.1 245.9 246.6 233.4zM438.6 233.4l-160-160c-12.5-12.5-32.75-12.5-45.25 0s-12.5 32.75 0 45.25L370.8 256l-137.4 137.4c-12.5 12.5-12.5 32.75 0 45.25C239.6 444.9 247.8 448 256 448s16.38-3.125 22.62-9.375l160-160C451.1 266.1 451.1 245.9 438.6 233.4z'></path></svg></a>";
			};

			echo "</div>";

			echo "<hr />";
			
			echo "<div class='w3-center'>";
                echo "<p>Tag ID - " . $tagid . "</p>";
                echo "<p>Tag - <a href ='../Posts.php?page=1&search=" . rawurlencode(str_replace(" ", "_", $tag[0])) . "'>" . $tag[0] . " (" . $tag[2] . ")</a></p>";
                echo "<p>Category - <a href ='TagList.php?page=1&cat=" . $tag[1] . "'>" . $TagCategoryTitle[$tag[1]] . "</a></p>";
			echo "</div>";

			echo "<hr />";

			echo "<div class='w3-center'>";
                // echo "<a href='EditTag.php?tagid=" . $tagid . "'>Edit Tag</a>";
				echo "<div><a onclick='showEdit()'>Edit Tag</a></div>";

				echo "<div id='edit-tag'>";
					echo "<div class='w3-center'>";
					echo "<input type='text' id='edit-input' oninput='TagSuggestions(this.value, 0)' />";
					
					echo "<select name='edit-categories' id='edit-category'>";
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
					echo "</select>";

					echo "<div class='w3-center'>";
						echo "<input class='w3-center' id='submit-edit' type='button' value='Submit' onclick='SubmitEdit()'/>";	
					echo "</div>";

					
				echo "</div>";
			echo "</div>";

			echo "<p>-</p>";

			//echo "<a href ='AddParent.php?tagid=" . $tagid . "'>Add Parent</a>";
			echo "<div><a onclick='showParent()'>Add Parent</a></div>";

			echo "<div id='add-parent'>";
				echo "<input type='text' id='parent-input' oninput='TagSuggestions(this.value, 1)' />";

				echo "<select name='parent-categories' id='parent-category'>";
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
				echo "</select>";
			
				echo "<div class='w3-center'>";
					echo "<input class='w3-center' id='submit-parent' type='button' value='Submit' onclick='SubmitParent()'/>";	
				echo "</div>";
			echo "</div>";

			echo "<p>-</p>";

			//echo "<div><a href ='AddChild.php?tagid=" . $tagid . "'>Add Child</a></div>";
			echo "<div><a onclick='showChild()'>Add Child</a></div>";

			echo "<div id='add-child'>";

				echo "<input type='text' id='child-input' oninput='TagSuggestions(this.value, 2)' />";

				echo "<div class='w3-center'>";
					echo "<input class='w3-center' id='submit-child' type='button' value='Submit' onclick='SubmitChild()'/>";	
				echo "</div>";
			echo "</div>";

			echo "<p>-</p>";

			//echo "<div><a href ='AddAlias.php?tagid=" . $tagid . "'>Add Alias</a></div>";
			echo "<div><a onclick='showAlias()'>Add Alias</a></div>";


			echo "<div id='add-alias'>";

				echo "<input type='text' id='alias-input' oninput='TagSuggestions(this.value, 3)' onpaste='textPaste()' />";

				echo "<div class='w3-center'>";
					echo "<input class='w3-center' id='submit-alias' type='button' value='Submit' onclick='SubmitAlias()'/>";	
				echo "</div>";

			echo "</div>";
			
			//parent tag
            if(!empty($parents)){
			    echo "<hr />";

                echo "<div id='parentdiv' class='w3-center'>\r\n";
                echo "<p><strong>Parents</strong> (" . $tag[0] . " Implicates these tags)</p><p>-</p>";

                foreach($parents as $parent){
                    echo "<div id='" . $parent[0] . "'>";
                    echo "<label>ID: <a href='Tag.php?tagid=" . $parent[0] . "'> " . $parent[0] . "</a> - Tag: <a href='../Posts.php?page=1&search=" . str_replace(" ", "_", $parent[1]) . "'>" . $parent[1] . "</a></label>";
                    echo "<input type='Button' value='x' onclick='RemoveParent(" . $tagid . ", " . $parent[0] . ")' />";
                    echo "</div>";
                }
				
			    echo "</div>\r\n";
            }
			
			//children
			if(!empty($children)){
			    echo "<hr />";

                echo "<div id='childrendiv' class='w3-center'>\r\n";
                echo "<p><strong>Children</strong> (Implicates " . $tag[0] . ")</p><p>-</p>";

                foreach($children as $child){
                    echo "<div id='" . $child[0] . "'>";
                    echo "<label>ID: <a href='Tag.php?tagid=" . $child[0] . "'> " . $child[0] . "</a> - Tag: <a href='../Posts.php?page=1&search=" . str_replace(" ", "_", $child[1]) . "'>" . $child[1] . "</a></label>";
                    echo "<input type='Button' value='x' onclick='RemoveParent(" . $child[0] . ", " . $tagid . ")' />";
                    echo "</div>";
                }
				
			    echo "</div>\r\n";
            }	

			//alias
			if(!empty($aliases)){
			    echo "<hr />";

                echo "<div id='aliasdiv' class='w3-center'>\r\n";
                echo "<p><strong>Aliases</strong> (These Tags are Aliased to " . $tag[0] . ")</p><p>-</p>";

                foreach($aliases as $alias){
                    echo "<div id='" . $alias[0] . "'>";
                    echo "<label>ID: <a href='Tag.php?tagid=" . $alias[0] . "'> " . $alias[0] . "</a> - Tag: <a href='../Posts.php?page=1&search=" . str_replace(" ", "_", $alias[1]) . "'>" . $alias[1] . "</a></label>";
					echo "<input type='Button' value='Swap' onclick='SwapSibling(" . $alias[0] . ", " . $tagid . ")' />";
                    echo "<input type='Button' value='x' onclick='RemoveSibling(" . $alias[0] . ", " . $tagid . ")' />";
                    echo "</div>";
                }
				
			    echo "</div>\r\n";
            }	

			//preferred
			if(!empty($preferred)){
			    echo "<hr />";

                echo "<div id='preferreddiv' class='w3-center'>\r\n";
                echo "<p><strong>Preferred</strong> (This Tag is Preferred to " . $tag[0] . ")</p><p>-</p>";

                foreach($preferred as $prefer){
                    echo "<div id='" . $prefer[0] . "'>";
                    echo "<label>ID: <a href='Tag.php?tagid=" . $prefer[0] . "'> " . $prefer[0] . "</a> - Tag: <a href='../Posts.php?page=1&search=" . str_replace(" ", "_", $prefer[1]) . "'>" . $prefer[1] . "</a></label>";
					echo "<input type='Button' value='Swap' onclick='SwapSibling(" . $tagid . ", " . $prefer[0] . ")' />";
                    echo "<input type='Button' value='x' onclick='RemoveSibling(" . $tagid . ", " . $prefer[0] . ")' />";
                    echo "</div>";
                }
				
			    echo "</div>\r\n";
            }	

			//tag map
			if(!empty($tagmap)){
			    echo "<hr />";

                echo "<div id='tagmapdiv' class='w3-center'>\r\n";
                echo "<p><strong>Tag Map</strong> (These Booru Tags are Aliased to " . $tag[0] . ")</p><p>-</p>";

                foreach($tagmap as $tagg){
                    echo "<div id='" . $tagg[0] . "'>";
                    echo "<p>ID: <a href='" . GetBooruLink($tagg[1]) . $tagg[0] . "'> " . $tagg[0] . " " . GetBooruSource($tagg[1]) . "</a>";
					echo "<input type='Button' value='x' onclick='RemoveTagMap(" . json_encode($tagg[0]) . ", " . $tagid . ", " . $tagg[1] . ")' />";
                    echo "</p></div>";
                }
				
			    echo "</div>\r\n";
            }

			echo "<div id='response' class='w3-center'>";

			echo "<hr />";
					
			echo "</div>";
			
			function GetBooruLink($booru_source)
			{
				switch($booru_source)
				{
					case 0:
						return "https://danbooru.donmai.us/posts?tags=";
						break;
					case 1:
						return "https://e621.net/posts?tags=";
						break;
					case 2:
						return "https://rule34.xxx/index.php?page=post&s=list&tags=";
						break;
					case 3:
						return "https://gelbooru.com/index.php?page=post&s=list&tags=";
						break;
					case 4:
						return "https://realbooru.com/index.php?page=post&s=list&tags=";
						break;
					case 5:
						return "https://chan.sankakucomplex.com/wiki/show?title=";
						break;
					default:
						return "https://www.google.com/search?q=";
						break;
				}
			}
			
			function GetBooruSource($booru_source)
			{
				switch($booru_source)
				{
					case 0:
						return "(Danbooru)";
						break;
					case 1:
						return "(e621)";
						break;
					case 2:
						return "(rule34.xxx)";
						break;
					case 3:
						return "(gelbooru)";
						break;
					case 4:
						return "(realbooru)";
						break;
					case 5:
						return "(Sankaku Complex)";
						break;
					case 6:
						return "(Hydrus PTR)";
						break;
					default:
						return "(google)";
						break;
				}
			}
		?>
	</body>
	<script type="text/javascript">
		var edit_input;
		var paren_input;
		
		$(document).ready(function()
		{
			edit_input = document.getElementById("edit-input");
			edit_input.value = <?php echo json_encode(str_replace(" ", "_", $tag[0])); ?>;
			awesomplete = new Awesomplete(edit_input, { sort: false } );
			//edit_input.focus();
			
			edit_category = document.getElementById("edit-category");
			edit_category.value = <?php echo $tag[1]; ?>;
			edit_submit = document.getElementById("submit-edit");

			parent_input = document.getElementById("parent-input");
			parent_awesomplete = new Awesomplete(parent_input, { sort: false } );

			parent_category = document.getElementById("parent-category");
			parent_category.value = <?php echo $tag[1]; ?>;

			parent_input.addEventListener("awesomplete-select", (event) => {
				//console.log("get category");
				GetCategory(event.text.value);
			});

			parent_input.addEventListener("awesomplete-selectcomplete", (event) => {
				SubmitParent();
			});
			parent_submit = document.getElementById("submit-parent");

			child_input = document.getElementById("child-input");
			child_awesomplete = new Awesomplete(child_input, { sort: false } );

			child_submit = document.getElementById("submit-child");

			alias_input = document.getElementById("alias-input");
			alias_awesomplete = new Awesomplete(alias_input, { sort: false } );

			alias_submit = document.getElementById("submit-alias");
			
			resdiv = document.getElementById("response");
		});

		function TagSuggestions(data, id)
		{
			switch(id)
			{
				case 0:
					edit_input.value = edit_input.value.replace(/ $/g, "_");					
					break;
				case 1:
					parent_input.value = parent_input.value.replace(/ $/g, "_");
					break;
				case 2:
					child_input.value = child_input.value.replace(/ $/g, "_");
					break;
				case 3:
					alias_input.value = alias_input.value.replace(/ $/g, "_");
					break;
			}
			data = data.replace(/ $/g, "_");
			
			$.ajax({
				url: 'TagSuggestionsAjax.php?txt=' + data,
				type: 'get',
				dataType: 'JSON',
				success: function(response){
					switch(id)
					{
						case 0:
							awesomplete.list = response;				
							break;
						case 1:
							parent_awesomplete.list = response;
							break;
						case 2:
							child_awesomplete.list = response;
							break;
						case 3:
							alias_awesomplete.list = response;
							break;
					}					
					
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					console.log(xhr.responseText);
				}
			});
		}

		function GetCategory(data)
		{
			$.ajax({
				url: 'CategoryAjax.php?txt=' + data,
				type: 'get',
				dataType: 'JSON',
				success: function(response){
					//console.log(response);
					parent_category.value = response;
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					console.log(xhr.responseText);
				}
			});
		}
		
		function textPaste(){
			setTimeout(function(){
				//console.log(input.value);
				alias_input.value = alias_input.value.replace(/ /g, "_");
			}, 0);
		}

		$(document).keydown(function(e) {
			///console.log(e);
			if(e.keyCode == 33) {
				window.location.href = 'Tag.php?tagid=<?php if ($index > 0) { echo $tagids[$index - 1][0]; } else { echo $tagids[0][0]; } ?>';
			}
			else if(e.keyCode == 34)
			{
				window.location.href = 'Tag.php?tagid=<?php if ($index < $tagcount) { echo $tagids[$index + 1][0]; } else { echo $tagids[$tagcount][0]; } ?>';
			}
			else if(e.keyCode == 35)
			{
				window.location.href = 'Tag.php?tagid=<?php echo $tagids[$tagcount][0] ?>';
			}
			else if(e.keyCode == 36)
			{
				window.location.href = 'Tag.php?tagid=<?php echo $tagids[0][0] ?>';
			}
		});

		function SubmitEdit()
		{
			edit_submit.value = "Submitted!"
			var tagid = <?php echo $tagid; ?>;
			var edittag = edit_input.value;			
			cat = edit_category.value;
			
			$.ajax({
				url: 'EditTagAjax.php?tagid=' + tagid + '&newtag=' + edittag + '&cat=' + cat,
				type: 'get',
				dataType: 'JSON',
				success: function(response){
					if(response.length > 0)
					{	
						resdiv.innerHTML += "<div><p>" + response + "</p></div>";						
					}
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					//console.log(url);
					console.log(xhr.responseText);
					resdiv.innerHTML += "<div><p style='color:red;'>Error</p></div>";
				}
			});

			edit_input.value = "";
			edit_category.selectedIndex = 0;
			edit_submit.value = "Submit";
			edit_input.focus();
		}

		function SubmitParent()
		{
			parent_submit.value = "Submitted!"
			var tagid = <?php echo $tagid; ?>;
			var parent_tag = parent_input.value;			
			cat = parent_category.value;
			
			$.ajax({
				url: 'AddParentAjax.php?tagid=' + tagid + '&parent=' + parent_tag + '&cat=' + cat,
				type: 'get',
				dataType: 'JSON',
				success: function(response){
					if(response.length > 0)
					{	
						resdiv.innerHTML += "<div><p>" + response + "</p></div>";						
					}
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					//console.log(url);
					console.log(xhr.responseText);
					resdiv.innerHTML += "<div><p style='color:red;'>Error</p></div>";
				}
			});

			parent_input.value = "";
			parent_category.selectedIndex = 0;
			parent_submit.value = "Submit";
			parent_input.focus();
		}

		function SubmitChild()
		{
			child_submit.value = "Submitted!"
			var tagid = <?php echo $tagid; ?>;
			var child_tag = child_input.value;
			
			$.ajax({
				url: 'AddChildAjax.php?tagid=' + tagid + '&child=' + child_tag,
				type: 'get',
				dataType: 'JSON',
				success: function(response){
					if(response.length > 0)
					{	
						resdiv.innerHTML += "<div><p>" + response + "</p></div>";						
					}
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					//console.log(url);
					console.log(xhr.responseText);
					resdiv.innerHTML += "<div><p style='color:red;'>Error</p></div>";
				}
			});

			child_input.value = "";
			child_submit.value = "Submit";
			child_input.focus();
		}

		function SubmitAlias()
		{
			var tagid = <?php echo $tagid; ?>;
			var alias_tag = alias_input.value;

			$.ajax({
				url: 'TagCountAjax.php?tag=' + encodeURIComponent(alias_tag),
				type: 'get',
				dataType: 'JSON',
				success: function(response){
					aliasConfirm(response);
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					console.log(xhr.responseText);
					resdiv.innerHTML += "<div><p style='color:red;'>Error</p></div>";
				}
			});
		}

		function aliasConfirm(tagcount){
			var tagid = <?php echo $tagid; ?>;
			var alias_tag = alias_input.value;

			if(tagcount > 10){
				if (confirm("Alias is Tagged in " + tagcount + " images. Add as Alias?") == true) {
					alias_submit.value = "Submitted!"					
					
					$.ajax({
						url: 'AddAliasAjax.php?tagid=' + tagid + '&alias=' + encodeURIComponent(alias_tag),
						type: 'get',
						dataType: 'JSON',
						success: function(response){
							if(response.length > 0)
							{	
								resdiv.innerHTML += "<div><p>" + response + "</p></div>";						
							}
						},
						error: function(xhr, ajaxOptions, thrownError)
						{
							//console.log(url);
							console.log(xhr.responseText);
							resdiv.innerHTML += "<div><p style='color:red;'>Error</p></div>";
						}
					});

					alias_input.value = "";
					alias_submit.value = "Submit";
					alias_input.focus();
				} else {
					resdiv.innerHTML += "<div><p style='color:red;'>Cancelled Alias Add</p></div>"
				}
			}
			else{
				alias_submit.value = "Submitted!"
				var tagid = <?php echo $tagid; ?>;
				var alias_tag = alias_input.value;
				
				$.ajax({
					url: 'AddAliasAjax.php?tagid=' + tagid + '&alias=' + encodeURIComponent(alias_tag),
					type: 'get',
					dataType: 'JSON',
					success: function(response){
						if(response.length > 0)
						{	
							resdiv.innerHTML += "<div><p>" + response + "</p></div>";						
						}
					},
					error: function(xhr, ajaxOptions, thrownError)
					{
						//console.log(url);
						console.log(xhr.responseText);
						resdiv.innerHTML += "<div><p style='color:red;'>Error</p></div>";
					}
				});

				alias_input.value = "";
				alias_submit.value = "Submit";
				alias_input.focus();
			}
		}
        
		function RemoveParent(childid, parentid)
		{
			$.ajax({
				url: 'RemoveParentAjax.php?parentid=' + parentid + '&childid=' + childid,
				type: 'get',
				dataType: 'JSON',
				success: function(response){
					console.log(response);
					if(response.length > 0)
					{	
						if(response[0] == "Error"){
							console.log("Error with Remove Parent");
						}	
						else{
							var rem = document.getElementById(response[0]);
							if(rem == null){
								rem = document.getElementById(response[1]);
							}
							rem.innerHTML = "<del style='color:red;'>" + rem.innerHTML + "</del>";
						}
					}
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					console.log(xhr.responseText);
				}
			});
		}

		function RemoveSibling(aliasid, preferredid)
		{
			$.ajax({
				url: 'RemoveSiblingAjax.php?aliasid=' + aliasid + '&preferredid=' + preferredid,
				type: 'get',
				dataType: 'JSON',
				success: function(response){
					if(response.length > 0)
					{							
						if(response == "Error"){
							console.log("Error with Remove Sibling");
						}	
						else{
							var rem = document.getElementById(response);
							rem.innerHTML = "<del style='color:red;'>" + rem.innerHTML + "</del>";
						}				
					}
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					console.log(xhr.responseText);
				}
			});
		}

		function RemoveTagMap(boorutag, tagid, bs)
		{
			$.ajax({
				url: 'RemoveTagMapAjax.php?boorutag=' + boorutag + '&tagid=' + tagid + '&bs=' + bs,
				type: 'get',
				dataType: 'JSON',
				success: function(response){
					if(response.length > 0)
					{							
						if(response == "Error"){
							console.log("Error with Remove Tag Map");
						}	
						else{
							console.log(response);
							var rem = document.getElementById(response);
							rem.innerHTML = "<del style='color:red;'>" + rem.innerHTML + "</del>";
						}				
					}
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					console.log(xhr.responseText);
				}
			});
		}

		function SwapSibling(aliasid, preferredid)
		{
			$.ajax({
				url: 'SwapSiblingAjax.php?aliasid=' + aliasid + '&preferredid=' + preferredid,
				type: 'get',
				dataType: 'JSON',
				success: function(response){
					if(response.length > 0)
					{							
						if(response == "Error"){
							console.log("Error with Remove Sibling");
						}	
						else{
							resdiv.innerHTML += "<div><p>" + response + "</p></div>";	
						}				
					}
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					console.log(xhr.responseText);
				}
			});
		}
		
	</script>
</html>