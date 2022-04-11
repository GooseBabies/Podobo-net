<?php
	session_start();
	//$TagOrder=array(14,4,0,1,2,3,8,6,7,9,10,12,13,11,16,5,15);
	//$TagCategoryTitle=array("General", "IP/Series", "Individual", "", "Artist", "Studio/Network", "Sex", "Afilliation/Group", "Race/Species/Ethnicity", "Body Part", "Clothing/Accessory", "Position", "Setting", "Action", "Meta", "Title", "Release Date");
    $ignored = [];

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

    $sql = $db->prepare("select distinct booru_tag, booru_source from booru_proc where ignored = 1 order by booru_tag");
	$result = $sql->execute();
	while ($row = $result->fetchArray()) {
        array_push($ignored, $row);
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
	    <title>Podobo - Ignored Tags</title>
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