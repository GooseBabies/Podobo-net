<?php
	session_start();
	
	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");

	$files = [];
	if(isset($_SESSION["filtered_data"]) && count($_SESSION["filtered_data"]) > 0){
		$files = $_SESSION["filtered_data"];
		$idcount = count($files)-1;
		$filtered = true;
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

    $db = null;

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1"/>
	    <title>Podobo</title>
	    <link rel="stylesheet" type="text/css" href="../style/PodoboStyle.css" />		
		<link rel="stylesheet" href="../style/w3.css" />
		<link rel="icon" type="image/x-icon" href="../imgs/favicon.ico">

	</head>
	<body>
	<div class="w3-bar w3-theme w3-left-align w3-medium container_header">
		<a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="Posts.php">Posts</a>		
		<a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="Tags/TagList.php">Tags</a>
		<a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="Wiki.php">Wiki</a>
		<a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="Slideshow.php">Slideshow</a>
		<a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="Tools.php">Tools</a>
	</div>
</body>
</html>
