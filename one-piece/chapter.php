<?php

if(isset($_GET["num"])) { $num = $_GET["num"]; } else { $num = 0001; }
//if(isset($_GET["page"])) { $page = $_GET["page"]; } else { $page = 1; }

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1"/>
	    <title>One Piece</title>
	    <link rel="stylesheet" type="text/css" href="../styleshits/happy.css" />		
		<link rel="stylesheet" href="../w3.css" />
        <style type="text/css" media="screen">
			img{
				margin: 10px;
			}
		</style>

	</head>
	<body>
		<div class="w3-bar w3-theme w3-left-align w3-medium container_header">
			  <a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="../main.php">Posts</a>
			  <a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="../viewer.php?id=11054">Random</a>
			  <a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="../BooruTag.php">Tag Processing</a>			  
			  <a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="../slideshow.php">Slideshow</a>
			  <a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="../tags.php">Tags</a>
		</div>
		<?php
        echo "<main class='row'>";
        echo "<div class='col-10'>";
		//echo "<ul class='center-fit'>";
		$dir = "W:\One Piece\Manga\One Piece Manga Colored" . "\\" . $num;
        $root = "/manga" . "//" . $num . "//";
		$dh = opendir($dir);
		while (false !== ($entry = readdir($dh))) {
			if ($entry != "." && $entry != "..") {
				echo "<div><img class='center-fit' src='" . $root . $entry . "'/></div>";
			}
		}
		closedir($dh);		
        echo "<div class='container_footer' align='center'>";
        echo "<a href='chapter.php?num=" . str_pad($num+1, 4, '0', STR_PAD_LEFT) . "'>Next</a>";
		echo "</div>";
        echo "</main>";
		?>
</body>
</html>
