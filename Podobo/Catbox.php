<?php
	session_start();
	
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
				array_push($files, $row[0]);
			}			
			$_SESSION["all_ids"] = $files;
			$filtered = false;
		}
		$_SESSION["search"] = "";
		$idcount = count($files)-1;
	}

	$r = rand(0, $idcount);

	$PageTitle = "Podobo - Catbox";

	function customPageHeader(){?>
		<script>

			$(document).ready(function()
			{				
				var HeaderButton = document.getElementById("tools");
				HeaderButton.className = "w3-bar-item w3-button w3-theme-l1";
			});
		</script>
		<style type="text/css">
		input[type=button] {
			margin: 20px;
		}
		</style>
	<?php }

	include_once('header.php');
			
?>
	<main class="row">
	
		<?php
		
		echo "</main>";	
		
		echo "<div class='col-2 w3-theme'>";

		echo "</div>";
		
		echo "<div class='col-7'>";
		echo "<div><img class='center-fit' src='https://files.catbox.moe/2dsvq9.png' /></div>";
		echo "</div>";

	?>		
	</body>
	<script type="text/javascript">
	</script>
</html>