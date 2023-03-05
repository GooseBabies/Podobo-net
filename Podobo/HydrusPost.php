<?php
	session_start();
	
	$TagColors=array("#FFFFFF", "#6495ED", "#FF4500", "#DAA520", "#FF8C00", "#7FFFD4", "#BA55D3", "#228B22", "#8A2BE2", "#FFFFFF", "#FFFFFF", "#FFFFFF", "#FFFFFF", "#FFFFFF", "#DAA520", "#8FBC8F", "#FFFFFF");
	$TagOrder=array(13,3,0,15,1,2,7,5,6,8,9,11,12,10,16,4,14);
	$TagCategoryTitle=array("General", "IP/Series", "Individual", "Rating", "Artist", "Studio/Network", "Sex", "Afilliation/Group", "Race/Species/Ethnicity", "Body Part", "Clothing/Accessory", "Position", "Setting", "Action", "Meta", "Title", "Release Date");
	
	//$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");	
	//$db = new SQLite3("Y:\\Database\\nevada.db");	
	$db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
	
	if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = 1; };

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
	
	$PageTitle = "Podobo - " . $id;

	function customPageHeader(){?>
		<script>
			$(document).ready(function()
			{				
				var HeaderButton = document.getElementById("posts");
				HeaderButton.className = "w3-bar-item w3-button w3-theme-l1";
			});
		</script>
		</style>
	<?php }

	include_once('header.php');
?>
            <main class="row">
                <div class="col-2 w3-theme main-left">
		
			<?php
			
			echo "<div id='tagarea' class='tagarea' >";
			echo "<ul id='tag-list'>";
			
			echo "</ul>";
			echo "</div>";
            echo "</div>";
			
			$db = null;

			echo "<div id='media-area' class='col-8'>";

			echo "</div>";
			echo "</main>";
		?>

	<script>
		$(document).ready(function()
		{
            mediaarea = document.getElementById('media-area');
            taglist = document.getElementById('tag-list');


			$.ajax({
                    url: 'https://67.253.187.197:45869/get_files/file_metadata?file_id=' + <?php echo $id; ?>,
					type: 'get',
                    headers: {"Hydrus-Client-API-Access-Key" : "832e84084dab1249084d5f7fc1823ab6e58851c992e733133d4b68d3693492b9"},
					dataType: 'JSON',
					success: function(response){	
                        //console.log(response.metadata[0].service_names_to_statuses_to_tags["all known tags"]);
						//console.log(response.metadata[0].service_names_to_statuses_to_tags["all known tags"][0]);
                        //console.log(response.metadata[0].ext);			
						for (let i = 0; i < response.metadata[0].service_names_to_statuses_to_tags["all known tags"][0].length; i++) {
                            //console.log(response.metadata[0].service_names_to_statuses_to_tags["all known tags"][0][i]);
                            DisplayTags(response.metadata[0].service_names_to_statuses_to_tags["all known tags"][0][i]);
                        }
                        if(response.metadata[0].ext == ".mp4" || response.metadata[0].ext == ".webm"){
                            DisplayVideo();
                        }
                        else{
                            DisplayImage();
                        }
					},
					error: function(xhr, ajaxOptions, thrownError)
					{
						console.log(xhr.responseText);
					}
				});
		});

        function DisplayImage(){
            mediaarea.innerHTML = "<img class='center-fit' src='https://67.253.187.197:45869/get_files/file?file_id=<?php echo $id; ?>&Hydrus-Client-API-Access-Key=832e84084dab1249084d5f7fc1823ab6e58851c992e733133d4b68d3693492b9' />";
        }

        function DisplayVideo(){
            mediaarea.innerHTML = "<video class='center-fit' loop controls src='https://67.253.187.197:45869/get_files/file?file_id=<?php echo $id; ?>&Hydrus-Client-API-Access-Key=832e84084dab1249084d5f7fc1823ab6e58851c992e733133d4b68d3693492b9'></video>";
        }

        function DisplayTags(tag){
            //console.log(tag);
            taglist.innerHTML += "<li>" + tag + "</li>";
        }
	</script>		
	</body>
</html>