<?php
	session_start();
	//$TagOrder=array(14,4,0,1,2,3,8,6,7,9,10,12,13,11,16,5,15);
	//$TagCategoryTitle=array("General", "IP/Series", "Individual", "", "Artist", "Studio/Network", "Sex", "Afilliation/Group", "Race/Species/Ethnicity", "Body Part", "Clothing/Accessory", "Position", "Setting", "Action", "Meta", "Title", "Release Date");
    $actresses = [];

	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");	

	$files = [];
	if(isset($_SESSION["filtered_data"]) && count($_SESSION["filtered_data"]) > 0){
		$files = $_SESSION["filtered_data"];
		$idcount = count($files)-1;
		$filtered = true;
		//$index = searchForId($id, $files);
	}
	else{
		if(isset($_SESSION["image_data"])){
			$files = $_SESSION["image_data"];				
			$filtered = false;
		}
		else{		
			$result = $db->query("SELECT ID, name, overall_rating, video, sound, tag_list FROM files order by id desc");				
			while ($row = $result->fetchArray()) {
				array_push($files, $row);
			}
			$_SESSION["image_data"] = $files;
			$filtered = false;
		}
		$idcount = count($files)-1;
	}

	$r = rand(0, $idcount);
	
	//tag Map

    $sql = $db->prepare("select tagid, tag_name, tag_count from parents join tags on tags.tagid = parents.child where parent = 10093 order by tag_count COLLATE NOCASE desc");
	$result = $sql->execute();
	while ($row = $result->fetchArray()) {
        array_push($actresses, $row);
    }
	
	$db = null;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html" charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="../../style/PodoboStyle.css" />
		<link rel="stylesheet" type="text/css" href="../../style/w3.css" />
		<link rel="stylesheet" href="../../awesomplete/awesomplete.css">
		<link rel="icon" type="image/x-icon" href="../../imgs/favicon.ico">
	    <title>Podobo - Actresses</title>
		<!-- <script type = "text/javascript" src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script> -->
		<script type = "text/javascript" src = "../../js/jquery-3.6.0.min.js"></script>
        <style type="text/css" media="screen">
			div {
                display: block;
            }
		</style>

	</head>
	<body>
		<div class="w3-bar w3-theme w3-left-align w3-medium container_header">
			<a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="../Posts.php">Posts</a>		
			<a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="TagList.php">Tags</a>
			<a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="../Wiki.php">Wiki</a>
			<a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="../Slideshow.php">Slideshow</a>
			<a class="w3-bar-item w3-button w3-theme-l1" href="Tools.php">Tools</a>
		</div>
		<div class="w3-bar w3-theme-l1 w3-left-align w3-small container_subheader">
			<a class="w3-bar-item w3-button w3-theme-l1" href="../BooruTag.php">Booru Tag</a>		
			<a class="w3-bar-item w3-button w3-theme-l1" href="IgnoredTagList.php">Ignored Tag List</a>
			<a class="w3-bar-item w3-button w3-theme-l1" href="../Dupes.php">Dupes</a>
		</div>
		<!--<main class="row">-->
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