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
	
	$sql = "SELECT id, path from files where rarity is null order by random() limit 1";
	$result = $db->query($sql);
	$row = $result->fetchArray();

	$id = $row[0];
    $path = $row[1];

	$PageTitle = "Podobo - Rarity";

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
		$path1link = strtolower(substr(pathinfo($path, PATHINFO_DIRNAME), 3)) . "\\" . rawurlencode(basename($path));

		//display_image($file1, $file2, $path1link, $path2link, $file1size, $file2size);
		
		echo "</main>";	
		
		echo "<div class='col-2 w3-theme'>";

		echo "</div>";
		
		echo "<div class='col-7'>";
		echo "<div><img class='center-fit' src='" . $path1link . "' /></div>";
		echo "</div>";

		echo "<div class='col-1 w3-theme w3-center'>";
		echo "<p> </p>";
		echo "<input type='button' value='Common' onclick='Rarity(0)'/>";
		echo "<input type='button' value='Uncommon' onclick='Rarity(1)'/>";
        echo "<input type='button' value='Rare' onclick='Rarity(2)'/>";
        echo "<input type='button' value='Ultrarare' onclick='Rarity(3)'/>";
        echo "<input type='button' value='Skip' onclick='Rarity(-1)'/>";
		echo "</div>";	

	?>		
	</body>
	<script type="text/javascript">	
		var id = <?php echo $id; ?>;

		function Rarity(rarity_value){
			$.ajax({
					url: 'RarityAjax.php?id=' + id + "&rarity=" + rarity_value,
					type: 'get',
					dataType: 'JSON',
					success: function(response){					
						location.reload();
					},
					error: function(xhr, ajaxOptions, thrownError)
					{
						console.log(xhr.responseText);
					}
				});
		}
	</script>
</html>