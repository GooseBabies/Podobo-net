<?php
	session_start();

	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");	
	$db->busyTimeout(100);
    $thumbs_source = "../thumbs/";
	$files = [];
    $harem = [];
    $paths = [];

	try{
        if(isset($_GET["order"])) { $order = $_GET["order"]; } else { $order = 0; }
        if(isset($_GET["page"])) { $page = $_GET["page"]; } else { $page = 1; }
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
					
		$PageTitle = "Game - Inventory";
		$InTags = true;
	
		function customPageHeader(){?>
			<script>
				$(document).ready(function()
				{				
					var HeaderButton = document.getElementById("tools");
					HeaderButton.className = "w3-bar-item w3-button w3-theme-l1";
				});
			</script>
			<style>
				.summon-id{
					display: block;
				}
			</style>
		<?php }
	
		include_once('../header.php');

        //order by
        //0 id
        //1 name
        //2 level
        //3 rarity
        //4 class
		switch($order){
			case 0:
				$orderby = "harem.id asc";
				break;
			case 1:
				$orderby = "name asc";
				break;
			case 2:
				$orderby = "level desc";
				break;
			case 3:
				$orderby = "rarity desc";
				break;
			case 4:
				$orderby = "class1 asc";
				break;
			case 5:
				$orderby = "summon_id asc";
				break;
			case 6:
			case 7:
			case 8:
			case 9:
			case 10:
				break;
		}

        $sql = $db->prepare("select harem.id, summon_id, level, name, tier1, tier2, tier3, tier4, rarity from harem join summons on summon_id = summons.id order by " . $orderby);
		//$sql->bindValue(':order', $orderby, SQLITE3_TEXT);
		//echo $sql->getSQL(true);
        $result = $sql->execute();
        while ($summon = $result->fetchArray()) {
            array_push($harem, $summon);
        }

        for($j = 0; $j<count($harem); $j++){
            switch($harem[$j][2]){
                case 1:
                case 2:
                case 3:
                    $tier = 4;
                    break;
                case 4:
                case 5:
                case 6:
                    $tier = 5;
                    break;
                case 7:
                case 8:
                case 9:
                    $tier = 6;
                    break;
                case 10:
                    $tier = 7;
                    break;
            }
            $sql = $db->prepare("select path from files where id = :tier");
            $sql->bindValue(':tier', $harem[$j][$tier], SQLITE3_INTEGER);
            $path = $sql->execute()->fetchArray()[0];
            array_push($paths, $path);
        }

        //print_r($harem);

        //0 harem id
        //1 summon id
        //2 level
        //3 name
        //4 tier (fileid)
        //5 rarity
        //6 path

        $columncount = 5;
        $rowcount = 5;
        $rownum = count($harem);

        echo "rownum: ". $rownum;
	
		$db = null;

		echo "<div class='col-10'><div class='posts-wrapper'>";			
			
			$maxpage = ceil($rownum/($columncount*$rowcount));
			$pagestart = ($page - 1) * ($columncount * $rowcount);
			
			for($i = $pagestart; $i<$pagestart+($rowcount*$columncount) and $i<$rownum; $i++)
			{
				if($i % $columncount == 0){
					echo "<div class='row-posts'>";
				} 
				echo "<div class='posts'>";
				echo "<div class='summon-id'>";
				echo "<a href ='SummonInfo.php?id=" . $harem[$i][0] . "'>";		
				
				echo "<img class='thumbs untagged' src ='" . $thumbs_source . pathinfo($paths[$i], PATHINFO_FILENAME) . ".jpg'" . "' onerror='this.src =\"" . $thumbs_source . "MissingThumb.jpg\"" . "' alt='N/A'/></a>";
			
				echo "<p>Name: " . $harem[$i][3] . "</p>";
				echo "<p>Rarity: " . $harem[$i][8] . "</p>";
				echo "<p>Level: " . $harem[$i][2] . "</p>";
                echo "</div>";
				
				echo "</div>";

				if($i % $columncount == ($columncount - 1) || $i == count($harem) - 1){
					echo "</div>";
				}
			}
			
			echo "</div>";
			
			echo "<div class='container_footer'>";
			
			
			$pagelimit = 5;
			if($page < $pagelimit + 1) { $start_page = 1; } else { $start_page = $page - ($maxpage < $pagelimit ? $maxpage + 1 : $pagelimit);}
			if($page > $maxpage - $pagelimit) { $end_page = $maxpage; } else { $end_page = $page + ($maxpage < $pagelimit ? $maxpage : $pagelimit); }
			
			if($page != 1) 
			{
				echo "<a href='Inventory.php?page=1&order=" . $order . "'><i class='fas fa-angles-left fa-2x'></i></a>";
				echo "<a href='Inventory.php?page=".($page - 1)."&order=" . $order . "'><i class='fas fa-angle-left fa-2x'></i></a>";	
			}
			
			for($k = $start_page; $k <= $end_page; $k++)
			{
				if($k == $page){
					echo "<a class='current-page' href='Inventory.php?page=".$k."&order=" . $order . "'>";
					echo $k;
					echo "</a>";
				}
				else{
					echo "<a class='other-page' href='PInventory.php?page=".$k."&order=" . $order . "'>";
					echo $k;
					echo "</a>";
				}
				
			}
			
			if($page != $maxpage) 
			{
				echo "<a href='Inventory.php?page=".($page + 1)."&order=" . $order . "'><i class='fas fa-angle-right fa-2x'></i></a>";
				echo "<a href='Inventory.php?page=".$maxpage."&order=" . $order . "'><i class='fas fa-angles-right fa-2x'></i></a>";
			}
			
			echo "</div>";
			echo "</div>";
	}catch(exception $e){

	}	
?>
	</body>
	<script type="text/javascript">
		$(document).ready(function()
		{
			resdiv = document.getElementById("response");
		});
		
	</script>
</html>