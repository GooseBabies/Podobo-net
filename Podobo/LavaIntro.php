<?php
	//session_start();
	
	//$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	//$db = new SQLite3("Y:\\Database\\nevada.db");	
    $dupefiles = [];   

	$PageTitle = "LavaPool";
	
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

    $db = null;

    $db = new SQLite3("Y:\\LavaPool\\Iowa.db");
	$db->busyTimeout(100);

    $sql = $db->prepare("select count(id) from dupes where processed=0");
    $dupecount = $sql->execute()->fetchArray()[0] ?? 0;

    $sql = $db->prepare("select count(id) from media where processed=0");
    $decidecount = $sql->execute()->fetchArray()[0] ?? 0;

    $sql = $db->prepare("select count(id) from blacklist where processed=0 and rej_count > 0");
    $blacklistcount = $sql->execute()->fetchArray()[0] ?? 0;

	$sql = $db->prepare("select * from dupes where processed = 0 limit 1");
	$dupeid = $sql->execute()->fetchArray();

    $sql = $db->prepare("select * from media where processed = 0 limit 1");
	$mediaid = $sql->execute()->fetchArray();

    $sql = $db->prepare("select * from blacklist where processed = 0 and rej_count > 0 limit 1");
	$blacklistid = $sql->execute()->fetchArray();

    echo "<p class='w3-center'>Dupe Items to Process: " . $dupecount . "</p>";
    echo "<p class='w3-center'>Items to Decide: " . $decidecount . "</p>";
    echo "<p class='w3-center'>Blacklist Items to Process: " . $blacklistcount . "</p>";

    echo "<hr />";

    echo "<div>";

    //print_r($dupeid);

    if($dupeid){
        echo "<div class='w3-center'><a href='LavaDupes.php?id=" . $dupeid[0] . "'>Process Dupes</a></div>";
    }
    else{
        if($mediaid){
            echo "<div class='w3-center'><a href='LavaDecide.php?id=" . $mediaid[0] . "'>Decide Media</a></div>";
        }
    }
    
    if($blacklistid){
        echo "<div class='w3-center'><a href='LavaBlacklist.php?id=" . $blacklistid[0] . "'>Process Blacklist</a></div>";
    }

    echo "</div>";

    $db = null;
?>

</body>
<script type="text/javascript">	
		$(document).ready(function()
		{
            
		});
	</script>
</html>
