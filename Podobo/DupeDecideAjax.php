<?php
	$db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
	$db->exec('PRAGMA foreign_keys = ON;');
	$db->busyTimeout(100);
	
	if(isset($_GET["id1"])) { $id1 = $_GET["id1"]; } else { $id1 = -1; };
	if(isset($_GET["id2"])) { $id2 = $_GET["id2"]; } else { $id2 = -1; };
	if(isset($_GET["decision"])) { $decision = $_GET["decision"]; } else { $decision = -1; };
	
	try{
		$db->exec('BEGIN;');
		$db->enableExceptions(true);

		if($id1 != -1 and $id2 != -1)
		{
			$sql = $db->prepare("UPDATE Dupes set decided = 1, decision = :decision WHERE id1 = :id1 AND id2 = :id2");
			$sql->bindValue(':id1', $id1, SQLITE3_INTEGER);
			$sql->bindValue(':id2', $id2, SQLITE3_INTEGER);
			$sql->bindValue(':decision', $decision, SQLITE3_INTEGER);
			$sql->execute();
		}

		$db->exec('COMMIT;');

        $sql = $db->prepare("select id from dupes where decided = 0 order by score desc limit 1");
	    $dupeid = $sql->execute()->fetchArray();

		if($dupeid){
            echo json_encode(array(1,$dupeid[0]));
        }
        else{
            
            echo json_encode(array(2,-1));
        }
	}
	catch(Exception $e){
		$output = $db->lastErrorMsg();
		$db->exec('ROLLBACK;');
	}
	
	$db = null;
?>