<?php
	session_start();

	$db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
	$db->busyTimeout(100);
	$files = [];

	try{
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
				array_push($files, $row[0]);
			}			
			$_SESSION["all_ids"] = $files;
			$filtered = false;
		}
		$_SESSION["search"] = "";
		$idcount = count($files)-1;
	}
	
		$r = rand(0, $idcount);

        $PageTitle = "Podobo - Commands";
	
		function customPageHeader(){?>
			<script>
				$(document).ready(function()
				{				
					var HeaderButton = document.getElementById("tools");
					HeaderButton.className = "w3-bar-item w3-button w3-theme-l1";
				});
			</script>
		<?php }
	
		include_once('header.php');
			
		echo "<div class='w3-center'>";
			echo "<input class='w3-center' id='IQDB-Command' type='button' value='IQDB Command' onclick='IQDBCommand()'/>\r\n";	

			echo "<hr />";

			echo "<input class='w3-center' id='Closing-Command' type='button' value='Closing Command' onclick='ClosingCommand()'/>\r\n";	
		echo "</div>";
	}catch(exception $e){

	}
?>
	</body>
	<script type="text/javascript">		
		$(document).ready(function()
		{			
			iqdb_command = document.getElementById("IQDB-Command");
			closing_command = document.getElementById("Closing-Command");
		});		
				
		function IQDBCommand()
		{
			$.ajax({
				url: 'IQDBCommandAjax.php',
				type: 'get',
				//dataType: 'JSON',
				success: function(response){					
					iqdb_command.value = "Commanded";
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					iqdb_command.value = "Error";
				}
			});
		}

		function ClosingCommand()
		{
			$.ajax({
				url: 'ClosingCommandAjax.php',
				type: 'get',
				//dataType: 'JSON',
				success: function(response){					
					closing_command.value = "Commanded";
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					closing_command.value = "Error";
				}
			});
		}
		
	</script>
</html>