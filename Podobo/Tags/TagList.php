<?php
	session_start();
	$tags = [];
	$TagCategoryTitle=array("General", "IP/Series", "Individual", "", "Artist", "Studio/Network", "Sex", "Afilliation/Group", "Race/Species/Ethnicity", "Body Part", "Clothing/Accessory", "Position", "Setting", "Action", "Meta", "Title", "Release Date");

	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");	
	
	if(isset($_GET["page"])) { $page = $_GET["page"]; } else { $page = 1; };
    if(isset($_GET["search"])) { $search = $_GET["search"]; } else { $search = ''; };
	if(isset($_GET["cat"])) { $cat = $_GET["cat"]; } else { $cat = -1; }
	$rowpageamount = 100;

	$files = [];
	if(isset($_SESSION["filtered_data"]) && count($_SESSION["filtered_data"]) > 0){
		$files = $_SESSION["filtered_data"];
		$idcount = count($files)-1;
		$filtered = true;
		//$index = searchForId($id, $files);
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
			$_SESSION["image_data"] = $files;
			$filtered = false;
		}
		$idcount = count($files)-1;
	}

	$r = rand(0, $idcount);
	
	$offset = ($page - 1) * $rowpageamount;
	if(empty($search)){
		if($cat == -1){
			$sql = $db->prepare("select tagid, tag_name, tag_count, category from tags where tag_count > 0 order by tag_name COLLATE NOCASE limit 100 OFFSET :offset");
			$sql->bindValue(':offset', $offset, SQLITE3_TEXT);
			$result = $sql->execute();
			while ($row = $result->fetchArray()) {
				array_push($tags, $row);
			}	
			$sql = $db->prepare("SELECT COUNT(*) FROM tags where tag_count > 0");
			$tagcount = $sql->execute()->fetchArray()[0] ?? 0;	
		}
		else if($cat == -2){
			$sql = $db->prepare("select tagid, tag_name, tag_count, category from tags where category != 15 and tag_count > 0 order by tag_name COLLATE NOCASE limit 100 OFFSET :offset");
			$sql->bindValue(':offset', $offset, SQLITE3_TEXT);
			$sql->bindValue(':category', $cat, SQLITE3_INTEGER);
			$result = $sql->execute();
			while ($row = $result->fetchArray()) {
				array_push($tags, $row);
			}
			$sql = $db->prepare("SELECT COUNT(*) FROM tags where category != 15 and tag_count > 0");
			$sql->bindValue(':category', $cat, SQLITE3_INTEGER);
			$tagcount = $sql->execute()->fetchArray()[0] ?? 0;
		}
		else{
			$sql = $db->prepare("select tagid, tag_name, tag_count, category from tags where category = :category and tag_count > 0 order by tag_name COLLATE NOCASE limit 100 OFFSET :offset");
			$sql->bindValue(':offset', $offset, SQLITE3_TEXT);
			$sql->bindValue(':category', $cat, SQLITE3_INTEGER);
			$result = $sql->execute();
			while ($row = $result->fetchArray()) {
				array_push($tags, $row);
			}
			$sql = $db->prepare("SELECT COUNT(*) FROM tags where category = :category and tag_count > 0");
			$sql->bindValue(':category', $cat, SQLITE3_INTEGER);
			$tagcount = $sql->execute()->fetchArray()[0] ?? 0;
		}
	}
	else{
		if($cat == -1){
			$sql = $db->prepare("select tagid, tag_name, tag_count, category from tags where tag_name like :search and tag_count > 0 order by tag_name COLLATE NOCASE limit 100 OFFSET :offset");
			$sql->bindValue(':search', "%" . $search . "%", SQLITE3_TEXT);
			$sql->bindValue(':offset', $offset, SQLITE3_TEXT);
			$result = $sql->execute();
			while ($row = $result->fetchArray()) {
				array_push($tags, $row);
			}	
			$sql = $db->prepare("SELECT COUNT(*) FROM tags where tag_name like :search and tag_count > 0");
			$tagcount = $sql->execute()->fetchArray()[0] ?? 0;
		}
		else if($cat == -2){
			$sql = $db->prepare("select tagid, tag_name, tag_count, category from tags where category != 15 and tag_name like :search and tag_count > 0 order by tag_name COLLATE NOCASE limit 100 OFFSET :offset");
			$sql->bindValue(':category', $cat, SQLITE3_INTEGER);
			$sql->bindValue(':search', "%" . $search . "%", SQLITE3_TEXT);
			$sql->bindValue(':offset', $offset, SQLITE3_TEXT);		
			$result = $sql->execute();
			while ($row = $result->fetchArray()) {
				array_push($tags, $row);
			}
			$sql = $db->prepare("SELECT COUNT(*) FROM tags where category != 15 and tag_name like :search and tag_count > 0");
			$sql->bindValue(':category', $cat, SQLITE3_INTEGER);
			$tagcount = $sql->execute()->fetchArray()[0] ?? 0;
		}
		else{
			$sql = $db->prepare("select tagid, tag_name, tag_count, category from tags where category = :category and tag_name like :search and tag_count > 0 order by tag_name COLLATE NOCASE limit 100 OFFSET :offset");
			$sql->bindValue(':category', $cat, SQLITE3_INTEGER);
			$sql->bindValue(':search', "%" . $search . "%", SQLITE3_TEXT);
			$sql->bindValue(':offset', $offset, SQLITE3_TEXT);		
			$result = $sql->execute();
			while ($row = $result->fetchArray()) {
				array_push($tags, $row);
			}
			$sql = $db->prepare("SELECT COUNT(*) FROM tags where category = :category and tag_name like :search and tag_count > 0");
			$sql->bindValue(':category', $cat, SQLITE3_INTEGER);
			$tagcount = $sql->execute()->fetchArray()[0] ?? 0;
		}
	}
	
	echo "<!--" .  $tagcount . "-->";

	//$tagcount = count($tags);
	$pagecount = ceil($tagcount/$rowpageamount);

    $db = null;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html" charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
	    <title>Podobo - Tags</title>
	    <link rel="stylesheet" type="text/css" href="../../style/PodoboStyle.css" />		
		<link rel="stylesheet" href="../../style/w3.css">
		<link rel="icon" type="image/x-icon" href="../../imgs/favicon.ico">
		<!-- <script type = "text/javascript" src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script> -->
		<script type = "text/javascript" src = "../../js/jquery-3.6.0.min.js"></script>
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

	</head>
	<body>
	<div class="w3-bar w3-theme w3-left-align w3-medium container_header">
			<a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="../Posts.php">Posts</a>		
			<a class="w3-bar-item w3-button w3-theme-l1" href="TagList.php">Tags</a>
			<a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="../Wiki.php">Wiki</a>
			<a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="../Slideshow.php">Slideshow</a>
			<a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="../Tools.php">Tools</a>
		</div>
		<div class="w3-bar w3-theme-l1 w3-left-align w3-small container_subheader">
			<a class="w3-bar-item w3-button w3-theme-l1" href="TagList.php">Tag List</a>
		</div>
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

				echo "<input type='submit' value='Search'/>";

				echo"</form>";

			
			echo "</div>";
			
			echo "<table><tr>";
			
			echo "<th>Tag ID</th><th>Tag</th><th>Category</th><th>Count</th></tr>";		
			
			for($i = 0; $i < count($tags); $i++){
				echo "<tr>";
				echo "<td>" . $tags[$i][0] . "</td>";
                echo "<td><a href ='../Posts.php?search=" . htmlspecialchars(str_replace(" ", "_", $tags[$i][1]), ENT_QUOTES) ."&page=1'>"  . $tags[$i][1] . "</a></td>";
				echo "<td><a href='Tag.php?tagid=" . $tags[$i][0] . "'>Edit Tag</a></td>";
				echo "<td>" . $TagCategoryTitle[$tags[$i][3]] . "</td>";
                echo "<td>" . $tags[$i][2] . "</td>";
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
				echo "<a href='TagList.php?cat=" . $cat . "&page=1'>&lt;&lt;</a>";
				echo "<a href='TagList.php?cat=".$cat."&page=" . ($page - 1) . "'>&lt;</a>";	
			};
			
			for($k = $offset; $k <= $end_page; $k++)
			{
				echo "<a href='TagList.php?cat=".$cat."&page=" . $k  . "'>";
				if($k==$page) { echo "<strong> ".$k." </strong>"; } else { echo $k;};
				echo "</a>";
			}
			
			echo "<!--" . $pagecount . "-->";

			if($page != $pagecount) 
			{
				echo "<a href='TagList.php?cat=".$cat."&page=" . ($page + 1) . "'>&gt;</a>";
				echo "<a href='TagList.php?cat=".$cat."&page=" . $pagecount . "'>&gt;&gt;</a>";
			};
			echo "</div>";
			
		?>
	</body>
	<script type="text/javascript">
		
		$(document).ready(function()
		{
			category = document.getElementById("category");
			category.value = <?php echo $cat; ?>;
		});
		
	</script>
</html>