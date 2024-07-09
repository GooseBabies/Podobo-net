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
    ?>

	<div id="subheader" class="w3-bar w3-theme-l1 w3-left-align w3-small container_subheader">
		<a id="booru-tag" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>LavaIntro.php">LavaPool</a>                        
	</div>

	<?php

    echo "<p class='w3-center'>End of Whitelist</p>";
	echo "<p class='w3-center'> - </p>";
    echo "<div class='w3-center'><a href='LavaIntro.php'>LavaPool</a></div>";

    $db = null;
?>



</body>
<script type="text/javascript">
		$(document).ready(function()
		{

		});
	</script>
</html>
