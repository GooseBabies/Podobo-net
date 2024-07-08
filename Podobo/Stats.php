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
				array_push($files, $row[0]);
			}			
			$_SESSION["all_ids"] = $files;
			$filtered = false;
		}
		$_SESSION["search"] = "";
		$idcount = count($files)-1;
	}

	$r = rand(0, $idcount);    

	$PageTitle = "Podobo";
	
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

	$sql = $db->prepare("select count(id) from files");
    $filescount = $sql->execute()->fetchArray()[0] ?? 0;

    echo "<p class='w3-bar-item state-indicator'><svg xmlns='http://www.w3.org/2000/svg' id='state-indicator' viewBox='0 0 512 512' focusable='false' height='18' width='18' class='fa-solid fa-circle'><path d='M256 512c141.4 0 256-114.6 256-256S397.4 0 256 0S0 114.6 0 256S114.6 512 256 512z'/></svg><a class='w3-bar-item state-message'>" . $message . $progress . "</a></p>";

    // $sql = $db->prepare("select count(id) from booru_proc where processed=0 and booru_source=0");
    // $danboorucount = $sql->execute()->fetchArray()[0] ?? 0;
    echo "<p id='0' onclick='Stats(0)'>Danbooru tags left to process: Click</p>";

    // $sql = $db->prepare("select count(id) from booru_proc where processed=0 and booru_source=1");
    // $e621count = $sql->execute()->fetchArray()[0] ?? 0;
    echo "<p id='1' onclick='Stats(1)'>e621 tags left to process: Click</p>";

    // $sql = $db->prepare("select count(id) from booru_proc where processed=0 and booru_source=2");
    // $rule34count = $sql->execute()->fetchArray()[0] ?? 0;
    echo "<p id='2' onclick='Stats(2)'>rule34.xxx tags left to process: Click</p>";

	echo "<p id='12' onclick='Stats(12)'>Gelbooru tags left to process: Click</p>";

    // $sql = $db->prepare("select count(id) from booru_proc where processed=0 and booru_source=6");
    // $hydruscount = $sql->execute()->fetchArray()[0] ?? 0;
    echo "<p id='3' onclick='Stats(3)'>Hydrus tags left to process: Click</p>";

    // $sql = $db->prepare("select count(id) from booru_proc where processed=0");
    // $totalcount = $sql->execute()->fetchArray()[0] ?? 0;
    echo "<p id='4' onclick='Stats(4)'>Total tags left to process: Click</p>";
	
    // $sql = $db->prepare("select count(distinct media_id) from media_tags");
    // $taggedcount = $sql->execute()->fetchArray()[0] ?? 0;
    echo "<p id='5' onclick='Stats(5)'>Files Tagged/Total Files: Click</p>";

    // $sql = $db->prepare("select count(id) from files where sources is not null and sources != ''");
    // $sourcescount = $sql->execute()->fetchArray()[0] ?? 0;
	echo "<p id='6' onclick='Stats(6)'>Files with Sources/Total Files: Click</p>";

    // $sql = $db->prepare("select count(id) from files where booru_tagged = 1 or hydrus_tagged = 1");
    // $autotaggedcount = $sql->execute()->fetchArray()[0] ?? 0;
    echo "<p id='7' onclick='Stats(7)'>Auto-tagged Files/Total Files: Click</p>";

    // $sql = $db->prepare("select count(id) from files where IQDB = 1");
    // $iqdbcount = $sql->execute()->fetchArray()[0] ?? 0;
	echo "<p id='8' onclick='Stats(8)'>IQDB/Total Files: Click</p>";

    // $sql = $db->prepare("select count(id) from files where ext in ('.jpg', '.jpeg', '.png')");
    // $imagecount = $sql->execute()->fetchArray()[0] ?? 0;
    echo "<p id='9' onclick='Stats(9)'>Total Images: Click</p>";
    
    // $sql = $db->prepare("select count(id) from files where ext ='.gif'");
    // $gifcount = $sql->execute()->fetchArray()[0] ?? 0;
    echo "<p id='10' onclick='Stats(10)'>Total GIFs: Click</p>";

    // $sql = $db->prepare("select count(id) from files where ext in ('.mp4', '.wmv', 'webm')");
    // $videocount = $sql->execute()->fetchArray()[0] ?? 0;
    echo "<p id='11' onclick='Stats(11)'>Total Videos: Click</p>";

    $db = null;
?>



</body>
<script type="text/javascript">	
		var files = <?php echo $filescount; ?>;
		function Stats(id){
			$.ajax({
					url: 'StatsAjax.php?id=' + id,
					type: 'get',
					dataType: 'JSON',
					success: function(response){
						console.log(response);				
						var spot = document.getElementById(id);
						switch(id){
							case 0:
								spot.innerHTML = "Danbooru tags left to process: " + response;
								break;
							case 1:
								spot.innerHTML = "e621 tags left to process: " + response;
								break;
							case 2:
								spot.innerHTML = "rule34.xxx tags left to process: " + response;
								break;
							case 3:
								spot.innerHTML = "Hydrus tags left to process: " + response;
								break;
							case 4:
								spot.innerHTML = "Total tags left to process: " + response;
								break;
							case 5:
								spot.innerHTML = "Files Tagged/Total Files: " + response + "/" + files + " (" + ((response/files)*100).toFixed(2) + "%)";
								break;
							case 6:
								spot.innerHTML = "Files with Sources/Total Files: " + response + "/" + files + " (" + ((response/files)*100).toFixed(2) + "%)";
								break;
							case 7:
								spot.innerHTML = "Auto-tagged Files/Total Files: " + response + "/" + files + " (" + ((response/files)*100).toFixed(2) + "%)";
								break;
							case 8:
								spot.innerHTML = "IQDB/Total Files: " + response + "/" + files + " (" + ((response/files)*100).toFixed(2) + "%)";
								break;
							case 9:
								spot.innerHTML = "Total Images: " + response;
								break;
							case 10:
								spot.innerHTML = "Total GIFs: " + response;
								break;
							case 11:
								spot.innerHTML = "Total Videos: " + response;
								break;
							case 12:
								spot.innerHTML = "Gelbooru tags left to process: " + response;
								break;
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
