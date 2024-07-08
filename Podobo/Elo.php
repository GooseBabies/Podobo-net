<?php
	//session_start();
	
	$db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
    $elofiles = [];

	$PageTitle = "ELO";
	
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

    //$db = null;

    //$db = new SQLite3("Y:\\LavaPool\\Iowa.db");
	$db->busyTimeout(100);

    //if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = -1; }; 

	$sql = $db->prepare("select id, path, ext from files where (booru_tagged = 1 or elo != 600) and duration < 600 order by random() limit 2");
	$result = $sql->execute();
    while ($row = $result->fetchArray()) {
        array_push($elofiles, $row);
    }

    //print_r($elofiles);

    $files1 = $elofiles[0];
    $files2 = $elofiles[1];

    ?>

	<?php

    //print_r($dupefiles);

    echo "<div class='rating-wrapper'>";
    
    echo "<p class='w3-center' id='desc'>" . $files1[0] . " vs " . $files2[0] . "</p>";

    echo "</div>";    

    echo "<div class='w3-center'><button class='dupe-button' onclick='Draw()'>Draw</button></div>";

    echo "<hr />";

    echo "<div class='elo-container'>";

    if($files1[2] == ".mp4" or $files1[2] == ".webm"){
        echo "<div class='elo-pic'><video class='dupe-fit' loop controls src='" . strtolower(substr(pathinfo($files1[1], PATHINFO_DIRNAME), 3)) . "\\" . rawurlencode(basename($files1[1])) . "'></video>";
        echo "<button class='w3-center dupe-button' id='img1' onclick='Better(1)'>Better</button></div>";
    }
    else{
        echo "<div class='elo-pic'><img class='dupe-fit' src='" . strtolower(substr(pathinfo($files1[1], PATHINFO_DIRNAME), 3)) . "\\" . rawurlencode(basename($files1[1])) . "' onclick='Better(1)' /></div>";
    }

    if($files2[2] == ".mp4" or $files2[2] == ".webm"){
        echo "<div class='elo-pic'><video class='dupe-fit' loop controls src='" . strtolower(substr(pathinfo($files2[1], PATHINFO_DIRNAME), 3)) . "\\" . rawurlencode(basename($files2[1])) . "'></video>";
        echo "<button class='w3-center dupe-button' id='img2'  onclick='Better(2)'>Better</button></div>";
    }
    else{
        echo "<div class='elo-pic'><img class='dupe-fit' src='" . strtolower(substr(pathinfo($files2[1], PATHINFO_DIRNAME), 3)) . "\\" . rawurlencode(basename($files2[1])) . "' onclick='Better(2)' /></div>";
    }
    
    //echo "<div class='half-fit'><img class='dupe-fit' src='" . strtolower(substr(pathinfo($files2[1], PATHINFO_DIRNAME), 3)) . "\\" . rawurlencode(basename($files2[1])) . "' style='width:100%' /></div>";

    echo "</div>";
    $db = null;
?>



</body>
<script type="text/javascript">	
        var id1 = <?php echo $files1[0]; ?>;
        var id2 = <?php echo $files2[0]; ?>;
		$(document).ready(function()
		{

		});

        function Draw(){
            Submit(id1 + ",0.5;" + id2 + ",0.5");
        }

        function Better(choice){
            if(choice == 1){
                Submit(id1 + ",1;" + id2 + ",0");
            }
            else{
                Submit(id1 + ",0;" + id2 + ",1");
            }
        }

        function Submit(result){

            $.ajax({
					url: 'EloAjax.php?result=' + result,
					type: 'get',
					dataType: 'JSON',
					success: function(response){
						console.log(response);
                        location.href = 'Elo.php';	
                        
					},
					error: function(xhr, ajaxOptions, thrownError)
					{
						console.log(xhr.responseText);
					}
				});
        }
	</script>
</html>
