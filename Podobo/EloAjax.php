<?php
	$db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
	$db->exec('PRAGMA foreign_keys = ON;');
	$db->busyTimeout(100);
	
	if(isset($_GET["result"])) { $result = $_GET["result"]; } else { $result = ''; };

	try{
        $db->exec('BEGIN;');
        $db->enableExceptions(true);

        $results = explode(";", $result);

        $result1 = explode(",", $results[0]);
        $result2 = explode(",", $results[1]);

        $sql = $db->prepare("select elo from files where id = :id");
        $sql->bindValue(":id", $result1[0], SQLITE3_INTEGER);
        $elo1 = $sql->execute()->fetchArray()[0];

        $sql = $db->prepare("select elo from files where id = :id");
        $sql->bindValue(":id", $result2[0], SQLITE3_INTEGER);
        $elo2 = $sql->execute()->fetchArray()[0];

        if($elo1 < 2100){
            $k1 = 32;
        }
        elseif($elo1 < 2400 and $elo1 >= 2100){
            $k1 = 24;
        }
        else{
            $k1 = 16;
        }

        if($elo2 < 2100){
            $k2 = 32;
        }
        elseif($elo2 < 2400 and $elo2 >= 2100){
            $k2 = 24;
        }
        else{
            $k2 = 16;
        }

        $E1 = 1/(1+10**(($elo2 - $elo1)/400));
        $E2 = 1/(1+10**(($elo1 - $elo2)/400));

        $new_elo1 = $elo1 + $k1*($result1[1] - $E1);
        $new_elo2 = $elo2 + $k2*($result2[1] - $E2);

		$sql = $db->prepare("update files set elo = :elo where id = :id;");
		$sql->bindValue(":elo", $new_elo1, SQLITE3_INTEGER);
		$sql->bindValue(":id", $result1[0], SQLITE3_INTEGER);
		$sql->execute();

        $sql = $db->prepare("update files set elo = :elo where id = :id;");
		$sql->bindValue(":elo", $new_elo2, SQLITE3_INTEGER);
		$sql->bindValue(":id", $result2[0], SQLITE3_INTEGER);
		$sql->execute();

		$db->exec('COMMIT;');
		
		$output = "Sucess";
    }
    catch(Exception $e){
		$output = $db->lastErrorMsg();
        $db->exec('ROLLBACK;');
    }

    echo json_encode($output);
	
	$db = null;
?>