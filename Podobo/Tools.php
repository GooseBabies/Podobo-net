<?php
	session_start();
	
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
				array_push($files, $row[0]);
			}			
			$_SESSION["all_ids"] = $files;
			$filtered = false;
		}
		$_SESSION["search"] = "";
		$idcount = count($files)-1;
	}

	$r = rand(0, $idcount);

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

	$sql = $db->prepare("SELECT COUNT(*) FROM dupes where decided = 0");
	$dupecount = $sql->execute()->fetchArray()[0];

	$sql = $db->prepare("select id from dupes where decided = 0 order by score desc limit 1");
	$dupeid = $sql->execute()->fetchArray();

	$db = null;

?>

<ul>

<li><a id="booru-tag" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>BooruTag.php">Booru Tag</a></li>
<li><a id="ignored-tags" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "" : "Tags/"?>IgnoredTagList.php">Ignored Tag List</a></li>
<?php
	if($dupecount > 0){
		echo "<li><a id='dupes' class='w3-bar-item w3-button w3-theme-l1' href='" . (isset($InTags) ? "../" : "") . "Dupes.php?id=" . $dupeid[0] . "'>Dupes [" . $dupecount . "]</a></li>";
	}	
?>
<li><a id="command" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>Command.php">Command</a></li>
<li><a id="slideshow" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>Slideshow.php">Slideshow</a></li>
<li><a id="hydrus" class="w3-bar-item w3-button w3-theme-l1" href="https://podobo.ddns.net:45869/">Hydrus</a></li>
<li><a id="stats" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>Stats.php">Stats</a></li>
<li><a id="rarity" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>Sets.php">Sets</a></li>
<li><a id="elo" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>Elo.php">Elo</a></li>
<li><a id="lavapool" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>LavaIntro.php">Lava Pool</a></li>

</ul>

</body>
</html>
