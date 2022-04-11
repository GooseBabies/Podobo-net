<?php
	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");	
	
	if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = 0; };
	if(isset($_GET["rating"])) { $rating = $_GET["rating"]; } else { $rating = 0; };

	$sql = $db->prepare("update files set overall_rating=:overall_rating, media_rating=:overall_rating, individual_rating=:overall_rating, sexual_rating=:overall_rating where id=:id;");
    $sql->bindValue(":overall_rating", $rating, SQLITE3_FLOAT);
    $sql->bindValue(":id", $id, SQLITE3_INTEGER);
	$sql->execute();
	
	echo json_encode(array($rating, $rating, $rating, $rating));
	
	$db = null;
?>