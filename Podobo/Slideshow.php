<?php
	// Start the session
	session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html" charset="utf-8" />                
		<meta name="viewport" content="width=device-width, initial-scale=1">
	    <title>Podobo - Slideshow</title>
	    <link rel="stylesheet" type="text/css" href="../style/PodoboStyle.css" />				
		<link rel="stylesheet" href="../style/w3.css" />
		<link rel="icon" type="image/x-icon" href="../imgs/favicon.ico"> 
		<!-- <script type = "text/javascript" src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>		 -->
		<script type = "text/javascript" src = "../js/jquery-3.6.0.min.js"></script>
		<script type = "text/javascript" src = "../js/NoSleep.js"></script>	
		<!-- <script src="https://kit.fontawesome.com/710df8e4cb.js" crossorigin="anonymous"></script> -->
		<!-- <script src="../js/710df8e4cb-edit.js" crossorigin="anonymous"></script> -->
		<script src="../style/releases/v5.15.4/js/all.min.js" crossorigin="anonymous"></script>
		<style type="text/css">
		.fas {
			color: white;
			margin-left: 10px;
			margin-right: 10px;
			margin-top: 3px;
			margin-bottom: 3px;
		}
		</style>
	</head>
	<?php
		$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");		
		$files = [];
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
		
		$db = null;
		$r = rand(0,$idcount);
		?>
		
		<!-- <body onload="myfunction()"> -->
		<body>
		<?php
		//echo "<div class='container'>";
		echo "<div class='w3-bar w3-theme w3-left-align w3-medium container_header'>";
				echo "<a class='w3-bar-item w3-button w3-hide-small w3-hover-blue-grey' href='Posts.php'>Posts</a>";
				echo "<a class='w3-bar-item w3-button w3-hide-small w3-hover-blue-grey' href='Tags/TagList.php'>Tags</a>";
				echo "<a class='w3-bar-item w3-button w3-hide-small w3-hover-blue-grey' href='Wiki.php'>Wiki</a>";
				echo "<a class='w3-bar-item w3-button w3-theme-l1' href='Slideshow.php'>Slideshow</a>";
				echo "<a class='w3-bar-item w3-button w3-hide-small w3-hover-blue-grey' href='Tools.php'>Tools</a>";
			echo "</div>";
		echo "<main class='row'>";
                echo "<div class='col-10'>";
				echo "<div class='w3-center'>";
				//echo "<input type='button' value='back' onclick='back()' />";
				echo "<i class='fas fa-arrow-left fa-2x' onclick='back()'></i>";
				//echo "<input type='button' value='Pause Slideshow' onclick='pauseSlideshow()' />";
				echo "<i class='fas fa-pause fa-2x' onclick='pauseSlideshow()'></i>";
				//echo "<input type='button' value='Toggle Music' onclick='pauseAudio()' />";
				echo "<i class='fas fa-volume-off fa-2x' onclick='pauseAudio()'></i>";
				//echo "<input type='button' value='forward' onclick='forward()' />";
				echo "<i class='fas fa-arrow-right fa-2x' onclick='forward()'></i>";
				echo "</div>";
			
		echo "<img class='center-fit' id='my_media' src ='' />";
                echo "</div>";
		echo "</main>";
		//echo "</div>";
	?>
	<script>
		var filearray = <?php echo json_encode($files); ?>;
		var filecount = <?php echo $idcount; ?>;
		let nIntervId;
		var noSleep = new NoSleep();
		var music;
		var musicPlaying = false;
		var index = 0;
		var slideshowPlaying = true;
		const musictracks = ["../audio/WAP.mp3", "../audio/popstars.mp3", "../audio/ThroatGoat.mp3", "../audio/PeurDesFilles.mp3"]

		$(document).ready(function()
		{
			noSleep.enable();
			music = document.getElementById("music");
			music.src = musictracks[Math.floor(Math.random()*musictracks.length)];
			music.addEventListener("ended",(event) => {
				UpdateAudio();
			});
			shuffle(filearray);
			myfunction();
		});
	
		function myfunction(){
			clearInterval(nIntervId);
			getimage(filearray[index][0]);
			index++;
		}
		function UpdateAudio(){
			music.src = musictracks[Math.floor(Math.random()*musictracks.length)];
			music.play();
		}
		function getdur(){
			var durty = document.getElementById('my_media').duration * 1000
			if(durty < 3000){
				durty = 3000;
			}
			nIntervId = setInterval(myfunction, durty);						
		}
		function mediaLoaded(){
			nIntervId = setInterval(myfunction, 3000);
		}
		function getimage(data){
			$.get( 'SlideshowAjax.php',
                  { id: data },
                  function(data) 
				  { 
					if(data.includes('.mp4') || data.includes('.webm'))
					{
						$('#my_media').replaceWith('<video class="center-fit" id="my_media" onloadedmetadata="getdur()" autoplay loop controls src ="' + data + '" />'); 
					}
					else if(data.includes('.mkv') || data.includes('.wmv') || data.includes('.avi'))
					{
						nIntervId = setInterval(myfunction, 1);
					}
					else
					{
						$('#my_media').replaceWith('<img class="center-fit" id="my_media" onload="mediaLoaded()" src ="' + data + '" />');					
					}
				  }
               );
		}

		$(document).keydown(function(e) {
			//console.log(e);
			if(e.keyCode == 39) {
				forward();
			}
			else if(e.keyCode == 37)
			{
				back();
			}
			else if(e.keyCode == 77)
			{
				pauseAudio();
			}
			else if(e.keyCode == 80)
			{
				pauseSlideshow();
			}
			else if(e.keyCode == 76) //L
			{
				UpdateAudio();
			}
		});

		function back(){
			index -= 2;
			myfunction();
		}

		function forward(){
			myfunction();
		}

		function pauseSlideshow(){
			if(slideshowPlaying == true){
				clearInterval(nIntervId);
				slideshowPlaying = false;
			}
			else{
				nIntervId = setInterval(myfunction, 3000);
				slideshowPlaying = true;
			}
		}

		function pauseAudio(){
			if(musicPlaying == true){
				music.pause();
				musicPlaying = false;
			}
			else{
				music.play();
				musicPlaying = true;
			}
		}

		function shuffle(array) {
			let currentIndex = array.length,  randomIndex;

			// While there remain elements to shuffle...
			while (currentIndex != 0) {

				// Pick a remaining element...
				randomIndex = Math.floor(Math.random() * currentIndex);
				currentIndex--;

				// And swap it with the current element.
				[array[currentIndex], array[randomIndex]] = [
				array[randomIndex], array[currentIndex]];
			}

			return array;
		}
	</script>
       <audio id ="music" src="" hidden>
	</body>
</html>