<?php

	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	
	if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = -1; }; 
	
	$sql = $db->prepare("SELECT sources FROM files where ID=:id");
    $sql->bindValue(':id', $id, SQLITE3_INTEGER);
	$sourcestring = $sql->execute()->fetchArray()[0] ?? '';

	$sources = array_filter(explode(" ", $sourcestring));
    
	for($j = 0; $j < count($sources); $j++)
	{
		if($sources[$j] != "" and $sources[$j] != " "){
			echo "<div id='" . json_encode($sources[$j]) . "'>";
			echo "<a href='" . $sources[$j] ."' target='_blank'>" . $sources[$j] . "</a>";
			echo "<input type='Button' value='x' class='rem-button' onclick='RemoveSource(" . json_encode($sources[$j]) . ", " . $id . ")' />";
			echo "</div>";
		}
	}
    
	$db = null;
?>