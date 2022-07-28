<?php
	session_start();

	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");	
	$db->busyTimeout(100);
	$files = [];

	try{
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
					
		$PageTitle = "Game - Generate Harem";
		$InTags = true;
	
		function customPageHeader(){?>
			<script>
				$(document).ready(function()
				{				
					var HeaderButton = document.getElementById("tools");
					HeaderButton.className = "w3-bar-item w3-button w3-theme-l1";
				});
			</script>
		<?php }
	
		include_once('../header.php');
	
		$db = null;

		echo "<div>";
			echo "<div class='w3-center'>";
				echo "<label>Female ID:</label>";
			echo "</div>";
			echo "<div class='w3-center'>";
				echo "<input type='text' id='tag-id' oninput='GetName(this.value)' />";
				echo "<input type='button' value='Generate' onclick='Generate()' />";
			echo "</div>";
		echo "</div>";

		echo "<div id='tag-name' class='w3-center'>";
		
		echo "</div>";

		echo "<div id='accept' class='w3-center'>";
			echo "<input type='button' value='Accept Harem' onclick='Accept()' />";
		echo "</div>";
		
		echo "<hr />";

		echo "<div id='response' class='w3-center'>";
		
		echo "</div>";
	}catch(exception $e){

	}	
?>
	</body>
	<script type="text/javascript">
		var tier1 = [-1, -1, -1, -1, -1];
		var tier2 = [-1, -1, -1, -1, -1];
		var tier3 = [-1, -1, -1, -1, -1];
		var tier4 = [-1, -1, -1, -1, -1];
		$(document).ready(function()
		{
			input = document.getElementById("tag-id");	
			namediv = document.getElementById("tag-name");
			resdiv = document.getElementById("response");	
		});

		function Generate()
		{
			tagid = input.value;
			$.ajax({
				url: 'GenSummonAjax.php?tagid=' + tagid,
				type: 'get',
				dataType: 'JSON',
				success: function(response){
					resdiv.innerHTML = "";
					for (let i = 0; i < response.length; i++) {
						eval("tier" + (i + 1) + " = response[" + i + "];");
						resdiv.innerHTML += "<div><p>Tier " + (i + 1) + " - file: " + response[i][0] + " or: " + response[i][1] + " mr: " + response[i][2] + " ir: " + response[i][3] + " sr: " + response[i][4] + "</p></div>";
					}
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					console.log(xhr.responseText);
				}
			});
		}

		function GetName(data)
		{
			$.ajax({
				url: 'GetNameAjax.php?tagid=' + data,
				type: 'get',
				dataType: 'JSON',
				success: function(response){					
					namediv.innerHTML = "<div><p>" + response + "</p></div>";	
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					console.log(xhr.responseText);
				}
			});
		}

		function Accept(){
			tagid = input.value;
			$.ajax({
				url: 'AcceptSummonAjax.php?tagid=' + tagid + '&tier1=' + tier1[0] + "," + tier1[1] + "," + tier1[2] + "," + tier1[3] + "," + tier1[4] + '&tier2=' + tier2[0] + "," + tier2[1] + "," + tier2[2] + "," + tier2[3] + "," + tier2[4] + '&tier3=' + tier3[0] + "," + tier3[1] + "," + tier3[2] + "," + tier3[3] + "," + tier3[4] + '&tier4=' + tier4[0] + "," + tier4[1] + "," + tier4[2] + "," + tier4[3] + "," + tier4[4],
				type: 'get',
				dataType: 'JSON',
				success: function(response){					
					resdiv.innerHTML += "<div><p>" + response + "</p></div>";	
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					console.log(xhr.responseText);
				}
			});

			tier1 = [-1, -1, -1, -1, -1];
			tier2 = [-1, -1, -1, -1, -1];
			tier3 = [-1, -1, -1, -1, -1];
			tier4 = [-1, -1, -1, -1, -1];
		}
		
	</script>
</html>