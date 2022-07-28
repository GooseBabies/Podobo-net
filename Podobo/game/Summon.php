<?php
	session_start();

	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");	
	$db->busyTimeout(100);
	$files = [];

	try{
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
					
		$PageTitle = "Game - Summon";
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
                .summon {
                animation: shake 0.8s;
                animation-iteration-count: 4;
                }

                @keyframes shake {
                0% { transform: translate(1px, 1px) rotate(0deg); }
                10% { transform: translate(-1px, -2px) rotate(-2deg); }
                20% { transform: translate(-3px, 0px) rotate(2deg); }
                30% { transform: translate(3px, 2px) rotate(0deg); }
                40% { transform: translate(1px, -1px) rotate(2deg); }
                50% { transform: translate(-1px, 2px) rotate(-2deg); }
                60% { transform: translate(-3px, 1px) rotate(0deg); }
                70% { transform: translate(3px, 1px) rotate(-2deg); }
                80% { transform: translate(-1px, -1px) rotate(2deg); }
                90% { transform: translate(1px, 2px) rotate(0deg); }
                100% { transform: translate(1px, -2px) rotate(-2deg); }
                }
            </style>
		<?php }
	
		include_once('../header.php');

        $sql = $db->prepare("select summon_pts from save limit 1");
        $sp = $sql->execute()->fetchArray()[0] ?? "";
	
		$db = null;

		echo "<div id='accept' class='w3-center'>";
			echo "<input type='button' value='Summon' onclick='Summon()' />";
            echo "<p id='spind'>SP: " . $sp . "</p>";
		echo "</div>";

        echo "<div class='w3-center'>";
                echo "<p>Cost to Summon - 10 SP</p>";
        echo "</div>";
		
		//echo "<hr />";

		echo "<div id='summon-image' class='w3-center'>";
                echo "<img id='pre-summon' Height='1000' src ='../../imgs/pre-summon.jpg' />";
		echo "</div>";

        echo "<div id='response' class='w3-center'>";
        echo "<div><p>Name: </p></div>";
        echo "<div><p>Rarity: </p></div>";
		echo "</div>";
	}catch(exception $e){

	}	
?>
	</body>
	<script type="text/javascript">
		$(document).ready(function()
		{
            sp = <?php echo $sp; ?>;
			resdiv = document.getElementById("response");	
            presummon = document.getElementById("pre-summon");
            spdisplay = document.getElementById("spind");
		});

		function Summon()
		{
            if(sp - 10 < 0){
                alert("Not Enought SP to Summon");
            }
            else{
                presummon.src='../../imgs/pre-summon.jpg';

                $.ajax({
                    url: 'SummonAjax.php',
                    type: 'get',
                    dataType: 'JSON',
                    success: function(response){
                        console.log(response);
                        presummon.classList.add("summon");                    
                        path = "..\\" + response[2];
                        name = response[0];
                        rarity = response[1];
                        setTimeout(reveal, 3200);
                        stamina = response[3];
                        endurance = response[4];
                        attack = response[5];           			
                    },
                    error: function(xhr, ajaxOptions, thrownError)
                    {
                        console.log(xhr.responseText);
                    }
                });
                
            }
		}

        function reveal(){
            presummon.src=path;

            switch(rarity){
                case 1:
                    rare = "Common";
                    break;
                case 2:
                    rare = "Uncommon";
                    break;
                case 3:
                    rare = "Rare";
                    break;
                case 4:
                    rare = "Super Rare";
                    break;
            }	
            resdiv.innerHTML = "<div><p>Name: " + name + " - Rarity: " + rare + "</p></div>";            
            resdiv.innerHTML += "<div><p>Stamina: " + stamina.toFixed(1) + " - Endurance: " + endurance.toFixed(1) + " - Attack: " + attack.toFixed(1) + "</p></div>";

            sp = sp - 10;
            spdisplay.innerHTML = "SP: " + sp;

            presummon.classList.remove("summon");
        }
		
	</script>
</html>