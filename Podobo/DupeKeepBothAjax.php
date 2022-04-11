<?php
	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	
	if(isset($_GET["hash1"])) { $hash1 = $_GET["hash1"]; } else { $hash1 = ""; };
	if(isset($_GET["hash2"])) { $hash2 = $_GET["hash2"]; } else { $hash2 = ""; };
	
	if($hash1 != "" and $hash2 != "")
	{
		$sql = $db->prepare("UPDATE Dupes set processed = 1 WHERE hash_1 = :hash1 AND hash_2 = :hash2");
		$sql->bindValue(':hash1', $hash1, SQLITE3_TEXT);
        $sql->bindValue(':hash2', $hash2, SQLITE3_TEXT);
		$sql->execute();
	}

    echo json_encode("");
	$db = null;
?>