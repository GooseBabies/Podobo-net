<?php
	session_start();
	//$TagOrder=array(14,4,0,1,2,3,8,6,7,9,10,12,13,11,16,5,15);
	//$TagCategoryTitle=array("General", "IP/Series", "Individual", "", "Artist", "Studio/Network", "Sex", "Afilliation/Group", "Race/Species/Ethnicity", "Body Part", "Clothing/Accessory", "Position", "Setting", "Action", "Meta", "Title", "Release Date");

	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");	
    
    if(isset($_GET["tagid"])) { $tagid = $_GET["tagid"]; } else { $tagid = 1; };

	$sql = $db->prepare("select tag_name, category from tags where tagid = :tagid");
    $sql->bindValue(':tagid', $tagid, SQLITE3_INTEGER);
	$tag = $sql->execute()->fetchArray();
	
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
	    <title>Podobo - Add Alias</title>
		<!-- <script type = "text/javascript" src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script> -->
		<script type = "text/javascript" src = "../../js/jquery-3.6.0.min.js"></script>
		<script type = "text/javascript" src = "../../awesomplete/awesomplete.js"></script>

	</head>
	<body>
		<div class="w3-bar w3-theme w3-left-align w3-medium container_header">
			<a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="../Posts.php">Posts</a>		
			<a class="w3-bar-item w3-button w3-theme-l1" href="TagList.php">Tags</a>
			<a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="../Wiki.php">Wiki</a>
			<a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="../Slideshow.php">Slideshow</a>
			<a class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey" href="../Tools.php">Tools</a>
		</div>
		<div class="w3-bar w3-theme-l1 w3-left-align w3-small container_subheader">
			<a class="w3-bar-item w3-button w3-theme-l1" href="TagList.php">Tag List</a>
		</div>
		<!--<main class="row">-->
		<?php
			
			echo "<hr />";
			
			echo "<div class='w3-center'>";
			echo "<p>" . $tag[0] . "</p>";
			echo "<p> - </p>";
			echo "<input type='text' id='alias' oninput='TagSuggestions(this.value)' onpaste='textPaste()' />";
			
			echo "<hr />";

			echo "<div class='w3-center'>";
				echo "<input class='w3-center' id='submitbutton' type='button' value='Submit' onclick='SubmitAlias()'/>";	
			echo "</div>";

			echo "<div id='response' class='w3-center'>";
			
			echo "</div>";
			
		?>
	</body>
	<script type="text/javascript">
		$(document).ready(function()
		{
			input = document.getElementById("alias");
			input.value = "";
			awesomplete = new Awesomplete(input, { sort: false } );
			input.focus();

			submit = document.getElementById("submitbutton");
			
			resdiv = document.getElementById("response");
		});
		
		function TagSuggestions(data)
		{
			input.value = input.value.replace(/ $/g, "_");	
			data = data.replace(/ $/g, "_");
			
			$.ajax({
				url: 'TagSuggestionsAjax.php?txt=' + data,
				type: 'get',
				dataType: 'JSON',
				success: function(response){					
					awesomplete.list = response;
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					console.log(xhr.responseText);
				}
			});
		}
		
		function SubmitAlias()
		{
			submit.value = "Submitted!"
			var tagid = <?php echo $tagid; ?>;
			var aliastag = input.value;
            cat = <?php echo $tag[1]; ?>;
			
			$.ajax({
				url: 'AddAliasAjax.php?tagid=' + tagid + '&alias=' + aliastag + '&cat=' + cat,
				type: 'get',
				dataType: 'JSON',
				success: function(response){
					if(response.length > 0)
					{	
						resdiv.innerHTML += "<hr />";
						resdiv.innerHTML += "<div><p>" + response + "</p></div>";						
					}
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					//console.log(url);
					console.log(xhr.responseText);
					resdiv.innerHTML += "<hr />";
					resdiv.innerHTML += "<div><p style='color:red;'>Error</p></div>";
				}
			});

			input.value = "";
			submit.value = "Submit";
			input.focus();
		}
		
		function textPaste(){
			setTimeout(function(){
				console.log(input.value);
				input.value = input.value.replace(/ /g, "_");
			}, 0);
		}
	</script>
</html>