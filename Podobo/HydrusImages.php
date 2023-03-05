<?php
// Start the session
session_start();
$thumbs_source = "thumbs/";
$columncount = 12;
$rowcount = 6;
$itemcount = $columncount * $rowcount;
$TagColors=array("#FFFFFF", "#6495ED", "#FF4500", "#FF4500", "#FF8C00", "#7FFFD4", "#BA55D3", "#228B22", "#8A2BE2", "#FFFFFF", "#FFFFFF", "#FFFFFF", "#FFFFFF", "#FFFFFF", "#DAA520", "#8FBC8F", "#FFFFFF");
$file_page_data = [];
$tags = [];

//$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
//$db = new SQLite3("Y:\\Database\\nevada.db");
$db = new SQLite3("D:\\Piaz\\Database\\nevada.db");

if(isset($_GET["namespace"])) { $namespace = html_entity_decode($_GET["namespace"]); } else { $namespace = ""; }
if(isset($_GET["tag"])) { $tag = html_entity_decode($_GET["tag"]); } else { $tag = ""; }

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

$rownum = count($files);

$r = rand(0, $rownum);

$PageTitle = "Podobo - Hydrus";
$list_view = true;
	
function customPageHeader(){?>
	<style type="text/css">
		input[type=text] {
		width: 230px;
		}
	</style>
<?php }

include_once('header.php');
?>
					
			<main class="row">

    <?php
    echo "<div class='col-10'><div id='resdiv' class='posts-wrapper'>";	
        
        echo "</div>";
        echo "</div>";
        ?>

    </main>
			
			
		<script type="text/javascript">
			$(document).ready(function()
			{

                $.ajax({
					url: 'https://67.253.187.197:45869/get_files/search_files?file_sort_type=6&file_sort_asc=false&tags=%5B%5B%22' + <?php echo ($namespace != '' ? json_encode(str_replace("_", "%20", $namespace) . '%3A') . "+" : ''); echo json_encode(str_replace("_", "%20", $tag)); ?> + '%22,%22' + <?php echo ($namespace != '' ? json_encode($namespace . '%3A') . "+" : ''); echo json_encode($tag); ?> + '%22%5D%2C%20%22system%3Alimit%3D98%22%5D',//&Hydrus-Client-API-Access-Key=832e84084dab1249084d5f7fc1823ab6e58851c992e733133d4b68d3693492b9',
					type: 'get',
                    headers: {"Hydrus-Client-API-Access-Key" : "832e84084dab1249084d5f7fc1823ab6e58851c992e733133d4b68d3693492b9"},
					dataType: 'JSON',
					success: function(response){	
						console.log(response.file_ids);					
						for (let i = 0; i < response.file_ids.length; i++) {
                            displayFunction(response.file_ids[i]);
                        }
					},
					error: function(xhr, ajaxOptions, thrownError)
					{
						console.log(xhr.responseText);
					}
				});
			});	

            function displayFunction(id){

                resdiv = document.getElementById('resdiv');
                resdiv.innerHTML += "<article class='post-article'><div class='post-preview'><a href='HydrusPost.php?id=" + id + "'><img class='hydrus-thumbs' src='https://67.253.187.197:45869/get_files/thumbnail?file_id=" + id + "&Hydrus-Client-API-Access-Key=832e84084dab1249084d5f7fc1823ab6e58851c992e733133d4b68d3693492b9' /></a></div></article>";
            }
		</script>
	</body>
</html>
