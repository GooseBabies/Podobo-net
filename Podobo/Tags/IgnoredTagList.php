<?php
	session_start();
    $ignored = [];

	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");	

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
	
	//tag Map

    $sql = $db->prepare("select distinct booru_tag, booru_source from booru_proc where ignored = 1 order by booru_tag");
	$result = $sql->execute();
	while ($row = $result->fetchArray()) {
        array_push($ignored, $row);
    }

	$PageTitle = "Podobo - Ignored Tags";
	$InTags = true;

	function customPageHeader(){?>
		<script>
			$(document).ready(function()
			{				
				var HeaderButton = document.getElementById("tools");
				HeaderButton.className = "w3-bar-item w3-button w3-theme-l1";
			});
		</script>
		<style type="text/css" media="screen">
			div {
                display: block;
            }
		</style>
	<?php }

	include_once('../header.php');
	
	$db = null;
			
			if(!empty($ignored)){
                echo "<div id='tagmapdiv' class='w3-center'>\r\n";
                echo "<p><strong>Ignored Tags</strong><p>-</p>";

                foreach($ignored as $tagg){
                    echo "<div id=" . json_encode($tagg[0] . "|" . $tagg[1]) . ">";
                    echo "<p>ID: <a href='" . GetBooruLink($tagg[1]) . $tagg[0] . "'> " . $tagg[0] . " " . GetBooruSource($tagg[1]) . "</a>";
					echo "<input type='Button' value='x' onclick='UnignoreTag(" . json_encode($tagg[0]) . ", " . $tagg[1] . ")' />";
                    echo "</p></div>";
                }
				
			    echo "</div>\r\n";
            }
			
			function GetBooruLink($booru_source)
			{
				switch($booru_source)
				{
					case 0:
						return "https://danbooru.donmai.us/posts?tags=";
						break;
					case 1:
						return "https://e621.net/posts?tags=";
						break;
					case 2:
						return "https://rule34.xxx/index.php?page=post&s=list&tags=";
						break;
					case 3:
						return "https://gelbooru.com/index.php?page=post&s=list&tags=";
						break;
					case 4:
						return "https://realbooru.com/index.php?page=post&s=list&tags=";
						break;
					case 5:
						return "https://chan.sankakucomplex.com/wiki/show?title=";
						break;
					default:
						return "https://www.google.com/search?q=";
						break;
				}
			}
			
			function GetBooruSource($booru_source)
			{
				switch($booru_source)
				{
					case 0:
						return "(Danbooru)";
						break;
					case 1:
						return "(e621)";
						break;
					case 2:
						return "(rule34.xxx)";
						break;
					case 3:
						return "(gelbooru)";
						break;
					case 4:
						return "(realbooru)";
						break;
					case 5:
						return "(Sankaku Complex)";
						break;
					case 6:
						return "(Hydrus PTR)";
						break;
					default:
						return "(google)";
						break;
				}
			}
		?>
	</body>
	<script type="text/javascript">
		
		$(document).ready(function()
		{
			
		});

		function UnignoreTag(boorutag, bs)
		{
			$.ajax({
				url: 'UnignoreTagAjax.php?boorutag=' + boorutag + '&bs=' + bs,
				type: 'get',
				dataType: 'JSON',
				success: function(response){
					if(response.length > 0)
					{							
						if(response == "Error"){
							console.log("Error with Remove Unignore Tag");
						}	
						else{
							console.log(response);
							var rem = document.getElementById(response);
							rem.innerHTML = "<del style='color:red;'>" + rem.innerHTML + "</del>";
						}				
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