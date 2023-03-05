<?php
	session_start();
	$tags = [];
	$tagids= [];
	$page_tag_data = [];
	$tag_limit = 100;
	$TagCategoryTitle=array("General", "IP/Series", "Individual", "Rating", "Artist", "Studio/Network", "Sex", "Afilliation/Group", "Race/Species/Ethnicity", "Body Part", "Clothing/Accessory", "Position", "Setting", "Action", "Meta", "Title", "Release Date");

	//$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	//$db = new SQLite3("Y:\\Database\\nevada.db");	
	$db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
	
	if(isset($_GET["page"])) { $page = $_GET["page"]; } else { $page = 1; };
    if(isset($_GET["search"])) { $search = html_entity_decode($_GET["search"]); } else { $search = ''; };
	if(isset($_GET["cat"])) { $cat = $_GET["cat"]; } else { $cat = -1; }
	if(isset($_GET["aliases"])) { $aliases = $_GET["aliases"]; } else { $aliases = 0; }
	if(isset($_GET["order"])) { $order = $_GET["order"]; } else { $order = 0; }
	if(isset($_GET["desc"])) { $desc = $_GET["desc"]; } else { $desc = 0; }
	$rowpageamount = 100;

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

	$offset = ($page - 1) * $tag_limit;
	$parentid = -1;
	$childid = -1;
	$aliasid = -1;
	$preferredid = -1;
	$order_column = "tag_name";
	$asc = " ASC";

	switch($order){
		case 0:
			$order_column = "tag_name";
			break;
		case 1:
			$order_column = "tagid";
			break;
		case 2:
			$order_column = "tag_count";
			break;
		case 3:
			$order_column = "category";
			break;
		default:
			$order_column = "tag_name";
			break;
	}

	switch($desc){
		case 0:
			$asc = " ASC";
			break;
		case 1:
			$asc = " DESC";
			break;;
		default:
			$asc = " ASC";
			break;
	}

	if(isset($_SESSION["tag_ids"]) and $search == "" && $cat == -1 && $aliases == 0)
	{
		$sql = $db->prepare("SELECT tag_name, tag_count, category, alias FROM tags order by " . $order_column . $asc . " limit :limit offset :offset");
		$sql->bindValue(':limit', $tag_limit, SQLITE3_INTEGER);
		$sql->bindValue(':offset', $offset, SQLITE3_INTEGER);
		echo "<!--" . $sql->getSQL() . "-->";
		$result = $sql->execute();
		while ($row = $result->fetchArray()) {
			array_push($page_tag_data, $row);
		}

		$_SESSION["tagsearch"] = $search;
		$tagids = $_SESSION["tag_ids"];
		unset($_SESSION["filtered_tag_ids"]);
		$filtered = false;
	}
	else
	{
		if($search == "" && $cat == -1 && $aliases == 0)
		{
			$sql = $db->prepare("SELECT tagid FROM tags order by " . $order_column . $asc);
			$result = $sql->execute();
			while ($row = $result->fetchArray()) {
				array_push($tagids, $row);
			}

			$sql = $db->prepare("SELECT tag_name, tag_count, category, alias FROM tags order by " . $order_column . $asc . " limit :limit offset :offset");
			$sql->bindValue(':limit', $tag_limit, SQLITE3_INTEGER);
			$sql->bindValue(':offset', $offset, SQLITE3_INTEGER);
			echo "<!--" . $sql->getSQL() . "-->";
			$result = $sql->execute();
			while ($row = $result->fetchArray()) {
				array_push($page_tag_data, $row);
			}
			
			$_SESSION["tagsearch"] = $search;
			$_SESSION["tag_ids"] = $tagids; //only store session data for full sql call
			unset($_SESSION["filtered_tag_ids"]);
			$filtered = false;
		}
		else
		{	
			$_SESSION["tagsearch"] = $search;
			//echo $search;
			
			if(empty($search)){
				if($cat == -1){
					$sql_ids = "select tagid from tags where alias = 0 COLLATE NOCASE order by " . $order_column . $asc;
					$sql_page = "select tag_name, tag_count, category, alias from tags where alias = 0 COLLATE NOCASE order by " . $order_column . $asc . " limit :limit OFFSET :offset";		
				}
				else if($cat == -2){
					
					if($aliases == 1){
						$sql_ids = "select tagid from tags where category != 15 COLLATE NOCASE order by " . $order_column . $asc;
						$sql_page = "select tag_name, tag_count, category, alias from tags where category != 15 COLLATE NOCASE order by " . $order_column . $asc . " limit :limit OFFSET :offset";
					}
					else{
						$sql_ids = "select tagid from tags where category != 15 and alias = 0 COLLATE NOCASE order by " . $order_column . $asc;
						$sql_page = "select tag_name, tag_count, category, alias from tags where category != 15 and alias = 0 COLLATE NOCASE order by " . $order_column . $asc . " limit :limit OFFSET :offset";
					}
				}
				else{
					if($aliases == 1){
						$sql_ids = "select tagid from tags where category = :category COLLATE NOCASE order by " . $order_column . $asc;
						$sql_page = "select tag_name, tag_count, category, alias from tags where category = :category COLLATE NOCASE order by " . $order_column . $asc . " limit :limit OFFSET :offset";
					}
					else{
						$sql_ids = "select tagid from tags where category = :category and alias = 0 COLLATE NOCASE order by " . $order_column . $asc;
						//echo "<!--" . $sql_ids . "-->";
						$sql_page = "select tag_name, tag_count, category, alias from tags where category = :category and alias = 0 COLLATE NOCASE order by " . $order_column . $asc . " limit :limit OFFSET :offset";	
					}		
				}
			}
			else{
				$search = str_replace("_", " ", $search);
				//echo "<!--" . $search . "-->";
				if(str_starts_with($search, "parent:")){
					$sql = $db->prepare("SELECT tagid FROM tags where tag_name = :tag COLLATE NOCASE");
					$sql->bindValue(':tag', substr($search, 7), SQLITE3_TEXT);
					$parentid = $sql->execute()->fetchArray()[0] ?? -1;

					$sql_ids = "select tagid from tags where tagid in (select child from parents where parent = :parentid) COLLATE NOCASE order by " . $order_column . $asc;
					$sql_page = "select tag_name, tag_count, category, alias from tags where tagid in (select child from parents where parent = :parentid) COLLATE NOCASE order by " . $order_column . $asc . " limit :limit OFFSET :offset";
				}
				else if(str_starts_with($search, "child:")){
					$sql = $db->prepare("SELECT tagid FROM tags where tag_name = :tag COLLATE NOCASE");
					//echo "<!--" . $search . "-->";
					$sql->bindValue(':tag', substr($search, 6), SQLITE3_TEXT);
					$childid = $sql->execute()->fetchArray()[0] ?? -1;
					//echo "<!--" . $childid . "-->";

					$sql_ids = "select tagid from tags where tagid in (select parent from parents where child = :childid) COLLATE NOCASE order by " . $order_column . $asc;
					$sql_page = "select tag_name, tag_count, category, alias from tags where tagid in (select parent from parents where child = :childid) COLLATE NOCASE order by " . $order_column . $asc . " limit :limit OFFSET :offset";
				}
				else if(str_starts_with($search, "alias:")){
					$sql = $db->prepare("SELECT tagid FROM tags where tag_name = :tag COLLATE NOCASE");
					$sql->bindValue(':tag', substr($search, 6), SQLITE3_TEXT);
					$aliasid = $sql->execute()->fetchArray()[0] ?? -1;
					//echo "<!--" . $aliasid . "-->";

					$sql_ids = "select tagid from tags where tagid in (select preferred from siblings where alias = :aliasid) COLLATE NOCASE order by " . $order_column . $asc;
					$sql_page = "select tag_name, tag_count, category, alias from tags where tagid in (select preferred from siblings where alias = :aliasid) COLLATE NOCASE order by " . $order_column . $asc . " limit :limit OFFSET :offset";
				}
				else if(str_starts_with($search, "preferred:")){
					$sql = $db->prepare("SELECT tagid FROM tags where tag_name = :tag COLLATE NOCASE");
					$sql->bindValue(':tag', substr($search, 10), SQLITE3_TEXT);
					$preferredid = $sql->execute()->fetchArray()[0] ?? -1;
					echo "<!--" . $preferredid . "-->";

					$sql_ids = "select tagid from tags where tagid in (select alias from siblings where preferred = :preferredid) COLLATE NOCASE order by " . $order_column . $asc;
					$sql_page = "select tag_name, tag_count, category, alias from tags where tagid in (select alias from siblings where preferred = :preferredid) COLLATE NOCASE order by " . $order_column . $asc . " limit :limit OFFSET :offset";
				}
				else if($cat == -1){
					if($aliases == 1){
						$sql_ids = "select tagid from tags where tag_name like :search COLLATE NOCASE order by " . $order_column . $asc;
						$sql_page = "select tag_name, tag_count, category, alias from tags where tag_name like :search COLLATE NOCASE order by " . $order_column . " limit :limit OFFSET :offset";
					}
					else {
						$sql_ids = "select tagid from tags where tag_name like :search and alias = 0 COLLATE NOCASE order by " . $order_column . $asc;
						$sql_page = "select tag_name, tag_count, category, alias from tags where tag_name like :search and alias = 0 COLLATE NOCASE order by " . $order_column . $asc . " limit :limit OFFSET :offset";	
					}
				}
				else if($cat == -2){
					if($aliases == 1){
						$sql_ids = "select tagid from tags where category != 15 and tag_name like :search COLLATE NOCASE order by " . $order_column . $asc;
						$sql_page = "select tag_name, tag_count, category, alias from tags where category != 15 and tag_name like :search COLLATE NOCASE order by " . $order_column . " limit :limit OFFSET :offset";
					}
					else {
						$sql_ids = "select tagid from tags where category != 15 and tag_name like :search and alias = 0 COLLATE NOCASE order by " . $order_column . $asc;
						$sql_page = "select tag_name, tag_count, category, alias from tags where category != 15 and tag_name like :search and alias = 0 COLLATE NOCASE order by " . $order_column . $asc . " limit :limit OFFSET :offset";	
					}
				}
				else{
					if($aliases == 1){
						$sql_ids = "select tagid from tags where category = :category and tag_name like :search COLLATE NOCASE order by " . $order_column . $asc;
						$sql_page = "select tag_name, tag_count, category, alias from tags where category = :category and tag_name like :search COLLATE NOCASE order by " . $order_column . $asc . " limit :limit OFFSET :offset";
					}
					else {
						$sql_ids = "select tagid from tags where category = :category and tag_name like :search and alias = 0 COLLATE NOCASE order by " . $order_column . $asc;
						$sql_page = "select tag_name, tag_count, category, alias from tags where category = :category and tag_name like :search and alias = 0 COLLATE NOCASE order by " . $order_column . $asc . " limit :limit OFFSET :offset";	
					}
				}
			}

			$sql = $db->prepare($sql_ids);
			$sql->bindValue(':category', $cat, SQLITE3_INTEGER);
			$sql->bindValue(':search', "%" . $search . "%", SQLITE3_TEXT);
			$sql->bindValue(':parentid', $parentid, SQLITE3_INTEGER);
			$sql->bindValue(':childid', $childid, SQLITE3_INTEGER);
			$sql->bindValue(':aliasid', $aliasid, SQLITE3_INTEGER);
			$sql->bindValue(':preferredid', $preferredid, SQLITE3_INTEGER);
			echo "<!--" .  $sql->getsql(true) . "-->";
			//echo "<!--" .  $order_column . "-->";
			$result = $sql->execute();
			while ($row = $result->fetchArray()) {
				array_push($tagids, $row);
			}

			$_SESSION["filtered_tag_ids"] = $tagids;
			$filtered = true;

			$sql = $db->prepare($sql_page);
			$sql->bindValue(':category', $cat, SQLITE3_INTEGER);
			$sql->bindValue(':search', "%" . $search . "%", SQLITE3_TEXT);
			$sql->bindValue(':limit', $tag_limit, SQLITE3_INTEGER);
			$sql->bindValue(':offset', $offset, SQLITE3_INTEGER);
			$sql->bindValue(':parentid', $parentid, SQLITE3_INTEGER);
			$sql->bindValue(':childid', $childid, SQLITE3_INTEGER);
			$sql->bindValue(':aliasid', $aliasid, SQLITE3_INTEGER);
			$sql->bindValue(':preferredid', $preferredid, SQLITE3_INTEGER);
			echo "<!--" .  $sql->getsql(true) . "-->";
			//echo "<!--" .  $order_column . "-->";
			$result = $sql->execute();
			while ($row = $result->fetchArray()) {
				array_push($page_tag_data, $row);
			}
		}
	}

	$tagcount = count($tagids);

	echo "<!--" .  $tagcount . "-->";
	echo "<!--" .  count($page_tag_data) . "-->";

	//$tagcount = count($tags);
	$pagecount = ceil($tagcount/$rowpageamount);

	$PageTitle = "Podobo - Tag List";
	$InTags = true;

	function customPageHeader(){?>
		<script>
			$(document).ready(function()
			{				
				var HeaderButton = document.getElementById("tags");
				HeaderButton.className = "w3-bar-item w3-button w3-theme-l1";
			});
		</script>
		<style type="text/css" media="screen">
			table{
				border-collapse:collapse;
				border:2px solid #FFFFFF;
				table-layout:fixed;
				width:90vw;
				margin-left: auto;
				margin-right: auto;
			}

			table td, th{
				border:2px solid #FFFFFF;
				font-size: small;
				padding:4px 4px;
				color:#FFFFFF;
				word-wrap:break-word;
			}
			form{
				margin:6px;
			}
		</style>
	<?php }

	include_once('../header.php');

    $db = null;
