<?php
	//session_start();
	
	//$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	//$db = new SQLite3("Y:\\Database\\nevada.db");	
    $dupefiles = [];
    $sizes = []; 

	$PageTitle = "LavaPool Dupes";
	
	function customPageHeader(){?>
		<script>
			$(document).ready(function()
			{				
				var HeaderButton = document.getElementById("tools");
				HeaderButton.className = "w3-bar-item w3-button w3-theme-l1";
			});
		</script>
	<?php }

	include_once('Lavaheader.php');

    $db = null;

    $db = new SQLite3("Y:\\LavaPool\\Iowa.db");
	$db->busyTimeout(100);

	$sql = $db->prepare("select id from dupes order by id desc limit 1");
	$dupeid = $sql->execute()->fetchArray()[0] ?? -1;

    $sql = $db->prepare("select * from media where processed = 0 limit 1");
    $media = $sql->execute()->fetchArray();

    ?>

	<div id="subheader" class="w3-bar w3-theme-l1 w3-left-align w3-small container_subheader">	
		<a id="back" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>LavaDupes.php?id=<?php echo ($dupeid); ?>">Back</a>
		<a id="forward" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>LavaDecide.php?id=<?php echo ($media[0]); ?>">Forward</a>
		<a id="booru-tag" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>LavaManualBlacklist.php">Add to Blacklist</a>
		<a id="booru-tag" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>LavaIntro.php">LavaPool</a>                        
	</div>

	<?php

    echo "<p class='w3-center'>End of Dupes</p>";
	echo "<p class='w3-center'> - </p>";
    echo "<div class='w3-center'><a href='LavaDecide.php?id=" . $media[0] . "'>LavaPool Decide</a></div>";

    $db = null;
?>



</body>
<script type="text/javascript">
		$(document).ready(function()
		{

		});
	</script>
</html>
