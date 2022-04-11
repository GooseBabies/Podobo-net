<?php
	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");	
	
	if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = 0; };
	if(isset($_GET["rating"])) { $rating = $_GET["rating"]; } else { $rating = 0; };

    $sql = $db->prepare("select media_rating from files where id = :id;");
    $sql->bindValue(":id", $id, SQLITE3_INTEGER);
    $media_rating = $sql->execute()->fetchArray()[0];

    $sql = $db->prepare("select sexual_rating from files where id = :id;");
    $sql->bindValue(":id", $id, SQLITE3_INTEGER);
    $sexual_rating = $sql->execute()->fetchArray()[0];
	
	$sql = $db->prepare("update files set individual_rating=:individual_rating where id=:id;");
    $sql->bindValue(":individual_rating", $rating, SQLITE3_INTEGER);
    $sql->bindValue(":id", $id, SQLITE3_INTEGER);
	$sql->execute();

    $overall_rating = round($media_rating * 0.4 + $rating * 0.3 + $sexual_rating * 0.3, 1);

    $sql = $db->prepare("update files set overall_rating=:overall_rating where id=:id;");
    $sql->bindValue(":overall_rating", $overall_rating, SQLITE3_FLOAT);
    $sql->bindValue(":id", $id, SQLITE3_INTEGER);
	$sql->execute();
	
	echo json_encode(array($overall_rating, $media_rating, $rating, $sexual_rating));
	
	$db = null;
?>