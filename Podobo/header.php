<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<?php	

	if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){ 
		if(!password_verify("#3MH7BtoJ3s&Rj8$", $_COOKIE["pass"])){
			header("location: login.php");
			exit;
		}
		else{
			$_SESSION["loggedin"] = true;
		}
	}

	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	$db->busyTimeout(100);

	$sql = "SELECT message_id FROM status where id=1";
	$result = $db->query($sql);
	$status = $result->fetchArray()[0] ?? 0;

	switch($status) {
		case 0:
			$message = "Podobo Stopped";
			$state = 0;
			$progress = "";
			break;
		case 1:
			$message = "Idle";
			$state = 1;
			$progress = "";
			break;
		case 2:
			$message = "Initializing";
			$state = 3;
			$progress = "";
			break;
		case 3:
			$message = "Importing new Images";
			$state = 3;
			$sql = "SELECT import_progress FROM status where id=1";
			$result = $db->query($sql);
			$progress = " - " . $result->fetchArray()[0] ?? '';
			break;
		case 4:
			$message = "IQDB in Progress";
			$state = 2;
			$sql = "SELECT iqdb_progress FROM status where id=1";
			$result = $db->query($sql);
			$iqdb = $result->fetchArray()[0] ?? '';
			$progress = " - " . $iqdb . " / 300";
			break;
		case 5:
			$message = "Processing Dupes";
			$state = 3;
			$sql = "SELECT dupes_progress FROM status where id=1";
			$result = $db->query($sql);
			$progress = " - " . $result->fetchArray()[0] ?? '';
			break;
		case 6:
			$message = "Auto Tagging";
			$state = 2;
			$sql = "SELECT auto_booru_progress FROM status where id=1";
			$result = $db->query($sql);
			$auto_booru = $result->fetchArray()[0] ?? '';
			$progress = " - " . $auto_booru . " / 300";
			break;
		case 7:
			$message = "Retro Parents Work";
			$state = 3;
			$sql = "SELECT closing_progress FROM status where id=1";
			$result = $db->query($sql);
			$progress = " - " . $result->fetchArray()[0] ?? '';
			break;
		case 8:
			$message = "Retro Siblings Work";
			$state = 3;
			$sql = "SELECT closing_progress FROM status where id=1";
			$result = $db->query($sql);
			$progress = " - " . $result->fetchArray()[0] ?? '';
			break;
		case 9:
			$message = "Adding to Trash";
			$state = 3;
			$sql = "SELECT closing_progress FROM status where id=1";
			$result = $db->query($sql);
			$progress = " - " . $result->fetchArray()[0] ?? '';
			break;
		case 10:
			$message = "Removing Broken Tags";
			$state = 3;
			$sql = "SELECT closing_progress FROM status where id=1";
			$result = $db->query($sql);
			$progress = " - " . $result->fetchArray()[0] ?? '';
			break;
		case 11:
			$message = "Removing Duplicate Sources";
			$state = 3;
			$sql = "SELECT closing_progress FROM status where id=1";
			$result = $db->query($sql);
			$progress = " - " . $result->fetchArray()[0] ?? '';
			break;
		case 12:
			$message = "Calculating Missing P-hashes";
			$state = 3;
			$sql = "SELECT closing_progress FROM status where id=1";
			$result = $db->query($sql);
			$progress = " - " . $result->fetchArray()[0] ?? '';
			break;
		case 13:
			$message = "Checking for Deleted Files";
			$state = 3;
			$sql = "SELECT closing_progress FROM status where id=1";
			$result = $db->query($sql);
			$progress = " - " . $result->fetchArray()[0] ?? '';
			break;
		case 14:
			$message = "Recalculating Tag Counts";
			$state = 3;
			$sql = "SELECT closing_progress FROM status where id=1";
			$result = $db->query($sql);
			$progress = " - " . $result->fetchArray()[0] ?? '';
			break;
		case 15:
			$message = "Removing Broken Siblings";
			$state = 3;
			$sql = "SELECT closing_progress FROM status where id=1";
			$result = $db->query($sql);
			$progress = " - " . $result->fetchArray()[0] ?? '';
			break;
		case 16:
			$message = "Removing Broken Parents";
			$state = 3;
			$sql = "SELECT closing_progress FROM status where id=1";
			$result = $db->query($sql);
			$progress = " - " . $result->fetchArray()[0] ?? '';
			break;
		case 17:
			$message = "Emptying Trash";
			$state = 3;
			$sql = "SELECT closing_progress FROM status where id=1";
			$result = $db->query($sql);
			$progress = " - " . $result->fetchArray()[0] ?? '';
			break;
		case 18:
			$message = "Backing Up Database";
			$state = 3;
			$sql = "SELECT closing_progress FROM status where id=1";
			$result = $db->query($sql);
			$progress = " - " . $result->fetchArray()[0] ?? '';
			break;
		case 19:
			$message = "Rotating Images CW";
			$state = 3;
			$sql = "SELECT closing_progress FROM status where id=1";
			$result = $db->query($sql);
			$progress = " - " . $result->fetchArray()[0] ?? '';
			break;
		case 19:
			$message = "Rotating Images CCW";
			$state = 3;
			$sql = "SELECT closing_progress FROM status where id=1";
			$result = $db->query($sql);
			$progress = " - " . $result->fetchArray()[0] ?? '';
			break;
			
		default:
			$message = "Closing Job";
			$state = 3;
			$sql = "SELECT closing_progress FROM status where id=1";
			$result = $db->query($sql);
			$progress = " - " . $result->fetchArray()[0] ?? '';
			break;
	}

	$sql = "SELECT iqdb_progress FROM status where id=1";
	$result = $db->query($sql);
	$iqdbcount = $result->fetchArray()[0] ?? '';
			
