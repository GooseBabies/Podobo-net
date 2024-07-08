<?php
    $db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
    $db->exec('PRAGMA foreign_keys = ON;');
    $db->busyTimeout(100);
	
	if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = 0; };
	if(isset($_GET["rating"])) { $rating = $_GET["rating"]; } else { $rating = 0; };

    try{
        $db->exec('BEGIN;');
        $db->enableExceptions(true);

        $sql = $db->prepare("select individual_rating from files where id = :id;");
        $sql->bindValue(":id", $id, SQLITE3_INTEGER);
        $individual_rating = $sql->execute()->fetchArray()[0];

        $sql = $db->prepare("select sexual_rating from files where id = :id;");
        $sql->bindValue(":id", $id, SQLITE3_INTEGER);
        $sexual_rating = $sql->execute()->fetchArray()[0];
        
        $sql = $db->prepare("update files set media_rating=:media_rating where id=:id;");
        $sql->bindValue(":media_rating", $rating, SQLITE3_INTEGER);
        $sql->bindValue(":id", $id, SQLITE3_INTEGER);
        $sql->execute();

        $overall_rating = round($rating * 0.4 + $individual_rating * 0.3 + $sexual_rating * 0.3, 1);

        $sql = $db->prepare("update files set overall_rating=:overall_rating where id=:id;");
        $sql->bindValue(":overall_rating", $overall_rating, SQLITE3_FLOAT);
        $sql->bindValue(":id", $id, SQLITE3_INTEGER);
        $sql->execute();

        $db->exec('COMMIT;');
        
        echo json_encode(array($overall_rating, $rating, $individual_rating, $sexual_rating));
    }
    catch(Exception $e){
        $output = $db->lastErrorMsg();
        $db->exec('ROLLBACK;');
    }
    
	$db = null;
?>