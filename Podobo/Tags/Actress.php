<?php
	session_start();
	//$TagOrder=array(14,4,0,1,2,3,8,6,7,9,10,12,13,11,16,5,15);
	//$TagCategoryTitle=array("General", "IP/Series", "Individual", "", "Artist", "Studio/Network", "Sex", "Afilliation/Group", "Race/Species/Ethnicity", "Body Part", "Clothing/Accessory", "Position", "Setting", "Action", "Meta", "Title", "Release Date");
    $actresses = [];

	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");	
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
	
	//tag Map

    $sql = $db->prepare("select tagid, tag_name, tag_count from parents join tags on tags.tagid = parents.child where parent = 10093 order by tag_count COLLATE NOCASE desc");
	$result = $sql->execute();
	while ($row = $result->fetchArray()) {
        array_push($actresses, $row);
    }

	$PageTitle = "Podobo - Actresses";
	$InTags = true;
	
	function customPageHeader(){?>
		<script>
			$(document).ready(function()
			{				
				var HeaderButton = document.getElementById("tags");
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
?>
		<?php
			
			if(!empty($actresses)){
                echo "<div id='actressesdiv' class='w3-center'>\r\n";
                echo "<p><strong>Actresses</strong><p>-</p>";

                foreach($actresses as $actress){
                    echo "<div id='" . $actress[0] . "'>";
					echo "<img src='../../actress/" . $actress[1] . ".jpg' />"; //_thumb3.jpg
                    echo "<p>ID: <a href='Tag.php?id=" . $actress[0] . "'> " . $actress[0] . "</a>: <a href='Posts.php?search=" . str_replace(" ", "_", $actress[1]) . "'> " . $actress[1] . "</a> (" . $actress[2] . ")</p>";
					//echo " (" . $actress[2] . ")</p>";
                    echo "</p></div>";
                }
				
			    echo "</div>\r\n";
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
				url: 'UnignoreTag.php?boorutag=' + boorutag + '&bs=' + bs,
				type: 'get',
				dataType: 'JSON',
				success: function(response){
					if(response.length > 0)
					{							
						if(response == "Error"){
							console.log("Error with Remove Parent");
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