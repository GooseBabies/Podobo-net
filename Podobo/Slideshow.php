<?php
	// Start the session
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
				array_push($files, $row);
			}			
			$_SESSION["all_ids"] = $files;
			$filtered = false;
		}
		$_SESSION["search"] = "";
		$idcount = count($files)-1;
	}
	
	$db = null;
	$r = rand(0,$idcount);

	$PageTitle = "Slideshow";
	
	function customPageHeader(){?>
		<script type = "text/javascript" src = "../js/NoSleep.js"></script>	
		<script>
			$(document).ready(function()
			{				
				var HeaderButton = document.getElementById("slideshow");
				HeaderButton.className = "w3-bar-item w3-button w3-theme-l1";
			});
		</script>
		<style type="text/css">
		.fas {
			color: white;
			margin-left: 10px;
			margin-right: 10px;
			margin-top: 3px;
			margin-bottom: 3px;
		}
		</style>
	<?php }

	include_once('header.php');
?>
	<main class="row">;
			<div class="col-10">
			<div class="w3-center">
			<i class="fas fa-arrow-left fa-2x" onclick="back()"></i>
			<i class="fas fa-pause fa-2x" onclick="pauseSlideshow()"></i>
			<i class="fas fa-volume-off fa-2x" onclick="pauseAudio()"></i>
			<i class="fas fa-arrow-right fa-2x" onclick="forward()"></i>
			</div>;
		
	<img class="center-fit" id="my_media" src ="" />;
			</div>;
	</main>;
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
			if(index > filearray.length - 1){
				index = 0;
			}
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