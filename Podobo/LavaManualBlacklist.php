<?php
	//session_start();
	
	//$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	//$db = new SQLite3("Y:\\Database\\nevada.db");	
    $dupefiles = [];   

	$PageTitle = "LavaPool Blacklist";
	
	function customPageHeader(){?>
		<script>
			$(document).ready(function()
			{				
				var HeaderButton = document.getElementById("tools");
				HeaderButton.className = "w3-bar-item w3-button w3-theme-l1";
			});
		</script>
	<?php }

	include_once('Lavaheader.php');
    $db = null;

    $db = new SQLite3("Y:\\LavaPool\\Iowa.db");
	$db->busyTimeout(100);

	?>

	<div id="subheader" class="w3-bar w3-theme-l1 w3-left-align w3-small container_subheader">
		<a id="booru-tag" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>LavaIntro.php">LavaPool</a>                        
	</div>

	<?php

    echo "<div class='w3-center'>";

    echo "<label><b>Blacklist: </b></label><input type='text' id='blacklist-item' />";
	echo "<div class='w3-center'>";
				echo "<select name='source' id='source'>";
					echo "<option>Danbooru</option>";
					echo "<option>e621</option>";
					echo "<option>rule34.xxx</option>";
					echo "<option>unknown</option>";
				echo "</select>";
			echo "</div>";

    echo "</div>";

    echo "<hr />";

    echo "<div class='w3-center'><button class='dupe-button' id='submit' onclick='Submit()'>Add to Blacklist</button></div>";

    $db = null;
?>



</body>
<script type="text/javascript">	
		$(document).ready(function()
		{
            
		});

        function Submit(){
            var input = document.getElementById('blacklist-item');
            var item = input.value;

			var source_input = document.getElementById('source');
            var source = source_input.options[source_input.selectedIndex].text;

			var submit = document.getElementById('submit');
            $.ajax({
					url: 'LavaManualBlacklistAjax.php?item=' + encodeURIComponent(item) + "&source=" + source,
					type: 'get',
					dataType: 'JSON',
					success: function(response){
						submit.innerHTML = response;
					},
					error: function(xhr, ajaxOptions, thrownError)
					{
						console.log(xhr.responseText);
					}
				});
        }
	</script>
</html>
