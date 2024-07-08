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
		<a id="booru-tag" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>LavaIntro.php">LavaPool</a>                        
	</div>

	<?php

    echo "<p class='w3-center' id='desc'>" . $blacklist[1] . " [" . $blacklist[2] . "|" . $blacklist[3] . "] - " . $blacklist[4] . "</p>";

    echo "<hr />";

    echo "<div>";

    echo "<div class='w3-center'><a href='https://danbooru.donmai.us/posts?tags=" . urlencode($blacklist[1]) . "'>Danbooru</a><p> - </p></div>";

    echo "<div class='w3-center'><a href='https://e621.net/posts?tags=" . urlencode($blacklist[1]) . "'>e621</a><p> - </p></div>";

    echo "<div class='w3-center'><a href='https://rule34.xxx/index.php?page=post&s=list&tags=" . urlencode($blacklist[1]) . "'>rule34</a><p> - </p></div>";

    echo "<div class='w3-center'><a href='https://podobo.ddns.net/Podobo/Posts.php?search=%24name%3A" . urlencode($blacklist[1]) . "'>Podobo</a><p> - </p></div>";

    echo "</div>";

    echo "<hr />";

    echo "<div>";

    echo "<div class='w3-center'><button class='dupe-button' onclick='Remove()'>Remove from Whitelist</button><p> - </p></div>";

    echo "<div class='w3-center'><button class='dupe-button' onclick='Keep()'>Keep on Whitelist</button><p> - </p></div>";

    echo "<div class='w3-center'><button class='dupe-button' onclick='Reset()'>Reset Count</button></div>";

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
                //remove 0
                //keep 1
                //reset 2
					url: 'LavaCheckWhitelistAjax.php?id=' + id + '&kept=' + kept,
					type: 'get',
					dataType: 'JSON',
					success: function(response){
						//console.log(response);
						if(response[0] == 1){
							location.href = 'LavaDupes.php?id=' + response[1];
						}
						else if(response[0] == 2){
							location.href = 'LavaDecide.php?id=' + response[1];
						}
                        else if(response[0] == 3){
                            location.href = 'LavaDecideEnd.php';
                        }
					},
					error: function(xhr, ajaxOptions, thrownError)
					{
						console.log(xhr.responseText);
					}
				});
        }

        function Keep(){
            Submit(1);
        }

        function Remove(){
            Submit(0);
        }

        function Reset(){
            Submit(2);
        }
	</script>
</html>
