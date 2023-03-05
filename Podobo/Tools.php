<?php
	session_start();
	
	//$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	//$db = new SQLite3("Y:\\Database\\nevada.db");
	$db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
	$db->busyTimeout(100);

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

    $db = null;

	$PageTitle = "Podobo";
	
	function customPageHeader(){?>
		<script>
			$(document).ready(function()
			{				
				var HeaderButton = document.getElementById("tools");
				HeaderButton.className = "w3-bar-item w3-button w3-theme-l1";
			});
		</script>
	<?php }

	include_once('header.php');

?>

<a id="booru-tag" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>BooruTag.php">Booru Tag</a>
<a id="ignored-tags" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "" : "Tags/"?>IgnoredTagList.php">Ignored Tag List</a>
<a id="dupes" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>Dupes.php">Dupes</a>
<a id="command" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>Command.php">Command</a>
<a id="slideshow" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>Slideshow.php">Slideshow</a>
<a id="hydrus" class="w3-bar-item w3-button w3-theme-l1" href="https://67.253.187.197:45869/">Hydrus</a>

</body>
</html>