?>
	<head>
		<meta http-equiv="Content-Type" content="text/html" charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
	    <title><?= isset($PageTitle) ? $PageTitle : "Default Title"?></title>
	    <link rel="stylesheet" type="text/css" href="<?= isset($InTags) ? "../" : ""?>../style/PodoboStyle.css" />		
		<link rel="stylesheet" href="<?= isset($InTags) ? "../" : ""?>../style/w3.css" />
		<link rel="stylesheet" href="<?= isset($InTags) ? "../" : ""?>../awesomplete/awesomplete.css">
		<link rel="icon" type="image/x-icon" href="<?= isset($InTags) ? "../" : ""?>../imgs/favicon.ico">
		<script type = "text/javascript" src = "<?= isset($InTags) ? "../" : ""?>../js/jquery-3.6.0.min.js"></script>
		<script type = "text/javascript" src = "<?= isset($InTags) ? "../" : ""?>../js/detectmobilebrowser.js"></script>
		<script type = "text/javascript" src="<?= isset($InTags) ? "../" : ""?>../awesomplete/awesomplete.js"></script>
		<script src="<?= isset($InTags) ? "../" : ""?>../style/releases/v5.15.4/js/all.min.js" crossorigin="anonymous"></script>
		<base target="_parent" />
        <?php if (function_exists('customPageHeader')){
            customPageHeader();
        }?>
        <script>
            function Posts_Enter() {
                document.getElementById("recent").style.display = "block";
                document.getElementById("random").style.display = "block";
                document.getElementById("oldest").style.display = "block";
                document.getElementById("videos").style.display = "block";
                document.getElementById("studio-videos").style.display = "block";
                document.getElementById("booru-tag").style.display = "none";
                document.getElementById("ignored-tags").style.display = "none";
                document.getElementById("dupes").style.display = "none";
				document.getElementById("command").style.display = "block"; 
            }

            function General_Enter() {
                document.getElementById("recent").style.display = "none";
                document.getElementById("random").style.display = "block";
                document.getElementById("oldest").style.display = "none";
                document.getElementById("videos").style.display = "none";
                document.getElementById("studio-videos").style.display = "block";
                document.getElementById("booru-tag").style.display = "block";
                document.getElementById("ignored-tags").style.display = "none";
                document.getElementById("dupes").style.display = "block";
				document.getElementById("command").style.display = "block"; 
            }

            function Tools_Enter() {
                document.getElementById("recent").style.display = "none";
                document.getElementById("random").style.display = "none";
                document.getElementById("oldest").style.display = "none";
                document.getElementById("videos").style.display = "none";
                document.getElementById("studio-videos").style.display = "none";
                document.getElementById("booru-tag").style.display = "block";
                document.getElementById("ignored-tags").style.display = "block";
                document.getElementById("dupes").style.display = "block";     
				document.getElementById("command").style.display = "block";            
            }			
        </script>
        <style type="text/css">
		#state-indicator {
            <?php
            switch($state){
                case 0:
                    echo "color: gray;";
                    break;
                case 1:
                    echo "color: green;";
                    break;
                case 2:
                    echo "color: yellow;";
                    break;
                case 3:
                    echo "color: red;";
                    break;
            }
			?>
		}
		</style>
	</head>
	<body>
            <div class="w3-bar w3-theme w3-left-align w3-medium container_header">
                <a id="posts" class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" onmouseenter="Posts_Enter()">Posts</a>		
				<a id="tags" class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="<?= isset($InTags) ? "" : "Tags/"?>TagList.php" onmouseenter="General_Enter()">Tags</a>
				<a id="wiki" class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="<?= isset($InTags) ? "../" : ""?>wiki/">Wiki</a>
				<a id="slideshow" class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="<?= isset($InTags) ? "../" : ""?>Slideshow.php">Slideshow</a>
				<a id="tools" class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" onmouseenter="Tools_Enter()">Tools</a>
				<a class="w3-bar-item w3-theme-l1"> | </a>
				<a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey"><i id="state-indicator" class="fa-solid fa-circle"></i></a>
				<a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey"><?php echo $message . $progress; ?></a>

            </div>
			<div id="subheader" class="w3-bar w3-theme-l1 w3-left-align w3-small container_subheader">
                <a id="recent" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>Posts.php">Recent</a>		
                <a id="random" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>Post.php?id=<?php echo $files[$r][0]; ?>">Random</a>
                <a id="oldest" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>Posts.php?page=<?php echo ceil($idcount/105) ?>">Oldest</a>
                <a id="videos" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>Posts.php?search=%24video">Videos</a>
                <a id="studio-videos" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>Posts.php?search=%24dur>600">Studio Videos</a>
                <a id="booru-tag" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>BooruTag.php">Booru Tag</a>
                <a id="ignored-tags" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "" : "Tags/"?>IgnoredTagList.php">Ignored Tag List</a>
                <a id="dupes" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>Dupes.php">Dupes</a>
				<a id="command" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>Command.php">Command</a>
                <a id="separator" class="w3-bar-item w3-theme-l1">|</a>
                <a id="edit-tags" class="w3-bar-item w3-button w3-theme-l1" onclick="showEdit()">Edit Tags</a>
				<?php if(isset($review) && $review == 1) { echo "<a id='mark-review' class='w3-bar-item w3-button w3-theme-l1' onclick='review()'>Mark Review</a>"; } else { echo ""; } ?>
				<?php if(isset($list_view)) { echo "<a id='list-view' class='w3-bar-item w3-button w3-theme-l1' onclick='ListView()'>List View</a>"; } else { echo ""; } ?>
                <a id="search-indicator" class="w3-bar-item w3-button w3-theme-l1" href=<?= isset($InTags) ? "../" : ""?><?php if($_SESSION["search"] != "") { echo "'Posts.php?search=" . $_SESSION["search"] . "'>Search: " . $_SESSION["search"]; } else { echo "'Posts.php'>Search: All"; }; ?></a>                        
            </div>
			