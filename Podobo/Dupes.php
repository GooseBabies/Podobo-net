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

	if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = -1; }; 

	$sql = $db->prepare("SELECT COUNT(*) FROM dupes where decided=0");
	$dupecount = $sql->execute()->fetchArray()[0];
	
	$sql = $db->prepare("SELECT id1, id2, score FROM dupes where id = :id");
	$sql->bindValue(":id", $id, SQLITE3_INTEGER);
	$dupe = $sql->execute()->fetchArray();

	$id1 = $dupe[0];
	$id2 = $dupe[1];
	
	$sql = $db->prepare("select path, height, width, sources from files where id = :id");
	$sql->bindValue(":id", $id1, SQLITE3_INTEGER);
	$file1 = $sql->execute()->fetchArray();

	$sql = $db->prepare("select path, height, width, sources from files where id = :id");
	$sql->bindValue(":id", $id2, SQLITE3_INTEGER);
	$file2 = $sql->execute()->fetchArray();	
	

	$sql = $db->prepare("update files set viewcount = viewcount + 1, last_viewed = date('now') where id = :id");
	$sql->bindValue(':id', $id1, SQLITE3_INTEGER);
	$result = $sql->execute();

	$sql = $db->prepare("INSERT INTO view_history (media_id, viewtime) VALUES (:media_id, datetime('now'))");
	$sql->bindValue(':media_id', $id1, SQLITE3_INTEGER);
	$result = $sql->execute();

	$sql = $db->prepare("update files set viewcount = viewcount + 1, last_viewed = date('now') where id = :id");
	$sql->bindValue(':id', $id2, SQLITE3_INTEGER);
	$result = $sql->execute();

	$sql = $db->prepare("INSERT INTO view_history (media_id, viewtime) VALUES (:media_id, datetime('now'))");
	$sql->bindValue(':media_id', $id2, SQLITE3_INTEGER);
	$result = $sql->execute();

	$PageTitle = "Podobo - Dupes [" . $dupecount . "]";

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
		$path1link = strtolower(substr(pathinfo($file1[0], PATHINFO_DIRNAME), 3)) . "\\" . rawurlencode(basename($file1[0]));
		$path2link = strtolower(substr(pathinfo($file2[0], PATHINFO_DIRNAME), 3)) . "\\" . rawurlencode(basename($file2[0]));

		$file1size = FileSizeConvert(filesize($file1[0]));
		$file2size = FileSizeConvert(filesize($file2[0]));

		$file1sources = array_filter(explode(" ", $file1[3]));
		$file2sources = array_filter(explode(" ", $file2[3]));
		
		//display_image($file1, $file2, $path1link, $path2link, $file1size, $file2size);
		
		echo "</main>";	
		

		echo "<div class='col-1 w3-theme>";
		echo "<div class='dupe-buttons'>";
		echo "<input type='button' value='Keep This' onclick='KeepThis()'/>";
		echo "<input type='button' value='Keep Both' onclick='KeepBoth()'/>";
		echo "<input type='button' value='Delete Both' onclick='DeleteBoth()'/>";
		echo "</div>";
		echo "</div>";	

		
		echo "<div class='col-7'>";
		echo "<div><img class='center-fit' id='imgfile1' src='" . $path1link . "' onclick='SwitchPath()' /></div>";
		echo "<div><img class='center-fit' id='imgfile2' src='" . $path2link . "' onclick='SwitchPath()' /></div>";
		echo "</div>";

		
		echo "<div class='col-2 w3-theme'>";
		
		echo "<div id='filename' class='w3-center' text-align='center'><p>" . basename($file1[0]) . "</p><p>" . $file1[1] . " x " .  $file1[2] . "</p><p>" .  $file1size . "</p></div>";

		echo "<div id='filesources1' class='w3-center' text-align='center'>";
		if(!empty($file1sources)){
			foreach($file1sources as $source1){
				echo "<div><a href='" . $source1 . "'>" . $source1 . "</a></div>";
			}
		}			
		echo "</div>";

		echo "<div id='filesources2' class='w3-center' text-align='center'>";
		if(!empty($file2sources)){
			foreach($file2sources as $source2){
				echo "<div><a href='" . $source2 . "'>" . $source2 . "</a></div>";
			}
		}			
		echo "</div>";
		echo "<div id='score' class='w3-center' text-align='center'><p>" . $dupe[2] . "</p></div>";

		echo "</div>";

		function FileSizeConvert($bytes)
		{
			$bytes = floatval($bytes);
			$arBytes = array(
				0 => array(
					"UNIT" => "TB",
					"VALUE" => pow(1024, 4)
				),
				1 => array(
					"UNIT" => "GB",
					"VALUE" => pow(1024, 3)
				),
				2 => array(
					"UNIT" => "MB",
					"VALUE" => pow(1024, 2)
				),
				3 => array(
					"UNIT" => "KB",
					"VALUE" => 1024
				),
				4 => array(
					"UNIT" => "B",
					"VALUE" => 1
				),
			);

			foreach($arBytes as $arItem)
			{
				if($bytes >= $arItem["VALUE"])
				{
					$result = $bytes / $arItem["VALUE"];
					$result = strval(round($result, 2)) . " " . $arItem["UNIT"];
					break;
				}
				else{
					$result = 0;
				}
			}
			return $result;
		}
	?>		
	</body>
	<script type="text/javascript">	
		var img1 = document.getElementById('imgfile1');
		var img2 = document.getElementById('imgfile2');
		var sources1 = document.getElementById('filesources1');
		var sources2 = document.getElementById('filesources2');
		img1.addEventListener('load', loaded)
		var imgname = document.getElementById('filename');

		var id1 = <?php echo json_encode($id1) ?>;
		var id2 = <?php echo json_encode($id2) ?>;
		var visibleimg = 1;

		function SwitchPath()
		{
			if(img1.style.display == "block"){
				img1.style.display = "none";
				img2.style.display = "block";
				imgname.innerHTML = <?php echo "\"<p>" . basename($file2[0]) . "</p><p>" . $file2[1] . " x " .  $file2[2] . "</p><p>" .  $file2size . "</p>\"" ?>;
				sources1.style.display = "none";
				sources2.style.display = "block";
				visibleimg = 2;
			}
			else{
				img1.style.display = "block";
				img2.style.display = "none";
				imgname.innerHTML = <?php echo "\"<p>" . basename($file1[0]) . "</p><p>" . $file1[1] . " x " .  $file1[2] . "</p><p>" .  $file1size . "</p>\"" ?>;
				sources1.style.display = "block";
				sources2.style.display = "none";
				visibleimg = 1;
			}
			
		}
		
		function loaded() {
			img1.style.display = "block";
			img2.style.display = "none";
			sources1.style.display = "block";
			sources2.style.display = "none";
		}

		//Dupe Decision 0 no decision; 1 keep id1, 2 keep id2, 3 keep both, 4 delete both

		function KeepThis(){
			Decide(visibleimg);
		}

		function KeepBoth(){
			Decide(3);
		}

		function DeleteBoth(){
			Decide(4);
		}

		function Decide(decision){
			$.ajax({
					url: 'DupeDecideAjax.php?id1=' + id1 + "&id2=" + id2 + "&decision=" + decision,
					type: 'get',
					dataType: 'JSON',
					success: function(response){					
						if(response[0] == 1){
							location.href = 'Dupes.php?id=' + response[1];
						}
						else if(response[0] == 2){
							location.href = 'Tools.php';
						}
					},
					error: function(xhr, ajaxOptions, thrownError)
					{
						console.log(xhr.responseText);
					}
				});
		}
	</script>
</html>