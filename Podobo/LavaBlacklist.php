<?php
	//session_start();
	
	//$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	//$db = new SQLite3("Y:\\Database\\nevada.db");	
    $dupefiles = [];   

	$PageTitle = "LavaPool Blacklist";
	
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

    if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = -1; }; 

	$sql = $db->prepare("select * from blacklist where id = :id");
    $sql->bindValue(":id", $id, SQLITE3_INTEGER);
	$blacklist = $sql->execute()->fetchArray();

    if(!$blacklist){
		//trigger C# to move files
        die("<script>location.href = 'LavaIntro.php'</script>");
    }

	?>

	<div id="subheader" class="w3-bar w3-theme-l1 w3-left-align w3-small container_subheader">	
		<a id="back" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>LavaBlacklist.php?id=<?php echo ($id-1); ?>">Back</a>
		<a id="forward" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>LavaBlacklist.php?id=<?php echo ($id+1); ?>">Forward</a>
		<a id="booru-tag" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>LavaManualBlacklist.php">Add to Blacklist</a>
		<a id="booru-tag" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>LavaIntro.php">LavaPool</a>                        
	</div>

	<?php

    echo "<p class='w3-center' id='desc'>" . $blacklist[0] . " - " . $blacklist[1] . " [" . $blacklist[2] . " | " . $blacklist[3] . "] - " . $blacklist[4] . "</p>";

    echo "<hr />";

    echo "<div>";

    echo "<div class='w3-center'><a href='https://danbooru.donmai.us/posts?tags=" . urlencode($blacklist[1]) . "'>Danbooru [" . $blacklist[5] . "]</a><p> - </p></div>";

    echo "<div class='w3-center'><a href='https://e621.net/posts?tags=" . urlencode($blacklist[1]) . "'>e621</a><p> - </p></div>";

    echo "<div class='w3-center'><a href='https://rule34.xxx/index.php?page=post&s=list&tags=" . urlencode($blacklist[1]) . "'>rule34 [" . $blacklist[6] . "]</a><p> - </p></div>";

    echo "<div class='w3-center'><a href='https://podobo.ddns.net/Podobo/Posts.php?search=%24start%3A" . urlencode($blacklist[1]) . "'>Podobo</a><p> - </p></div>";

    echo "</div>";

    echo "<hr />";

    echo "<div>";

    echo "<div class='w3-center'><button class='dupe-button' onclick='Blacklist()'>Add to Blacklist</button><p> - </p></div>";

    echo "<div class='w3-center'><button class='dupe-button' onclick='Whitelist()'>Add to Whitelist</button><p> - </p></div>";

    echo "<div class='w3-center'><button class='dupe-button' onclick='Remove()'>Ignore</button></div>";

    echo "</div>";

    $db = null;
?>



</body>
<script type="text/javascript">	
        var id = <?php echo json_encode($id) ?>;
        var item = <?php echo json_encode(urlencode($blacklist[1])) ?>;
		$(document).ready(function()
		{
            // $.ajax({
            //         url: 'https://testbooru.donmai.us/posts.json?api_key=t4jWjaPSx2iCQthawYYuLXsm&login=GooseB&tags=' + item,
			// 		type: 'get',
			// 		dataType: 'JSON',
			// 		success: function(response){
            //             console.log(response);
			// 		},
			// 		error: function(xhr, ajaxOptions, thrownError)
			// 		{
			// 			console.log(xhr.responseText);
			// 		}
			// 	});
		});

        function Submit(kept){
            $.ajax({
					url: 'LavaBlacklistAjax.php?id=' + id + '&kept=' + kept,
					type: 'get',
					dataType: 'JSON',
					success: function(response){
						//console.log(response);
						if(response == -1){
							location.href = 'LavaBlacklistEnd.php';
						}
						else{
							location.href = 'LavaBlacklist.php?id=' + response;
						}
					},
					error: function(xhr, ajaxOptions, thrownError)
					{
						console.log(xhr.responseText);
					}
				});
        }

        function Blacklist(){
            Submit(0);
        }

        function Remove(){
            Submit(1);
        }

        function Whitelist(){
            $.ajax({
					url: 'LavaWhitelistAjax.php?id=' + id,
					type: 'get',
					dataType: 'JSON',
					success: function(response){
						//console.log(response);
                        if(response == -1){
							location.href = 'LavaBlacklistEnd.php';
						}
						else{
							location.href = 'LavaBlacklist.php?id=' + response;
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
