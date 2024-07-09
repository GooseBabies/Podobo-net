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
	
	$sql = $db->prepare("SELECT id1, id2, score, decision FROM dupes where id = :id");
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
			margin: 5px;
		}
		</style>
	<?php }

	include_once('header.php');
			
?>
	<main class="row">
	
		<?php
		$path1link = strtolower(substr(pathinfo($file1[0], PATHINFO_DIRNAME), 3)) . "\\" . rawurlencode(basename($file1[0]));
		$path2link = strtolower(substr(pathinfo($file2[0], PATHINFO_DIRNAME), 3)) . "\\" . rawurlencode(basename($file2[0]));

		$filesize1 = filesize($file1[0]);
		$filesize2 = filesize($file2[0]);
		$file1IsBigger = ($filesize1 > $filesize2 ? true : false);

		$file1size = FileSizeConvert($filesize1);
		$file2size = FileSizeConvert($filesize2);

		$file1sources = array_filter(explode(" ", trim($file1[3])));
		$file2sources = array_filter(explode(" ", trim($file2[3])));

		
		echo "<div class='col-10 w3-theme'>";

			echo "<div id='fileid' class='w3-center' text-align='center'><a href='Post.php?id=". $id1 . "'>[" . $id1 . "]</a></div>";

			$file1nameparts = explode("_", basename($file1[0]));
			$file2nameparts = explode("_", basename($file2[0]));

			$file1nameparts1 = $file1nameparts[0];
			$file1nameparts2 = (count($file1nameparts) > 2 ? $file1nameparts[2] : "");

			$file2nameparts1 = $file2nameparts[0];
			$file2nameparts2 = (count($file2nameparts) > 2 ? $file2nameparts[2] : "");
		
			echo "<div id='filename' class='w3-center' text-align='center'><p>" . $file1nameparts1 . " - " . $file1nameparts2 .  "</p><p>" . $file1[1] . " x " .  $file1[2] . " - <span " . ($file1IsBigger ? "style='color:lime'" : "style='color:red'") . ">" .  $file1size . "</span></p></div>";

			echo "<div id='filesources1' class='w3-center' text-align='center'>";
			echo "<p>";
			if(!empty($file1sources)){
				for ($i = 0; $i <= count($file1sources) - 1; $i++){
					echo "<a href='" . $file1sources[$i] . "'>[Source " . ($i + 1) . "]</a>";
				}
			}
			else{
				echo "No Source";
			}			
			echo "</p>";
			echo "</div>";

			echo "<div id='filesources2' class='w3-center' text-align='center'>";
			echo "<p>";
			if(!empty($file2sources)){
				for ($i = 0; $i <= count($file2sources) - 1; $i++){
					echo "<a href='" . $file2sources[$i] . "'>[Source " . ($i + 1) . "]</a>";
				}
			}	
			else{
				echo "No Source";
			}
			echo "</p>";		
			echo "</div>";

			echo "<div id='score' class='w3-center' text-align='center'><p>" . $dupe[2] . "</p></div>";

			echo "<div><img class='dupe-fit' id='imgfile1' src='" . $path1link . "' onclick='SwitchPath()' /></div>";
			echo "<div><img class='dupe-fit' id='imgfile2' src='" . $path2link . "' onclick='SwitchPath()' /></div>";

			echo "<hr />";

			echo "<div class='dupe-buttons'>";
			echo "<input type='button' value='Keep (same)' onclick='KeepThis(1)'/>";
			echo "<input type='button' value='Keep (diff)' onclick='KeepThis(0)'/>";
			echo "<input type='button' value='Keep Both' onclick='KeepBoth()'/>";
			echo "<input type='button' value='Delete Both' onclick='DeleteBoth()'/>";
			echo "</div>";

		echo "</div>";

		echo "</main>";	

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
		var fileid = document.getElementById('fileid');

		var id1 = <?php echo json_encode($id1) ?>;
		var id2 = <?php echo json_encode($id2) ?>;
		var visibleimg = 1;

		function SwitchPath()
		{
			if(img1.style.display == "block"){
				img1.style.display = "none";
				img2.style.display = "block";
				imgname.innerHTML = <?php echo "\"<p>" . $file2nameparts1 . " - " . $file2nameparts2 .  "</p><p>" . $file2[1] . " x " .  $file2[2] . " - <span " . ($file1IsBigger ? "style='color:red'" : "style='color:lime'") . ">" .  $file2size . "</span></p>\"" ?>;
				sources1.style.display = "none";
				sources2.style.display = "block";
				fileid.innerHTML = "<a href='Post.php?id=" + id2 + "'>[" + id2 + "]</a>";
				visibleimg = 2;
			}
			else{
				img1.style.display = "block";
				img2.style.display = "none";
				imgname.innerHTML = <?php echo "\"<p>" . $file1nameparts1 . " - " . $file1nameparts2 .  "</p><p>" . $file1[1] . " x " .  $file1[2] . " - <span " . ($file1IsBigger ? "style='color:lime'" : "style='color:red'") . ">" .  $file1size . "</span></p>\"" ?>;
				sources1.style.display = "block";
				sources2.style.display = "none";
				fileid.innerHTML = "<a href='Post.php?id=" + id1 + "'>[" + id1 + "]</a>";
				visibleimg = 1;
			}
			
		}
		
		function loaded() {
			img1.style.display = "block";
			img2.style.display = "none";
			sources1.style.display = "block";
			sources2.style.display = "none";
		}

		//Dupe Decision 0 no decision; 1 keep id1 (diff), 2 keep id2 (diff), 3 keep both, 4 delete both, 5 transitive (one was chosen earlier to get rid of, no need to re-check), 6 keep id1 (same), 7 keep id2 (same)

		function KeepThis(same){
			if(same == 1){
				Decide(visibleimg + 5);
			}
			else{
				Decide(visibleimg);
			}
		}

		//handle right click
		document.addEventListener('contextmenu', function(e) {
			KeepBoth();
			e.preventDefault();
		}, false);

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