?>
		<main class="row">
		<?php
			
			echo "<div class='col-10'>";
			
			echo "<div class='w3-center'>";
				echo "<form action='TagList.php'>";
				echo "<input type='text' id='tags' name='search' oninput='TagSuggestions(this.value)'/>";
				//echo "<input type='hidden' onsubmit='AddTag()'/>";
				//echo "</form>";

				echo "<select name='cat' id='category'>";
					echo "<option value='-1'>All</option>";
					echo "<option value='-2'>All but Title</option>";
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

				echo "<label for='aliases'>aliases</label>";
				echo "<input type='checkbox' value='1' name='aliases' " . ($aliases == 1 ? "checked" : "") . " />";

				echo "<select name='order' id='order'>";
					echo "<option value='0'>Name</option>";
					echo "<option value='1'>ID</option>";
					echo "<option value='2'>Count</option>";
					echo "<option value='3'>Category</option>";
				echo "</select>";

				echo "<label for='desc'>DESC</label>";
				echo "<input type='checkbox' value='1' name='desc' " . ($desc == 1 ? "checked" : "") . " />";

				echo "<input id='search-box' type='submit' value='Search'/>";

				echo"</form>";

			
			echo "</div>";
			
			echo "<table><tr>";
			
			echo "<th>Tag ID</th><th>Tag</th><th>Edit/Wiki</th><th>Category</th><th>Count</th></tr>";		
			
			//echo "<!--" .  $tagids[0][0] . "-->";

			for($i = 0; $i < count($page_tag_data); $i++){
				//echo "<!--" .  $page_tag_data[$i][3] . "-->";
				echo "<tr>";
				echo "<td>" . $tagids[(($page - 1) * $tag_limit) + $i][0] . "</td>";
                echo "<td><a href ='../Posts.php?search=" . htmlspecialchars(str_replace(" ", "_", $page_tag_data[$i][0]), ENT_QUOTES) ."&page=1'>" . ($page_tag_data[$i][3] == 1 ? "<i>" : "") . $page_tag_data[$i][0] . ($page_tag_data[$i][3] == 1 ? "</i>" : "") . "</a></td>";
				echo "<td><a href='Tag.php?tagid=" . $tagids[(($page - 1) * $tag_limit) + $i][0] . "'>Edit Tag</a> - <a href='../wiki/" . htmlspecialchars(str_replace(" ", "_", $page_tag_data[$i][0]), ENT_QUOTES) . "'>Wiki</a></td>";
				echo "<td>" . $TagCategoryTitle[$page_tag_data[$i][2]] . "</td>";
                echo "<td>" . $page_tag_data[$i][1] . "</td>";
				echo "</tr>";
			}
			
			echo "</tr></table>";
			
			echo "</div>";
			echo "</main>";
			
			echo "<div class='container_footer' align='center'>";
			
			$pagelimit = 5;
			if($page < $pagelimit) { $offset = 1; } else { $offset = $page - ($pagecount < $pagelimit ? $pagecount + 1 : $pagelimit);};
			if($page > $pagecount - $pagelimit) { $end_page = $pagecount; } else { $end_page = $page + ($pagecount < $pagelimit ? $pagecount : $pagelimit); };
			
			if($page != 1) 
			{
				echo "<a href='TagList.php?search=" . htmlspecialchars($search) . "&cat=" . $cat . "&page=1'><i class='fas fa-angles-left fa-2x'></i></a>";
				echo "<a href='TagList.php?search=" . htmlspecialchars($search) . "&cat=".$cat."&page=" . ($page - 1) . "'><i class='fas fa-angle-left fa-2x'></i></a>";	
			};
			
			for($k = $offset; $k <= $end_page; $k++)
			{
				if($k == $page){
					echo "<a class='current-page' href='TagList.php?search=" . htmlspecialchars($search) . "&cat=".$cat."&page=" . $k  . "'>";
					echo $k;
					echo "</a>";
				}
				else{
					echo "<a class='other-page' href='TagList.php?search=" . htmlspecialchars($search) . "&cat=".$cat."&page=" . $k  . "'>";
					echo $k;
					echo "</a>";
				}
			}
			
			echo "<!--" . $pagecount . "-->";

			if($page != $pagecount) 
			{
				echo "<a href='TagList.php?search=" . htmlspecialchars($search) . "&cat=".$cat."&page=" . ($page + 1) . "'><i class='fas fa-angle-right fa-2x'></i></a>";
				echo "<a href='TagList.php?search=" . htmlspecialchars($search) . "&cat=".$cat."&page=" . $pagecount . "'><i class='fas fa-angles-right fa-2x'></i></a>";
			};
			echo "</div>";
			
		?>
	</body>
	<script type="text/javascript">
		
		$(document).ready(function()
		{
			category = document.getElementById("category");
			category.value = <?php echo $cat; ?>;

			order = document.getElementById("order");
			order.value = <?php echo $order; ?>;

			searchbox = document.getElementById("tags");
			searchbox.value = <?php echo json_encode($search); ?>;
		});
		
	</script>
</html>