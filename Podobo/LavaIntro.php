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

    $sql = $db->prepare("select count(id) from blacklist where processed=0 and rej_count > 1");
    $blacklistcount = $sql->execute()->fetchArray()[0] ?? 0;

    $sql = $db->prepare("select count(id) from blacklist where rej_count > 30 and whitelisted = 1 and permawhitelisted = 0");
    $whitelistcount = $sql->execute()->fetchArray()[0] ?? 0;

	$sql = $db->prepare("select * from dupes where processed = 0 limit 1");
	$dupeid = $sql->execute()->fetchArray();

    $sql = $db->prepare("select * from media where processed = 0 limit 1");
	$mediaid = $sql->execute()->fetchArray();

    $sql = $db->prepare("select id from blacklist where processed = 0 and rej_count > 1 limit 1");
	$blacklistid = $sql->execute()->fetchArray();

    $sql = $db->prepare("select id from blacklist where rej_count > 30 and whitelisted = 1 and permawhitelisted = 0 limit 1");
	$whitelistid = $sql->execute()->fetchArray();

    $sql = $db->prepare("select state from processed");
	$state = $sql->execute()->fetchArray()[0];

    $state_message = "Idle";

    switch($state){
        case 0:
            $state_message = "Idle";
            break;
        case 1:
            $state_message = "Importing Files";
            break;
        case 2:
            $state_message = "Calculating Dupes";
            break;
        case 3:
            $state_message = "Ready to Decide";
            break;
        case 4:
            $state_message = "Finished Deciding";
            break;
        case 5:
            $state_message = "Sorting Files";
            break;
        case 6:
            $state_message = "Assembling Blacklist";
            break;
        case 7:
            $state_message = "Auto-Ignoring Blacklist Items";
            break;
        case 8:
            $state_message = "Deciding Blacklist";
            break;
        case 9:
            $state_message = "Finished with Blacklist";
            break;
        case 10:
            $state_message = "Moving Blacklist";
            break;
        default:
            $state_message = "Idle";
            break;
    }

    echo "<p class='w3-center'>State: " . $state_message . "</p>";
    echo "<p class='w3-center'>Dupe Items to Process: " . $dupecount . "</p>";
    echo "<p class='w3-center'>Items to Decide: " . $decidecount . "</p>";
    echo "<p class='w3-center'>Blacklist Items to Process: " . $blacklistcount . "</p>";
    echo "<p class='w3-center'>Whitelist Items to Process: " . $whitelistcount . "</p>";

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

    if($whitelistid){
        echo "<div class='w3-center'><a href='LavaCheckWhitelist.php?id=" . $whitelistid[0] . "'>Process Whitelist</a></div>";
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
