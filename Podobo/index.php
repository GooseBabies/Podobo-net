<?php
	session_start();
	
	//$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	//$db = new SQLite3("Y:\\Database\\nevada.db");
	$db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
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

	$PageTitle = "Podobo";

	include_once('header.php');

    $db = null;

	echo "<div>";
	echo "<form class='index-search' action='Posts.php' method='GET'>";
	echo "<input type='text' id='tag-input' oninput='TagSuggestions(this.value)' name='search' value='' data-multiple/>";
	echo "<input type='submit' hidden />";
	echo "</form>";
	echo "</div>";

?>
<script type="text/javascript">
	var awesomplete;
	var input;
	$(document).ready(function()
	{
		input = document.getElementById("tag-input");
		awesomplete = new Awesomplete(input, { sort: false, tabSelect: true , filter: function(text, input) {
				//console.log(input.match(/[^ ]*$/));
				//return Awesomplete.FILTER_CONTAINS(text.value, input.match(/[^ ]*$/)[0]); 
				return Awesomplete.FILTER_CONTAINS(text.value, input.match(/[^( |\|\||\~|\!)]*$/)[0]);
			},

			item: function(text, input) {
				//console.log(input.match(/[^ ]*$/)[0]);
				//return Awesomplete.ITEM(text, input.match(/[^ ]*$/)[0]);
				return Awesomplete.ITEM(text, input.match(/[^( |\|\||\~|\!)]*$/)[0]);
			},

			replace: function(text) {						
				//var before = this.input.value.match(/^.+ \s*|/)[0];
				//var before = this.input.value.match(/^.+( |\|\|\!|\~)\s*|/)[0]; //matches everything before the last space or || ^.+( |\|\||\!|\~)\s*|^(\~|\!)
				var before = this.input.value.match(/^.+( |\|\||\!|\~)\s*|^(\~|\!)|/)[0];  //matches everything before and including the last space, ~, !, or ||
				console.log(before);
				this.input.value = before + text.value;
		}  } );
	});			
	
	function TagSuggestions(data)
	{
		input.value = input.value.replace(/ $/g, "_");
		data = data.replace(/ $/g, "_");				
		
		input.value = input.value.replace(/__/g, " ");
		data = data.replace(/__/g, " ");
		
		//data = data.replace(/^.+ \s*|/g, "");
		data = data.replace(/^.+( |\|\|)\s*|/g, "");
		data = data.replace(/^(\!|\~)/g, "");
		
		console.log(data);
		
		$.ajax({
			url: 'Tags/TagSuggestionsAjax.php?txt=' + data,
			type: 'get',
			dataType: 'JSON',
			success: function(response){	
				//console.log(response);					
				awesomplete.list = response;
			},
			error: function(xhr, ajaxOptions, thrownError)
			{
				console.log(xhr.responseText);
			}
		});
	}
</script>
</body>
</html>
