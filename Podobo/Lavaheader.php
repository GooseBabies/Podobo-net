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

	$db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
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
			$message = "Importing new";
			$state = 3;
			$sql = "SELECT import_progress FROM status where id=1";
			$result = $db->query($sql);
			$progress = " - " . $result->fetchArray()[0] ?? '';
			break;
		case 4:
			$message = "IQDB";
			$state = 2;
			$sql = "SELECT iqdb_progress FROM status where id=1";
			$result = $db->query($sql);
			$iqdb = $result->fetchArray()[0] ?? '';
			$progress = " - " . $iqdb . " / 300";
			break;
		case 5:
			$message = "Dupes";
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
			$message = "Retro Parents";
			$state = 3;
			$sql = "SELECT closing_progress FROM status where id=1";
			$result = $db->query($sql);
			$progress = " - " . $result->fetchArray()[0] ?? '';
			break;
		case 8:
			$message = "Retro Siblings";
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
			$message = "Broken Tags";
			$state = 3;
			$sql = "SELECT closing_progress FROM status where id=1";
			$result = $db->query($sql);
			$progress = " - " . $result->fetchArray()[0] ?? '';
			break;
		case 11:
			$message = "Duped Sources";
			$state = 3;
			$sql = "SELECT closing_progress FROM status where id=1";
			$result = $db->query($sql);
			$progress = " - " . $result->fetchArray()[0] ?? '';
			break;
		case 12:
			$message = "Missing P-hashes";
			$state = 3;
			$sql = "SELECT closing_progress FROM status where id=1";
			$result = $db->query($sql);
			$progress = " - " . $result->fetchArray()[0] ?? '';
			break;
		case 13:
			$message = "Deleted Files";
			$state = 3;
			$sql = "SELECT closing_progress FROM status where id=1";
			$result = $db->query($sql);
			$progress = " - " . $result->fetchArray()[0] ?? '';
			break;
		case 14:
			$message = "Tag Counts";
			$state = 3;
			$sql = "SELECT closing_progress FROM status where id=1";
			$result = $db->query($sql);
			$progress = " - " . $result->fetchArray()[0] ?? '';
			break;
		case 15:
			$message = "Broken Siblings";
			$state = 3;
			$sql = "SELECT closing_progress FROM status where id=1";
			$result = $db->query($sql);
			$progress = " - " . $result->fetchArray()[0] ?? '';
			break;
		case 16:
			$message = "Broken Parents";
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
			$message = "DB Backup";
			$state = 3;
			$sql = "SELECT closing_progress FROM status where id=1";
			$result = $db->query($sql);
			$progress = " - " . $result->fetchArray()[0] ?? '';
			break;
		case 19:
			$message = "Rotate CW";
			$state = 3;
			$sql = "SELECT closing_progress FROM status where id=1";
			$result = $db->query($sql);
			$progress = " - " . $result->fetchArray()[0] ?? '';
			break;
		case 19:
			$message = "Rotate CCW";
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
				<a id="home" class="w3-bar-item home-icon w3-button w3-hide-small w3-hover-blue-grey" href="<?= isset($InTags) ? "../../" : "../"?>Podobo/"><svg xmlns="http://www.w3.org/2000/svg" focusable="false" height="18" width="18" viewBox="0 0 576 512"><path fill="#FFFFFF" d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg><a>
                <a id="posts" class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="<?= isset($InTags) ? "../" : ""?>Posts.php">Posts</a>		
				<a id="tags" class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="<?= isset($InTags) ? "" : "Tags/"?>TagList.php">Tags</a>
				<a id="wiki" class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="<?= isset($InTags) ? "../" : ""?>wiki/">Wiki</a>				
				<a id="tools" class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="<?= isset($InTags) ? "../" : ""?>Tools.php">Tools</a>
				<a class="w3-bar-item">|</a>
				<a class="w3-bar-item state-indicator"><svg xmlns="http://www.w3.org/2000/svg" id="state-indicator" viewBox="0 0 512 512" focusable="false" height="18" width="18" class="fa-solid fa-circle"><path d='M256 512c141.4 0 256-114.6 256-256S397.4 0 256 0S0 114.6 0 256S114.6 512 256 512z'/></svg><a class="w3-bar-item state-message"><?php echo $message . $progress; ?></a>

            </div>			
			