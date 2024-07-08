<?php
	//session_start();
	
	//$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	//$db = new SQLite3("Y:\\Database\\nevada.db");	
    $dupefiles = [];   

	$PageTitle = "LavaPool Decider";
	
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

    if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = -1; }; 

	$sql = $db->prepare("select * from media where id = :id");
    $sql->bindValue(":id", $id, SQLITE3_INTEGER);
	$media = $sql->execute()->fetchArray();

    if(!$media){
        $sql = $db->prepare("select * from blacklist where processed = 0 and rej_count > 0 limit 1");
	    $blacklist = $sql->execute()->fetchArray();
        die("<script>location.href = 'LavaBlacklist.php?id=" . $blacklist[0] . "'</script>");
    }

	?>

	<div id="subheader" class="w3-bar w3-theme-l1 w3-left-align w3-small container_subheader">	
		<a id="back" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>LavaDecide.php?id=<?php echo ($id-1); ?>">Back</a>
		<a id="forward" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>LavaDecide.php?id=<?php echo ($id+1); ?>">Forward</a>
		<a id="booru-tag" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>LavaManualBlacklist.php">Add to Blacklist</a>  
		<a id="booru-tag" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>LavaIntro.php">LavaPool</a>                  
	</div>

	<?php

	$name = explode("_", $media[2]);

	if($name[2] == "Danbooru"){
		echo "<div class='w3-center'><a id='desc' href='https://danbooru.donmai.us/posts/" . pathinfo($name[3])['filename'] . "' target='_blank'>" . $media[2] . "</a></div>";
	}
	else if($name[2] == "e621"){
		echo "<div class='w3-center'><a id='desc' href='https://e621.net/posts/" . pathinfo($name[3])['filename'] . "' target='_blank'>" . $media[2] . "</a></div>";
	}
	else if($name[2] == "rule34.xxx"){
		echo "<div class='w3-center'><a id='desc' href='https://rule34.xxx/index.php?page=post&s=view&id=" . pathinfo($name[3])['filename'] . "' target='_blank'>" . $media[2] . "</a></div>";
	}
	else{
		echo "<p class='w3-center' id='desc'>" . $media[2] . "</p>";
	}
    
    echo "<p class='w3-center' id='desc2'></p>";
    //echo "<p class='w3-center' id='desc3'>" . $media[5] . "</p>";

    echo "<hr />";

    echo "<div>";

	if($media[3] == ".mp4" || $media[3] == ".webm"){
		//echo "<div><img class='center-fit' id='image' onclick='Accept()' src='LavaPool\\Files\\" . rawurlencode($media[2]) . "' /></div>";
		echo "<div><video id='video-player' class='center-fit' loop controls muted src='LavaPool\\Files\\" . rawurlencode($media[2]) . "'></video>";
		echo "<div class='w3-center'><button class='dupe-button' onclick='Accept()'>Submit</button></div>";
	}
	else{
		echo "<div><img class='center-fit' id='image' onclick='Accept()' src='LavaPool\\Files\\" . rawurlencode($media[2]) . "' /></div>";
	}

    

    echo "</div>";

    $db = null;
?>



</body>
<script type="text/javascript">	
        var id = <?php echo json_encode($id) ?>;
		var size = <?php echo json_encode($media[5]) ?>;
		$(document).ready(function()
		{
            var img = document.getElementById('image');
            var desc2 = document.getElementById('desc2');         
            var width = img.naturalWidth;
            var height = img.naturalHeight;
            desc2.innerHTML = height + " x " + width + " - " + size;
		});

        function Submit(kept){
            $.ajax({
					url: 'LavaDecideAjax.php?id=' + id + '&kept=' + kept,
					type: 'get',
					dataType: 'JSON',
					success: function(response){
						//console.log(response);
						if(response[0] == 1){
							location.href = 'LavaDecide.php?id=' + response[1];
						}
						else if(response[0] == 2){
							location.href = 'LavaDecideEnd.php';
						}
						else if(response[0] == 3){
                            location.href = 'LavaCheckWhitelist.php?id=' + response[1];
                        }
                        
					},
					error: function(xhr, ajaxOptions, thrownError)
					{
						console.log(xhr.responseText);
					}
				});
        }

        function Accept(){
            Submit(1);
        }

        //handle right click
		document.addEventListener('contextmenu', function(e) {
			Submit(0);
			e.preventDefault();
		}, false);

        function handleTouchStart(evt) {
			//const firstTouch = getTouches(evt)[0];                                      
			//xDown = firstTouch.clientX;
			//yDown = firstTouch.clientY;  
			xDown = evt.changedTouches[0].pageX;
			yDown = evt.changedTouches[0].pageY;                                   
		};    

		document.addEventListener('touchstart', handleTouchStart, false);   
		document.addEventListener('touchmove', handleTouchMove, false);     
		document.addEventListener('touchend', handleTouchEnd, false);

		var xDown = null;
		var yDown = null;

		function handleTouchMove(evt) 
		{
			//calculate of moving right or left and slide image slightly right or left
			var xUp = evt.changedTouches[0].pageX;
			var yUp = evt.changedTouches[0].pageY;

			var xDiff = xDown - xUp;
			var yDiff = yDown - yUp;

			var img = document.getElementById('image');

			if ( xDiff > 0 ) {
				//swipe left
				img.style.transform = "translate(-10,0)";

			} else {
				//swipe right
				img.style.transform = "translate(10,0)";
			}
		}  		
																				 
		function handleTouchEnd(evt) {
			//desc2.innerHTML = xDown.toFixed(2) + " " + xUp.toFixed(2) + " " + yDown.toFixed(2) + " " + yUp.toFixed(2);
			if ( ! xDown || ! yDown) {
				return;
			}

			//var desc2 = document.getElementById('desc2');

			var xUp = evt.changedTouches[0].pageX;
			var yUp = evt.changedTouches[0].pageY;

			var xDiff = xDown - xUp;
			var yDiff = yDown - yUp;
			
			if(Math.abs( xDiff ) > Math.abs( yDiff )){
				if(Math.abs(xDiff)>100){
                if ( xDiff > 0 ) {
	                //swipe left
				    Submit(0);
				} else {
					//swipe right
				    Submit(1);
					//desc2.innerHTML = xDown.toFixed(2) + " " + xUp.toFixed(2) + " " + yDown.toFixed(2) + " " + yUp.toFixed(2);
                }
				}
			   
		    }
			xDown = null;      
			yDown = null;     
			
			e.preventDefault();
		};
	</script>
</html>
