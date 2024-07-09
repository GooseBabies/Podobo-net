<?php
	//session_start();
	
	//$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	//$db = new SQLite3("Y:\\Database\\nevada.db");	
    $dupefiles = [];
    $sizes = []; 

	$PageTitle = "LavaPool Dupes";
	
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

	$sql = $db->prepare("select * from dupes where id = :id");
    $sql->bindValue(":id", $id, SQLITE3_INTEGER);
	$dupelist = $sql->execute()->fetchArray();

    //print_r($dupelist);

    if($dupelist){
        $dupeids = explode(";", $dupelist[1]);
        //echo $dupelist;
    }
    else{
        $sql = $db->prepare("select * from media where processed = 0 limit 1");
	    $media = $sql->execute()->fetchArray();
        die("<script>location.href = 'LavaDecide.php?id=" . $media[0] . "'</script>");
    }

    $dupeid = $dupelist[0];

    //print_r($dupeids);

    for($q = 0; $q <= count($dupeids) - 1; $q++)
    {
        $sql = $db->prepare("select name, filesize from media where id = :id");
        $sql->bindValue(":id", $dupeids[$q], SQLITE3_TEXT);
        $result = $sql->execute();
        while ($row = $result->fetchArray()) {
            array_push($dupefiles, $row[0]);
            array_push($sizes, $row[1]);
            //echo "cham";
        }
    }

    ?>

	<div id="subheader" class="w3-bar w3-theme-l1 w3-left-align w3-small container_subheader">	
		<a id="back" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>LavaDupes.php?id=<?php echo ($id-1); ?>">Back</a>
		<a id="forward" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>LavaDupes.php?id=<?php echo ($id+1); ?>">Forward</a>
		<a id="booru-tag" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>LavaManualBlacklist.php">Add to Blacklist</a>
		<a id="booru-tag" class="w3-bar-item w3-button w3-theme-l1" href="<?= isset($InTags) ? "../" : ""?>LavaIntro.php">LavaPool</a>                        
	</div>

	<?php

    //print_r($dupefiles);

    echo "<div class='rating-wrapper'>";

    for($r = 0; $r <= count($dupefiles) - 1; $r++)
    {
        echo "<div class='container-footer'>";
        echo "<p><button class='dupe-button' id='button" . $r . "' onclick='ShowImage(" . $r . ")'>" . $r . "</button></p>";
        echo "<div class='w3-center'><input class='check' type='checkbox' autocomplete='off' id='" . $dupeids[$r] . "' value='" . $dupeids[$r] . "'></div>";
        echo "</div>";
    }
    

    echo "</div>";    

    echo "<p class='w3-center' id='desc'></p>";
    echo "<p class='w3-center' id='desc2'></p>";

    echo "<div class='w3-center'><button class='dupe-button' onclick='TrashAll()'>Trash All</button><button class='dupe-button' onclick='Submit()'>Submit</button></div>";

    echo "<hr />";

    echo "<div>";

    for($s = 0; $s <= count($dupefiles) - 1; $s++)
    {
        echo "<div><img class='dupe-fit dupe-images' id='image" . $s . "' src='LavaPool\\Files\\" . rawurlencode($dupefiles[$s]) . "' onclick='markTrash(" . $s . ")' /></div>";
    }

    echo "</div>";    

    $db = null;
?>



</body>
<script type="text/javascript">	
        var files = <?php echo json_encode($dupefiles) ?>;
        var sizes = <?php echo json_encode($sizes) ?>;
        var ids = <?php echo json_encode($dupeids) ?>;
        var id = <?php echo json_encode($dupeid) ?>;
		$(document).ready(function()
		{

		});

        function ShowImage(id){
            HideImages();
            var img = document.getElementById('image' + id);
            var button = document.getElementById('button' + id);
            var desc = document.getElementById('desc');
            var desc2 = document.getElementById('desc2');
            img.style.display = "block";
            button.classList = "dupe-button-pressed";
            desc.innerHTML = files[id];
            
            var width = img.naturalWidth;
            var height = img.naturalHeight;
            desc2.innerHTML = width + " x " + height + " - " + sizes[id];
        }

        function HideImages(){
            var elements = document.getElementsByClassName('dupe-images');
            for (let i = 0; i < elements.length; i++) {
                elements[i].style.display = "none";
            }

            var buttons = document.getElementsByClassName('dupe-button-pressed');
            for (let i = 0; i < buttons.length; i++) {
                buttons[i].classList = "dupe-button";
            }
        }

        function markTrash(id){
            var checks = document.getElementsByClassName('check');
            if(checks[id].checked){
                checks[id].checked = false;
            }
            else{
                checks[id].checked = true;
            }
        }

        function TrashAll(){
            var checks = document.getElementsByClassName('check');
            for (let i = 0; i < checks.length; i++) {
                checks[i].checked = true;
            }
        }

        function Submit(){
            var item = "";
            var checks = document.getElementsByClassName('check');
            for (let i = 0; i < checks.length; i++) {
                if(checks[i].checked){
                    item += ids[i] + "," + 1 + ";";
                }
                else{
                    item += ids[i] + "," + 0 + ";";
                }
            }

            $.ajax({
					url: 'LavaDupesSubmitAjax.php?id=' + id + '&dupe=' + item,
					type: 'get',
					dataType: 'JSON',
					success: function(response){
						//console.log(response);
                        if(response == -1){
                            location.href = 'LavaDupesEnd.php';
                            
                        }
                        else{
                            location.href = 'LavaDupes.php?id=' + response;
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
