
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1"/>
	    <title>Piaz-Online</title>
	    <link rel="stylesheet" type="text/css" href="../styleshits/happy.css" />		
		<link rel="stylesheet" href="../w3.css" />

	</head>
	<body>
		<div class="w3-bar w3-theme w3-left-align w3-medium container_header">
			  <a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="../main.php">Posts</a>
			  <a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="../viewer.php?id=32554">Random</a>
			  <a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="../BooruTag.php">Tag Processing</a>			  
			  <a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="../slideshow.php">Slideshow</a>
			  <a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="../tags.php">Tags</a>
		</div>
		<?php
		echo "<ul>";
		$dir = "W:\One Piece\Manga\One Piece Manga Colored";
		$dh = opendir($dir);
		while (false !== ($entry = readdir($dh))) {
			if ($entry != "." && $entry != "..") {
				echo "<li><a href='/one-piece/chapter.php?num=" . $entry . "'>" . $entry . "</a></li>";
			}
		}
		closedir($dh);
		//echo "<p>boobies</p>";
		echo "</ul>";
		?>
</body>
</html>
